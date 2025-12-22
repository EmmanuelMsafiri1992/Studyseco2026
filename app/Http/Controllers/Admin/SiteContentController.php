<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use App\Models\StudentStory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteContentController extends Controller
{
    public function __construct()
    {
        // Admin check will be handled by route middleware or within methods
    }

    /**
     * Display site content management dashboard
     */
    public function index()
    {
        // Check if user is admin
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators can manage site content.');
        }

        $contents = SiteContent::orderBy('sort_order')->get();
        $studentStories = StudentStory::orderBy('sort_order')->get();

        return Inertia::render('Admin/SiteContent/Index', [
            'contents' => $contents,
            'studentStories' => $studentStories,
        ]);
    }

    /**
     * Store new site content
     */
    public function storeContent(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:site_contents,key',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_data' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = SiteContent::max('sort_order') + 1;

        SiteContent::create($validated);

        return back()->with('success', 'Content created successfully.');
    }

    /**
     * Update site content
     */
    public function updateContent(Request $request, SiteContent $content)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:site_contents,key,' . $content->id,
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_data' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $content->update($validated);

        return back()->with('success', 'Content updated successfully.');
    }

    /**
     * Delete site content
     */
    public function destroyContent(SiteContent $content)
    {
        $content->delete();
        return back()->with('success', 'Content deleted successfully.');
    }

    /**
     * Store new student story
     */
    public function storeStudentStory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'country_flag' => 'required|string|max:10',
            'current_status' => 'required|string',
            'story' => 'required|string',
            'achievements' => 'nullable|array',
            'msce_credits' => 'nullable|integer|min:0|max:10',
            'avatar_color_from' => 'required|string|max:50',
            'avatar_color_to' => 'required|string|max:50',
            'achievement_icon' => 'required|string|max:10',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['sort_order'] = StudentStory::max('sort_order') + 1;

        StudentStory::create($validated);

        return back()->with('success', 'Student story created successfully.');
    }

    /**
     * Update student story
     */
    public function updateStudentStory(Request $request, StudentStory $story)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'country_flag' => 'required|string|max:10',
            'current_status' => 'required|string',
            'story' => 'required|string',
            'achievements' => 'nullable|array',
            'msce_credits' => 'nullable|integer|min:0|max:10',
            'avatar_color_from' => 'required|string|max:50',
            'avatar_color_to' => 'required|string|max:50',
            'achievement_icon' => 'required|string|max:10',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $story->update($validated);

        return back()->with('success', 'Student story updated successfully.');
    }

    /**
     * Delete student story
     */
    public function destroyStudentStory(StudentStory $story)
    {
        $story->delete();
        return back()->with('success', 'Student story deleted successfully.');
    }

    /**
     * Update content order
     */
    public function updateContentOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:site_contents,id'
        ]);

        foreach ($validated['order'] as $index => $id) {
            SiteContent::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Content order updated.');
    }

    /**
     * Update student stories order
     */
    public function updateStoryOrder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:student_stories,id'
        ]);

        foreach ($validated['order'] as $index => $id) {
            StudentStory::where('id', $id)->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Story order updated.');
    }
}