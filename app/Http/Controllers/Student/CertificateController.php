<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * Display a listing of the student's certificates.
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['teacher', 'subject'])
            ->where('student_id', auth()->id());

        // Filter by certificate type
        if ($request->filled('certificate_type')) {
            $query->where('certificate_type', $request->certificate_type);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $certificates = $query->latest('issued_at')->paginate(12);

        return Inertia::render('Student/Certificates/Index', [
            'certificates' => $certificates,
            'filters' => $request->only(['certificate_type', 'subject_id']),
        ]);
    }

    /**
     * Display the specified certificate.
     */
    public function show(Certificate $certificate)
    {
        // Ensure student can only view their own certificates
        if ($certificate->student_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $certificate->load(['teacher', 'subject']);

        return Inertia::render('Student/Certificates/Show', [
            'certificate' => $certificate,
        ]);
    }

    /**
     * Download the certificate PDF.
     */
    public function download(Certificate $certificate)
    {
        // Ensure student can only download their own certificates
        if ($certificate->student_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            abort(404, 'Certificate PDF not found.');
        }

        return Storage::disk('public')->download(
            $certificate->pdf_path,
            $certificate->title . '.pdf'
        );
    }
}
