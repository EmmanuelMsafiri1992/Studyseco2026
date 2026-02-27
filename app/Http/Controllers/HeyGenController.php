<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\HeyGenService;
use App\Jobs\GenerateHeyGenVideoJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class HeyGenController extends Controller
{
    protected HeyGenService $heygenService;

    public function __construct(HeyGenService $heygenService)
    {
        $this->heygenService = $heygenService;
    }

    /**
     * Check if HeyGen API is configured.
     */
    public function status()
    {
        return response()->json([
            'configured' => $this->heygenService->isConfigured(),
        ]);
    }

    /**
     * Get available avatars.
     */
    public function avatars()
    {
        try {
            $avatars = $this->heygenService->getAvatars();

            // Group avatars by type for easier selection
            $grouped = collect($avatars)->groupBy(function ($avatar) {
                return $avatar['avatar_type'] ?? 'other';
            })->toArray();

            return response()->json([
                'success' => true,
                'avatars' => $avatars,
                'grouped' => $grouped,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available voices.
     */
    public function voices()
    {
        try {
            $voices = $this->heygenService->getVoices();

            // Group voices by language for easier selection
            $grouped = collect($voices)->groupBy(function ($voice) {
                return $voice['language'] ?? 'other';
            })->toArray();

            return response()->json([
                'success' => true,
                'voices' => $voices,
                'grouped' => $grouped,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get remaining credits.
     */
    public function credits()
    {
        try {
            $credits = $this->heygenService->getCredits();

            return response()->json([
                'success' => true,
                'credits' => $credits,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate AI video for a lesson.
     */
    public function generate(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'script' => 'required|string|min:10|max:10000',
            'avatar_id' => 'required|string',
            'voice_id' => 'required|string',
            'test_mode' => 'boolean',
        ]);

        // Check if already generating
        if (in_array($lesson->heygen_status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'error' => 'Video generation is already in progress for this lesson',
            ], 400);
        }

        try {
            // Update lesson with HeyGen data
            $lesson->update([
                'heygen_script' => $validated['script'],
                'heygen_avatar_id' => $validated['avatar_id'],
                'heygen_voice_id' => $validated['voice_id'],
                'heygen_status' => 'pending',
                'heygen_error' => null,
                'heygen_video_id' => null,
                'heygen_test_mode' => $validated['test_mode'] ?? false,
            ]);

            // Dispatch job
            GenerateHeyGenVideoJob::dispatch($lesson);

            Log::info('HeyGen: Generation job dispatched', [
                'lesson_id' => $lesson->id,
                'avatar_id' => $validated['avatar_id'],
                'voice_id' => $validated['voice_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Video generation started. This may take a few minutes.',
                'lesson' => $lesson->fresh(),
            ]);
        } catch (Exception $e) {
            Log::error('HeyGen: Failed to start generation', [
                'lesson_id' => $lesson->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get generation status for a lesson.
     */
    public function generationStatus(Lesson $lesson)
    {
        return response()->json([
            'success' => true,
            'lesson_id' => $lesson->id,
            'heygen' => [
                'status' => $lesson->heygen_status,
                'video_id' => $lesson->heygen_video_id,
                'error' => $lesson->heygen_error,
                'started_at' => $lesson->heygen_started_at?->toIso8601String(),
                'completed_at' => $lesson->heygen_completed_at?->toIso8601String(),
            ],
            'video_url' => $lesson->video_url,
            'has_video' => !empty($lesson->video_path),
        ]);
    }

    /**
     * Cancel a pending generation (if possible).
     */
    public function cancel(Lesson $lesson)
    {
        if (!in_array($lesson->heygen_status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'error' => 'No active generation to cancel',
            ], 400);
        }

        // We can't actually cancel HeyGen jobs, but we can mark it as cancelled
        $lesson->update([
            'heygen_status' => 'failed',
            'heygen_error' => 'Cancelled by user',
            'heygen_completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Generation cancelled',
        ]);
    }

    /**
     * Retry a failed generation.
     */
    public function retry(Lesson $lesson)
    {
        if ($lesson->heygen_status !== 'failed') {
            return response()->json([
                'success' => false,
                'error' => 'Can only retry failed generations',
            ], 400);
        }

        if (empty($lesson->heygen_script) || empty($lesson->heygen_avatar_id) || empty($lesson->heygen_voice_id)) {
            return response()->json([
                'success' => false,
                'error' => 'Missing script, avatar, or voice configuration',
            ], 400);
        }

        $lesson->update([
            'heygen_status' => 'pending',
            'heygen_error' => null,
            'heygen_video_id' => null,
        ]);

        GenerateHeyGenVideoJob::dispatch($lesson);

        return response()->json([
            'success' => true,
            'message' => 'Generation retry started',
        ]);
    }
}
