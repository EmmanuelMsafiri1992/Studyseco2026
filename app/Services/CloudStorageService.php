<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Google\Cloud\Storage\StorageClient;

class CloudStorageService
{
    protected string $provider = 'local';
    protected bool $cloudEnabled = false;
    protected array $s3Config = [];
    protected array $gcsConfig = [];

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load cloud storage configuration from system settings
     */
    protected function loadConfiguration(): void
    {
        $this->provider = SystemSetting::get('cloud_storage_provider', 'local');

        // Load S3 config
        $s3Enabled = (bool) SystemSetting::get('s3_enabled', false);
        if ($s3Enabled) {
            $this->s3Config = [
                'key' => SystemSetting::get('s3_access_key', ''),
                'secret' => SystemSetting::get('s3_secret_key', ''),
                'region' => SystemSetting::get('s3_region', 'us-east-1'),
                'bucket' => SystemSetting::get('s3_bucket', ''),
                'url' => SystemSetting::get('s3_url', ''),
                'endpoint' => SystemSetting::get('s3_endpoint', ''),
            ];
        }

        // Load GCS config
        $gcsEnabled = (bool) SystemSetting::get('gcs_enabled', false);
        if ($gcsEnabled) {
            $this->gcsConfig = [
                'project_id' => SystemSetting::get('gcs_project_id', ''),
                'bucket' => SystemSetting::get('gcs_bucket', ''),
                'key_file' => SystemSetting::get('gcs_key_file', ''),
                'path_prefix' => SystemSetting::get('gcs_path_prefix', ''),
                'url' => SystemSetting::get('gcs_url', ''),
            ];
        }

        // Determine if cloud storage is enabled based on provider
        if ($this->provider === 's3' && $s3Enabled && !empty($this->s3Config['bucket'])) {
            $this->cloudEnabled = true;
            $this->configureS3Disk();
        } elseif ($this->provider === 'gcs' && $gcsEnabled && !empty($this->gcsConfig['bucket'])) {
            $this->cloudEnabled = true;
            $this->configureGcsDisk();
        }
    }

    /**
     * Configure S3 disk at runtime
     */
    protected function configureS3Disk(): void
    {
        $diskConfig = [
            'driver' => 's3',
            'key' => $this->s3Config['key'],
            'secret' => $this->s3Config['secret'],
            'region' => $this->s3Config['region'],
            'bucket' => $this->s3Config['bucket'],
            'throw' => false,
            'report' => false,
        ];

        if (!empty($this->s3Config['url'])) {
            $diskConfig['url'] = $this->s3Config['url'];
        }

        if (!empty($this->s3Config['endpoint'])) {
            $diskConfig['endpoint'] = $this->s3Config['endpoint'];
            $diskConfig['use_path_style_endpoint'] = true;
        }

        Config::set('filesystems.disks.s3', $diskConfig);
    }

    /**
     * Configure GCS disk at runtime
     */
    protected function configureGcsDisk(): void
    {
        $keyFile = $this->gcsConfig['key_file'];
        $keyFileArray = [];

        if (!empty($keyFile)) {
            $keyFileArray = json_decode($keyFile, true) ?: [];
        }

        $diskConfig = [
            'driver' => 'gcs',
            'project_id' => $this->gcsConfig['project_id'],
            'key_file' => $keyFileArray,
            'bucket' => $this->gcsConfig['bucket'],
            'path_prefix' => $this->gcsConfig['path_prefix'] ?? null,
            'storage_api_uri' => null,
            'api_endpoint' => null,
            'visibility' => 'public',
            'visibility_handler' => null,
            'metadata' => ['cacheControl' => 'public,max-age=86400'],
        ];

        Config::set('filesystems.disks.gcs', $diskConfig);
    }

    /**
     * Check if cloud storage is enabled
     */
    public function isEnabled(): bool
    {
        return $this->cloudEnabled;
    }

    /**
     * Get the current storage provider
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Get the appropriate storage disk name
     */
    public function getDisk(): string
    {
        if (!$this->cloudEnabled) {
            return 'public';
        }

        return $this->provider === 'gcs' ? 'gcs' : 's3';
    }

    /**
     * Get the storage instance
     */
    public function storage()
    {
        return Storage::disk($this->getDisk());
    }

    /**
     * Store a file to the appropriate disk
     */
    public function store(string $path, $file, string $filename = null): string
    {
        $disk = $this->getDisk();

        if ($filename) {
            return Storage::disk($disk)->putFileAs($path, $file, $filename);
        }

        return Storage::disk($disk)->putFile($path, $file);
    }

    /**
     * Store file contents to the appropriate disk
     */
    public function put(string $path, $contents, array $options = []): bool
    {
        return Storage::disk($this->getDisk())->put($path, $contents, $options);
    }

