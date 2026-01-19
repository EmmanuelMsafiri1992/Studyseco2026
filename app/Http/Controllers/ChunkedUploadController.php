<?php

namespace App\Http\Controllers;

use App\Services\CloudStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkedUploadController extends Controller
{
    /**
     * Initialize chunked upload
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'fileName' => 'required|string',
            'fileSize' => 'required|integer',
            'chunkSize' => 'required|integer',
            'totalChunks' => 'required|integer',
        ]);

        $uploadId = Str::uuid();
        $tempDir = "temp/uploads/{$uploadId}";
        
        // Create temporary directory
        Storage::disk('public')->makeDirectory($tempDir);
        
        // Store upload metadata
        $metadata = [
            'uploadId' => $uploadId,
            'fileName' => $request->fileName,
            'fileSize' => $request->fileSize,
            'chunkSize' => $request->chunkSize,
            'totalChunks' => $request->totalChunks,
            'uploadedChunks' => [],
            'createdAt' => now()->toISOString(),
        ];
        
        Storage::disk('public')->put("{$tempDir}/metadata.json", json_encode($metadata));
        
        return response()->json([
            'success' => true,
            'uploadId' => $uploadId,
            'chunkSize' => $request->chunkSize,
        ]);
    }

    /**
     * Upload individual chunk
     */
    public function uploadChunk(Request $request, $uploadId)
    {
        try {
            $tempDir = "temp/uploads/{$uploadId}";
            $metadataPath = "{$tempDir}/metadata.json";

            // Check if upload session exists
            if (!Storage::disk('public')->exists($metadataPath)) {
                return response()->json(['success' => false, 'error' => 'Upload session not found'], 404);
            }

            $chunkNumber = $request->input('chunkNumber', 0);
            $totalChunksInput = $request->input('totalChunks', 1);

            // Get chunk content - try multiple methods
            $chunkContent = null;
            $chunkSize = 0;

            // Method 1: Try standard file upload
            if ($request->hasFile('chunk')) {
                $chunkFile = $request->file('chunk');
                $chunkContent = file_get_contents($chunkFile->getRealPath());
                $chunkSize = strlen($chunkContent);
                \Log::info("Chunk {$chunkNumber}: Got content via hasFile", ['size' => $chunkSize]);
            }

            // Method 2: Try allFiles array
            if (!$chunkContent || $chunkSize === 0) {
                $allFiles = $request->allFiles();
                if (!empty($allFiles)) {
                    $firstFile = reset($allFiles);
                    if ($firstFile && method_exists($firstFile, 'getRealPath')) {
                        $chunkContent = file_get_contents($firstFile->getRealPath());
                        $chunkSize = strlen($chunkContent);
                        \Log::info("Chunk {$chunkNumber}: Got content via allFiles", ['size' => $chunkSize]);
                    }
                }
            }

            // Method 3: Try raw input stream (for blob uploads)
            if (!$chunkContent || $chunkSize === 0) {
                // Check if chunk is in the files array under a nested structure
                $files = $_FILES;
                if (isset($files['chunk']) && isset($files['chunk']['tmp_name']) && is_uploaded_file($files['chunk']['tmp_name'])) {
                    $chunkContent = file_get_contents($files['chunk']['tmp_name']);
                    $chunkSize = strlen($chunkContent);
                    \Log::info("Chunk {$chunkNumber}: Got content via \$_FILES", ['size' => $chunkSize]);
                }
            }

            // Method 4: Check for nested array in allFiles
            if (!$chunkContent || $chunkSize === 0) {
                $allFiles = $request->allFiles();
                foreach ($allFiles as $key => $file) {
                    if (is_array($file)) {
                        // Nested array structure
                        foreach ($file as $nestedFile) {
                            if ($nestedFile && method_exists($nestedFile, 'getRealPath')) {
                                $realPath = $nestedFile->getRealPath();
                                if ($realPath && file_exists($realPath)) {
                                    $chunkContent = file_get_contents($realPath);
                                    $chunkSize = strlen($chunkContent);
                                    \Log::info("Chunk {$chunkNumber}: Got content via nested allFiles", ['size' => $chunkSize]);
                                    break 2;
                                }
                            }
                        }
                    } elseif ($file && method_exists($file, 'getRealPath')) {
                        $realPath = $file->getRealPath();
                        if ($realPath && file_exists($realPath)) {
                            $chunkContent = file_get_contents($realPath);
                            $chunkSize = strlen($chunkContent);
                            \Log::info("Chunk {$chunkNumber}: Got content via direct allFiles iteration", ['size' => $chunkSize]);
                            break;
                        }
                    }
                }
            }

            if (!$chunkContent || $chunkSize === 0) {
                \Log::error("Chunk {$chunkNumber}: No content received", [
                    'hasFile' => $request->hasFile('chunk'),
                    'allFilesKeys' => array_keys($request->allFiles()),
                    'filesGlobal' => array_keys($_FILES ?? []),
                    'contentType' => $request->header('Content-Type'),
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'No chunk content received',
                    'debug' => [
                        'hasFile' => $request->hasFile('chunk'),
                        'allFilesKeys' => array_keys($request->allFiles()),
                    ]
                ], 422);
            }

            // Store chunk content directly using put() instead of putFileAs()
            $chunkPath = "{$tempDir}/chunk_{$chunkNumber}";
            Storage::disk('public')->put($chunkPath, $chunkContent);

            // Verify the chunk was saved correctly
            $savedSize = Storage::disk('public')->size($chunkPath);
            \Log::info("Chunk {$chunkNumber}: Saved", ['originalSize' => $chunkSize, 'savedSize' => $savedSize]);

            if ($savedSize !== $chunkSize) {
                \Log::error("Chunk {$chunkNumber}: Size mismatch after save", ['expected' => $chunkSize, 'actual' => $savedSize]);
            }

            // Count actual chunk files
            $chunkFiles = Storage::disk('public')->files($tempDir);
            $uploadedChunks = count(array_filter($chunkFiles, function($file) {
                return str_contains($file, 'chunk_');
            }));

            $totalChunks = (int) $request->input('totalChunks', 1);
            $progress = ($uploadedChunks / $totalChunks) * 100;

            return response()->json([
                'success' => true,
                'chunkNumber' => $chunkNumber,
                'uploadedChunks' => $uploadedChunks,
                'totalChunks' => $totalChunks,
                'progress' => round($progress, 2),
                'isComplete' => $uploadedChunks === $totalChunks,
                'chunkSize' => $chunkSize,
            ]);
        } catch (\Exception $e) {
            \Log::error("Chunk upload error: " . $e->getMessage(), [
                'uploadId' => $uploadId,
                'chunkNumber' => $request->input('chunkNumber'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Finalize upload by combining chunks
     */
    public function finalize(Request $request, $uploadId)
    {
        try {
            $tempDir = "temp/uploads/{$uploadId}";
            $metadataPath = "{$tempDir}/metadata.json";

            \Log::info("Finalizing upload: {$uploadId}");

            if (!Storage::disk('public')->exists($metadataPath)) {
                \Log::error("Upload session not found: {$uploadId}");
                return response()->json(['success' => false, 'error' => 'Upload session not found'], 404);
            }

            $metadata = json_decode(Storage::disk('public')->get($metadataPath), true);
            \Log::info("Metadata loaded", ['totalChunks' => $metadata['totalChunks'], 'fileName' => $metadata['fileName']]);

            // Count actual chunk files instead of metadata (more reliable)
            $chunkFiles = Storage::disk('public')->files($tempDir);
            $actualChunks = array_filter($chunkFiles, function($file) {
                return str_contains($file, 'chunk_');
            });
            $uploadedChunkCount = count($actualChunks);

            \Log::info("Chunks found", ['expected' => $metadata['totalChunks'], 'found' => $uploadedChunkCount, 'files' => $actualChunks]);

            // Check if all chunks are uploaded
            if ($uploadedChunkCount < $metadata['totalChunks']) {
                return response()->json([
                    'success' => false,
                    'error' => "Not all chunks uploaded. Expected {$metadata['totalChunks']}, got {$uploadedChunkCount}",
                    'expected' => $metadata['totalChunks'],
                    'received' => $uploadedChunkCount,
                ], 400);
            }

            // Initialize cloud storage service
            $storageService = new CloudStorageService();

            // Combine chunks locally first
            $finalFileName = 'lessons/videos/' . Str::uuid() . '_' . $metadata['fileName'];
            $localFinalPath = storage_path('app/public/' . $finalFileName);

            \Log::info("Final path: {$localFinalPath}");

            // Create directory if it doesn't exist
            $directory = dirname($localFinalPath);
            if (!file_exists($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    \Log::error("Failed to create directory: {$directory}");
                    return response()->json(['success' => false, 'error' => 'Failed to create video directory'], 500);
                }
            }

            // Combine chunks in order using Storage facade for consistency
            $combinedContent = '';
            $totalBytesRead = 0;

            for ($i = 0; $i < $metadata['totalChunks']; $i++) {
                $chunkRelativePath = "{$tempDir}/chunk_{$i}";

                if (!Storage::disk('public')->exists($chunkRelativePath)) {
                    \Log::error("Chunk not found: {$chunkRelativePath}");
                    return response()->json([
                        'success' => false,
                        'error' => "Chunk {$i} not found at {$chunkRelativePath}",
                    ], 500);
                }

                $chunkData = Storage::disk('public')->get($chunkRelativePath);
                $chunkSize = strlen($chunkData);
                $totalBytesRead += $chunkSize;

                \Log::info("Reading chunk {$i}", ['size' => $chunkSize, 'totalSoFar' => $totalBytesRead]);

                // Write chunk directly to file to avoid memory issues with large files
                if ($i === 0) {
                    $writeResult = file_put_contents($localFinalPath, $chunkData);
                } else {
                    $writeResult = file_put_contents($localFinalPath, $chunkData, FILE_APPEND);
                }

                if ($writeResult === false) {
                    \Log::error("Failed to write chunk {$i} to final file");
                    return response()->json([
                        'success' => false,
                        'error' => "Failed to write chunk {$i} to final file",
                    ], 500);
                }
            }

            // Verify final file was created and has content
            if (!file_exists($localFinalPath)) {
                \Log::error("Final file does not exist after combining chunks");
                return response()->json(['success' => false, 'error' => 'Failed to create final video file'], 500);
            }

            $finalFileSize = filesize($localFinalPath);
            \Log::info("Final file created", ['size' => $finalFileSize, 'expectedSize' => $totalBytesRead]);

            if ($finalFileSize === 0) {
                \Log::error("Final file is empty!");
                unlink($localFinalPath);
                return response()->json(['success' => false, 'error' => 'Final video file is empty - chunks may not have been read correctly'], 500);
            }

            // Get video duration if possible
            $duration = null;
            try {
                if (class_exists('getID3')) {
                    $getID3 = new \getID3();
                    $fileInfo = $getID3->analyze($localFinalPath);
                    if (isset($fileInfo['playtime_seconds'])) {
                        $duration = ceil($fileInfo['playtime_seconds'] / 60);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Could not get video duration: " . $e->getMessage());
            }

            $storageDisk = 'public';

            // If cloud storage is enabled, upload the combined file to cloud
            if ($storageService->isEnabled()) {
                try {
                    $cloudDisk = $storageService->getDisk();

                    // Upload to cloud storage
                    $fileStream = fopen($localFinalPath, 'r');
                    Storage::disk($cloudDisk)->put($finalFileName, $fileStream, 'public');
                    fclose($fileStream);

                    // Delete the local file after successful cloud upload
                    unlink($localFinalPath);

                    $storageDisk = $cloudDisk;
                } catch (\Exception $e) {
                    // If cloud upload fails, keep local file and log error
                    \Log::error("Cloud storage upload failed for chunked upload: " . $e->getMessage());
                }
            }

            // Clean up temporary files
            $this->cleanupTempFiles($tempDir);

            \Log::info("Upload finalized successfully", ['filePath' => $finalFileName, 'size' => $finalFileSize]);

            return response()->json([
                'success' => true,
                'filePath' => $finalFileName,
                'fileSize' => $finalFileSize,
                'duration' => $duration,
                'storage_disk' => $storageDisk,
                'message' => 'Video uploaded successfully!',
            ]);
        } catch (\Exception $e) {
            \Log::error("Finalize upload error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'uploadId' => $uploadId,
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    /**
     * Get upload status
     */
    public function status($uploadId)
    {
        $tempDir = "temp/uploads/{$uploadId}";
        $metadataPath = "{$tempDir}/metadata.json";
        
        if (!Storage::disk('public')->exists($metadataPath)) {
            return response()->json(['error' => 'Upload session not found'], 404);
        }
        
        $metadata = json_decode(Storage::disk('public')->get($metadataPath), true);
        $progress = (count($metadata['uploadedChunks']) / $metadata['totalChunks']) * 100;
        
        return response()->json([
            'uploadId' => $uploadId,
            'progress' => round($progress, 2),
            'uploadedChunks' => count($metadata['uploadedChunks']),
            'totalChunks' => $metadata['totalChunks'],
            'isComplete' => count($metadata['uploadedChunks']) === $metadata['totalChunks'],
        ]);
    }

    /**
     * Clean up temporary files
     */
    private function cleanupTempFiles($tempDir)
    {
        try {
            Storage::disk('public')->deleteDirectory($tempDir);
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning("Failed to cleanup temp directory: {$tempDir}");
        }
    }

    /**
     * Cancel upload and cleanup
     */
    public function cancel($uploadId)
    {
        $tempDir = "temp/uploads/{$uploadId}";
        $this->cleanupTempFiles($tempDir);
        
        return response()->json([
            'success' => true,
            'message' => 'Upload cancelled and cleaned up',
        ]);
    }
}