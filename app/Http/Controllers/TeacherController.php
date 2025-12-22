<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'teacher');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(12);

        $stats = [
            'total_teachers' => User::where('role', 'teacher')->count(),
            'active_teachers' => User::where('role', 'teacher')->where('is_active', true)->count(),
            'new_this_month' => User::where('role', 'teacher')
                ->where('created_at', '>=', now()->startOfMonth())
                ->count()
        ];

        return Inertia::render('Teachers/Index', [
            'teachers' => $teachers,
            'stats' => $stats,
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Teachers/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
    }

    public function show(User $user)
    {
        if ($user->role !== 'teacher') {
            abort(404);
        }

        return Inertia::render('Teachers/Show', [
            'teacher' => $user
        ]);
    }

    public function edit(User $user)
    {
        if ($user->role !== 'teacher') {
            abort(404);
        }

        return Inertia::render('Teachers/Edit', [
            'teacher' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== 'teacher') {
            abort(404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'is_active']));

        return redirect()->route('teachers.show', $user)->with('success', 'Teacher updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->role !== 'teacher') {
            abort(404);
        }

        $user->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}