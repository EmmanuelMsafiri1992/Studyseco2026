<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LibraryController extends Controller
{
    /**
     * Display library management dashboard
     */
    public function index(Request $request)
    {
        $query = Resource::with('subject');
        
        // Apply filters
        if ($request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->subject) {
            $query->where('subject_id', $request->subject);
        }
        
        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        
        $resources = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get filter options
        $subjects = Subject::where('is_active', true)->get();
        $types = ['book', 'past_paper', 'document', 'video', 'audio', 'presentation'];
        $categories = Resource::distinct()->pluck('category')->filter()->values()->all();
        $grades = Resource::distinct()->pluck('grade_level')->filter()->values()->all();
        $years = Resource::distinct()->pluck('year')->filter()->values()->all();
        
        // Get statistics
        $stats = [
            'total_resources' => Resource::count(),
            'active_resources' => Resource::where('is_active', true)->count(),
            'total_books' => Resource::where('type', 'book')->count(),
            'total_papers' => Resource::where('type', 'past_paper')->count(),
            'total_documents' => Resource::where('type', 'document')->count(),
            'total_views' => Resource::sum('view_count'),
        ];
        
        return Inertia::render('Admin/Library/Index', [
            'resources' => $resources,
            'subjects' => $subjects,
            'types' => $types,
            'categories' => $categories,
            'grades' => $grades,
            'years' => $years,
            'stats' => $stats,
            'filters' => $request->only(['type', 'subject', 'status', 'search'])
        ]);
    }

    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        $subjects = Subject::where('is_active', true)->get();
        $types = [
            'book' => 'Book/Textbook',
            'past_paper' => 'Past Paper',
            'document' => 'Document',
            'video' => 'Video',
            'audio' => 'Audio',
            'presentation' => 'Presentation'
        ];
        $categories = Resource::distinct()->pluck('category')->filter()->values();
        $gradelevels = range(1, 12);
        $examBoards = ['MANEB', 'Cambridge', 'IB', 'Other'];
        
        return Inertia::render('Admin/Library/Create', [
            'subjects' => $subjects,
            'types' => $types,
            'categories' => $categories,
            'gradeLevels' => $gradelevels,
            'examBoards' => $examBoards
        ]);
    }

    /**
     * Store a newly created resource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:book,past_paper,document,video,audio,presentation',
            'category' => 'nullable|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_level' => 'nullable|integer|min:1|max:12',
            'exam_board' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'file' => 'nullable|file|max:1048576', // 1GB max
            'thumbnail' => 'nullable|image|max:2048', // 2MB max for images
            'access_permissions' => 'nullable|array',
            'is_protected' => 'boolean',
            'protection_settings' => 'nullable|array',
        ]);

        $filePath = null;
        $fileType = null;
        $fileSize = null;
        $thumbnailPath = null;

        // Handle main file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('library-resources', 'public');
            $fileType = $file->getMimeType();
            $fileSize = $file->getSize();
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail');
            $thumbnailPath = $thumbnail->store('library-thumbnails', 'public');
        }

        $resource = Resource::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'subject_id' => $validated['subject_id'],
            'grade_level' => $validated['grade_level'],
            'exam_board' => $validated['exam_board'],
            'year' => $validated['year'],
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'thumbnail' => $thumbnailPath,
            'access_permissions' => $validated['access_permissions'],
            'is_active' => true,
            'is_protected' => $validated['is_protected'] ?? false,
            'protection_settings' => $validated['protection_settings'],
        ]);

        return redirect()->route('admin.library.index')
            ->with('success', 'Resource created successfully!');
    }

    /**
     * Display the specified resource
     */
    public function show(Resource $resource)
    {
        $resource->load('subject');
        
        return Inertia::render('Admin/Library/Show', [
            'resource' => $resource
        ]);
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(Resource $resource)
    {
        $subjects = Subject::where('is_active', true)->get();
        $types = [
            'book' => 'Book/Textbook',
            'past_paper' => 'Past Paper',
            'document' => 'Document',
            'video' => 'Video',
            'audio' => 'Audio',
            'presentation' => 'Presentation'
        ];
        $categories = Resource::distinct()->pluck('category')->filter()->values();
        $gradeLevels = range(1, 12);
        $examBoards = ['MANEB', 'Cambridge', 'IB', 'Other'];
        
        return Inertia::render('Admin/Library/Edit', [
            'resource' => $resource->load('subject'),
            'subjects' => $subjects,
            'types' => $types,
            'categories' => $categories,
            'gradeLevels' => $gradeLevels,
            'examBoards' => $examBoards
        ]);
    }

    /**
     * Update the specified resource
     */
    public function update(Request $request, Resource $resource)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|in:book,past_paper,document,video,audio,presentation',
            'category' => 'nullable|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
            'grade_level' => 'nullable|integer|min:1|max:12',
            'exam_board' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'file' => 'nullable|file|max:1048576', // 1GB max
            'thumbnail' => 'nullable|image|max:2048',
            'access_permissions' => 'nullable|array',
            'is_protected' => 'boolean',
            'protection_settings' => 'nullable|array',
        ]);

        // Handle file replacement
        if ($request->hasFile('file')) {
            // Delete old file
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            
            $file = $request->file('file');
            $validated['file_path'] = $file->store('library-resources', 'public');
            $validated['file_type'] = $file->getMimeType();
            $validated['file_size'] = $file->getSize();
        }

        // Handle thumbnail replacement
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($resource->thumbnail) {
                Storage::disk('public')->delete($resource->thumbnail);
            }
            
            $thumbnail = $request->file('thumbnail');
            $validated['thumbnail'] = $thumbnail->store('library-thumbnails', 'public');
        }

        $resource->update($validated);

        return redirect()->route('admin.library.index')
            ->with('success', 'Resource updated successfully!');
    }

    /**
     * Remove the specified resource
     */
    public function destroy(Resource $resource)
    {
        // Delete associated files
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }
        
        if ($resource->thumbnail) {
            Storage::disk('public')->delete($resource->thumbnail);
        }
        
        $resource->delete();

        return redirect()->route('admin.library.index')
            ->with('success', 'Resource deleted successfully!');
    }

    /**
     * Toggle resource active status
     */
    public function toggle(Resource $resource)
    {
        $resource->update([
            'is_active' => !$resource->is_active
        ]);

        $status = $resource->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Resource {$status} successfully!");
    }

    /**
     * Handle bulk upload
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:1048576',
            'subject_id' => 'nullable|exists:subjects,id',
            'type' => 'required|in:book,past_paper,document,video,audio,presentation',
            'category' => 'nullable|string|max:100',
            'grade_level' => 'nullable|integer|min:1|max:12',
        ]);

        $uploadedCount = 0;
        
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePath = $file->store('library-resources', 'public');
                $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                
                Resource::create([
                    'title' => $title,
                    'type' => $request->type,
                    'category' => $request->category,
                    'subject_id' => $request->subject_id,
                    'grade_level' => $request->grade_level,
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'is_active' => true,
                ]);
                
                $uploadedCount++;
            }
        }

        return back()->with('success', "Successfully uploaded {$uploadedCount} resources!");
    }

    /**
     * Handle bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'resource_ids' => 'required|array',
            'resource_ids.*' => 'exists:resources,id'
        ]);

        $resources = Resource::whereIn('id', $validated['resource_ids']);

        switch ($validated['action']) {
            case 'activate':
                $resources->update(['is_active' => true]);
                $message = 'Resources activated successfully!';
                break;
                
            case 'deactivate':
                $resources->update(['is_active' => false]);
                $message = 'Resources deactivated successfully!';
                break;
                
            case 'delete':
                // Delete associated files
                foreach ($resources->get() as $resource) {
                    if ($resource->file_path) {
                        Storage::disk('public')->delete($resource->file_path);
                    }
                    if ($resource->thumbnail) {
                        Storage::disk('public')->delete($resource->thumbnail);
                    }
                }
                $resources->delete();
                $message = 'Resources deleted successfully!';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Manage categories
     */
    public function manageCategories()
    {
        $categories = Resource::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->map(function ($category) {
                return [
                    'name' => $category,
                    'count' => Resource::where('category', $category)->count()
                ];
            });

        return Inertia::render('Admin/Library/Categories', [
            'categories' => $categories
        ]);
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:resources,category'
        ]);

        // Create a dummy resource with the category to register it
        // In a real app, you might have a separate categories table
        return back()->with('success', 'Category will be available when first resource is created with it.');
    }

    /**
     * Delete category
     */
    public function destroyCategory($category)
    {
        Resource::where('category', $category)->update(['category' => null]);
        
        return back()->with('success', 'Category removed from all resources.');
    }
}