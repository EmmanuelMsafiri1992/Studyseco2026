<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Topic;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use getID3;

class LessonController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,mkv|max:1048576', // 1GB max for regular uploads
            'video_path' => 'nullable|string', // For chunked uploads
            'video_filename' => 'nullable|string', // For chunked uploads
            'duration_minutes' => 'nullable|integer|min:1', // For chunked uploads
            'order_index' => 'nullable|integer|min:0',
        ]);

        if (!isset($validated['order_index'])) {
            $validated['order_index'] = Lesson::where('topic_id', $validated['topic_id'])->count();
        }

        $lesson = new Lesson($validated);

        // Handle traditional file upload
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $videoPath = $video->store('lessons/videos', 'public');
            
            $lesson->video_path = $videoPath;
            $lesson->video_filename = $video->getClientOriginalName();

            // Try to get video duration
            try {
                $fullPath = storage_path('app/public/' . $videoPath);
                if (class_exists('getID3')) {
                    $getID3 = new getID3();
                    $fileInfo = $getID3->analyze($fullPath);
                    if (isset($fileInfo['playtime_seconds'])) {
                        $lesson->duration_minutes = ceil($fileInfo['playtime_seconds'] / 60);
                    }
                }
            } catch (\Exception $e) {
                // Continue without duration if there's an error
            }
        }
        // Handle chunked upload (video already processed and stored)
        elseif ($request->has('video_path')) {
            $lesson->video_path = $validated['video_path'];
            $lesson->video_filename = $validated['video_filename'] ?? 'uploaded_video.mp4';
            
            if (isset($validated['duration_minutes'])) {
                $lesson->duration_minutes = $validated['duration_minutes'];
            }
        }

        $lesson->save();

        return response()->json([
            'success' => true,
            'lesson' => $lesson,
            'message' => 'Lesson created successfully!'
        ]);
    }

    public function show(Lesson $lesson)
    {
        $lesson->load([
            'topic.subject.topics.lessons' => function ($query) {
                $query->where('is_published', true)->orderBy('order_index');
            }
        ]);

        // Get subject with all topics and lessons for navigation
        $subject = $lesson->topic->subject;
        $subject->load([
            'topics' => function ($query) {
                $query->where('is_active', true)->orderBy('order_index');
            },
            'topics.lessons' => function ($query) {
                $query->where('is_published', true)->orderBy('order_index');
            }
        ]);

        return Inertia::render('Subjects/SubjectPage/LessonPlayer', [
            'lesson' => $lesson,
            'subject' => $subject,
        ]);
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:1',
            'order_index' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv,mkv|max:1048576', // 1GB max for larger video files
        ]);

        if ($request->hasFile('video')) {
            // Delete old video if exists
            if ($lesson->video_path) {
                Storage::disk('public')->delete($lesson->video_path);
            }

            $video = $request->file('video');
            $videoPath = $video->store('lessons/videos', 'public');
            
            $validated['video_path'] = $videoPath;
            $validated['video_filename'] = $video->getClientOriginalName();

            // Try to get video duration
            try {
                $fullPath = storage_path('app/public/' . $videoPath);
                if (class_exists('getID3')) {
                    $getID3 = new getID3();
                    $fileInfo = $getID3->analyze($fullPath);
                    if (isset($fileInfo['playtime_seconds'])) {
                        $validated['duration_minutes'] = ceil($fileInfo['playtime_seconds'] / 60);
                    }
                }
            } catch (\Exception $e) {
                // Continue without duration if there's an error
            }
        }

        $lesson->update($validated);

        return response()->json([
            'success' => true,
            'lesson' => $lesson->fresh(),
            'message' => 'Lesson updated successfully!'
        ]);
    }

    public function destroy(Lesson $lesson)
    {
        if ($lesson->video_path) {
            Storage::disk('public')->delete($lesson->video_path);
        }

        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully!'
        ]);
    }

    public function publish(Lesson $lesson)
    {
        $lesson->update(['is_published' => true]);

        return response()->json([
            'success' => true,
            'lesson' => $lesson->fresh(),
            'message' => 'Lesson published successfully!'
        ]);
    }

    public function unpublish(Lesson $lesson)
    {
        $lesson->update(['is_published' => false]);

        return response()->json([
            'success' => true,
            'lesson' => $lesson->fresh(),
            'message' => 'Lesson unpublished successfully!'
        ]);
    }
}
