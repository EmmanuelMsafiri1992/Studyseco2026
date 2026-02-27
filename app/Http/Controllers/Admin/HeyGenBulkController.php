<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Subject;
use App\Services\HeyGenService;
use App\Jobs\GenerateHeyGenVideoJob;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class HeyGenBulkController extends Controller
{
    protected HeyGenService $heygenService;

    public function __construct(HeyGenService $heygenService)
    {
        $this->heygenService = $heygenService;
    }

    /**
     * Show the bulk generation page.
     */
    public function index()
    {
        $subjects = Subject::with(['topics.lessons' => function ($query) {
            $query->select('id', 'topic_id', 'title', 'description', 'video_path', 'heygen_status', 'heygen_script', 'heygen_avatar_id', 'heygen_voice_id');
        }])->where('is_active', true)->get();

        // Get lessons that need videos (no video or failed HeyGen)
        $lessonsNeedingVideos = Lesson::whereNull('video_path')
            ->orWhere('heygen_status', 'failed')
            ->with('topic.subject')
            ->get();

        // Get lessons currently generating
        $lessonsGenerating = Lesson::whereIn('heygen_status', ['pending', 'processing'])
            ->with('topic.subject')
            ->get();

        // Get HeyGen stats
        $stats = [
            'total_lessons' => Lesson::count(),
            'lessons_with_video' => Lesson::whereNotNull('video_path')->count(),
            'lessons_without_video' => Lesson::whereNull('video_path')->count(),
            'heygen_pending' => Lesson::where('heygen_status', 'pending')->count(),
            'heygen_processing' => Lesson::where('heygen_status', 'processing')->count(),
            'heygen_completed' => Lesson::where('heygen_status', 'completed')->count(),
            'heygen_failed' => Lesson::where('heygen_status', 'failed')->count(),
        ];

        // Check if HeyGen is configured
        $heygenConfigured = $this->heygenService->isConfigured();

        // Get avatars and voices if configured
        $avatars = [];
        $voices = [];
        $credits = null;

        if ($heygenConfigured) {
            try {
                $avatars = $this->heygenService->getAvatars();
                $voices = $this->heygenService->getVoices();
                $credits = $this->heygenService->getCredits();
            } catch (\Exception $e) {
                Log::error('HeyGen Bulk: Failed to load resources', ['error' => $e->getMessage()]);
            }
        }

        return Inertia::render('Admin/HeyGen/BulkGenerate', [
            'subjects' => $subjects,
            'lessonsNeedingVideos' => $lessonsNeedingVideos,
            'lessonsGenerating' => $lessonsGenerating,
            'stats' => $stats,
            'heygenConfigured' => $heygenConfigured,
            'avatars' => $avatars,
            'voices' => $voices,
            'credits' => $credits,
        ]);
    }

    /**
     * Generate videos for multiple lessons.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'lessons' => 'required|array|min:1',
            'lessons.*.id' => 'required|exists:lessons,id',
            'lessons.*.script' => 'required|string|min:10',
            'lessons.*.avatar_id' => 'required|string',
            'lessons.*.voice_id' => 'required|string',
        ]);

        $queued = 0;
        $skipped = 0;
        $errors = [];

        foreach ($validated['lessons'] as $lessonData) {
            $lesson = Lesson::find($lessonData['id']);

            if (!$lesson) {
                $errors[] = "Lesson ID {$lessonData['id']} not found";
                continue;
            }

            // Skip if already generating
            if (in_array($lesson->heygen_status, ['pending', 'processing'])) {
                $skipped++;
                continue;
            }

            try {
                // Update lesson with HeyGen data
                $lesson->update([
                    'heygen_script' => $lessonData['script'],
                    'heygen_avatar_id' => $lessonData['avatar_id'],
                    'heygen_voice_id' => $lessonData['voice_id'],
                    'heygen_status' => 'pending',
                    'heygen_error' => null,
                    'heygen_video_id' => null,
                ]);

                // Dispatch job
                GenerateHeyGenVideoJob::dispatch($lesson);
                $queued++;

                Log::info('HeyGen Bulk: Queued lesson', ['lesson_id' => $lesson->id]);
            } catch (\Exception $e) {
                $errors[] = "Failed to queue lesson {$lesson->id}: {$e->getMessage()}";
                Log::error('HeyGen Bulk: Failed to queue lesson', [
                    'lesson_id' => $lesson->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'queued' => $queued,
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => "Queued {$queued} lessons for video generation" . ($skipped > 0 ? " ({$skipped} skipped - already generating)" : ''),
        ]);
    }

    /**
     * Get generation status for multiple lessons.
     */
    public function status(Request $request)
    {
        $lessonIds = $request->input('lesson_ids', []);

        $lessons = Lesson::whereIn('id', $lessonIds)
            ->select('id', 'title', 'heygen_status', 'heygen_error', 'heygen_started_at', 'heygen_completed_at', 'video_path')
            ->get()
            ->keyBy('id');

        return response()->json([
            'success' => true,
            'lessons' => $lessons,
        ]);
    }

    /**
     * Cancel multiple generations.
     */
    public function cancelBulk(Request $request)
    {
        $lessonIds = $request->input('lesson_ids', []);

        $cancelled = Lesson::whereIn('id', $lessonIds)
            ->whereIn('heygen_status', ['pending', 'processing'])
            ->update([
                'heygen_status' => 'failed',
                'heygen_error' => 'Cancelled by user',
                'heygen_completed_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'cancelled' => $cancelled,
            'message' => "Cancelled {$cancelled} video generations",
        ]);
    }

    /**
     * Retry failed generations.
     */
    public function retryFailed(Request $request)
    {
        $lessonIds = $request->input('lesson_ids', []);

        $lessons = Lesson::whereIn('id', $lessonIds)
            ->where('heygen_status', 'failed')
            ->whereNotNull('heygen_script')
            ->whereNotNull('heygen_avatar_id')
            ->whereNotNull('heygen_voice_id')
            ->get();

        $retried = 0;
        foreach ($lessons as $lesson) {
            $lesson->update([
                'heygen_status' => 'pending',
                'heygen_error' => null,
                'heygen_video_id' => null,
            ]);

            GenerateHeyGenVideoJob::dispatch($lesson);
            $retried++;
        }

        return response()->json([
            'success' => true,
            'retried' => $retried,
            'message' => "Retrying {$retried} failed video generations",
        ]);
    }

    /**
     * Generate script suggestions using AI (placeholder for future enhancement).
     */
    public function suggestScript(Request $request)
    {
        $lessonId = $request->input('lesson_id');
        $lesson = Lesson::with('topic.subject')->find($lessonId);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'error' => 'Lesson not found',
            ], 404);
        }

        // Generate a basic script template based on lesson info
        $subject = $lesson->topic->subject->name ?? 'this subject';
        $topic = $lesson->topic->name ?? 'this topic';
        $title = $lesson->title;
        $description = $lesson->description ?? '';

        $suggestedScript = "Welcome to this lesson on {$title}. ";
        $suggestedScript .= "In this {$subject} lesson, we will explore {$topic}. ";

        if ($description) {
            $suggestedScript .= $description . " ";
        }

        $suggestedScript .= "Let's begin by understanding the key concepts. ";
        $suggestedScript .= "By the end of this lesson, you will have a clear understanding of the material. ";
        $suggestedScript .= "If you have any questions, feel free to reach out to your instructor. ";
        $suggestedScript .= "Now, let's dive in!";

        return response()->json([
            'success' => true,
            'script' => $suggestedScript,
        ]);
    }
}
