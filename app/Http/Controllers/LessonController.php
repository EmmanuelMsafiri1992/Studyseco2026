<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Topic;
use App\Services\CloudStorageService;
use App\Jobs\TranscodeVideoJob;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use getID3;

class LessonController extends Controller
{
    public function store(Request $request)
    {
        try {
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

            // Initialize S3 storage service
            $storageService = new CloudStorageService();

            // Handle traditional file upload
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $filename = Str::uuid() . '_' . $video->getClientOriginalName();

                // Use S3 if enabled, otherwise use public disk
                if ($storageService->isEnabled()) {
                    $videoPath = $storageService->store('lessons/videos', $video, $filename);
                    $lesson->storage_disk = 's3';
                } else {
                    $videoPath = $video->storeAs('lessons/videos', $filename, 'public');
                    $lesson->storage_disk = 'public';
                }

                $lesson->video_path = $videoPath;
                $lesson->video_filename = $video->getClientOriginalName();

                // Try to get video duration (only for local storage)
                if (!$storageService->isEnabled()) {
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
            }
            // Handle chunked upload (video already processed and stored)
            elseif ($request->has('video_path')) {
                $lesson->video_path = $validated['video_path'];
                $lesson->video_filename = $validated['video_filename'] ?? 'uploaded_video.mp4';
                $lesson->storage_disk = $request->input('storage_disk', 'public');

                if (isset($validated['duration_minutes'])) {
                    $lesson->duration_minutes = $validated['duration_minutes'];
                }
            }

            $lesson->save();

            // Dispatch transcoding job if video was uploaded and auto-transcoding is enabled
            if ($lesson->video_path && config('transcoding.auto_transcode', true)) {
                $this->dispatchTranscodingJob($lesson);
            }

            return response()->json([
                'success' => true,
                'lesson' => $lesson,
                'message' => 'Lesson created successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Lesson store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
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
            'video_path' => 'nullable|string', // For chunked uploads
            'video_filename' => 'nullable|string', // For chunked uploads
            'storage_disk' => 'nullable|string', // For chunked uploads
        ]);

        if ($request->hasFile('video')) {
            // Initialize S3 storage service
            $storageService = new CloudStorageService();

            // Delete old video if exists
            if ($lesson->video_path) {
                $oldDisk = $lesson->storage_disk ?? 'public';
                Storage::disk($oldDisk)->delete($lesson->video_path);
            }

            $video = $request->file('video');
            $filename = Str::uuid() . '_' . $video->getClientOriginalName();

            // Use S3 if enabled, otherwise use public disk
            if ($storageService->isEnabled()) {
                $videoPath = $storageService->store('lessons/videos', $video, $filename);
                $validated['storage_disk'] = 's3';
            } else {
                $videoPath = $video->storeAs('lessons/videos', $filename, 'public');
                $validated['storage_disk'] = 'public';
            }

            $validated['video_path'] = $videoPath;
            $validated['video_filename'] = $video->getClientOriginalName();

            // Try to get video duration (only for local storage)
            if (!$storageService->isEnabled()) {
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
        }
        // Handle chunked upload (video already processed and stored)
        elseif ($request->has('video_path') && $request->video_path) {
            // Delete old video if exists
            if ($lesson->video_path) {
                $oldDisk = $lesson->storage_disk ?? 'public';
                try {
                    Storage::disk($oldDisk)->delete($lesson->video_path);
                } catch (\Exception $e) {
                    // Continue even if delete fails
                }
            }

            $validated['video_path'] = $request->video_path;
            $validated['video_filename'] = $request->video_filename ?? 'uploaded_video.mp4';
            $validated['storage_disk'] = $request->storage_disk ?? 'public';
        }

        // Check if video was changed
        $videoChanged = isset($validated['video_path']) && $validated['video_path'] !== $lesson->video_path;

        $lesson->update($validated);

        // Dispatch transcoding job if video was changed and auto-transcoding is enabled
        if ($videoChanged && config('transcoding.auto_transcode', true)) {
            $this->dispatchTranscodingJob($lesson->fresh());
        }

        return response()->json([
            'success' => true,
            'lesson' => $lesson->fresh(),
            'message' => 'Lesson updated successfully!'
        ]);
    }

    public function destroy(Lesson $lesson)
    {
        if ($lesson->video_path) {
            $disk = $lesson->storage_disk ?? 'public';
            Storage::disk($disk)->delete($lesson->video_path);
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

    /**
     * Get the transcoding status for a lesson.
     */
    public function transcodingStatus(Lesson $lesson)
    {
        return response()->json([
            'success' => true,
            'lesson_id' => $lesson->id,
            'transcoding' => $lesson->getTranscodingStatusDetails(),
            'hls_url' => $lesson->hls_url,
            'available_qualities' => $lesson->available_qualities,
        ]);
    }

    /**
     * Manually trigger transcoding for a lesson.
     */
    public function startTranscoding(Lesson $lesson)
    {
        if (!$lesson->video_path) {
            return response()->json([
                'success' => false,
                'error' => 'No video file found for this lesson',
            ], 400);
        }

        if (in_array($lesson->transcoding_status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'error' => 'Transcoding is already in progress',
            ], 400);
        }

        $this->dispatchTranscodingJob($lesson);

        return response()->json([
            'success' => true,
            'message' => 'Transcoding job dispatched successfully',
        ]);
    }

    /**
     * Dispatch the transcoding job for a lesson.
     */
    protected function dispatchTranscodingJob(Lesson $lesson): void
    {
        // Store original video path before transcoding
        if (!$lesson->original_video_path) {
            $lesson->update([
                'original_video_path' => $lesson->video_path,
                'transcoding_status' => 'pending',
                'transcoding_progress' => 0,
            ]);
        } else {
            $lesson->update([
                'transcoding_status' => 'pending',
                'transcoding_progress' => 0,
            ]);
        }

        // Delete existing quality records if re-transcoding
        $lesson->videoQualities()->delete();

        // Dispatch the job
        TranscodeVideoJob::dispatch($lesson);

        \Log::info('Transcoding job dispatched', ['lesson_id' => $lesson->id]);
    }
}
