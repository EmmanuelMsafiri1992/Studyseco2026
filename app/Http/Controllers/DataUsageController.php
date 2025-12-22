<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataUsageController extends Controller
{
    public function getEstimation()
    {
        // Estimated data usage for different activities (in MB)
        $dataEstimates = [
            'video_lesson_per_hour' => 300,  // 300MB per hour of video
            'audio_lesson_per_hour' => 60,   // 60MB per hour of audio
            'text_reading_per_hour' => 5,    // 5MB per hour of text
            'quiz_per_session' => 2,         // 2MB per quiz session
            'chat_per_hour' => 10,           // 10MB per hour of chat
            'pdf_download_avg' => 15,        // 15MB average per PDF
            'image_viewing_per_hour' => 20,  // 20MB per hour of image viewing
        ];

        // Calculate monthly estimates for different usage patterns
        $monthlyEstimates = [
            'light_user' => [
                'description' => 'Light usage (2-3 hours/week)',
                'video_hours' => 10,
                'audio_hours' => 5,
                'text_hours' => 8,
                'quiz_sessions' => 4,
                'chat_hours' => 2,
                'pdf_downloads' => 5,
                'total_mb' => 0
            ],
            'moderate_user' => [
                'description' => 'Moderate usage (5-7 hours/week)',
                'video_hours' => 20,
                'audio_hours' => 10,
                'text_hours' => 15,
                'quiz_sessions' => 8,
                'chat_hours' => 5,
                'pdf_downloads' => 10,
                'total_mb' => 0
            ],
            'heavy_user' => [
                'description' => 'Heavy usage (10+ hours/week)',
                'video_hours' => 40,
                'audio_hours' => 20,
                'text_hours' => 25,
                'quiz_sessions' => 15,
                'chat_hours' => 10,
                'pdf_downloads' => 20,
                'total_mb' => 0
            ]
        ];

        // Calculate totals
        foreach ($monthlyEstimates as $key => &$estimate) {
            $total = 0;
            $total += $estimate['video_hours'] * $dataEstimates['video_lesson_per_hour'];
            $total += $estimate['audio_hours'] * $dataEstimates['audio_lesson_per_hour'];
            $total += $estimate['text_hours'] * $dataEstimates['text_reading_per_hour'];
            $total += $estimate['quiz_sessions'] * $dataEstimates['quiz_per_session'];
            $total += $estimate['chat_hours'] * $dataEstimates['chat_per_hour'];
            $total += $estimate['pdf_downloads'] * $dataEstimates['pdf_download_avg'];
            
            $estimate['total_mb'] = $total;
            $estimate['total_gb'] = round($total / 1024, 2);
            
            // Add breakdown for transparency
            $estimate['breakdown'] = [
                'video' => $estimate['video_hours'] * $dataEstimates['video_lesson_per_hour'],
                'audio' => $estimate['audio_hours'] * $dataEstimates['audio_lesson_per_hour'],
                'text' => $estimate['text_hours'] * $dataEstimates['text_reading_per_hour'],
                'quizzes' => $estimate['quiz_sessions'] * $dataEstimates['quiz_per_session'],
                'chat' => $estimate['chat_hours'] * $dataEstimates['chat_per_hour'],
                'downloads' => $estimate['pdf_downloads'] * $dataEstimates['pdf_download_avg'],
            ];
        }

        // Data saving tips
        $dataSavingTips = [
            [
                'title' => 'Download during Wi-Fi',
                'description' => 'Download study materials when connected to Wi-Fi to avoid using mobile data',
                'potential_savings' => '70-90%'
            ],
            [
                'title' => 'Use audio lessons',
                'description' => 'Audio lessons use 5x less data than video lessons',
                'potential_savings' => '80%'
            ],
            [
                'title' => 'Adjust video quality',
                'description' => 'Lower video quality can reduce data usage by 50-70%',
                'potential_savings' => '60%'
            ],
            [
                'title' => 'Offline reading',
                'description' => 'Download PDFs and notes for offline reading',
                'potential_savings' => '100%'
            ],
            [
                'title' => 'Batch downloads',
                'description' => 'Download multiple lessons at once to minimize connection overhead',
                'potential_savings' => '20-30%'
            ]
        ];

        return response()->json([
            'estimates' => $monthlyEstimates,
            'tips' => $dataSavingTips,
            'last_updated' => now()->toISOString()
        ]);
    }

    public function getUserUsageStats(Request $request)
    {
        $user = auth()->user();
        
        // Mock user usage stats (in a real implementation, you'd track actual usage)
        $userStats = [
            'current_month' => [
                'total_mb' => rand(800, 2500),
                'video_hours' => rand(5, 25),
                'audio_hours' => rand(3, 15),
                'downloads' => rand(5, 20),
                'last_7_days' => rand(200, 600)
            ],
            'average_session' => [
                'duration_minutes' => rand(25, 90),
                'data_mb' => rand(50, 200),
                'activities' => ['video', 'quiz', 'reading', 'chat']
            ],
            'comparison' => [
                'vs_light_user' => rand(-20, 50),
                'vs_moderate_user' => rand(-30, 30),
                'vs_heavy_user' => rand(-50, -10)
            ]
        ];

        return response()->json([
            'stats' => $userStats,
            'recommendations' => $this->getPersonalizedRecommendations($userStats)
        ]);
    }

    private function getPersonalizedRecommendations($stats)
    {
        $recommendations = [];
        
        if ($stats['current_month']['total_mb'] > 2000) {
            $recommendations[] = [
                'type' => 'high_usage',
                'message' => 'Your data usage is high this month. Consider downloading content on Wi-Fi.',
                'action' => 'Switch to audio lessons to save 80% data'
            ];
        }
        
        if ($stats['current_month']['video_hours'] > 15) {
            $recommendations[] = [
                'type' => 'video_heavy',
                'message' => 'You watch many video lessons. Try mixing with audio lessons.',
                'action' => 'Use audio for revision sessions'
            ];
        }
        
        return $recommendations;
    }
}