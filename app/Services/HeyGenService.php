<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class HeyGenService
{
    protected ?string $apiKey;
    protected string $baseUrl = 'https://api.heygen.com';

    public function __construct()
    {
        // First try to get from system settings (dashboard), then fall back to config/env
        $this->apiKey = SystemSetting::get('heygen_api_key') ?: (config('services.heygen.api_key') ?? '');
    }

    /**
     * Check if HeyGen API is configured.
     */
    public function isConfigured(): bool
    {
        $enabled = SystemSetting::get('heygen_enabled', false);
        return $enabled && !empty($this->apiKey);
    }

    /**
     * Check if HeyGen has an API key (regardless of enabled status).
     */
    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get the default test mode setting.
     */
    public function getDefaultTestMode(): bool
    {
        return (bool) SystemSetting::get('heygen_default_test_mode', true);
    }

    /**
     * Get available avatars from HeyGen.
     */
    public function getAvatars(): array
    {
        $response = $this->makeRequest('GET', '/v2/avatars');

        if (!$response['success']) {
            throw new Exception($response['error'] ?? 'Failed to fetch avatars');
        }

        return $response['data']['avatars'] ?? [];
    }

    /**
     * Get available voices from HeyGen.
     */
    public function getVoices(): array
    {
        $response = $this->makeRequest('GET', '/v2/voices');

        if (!$response['success']) {
            throw new Exception($response['error'] ?? 'Failed to fetch voices');
        }

        return $response['data']['voices'] ?? [];
    }

    /**
     * Generate a video with an avatar.
     *
     * @param string $script The text for the avatar to speak
     * @param string $avatarId The avatar ID to use
     * @param string $voiceId The voice ID to use
     * @param array $options Additional options (background, etc.)
     * @return array Contains video_id for tracking
     */
    public function generateVideo(
        string $script,
        string $avatarId,
        string $voiceId,
        array $options = []
    ): array {
        $payload = [
            'video_inputs' => [
                [
                    'character' => [
                        'type' => 'avatar',
                        'avatar_id' => $avatarId,
                        'avatar_style' => $options['avatar_style'] ?? 'normal',
                    ],
                    'voice' => [
                        'type' => 'text',
                        'input_text' => $script,
                        'voice_id' => $voiceId,
                        'speed' => $options['speed'] ?? 1.0,
                    ],
                    'background' => $options['background'] ?? [
                        'type' => 'color',
                        'value' => '#ffffff',
                    ],
                ],
            ],
            'dimension' => $options['dimension'] ?? [
                'width' => 1920,
                'height' => 1080,
            ],
            'aspect_ratio' => $options['aspect_ratio'] ?? null,
            'test' => $options['test'] ?? false,
        ];

        // Remove null values
        $payload = array_filter($payload, fn($v) => $v !== null);

        Log::info('HeyGen: Generating video', [
            'avatar_id' => $avatarId,
            'voice_id' => $voiceId,
            'script_length' => strlen($script),
        ]);

        $response = $this->makeRequest('POST', '/v2/video/generate', $payload);

        if (!$response['success']) {
            throw new Exception($response['error'] ?? 'Failed to generate video');
        }

        return [
            'video_id' => $response['data']['video_id'],
        ];
    }

    /**
     * Check the status of a video generation.
     *
     * @param string $videoId The video ID to check
     * @return array Contains status, video_url (if completed), etc.
     */
    public function getVideoStatus(string $videoId): array
    {
        $response = $this->makeRequest('GET', "/v1/video_status.get?video_id={$videoId}");

        if (!$response['success']) {
            throw new Exception($response['error'] ?? 'Failed to get video status');
        }

        $data = $response['data'];

        return [
            'status' => $data['status'] ?? 'unknown',
            'video_url' => $data['video_url'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            'duration' => $data['duration'] ?? null,
            'error' => $data['error'] ?? null,
        ];
    }

    /**
     * Get video details by ID.
     */
    public function getVideo(string $videoId): array
    {
        $response = $this->makeRequest('GET', "/v1/video/{$videoId}");

        if (!$response['success']) {
            throw new Exception($response['error'] ?? 'Failed to get video');
        }

        return $response['data'];
    }

    /**
     * Get remaining API credits.
     */
    public function getCredits(): array
    {
        $response = $this->makeRequest('GET', '/v1/user/remaining_quota');

        if (!$response['success']) {
            throw new Exception($response['error'] ?? 'Failed to get credits');
        }

        return $response['data'];
    }

    /**
     * Make an HTTP request to the HeyGen API.
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'HeyGen API key not configured',
            ];
        }

        try {
            $url = $this->baseUrl . $endpoint;

            $request = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60);

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url),
                'POST' => $request->post($url, $data),
                'DELETE' => $request->delete($url),
                default => throw new Exception("Unsupported HTTP method: {$method}"),
            };

            $body = $response->json();

            if ($response->failed()) {
                Log::error('HeyGen API error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $body,
                ]);

                return [
                    'success' => false,
                    'error' => $body['error'] ?? $body['message'] ?? 'API request failed',
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => true,
                'data' => $body['data'] ?? $body,
            ];

        } catch (Exception $e) {
            Log::error('HeyGen API exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
