<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // For admins and teachers, show all subjects
        if (in_array($user->role, ['admin', 'teacher'])) {
            $subjects = Subject::with(['topics.lessons'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            // For students, show only enrolled subjects
            $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
                ->where('status', 'approved')
                ->first();
                
            if (!$enrollment || !$enrollment->selected_subjects) {
                // No enrollment or no selected subjects - show empty array
                $subjects = collect([]);
            } else {
                // Show only selected subjects
                $subjects = Subject::with(['topics.lessons'])
                    ->where('is_active', true)
                    ->whereIn('id', $enrollment->selected_subjects)
                    ->orderBy('name')
                    ->get();
            }
        }

        $subjects = $subjects->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'description' => $subject->description,
                'department' => $subject->department,
                'grade_level' => $subject->grade_level,
                'credits' => $subject->credits,
                'teacher_name' => $subject->teacher_name,
                'cover_image' => $subject->cover_image,
                'topic_count' => $subject->topics->count(),
                'lesson_count' => $subject->lesson_count,
                'total_duration' => $subject->total_duration,
            ];
        });

        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects,
        ]);
    }

    public function create()
    {
        $departments = \App\Models\Department::active()->ordered()->get();
        
        return Inertia::render('Subjects/Create', [
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subjects,code',
            'description' => 'nullable|string',
            'department' => 'required|string|max:100',
            'grade_level' => 'required|string|max:50',
            'credits' => 'required|integer|min:1|max:10',
            'teacher_name' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('subjects/covers', 'public');
        }

        Subject::create($validated);

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully!');
    }

    public function show(Subject $subject)
    {
        $user = auth()->user();
        
        // Check if user has access to this subject
        if (!in_array($user->role, ['admin', 'teacher'])) {
            $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
                ->where('status', 'approved')
                ->first();
                
            if (!$enrollment || !$enrollment->selected_subjects || !in_array($subject->id, $enrollment->selected_subjects)) {
                abort(403, 'You do not have access to this subject. Please check your enrollment.');
            }
        }

        $subject->load([
            'topics' => function ($query) {
                $query->where('is_active', true)->orderBy('order_index');
            },
            'topics.lessons' => function ($query) {
                $query->where('is_published', true)->orderBy('order_index');
            }
        ]);

        return Inertia::render('Subjects/SubjectPage/SubjectPage', [
            'subject' => $subject,
        ]);
    }

    public function edit(Subject $subject)
    {
        $departments = \App\Models\Department::active()->ordered()->get();
        
        return Inertia::render('Subjects/Edit', [
            'subject' => $subject,
            'departments' => $departments
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'description' => 'nullable|string',
            'department' => 'required|string|max:100',
            'grade_level' => 'required|string|max:50',
            'credits' => 'required|integer|min:1|max:10',
            'teacher_name' => 'nullable|string|max:255',
            'cover_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($subject->cover_image) {
                Storage::disk('public')->delete($subject->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('subjects/covers', 'public');
        }

        $subject->update($validated);

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully!');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->cover_image) {
            Storage::disk('public')->delete($subject->cover_image);
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully!');
    }
}
