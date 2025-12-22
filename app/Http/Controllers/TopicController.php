<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
        ]);

        if (!isset($validated['order_index'])) {
            $validated['order_index'] = Topic::where('subject_id', $validated['subject_id'])->count();
        }

        $topic = Topic::create($validated);

        return response()->json([
            'success' => true,
            'topic' => $topic->load('lessons'),
            'message' => 'Topic created successfully!'
        ]);
    }

    public function show(Topic $topic)
    {
        $topic->load(['lessons' => function ($query) {
            $query->where('is_published', true)->orderBy('order_index');
        }, 'subject']);

        return response()->json($topic);
    }

    public function update(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $topic->update($validated);

        return response()->json([
            'success' => true,
            'topic' => $topic->fresh()->load('lessons'),
            'message' => 'Topic updated successfully!'
        ]);
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();

        return response()->json([
            'success' => true,
            'message' => 'Topic deleted successfully!'
        ]);
    }

    public function reorder(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'order_index' => 'required|integer|min:0',
        ]);

        $topic->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Topic order updated successfully!'
        ]);
    }
}
