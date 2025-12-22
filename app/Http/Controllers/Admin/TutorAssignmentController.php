<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TutorAssignmentController extends Controller
{
    public function index()
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can manage tutor assignments.');
        }

        // Get all enrollments with their assigned tutors
        $enrollments = Enrollment::with(['user', 'assignedTutor'])
            ->where('status', 'approved')
            ->paginate(20);

        // Get available tutors
        $tutors = User::where('role', 'teacher')
            ->where('is_active', true)
            ->get(['id', 'name', 'email']);

        // Get assignment statistics
        $stats = [
            'total_students' => Enrollment::where('status', 'approved')->count(),
            'assigned_students' => Enrollment::where('status', 'approved')
                ->whereNotNull('assigned_tutor_id')->count(),
            'unassigned_students' => Enrollment::where('status', 'approved')
                ->whereNull('assigned_tutor_id')->count(),
            'total_tutors' => User::where('role', 'teacher')->where('is_active', true)->count(),
        ];

        return Inertia::render('Admin/TutorAssignments/Index', [
            'enrollments' => $enrollments,
            'tutors' => $tutors,
            'stats' => $stats
        ]);
    }

    public function assign(Request $request)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can assign tutors.');
        }

        $request->validate([
            'enrollment_id' => 'required|integer|exists:enrollments,id',
            'tutor_id' => 'required|integer|exists:users,id'
        ]);

        $enrollment = Enrollment::findOrFail($request->enrollment_id);
        $tutor = User::where('id', $request->tutor_id)
            ->where('role', 'teacher')
            ->firstOrFail();

        // Update enrollment with assigned tutor
        $enrollment->update([
            'assigned_tutor_id' => $tutor->id,
            'tutor_assigned_at' => now(),
            'tutor_assigned_by' => auth()->id()
        ]);

        // Log the assignment
        AuditService::log(
            'tutor_assigned',
            $enrollment,
            null,
            ['tutor_id' => $tutor->id, 'tutor_name' => $tutor->name],
            "Tutor {$tutor->name} assigned to student {$enrollment->name}"
        );

        return back()->with('success', "Tutor {$tutor->name} successfully assigned to {$enrollment->name}.");
    }

    public function unassign(Request $request)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can unassign tutors.');
        }

        $request->validate([
            'enrollment_id' => 'required|integer|exists:enrollments,id'
        ]);

        $enrollment = Enrollment::findOrFail($request->enrollment_id);
        $previousTutor = $enrollment->assignedTutor;

        // Update enrollment to remove assigned tutor
        $enrollment->update([
            'assigned_tutor_id' => null,
            'tutor_assigned_at' => null,
            'tutor_assigned_by' => null
        ]);

        // Log the unassignment
        AuditService::log(
            'tutor_unassigned',
            $enrollment,
            ['tutor_id' => $previousTutor?->id, 'tutor_name' => $previousTutor?->name],
            null,
            "Tutor unassigned from student {$enrollment->name}"
        );

        return back()->with('success', "Tutor successfully unassigned from {$enrollment->name}.");
    }

    public function bulkAssign(Request $request)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can perform bulk assignments.');
        }

        $request->validate([
            'enrollment_ids' => 'required|array|min:1',
            'enrollment_ids.*' => 'integer|exists:enrollments,id',
            'tutor_id' => 'required|integer|exists:users,id'
        ]);

        $tutor = User::where('id', $request->tutor_id)
            ->where('role', 'teacher')
            ->firstOrFail();

        $enrollments = Enrollment::whereIn('id', $request->enrollment_ids)->get();
        $assignedCount = 0;

        foreach ($enrollments as $enrollment) {
            $enrollment->update([
                'assigned_tutor_id' => $tutor->id,
                'tutor_assigned_at' => now(),
                'tutor_assigned_by' => auth()->id()
            ]);

            // Log each assignment
            AuditService::log(
                'tutor_assigned',
                $enrollment,
                null,
                ['tutor_id' => $tutor->id, 'tutor_name' => $tutor->name],
                "Tutor {$tutor->name} bulk-assigned to student {$enrollment->name}"
            );

            $assignedCount++;
        }

        return back()->with('success', "Successfully assigned tutor {$tutor->name} to {$assignedCount} students.");
    }

    public function getTutorStats(User $tutor)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        $assignments = Enrollment::where('assigned_tutor_id', $tutor->id)
            ->where('status', 'approved')
            ->with('user')
            ->get();

        $stats = [
            'total_assigned' => $assignments->count(),
            'active_students' => $assignments->where('access_expires_at', '>', now())->count(),
            'trial_students' => $assignments->where('is_trial', true)->count(),
            'recent_assignments' => $assignments->where('tutor_assigned_at', '>=', now()->subDays(7))->count()
        ];

        return response()->json([
            'tutor' => $tutor,
            'stats' => $stats,
            'students' => $assignments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'name' => $enrollment->user ? $enrollment->user->name : $enrollment->name,
                    'email' => $enrollment->email,
                    'subjects_count' => count($enrollment->selected_subjects ?: []),
                    'access_expires_at' => $enrollment->access_expires_at,
                    'is_trial' => $enrollment->is_trial,
                    'assigned_at' => $enrollment->tutor_assigned_at
                ];
            })
        ]);
    }
}