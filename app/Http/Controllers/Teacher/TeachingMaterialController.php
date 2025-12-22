<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeachingMaterial;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TeachingMaterialController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get subjects for filtering and selection
        $subjects = Subject::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'department' => $subject->department
                ];
            });

        // Get teacher's materials
        $materials = TeachingMaterial::byTeacher($user->id)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($material) {
                return [
                    'id' => $material->id,
                    'title' => $material->title,
                    'description' => $material->description,
                    'subject' => $material->subject ? $material->subject->name : 'Unknown',
                    'subject_id' => $material->subject_id,
                    'material_type' => $material->material_type,
                    'grade_level' => $material->grade_level,
                    'file_name' => $material->file_name,
                    'file_size' => $material->formatted_file_size,
                    'file_extension' => $material->file_extension,
                    'download_count' => $material->download_count,
                    'view_count' => $material->view_count,
                    'is_public' => $material->is_public,
                    'is_active' => $material->is_active,
                    'created_at' => $material->created_at,
                    'icon' => $material->file_type_icon,
                    'color' => $material->file_type_color,
                ];
            });

        // Get upload stats
        $uploadStats = [
            'total' => TeachingMaterial::byTeacher($user->id)->count(),
            'this_month' => TeachingMaterial::byTeacher($user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_downloads' => TeachingMaterial::byTeacher($user->id)->sum('download_count'),
            'total_views' => TeachingMaterial::byTeacher($user->id)->sum('view_count'),
        ];

        return Inertia::render('Teacher/Materials/Index', [
            'materials' => $materials,
            'subjects' => $subjects,
            'uploadStats' => $uploadStats,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'material_type' => 'required|string|in:lesson_plan,worksheet,presentation,reference,assignment,video,audio',
            'description' => 'nullable|string',
            'grade_level' => 'nullable|string|max:100',
            'duration' => 'nullable|integer|min:1|max:999',
            'tags' => 'nullable|string',
            'is_public' => 'boolean',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,mp4,mov,avi,mp3,wav,jpg,jpeg,png|max:51200', // 50MB
        ]);

        // Store the file
        $file = $request->file('file');
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9\-\.]/', '_', $file->getClientOriginalName());
        $filePath = $file->storeAs('teaching-materials', $fileName, 'public');

        // Parse tags
        $tags = null;
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
        }

        // Create teaching material record
        $material = TeachingMaterial::create([
            'teacher_id' => $user->id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'material_type' => $request->material_type,
            'grade_level' => $request->grade_level,
            'duration' => $request->duration,
            'tags' => $tags,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_extension' => $file->getClientOriginalExtension(),
            'is_public' => $request->boolean('is_public'),
            'is_active' => true,
        ]);

        return redirect()->route('teacher.materials.index')
            ->with('success', 'Teaching material uploaded successfully!');
    }

    public function show(TeachingMaterial $material)
    {
        // Check if user can view this material
        $user = auth()->user();
        if ($material->teacher_id !== $user->id && !$material->is_public) {
            abort(403, 'You do not have permission to view this material.');
        }

        $material->load('subject', 'teacher');
        $material->incrementViewCount();

        return Inertia::render('Teacher/Materials/Show', [
            'material' => $material,
        ]);
    }

    public function download(TeachingMaterial $material)
    {
        // Check if user can download this material
        $user = auth()->user();
        if ($material->teacher_id !== $user->id && !$material->is_public) {
            abort(403, 'You do not have permission to download this material.');
        }

        $material->incrementDownloadCount();

        $filePath = storage_path('app/public/' . $material->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $material->file_name);
    }

    public function update(Request $request, TeachingMaterial $material)
    {
        // Check ownership
        if ($material->teacher_id !== auth()->id()) {
            abort(403, 'You do not have permission to edit this material.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'material_type' => 'required|string|in:lesson_plan,worksheet,presentation,reference,assignment,video,audio',
            'description' => 'nullable|string',
            'grade_level' => 'nullable|string|max:100',
            'duration' => 'nullable|integer|min:1|max:999',
            'tags' => 'nullable|string',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Parse tags
        $tags = null;
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
        }

        $material->update([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'material_type' => $request->material_type,
            'grade_level' => $request->grade_level,
            'duration' => $request->duration,
            'tags' => $tags,
            'is_public' => $request->boolean('is_public'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('teacher.materials.index')
            ->with('success', 'Teaching material updated successfully!');
    }

    public function destroy(TeachingMaterial $material)
    {
        // Check ownership
        if ($material->teacher_id !== auth()->id()) {
            abort(403, 'You do not have permission to delete this material.');
        }

        // Delete the file from storage
        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('teacher.materials.index')
            ->with('success', 'Teaching material deleted successfully!');
    }
}