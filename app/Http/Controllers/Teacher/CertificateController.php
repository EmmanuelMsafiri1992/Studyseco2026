<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * Display a listing of certificates issued by the teacher.
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['student', 'subject'])
            ->where('teacher_id', auth()->id());

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by certificate type
        if ($request->filled('certificate_type')) {
            $query->where('certificate_type', $request->certificate_type);
        }

        // Search by student name
        if ($request->filled('search')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $certificates = $query->latest('issued_at')->paginate(15);

        // Get all subjects for filter dropdown
        $subjects = Subject::all();

        return Inertia::render('Teacher/Certificates/Index', [
            'certificates' => $certificates,
            'subjects' => $subjects,
            'filters' => $request->only(['subject_id', 'certificate_type', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new certificate.
     */
    public function create()
    {
        // Get all subjects
        $subjects = Subject::all();

        // Get all students
        $students = User::where('role', 'student')->get();

        return Inertia::render('Teacher/Certificates/Create', [
            'subjects' => $subjects,
            'students' => $students,
        ]);
    }

    /**
     * Store a newly created certificate in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'certificate_type' => 'required|in:completion,achievement,excellence',
        ]);

        $validated['teacher_id'] = auth()->id();
        $validated['issued_at'] = now();

        $certificate = Certificate::create($validated);

        // Generate PDF
        $this->generatePDF($certificate);

        return redirect()->route('teacher.certificates.index')
            ->with('success', 'Certificate issued successfully!');
    }

    /**
     * Display the specified certificate.
     */
    public function show(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        $certificate->load(['student', 'teacher', 'subject']);

        return Inertia::render('Teacher/Certificates/Show', [
            'certificate' => $certificate,
        ]);
    }

    /**
     * Remove the specified certificate from storage.
     */
    public function destroy(Certificate $certificate)
    {
        $this->authorize('delete', $certificate);

        // Delete PDF file if exists
        if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }

        $certificate->delete();

        return redirect()->route('teacher.certificates.index')
            ->with('success', 'Certificate deleted successfully!');
    }

    /**
     * Download the certificate PDF.
     */
    public function download(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            $this->generatePDF($certificate);
        }

        return Storage::disk('public')->download($certificate->pdf_path);
    }

    /**
     * Get students for a specific subject.
     */
    public function getStudents(Subject $subject)
    {
        // Get all students enrolled in this subject
        $students = User::where('role', 'student')
            ->whereHas('enrollments', function($q) use ($subject) {
                $q->where('status', 'approved')
                  ->whereJsonContains('selected_subjects', $subject->id);
            })
            ->get();

        return response()->json($students);
    }

    /**
     * Generate PDF for certificate.
     */
    protected function generatePDF(Certificate $certificate)
    {
        $certificate->load(['student', 'teacher', 'subject']);

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $certificate,
        ]);

        $filename = 'certificates/' . $certificate->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        $certificate->update(['pdf_path' => $filename]);
    }
}