    /**
     * Delete a file from the appropriate disk
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->getDisk())->delete($path);
    }

    /**
     * Get the URL for a file
     */
    public function url(string $path): string
    {
        if ($this->cloudEnabled) {
            if ($this->provider === 's3') {
                if (!empty($this->s3Config['url'])) {
                    return rtrim($this->s3Config['url'], '/') . '/' . ltrim($path, '/');
                }
                return Storage::disk('s3')->url($path);
            } elseif ($this->provider === 'gcs') {
                if (!empty($this->gcsConfig['url'])) {
                    return rtrim($this->gcsConfig['url'], '/') . '/' . ltrim($path, '/');
                }
                // Default GCS public URL format
                return 'https://storage.googleapis.com/' . $this->gcsConfig['bucket'] . '/' . ltrim($path, '/');
            }
        }

        return Storage::url($path);
    }

    /**
     * Check if a file exists
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->getDisk())->exists($path);
    }

    /**
     * Get file contents
     */
    public function get(string $path): ?string
    {
        return Storage::disk($this->getDisk())->get($path);
    }

    /**
     * Get the full path to a file on the disk
     */
    public function path(string $path): string
    {
        return Storage::disk($this->getDisk())->path($path);
    }

    /**
     * Test S3 connection with provided credentials
     */
    public static function testS3Connection(array $credentials): array
    {
        try {
            $config = [
                'version' => 'latest',
                'region' => $credentials['region'] ?? 'us-east-1',
                'credentials' => [
                    'key' => $credentials['access_key'],
                    'secret' => $credentials['secret_key'],
                ],
            ];

            if (!empty($credentials['endpoint'])) {
                $config['endpoint'] = $credentials['endpoint'];
                $config['use_path_style_endpoint'] = true;
            }

            $s3Client = new S3Client($config);

            $result = $s3Client->listObjectsV2([
                'Bucket' => $credentials['bucket'],
                'MaxKeys' => 1,
            ]);

            return [
                'success' => true,
                'message' => 'Successfully connected to S3 bucket: ' . $credentials['bucket'],
            ];
        } catch (AwsException $e) {
            $errorCode = $e->getAwsErrorCode();
            $message = match ($errorCode) {
                'InvalidAccessKeyId' => 'Invalid AWS Access Key ID.',
                'SignatureDoesNotMatch' => 'Invalid AWS Secret Access Key.',
                'NoSuchBucket' => 'The specified bucket does not exist.',
                'AccessDenied' => 'Access denied. Check your IAM permissions.',
                'InvalidBucketName' => 'Invalid bucket name.',
                default => 'Connection failed: ' . $e->getAwsErrorMessage(),
            };

            return [
                'success' => false,
                'message' => $message,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test GCS connection with provided credentials
     */
    public static function testGcsConnection(array $credentials): array
    {
        try {
            $keyFile = $credentials['key_file'] ?? '';
            $keyFileArray = json_decode($keyFile, true);

            if (empty($keyFileArray)) {
                return [
                    'success' => false,
                    'message' => 'Invalid service account JSON key. Please check the format.',
                ];
            }

            $storage = new StorageClient([
                'projectId' => $credentials['project_id'],
                'keyFile' => $keyFileArray,
            ]);

            $bucket = $storage->bucket($credentials['bucket']);

            // Try to get bucket info to verify access
            if (!$bucket->exists()) {
                return [
                    'success' => false,
                    'message' => 'The specified bucket does not exist or is not accessible.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Successfully connected to GCS bucket: ' . $credentials['bucket'],
            ];
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if (strpos($message, 'invalid_grant') !== false) {
                $message = 'Invalid service account credentials.';
            } elseif (strpos($message, 'Permission') !== false || strpos($message, 'denied') !== false) {
                $message = 'Access denied. Check your service account permissions.';
            } elseif (strpos($message, 'project') !== false) {
                $message = 'Invalid project ID or project not found.';
            }

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $message,
            ];
        }
    }

    /**
     * Get storage info (for admin dashboard)
     */
    public function getStorageInfo(): array
    {
        return [
            'provider' => $this->provider,
            'enabled' => $this->cloudEnabled,
            's3' => [
                'bucket' => $this->s3Config['bucket'] ?? null,
                'region' => $this->s3Config['region'] ?? null,
                'has_cdn' => !empty($this->s3Config['url']),
            ],
            'gcs' => [
                'bucket' => $this->gcsConfig['bucket'] ?? null,
                'project_id' => $this->gcsConfig['project_id'] ?? null,
                'has_cdn' => !empty($this->gcsConfig['url']),
            ],
        ];
    }
}
