<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'student')->with('enrollments');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('enrollments', function($q) {
                    $q->where('status', 'approved')->where('access_expires_at', '>', now());
                });
            } elseif ($request->status === 'inactive') {
                $query->whereDoesntHave('enrollments', function($q) {
                    $q->where('status', 'approved')->where('access_expires_at', '>', now());
                });
            }
        }

        $students = $query->paginate(12);

        // Add computed properties and get phone from enrollment if not in user record
        $students->getCollection()->transform(function ($student) {
            $activeEnrollment = $student->enrollments->where('status', 'approved')
                ->where('access_expires_at', '>', now())
                ->first();
            
            // Get latest enrollment if no active enrollment exists
            $latestEnrollment = $student->enrollments->sortByDesc('created_at')->first();
            
            $student->is_active = (bool) $activeEnrollment;
            $student->enrollment_status = $activeEnrollment ? 'Active' : 'Inactive';
            $student->subjects_count = $activeEnrollment && $activeEnrollment->selected_subjects 
                ? count($activeEnrollment->selected_subjects) : 0;
            $student->access_expires_at = $activeEnrollment?->access_expires_at;
            
            // Get phone from enrollment if not in user record
            if (!$student->phone && $latestEnrollment && $latestEnrollment->phone) {
                $student->phone = $latestEnrollment->phone;
            }
            
            return $student;
        });

        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'active_students' => User::where('role', 'student')
                ->whereHas('enrollments', function($q) {
                    $q->where('status', 'approved')->where('access_expires_at', '>', now());
                })->count(),
            'new_this_month' => User::where('role', 'student')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count()
        ];

        return Inertia::render('Students/Index', [
            'students' => $students,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Students/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'is_active' => true,
        ]);

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    public function show(User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }

        $user->load([
            'enrollments', 
            'payments' => function($q) {
                $q->orderBy('created_at', 'desc');
            }
        ]);

        $activeEnrollment = $user->enrollments->where('status', 'approved')
            ->where('access_expires_at', '>', now())
            ->first();

        return Inertia::render('Students/Show', [
            'student' => $user,
            'activeEnrollment' => $activeEnrollment,
        ]);
    }

    public function edit(User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }

        return Inertia::render('Students/Edit', [
            'student' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'is_active']));

        return redirect()->route('students.show', $user)->with('success', 'Student updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'student') {
            abort(404);
        }

        $user->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }
}