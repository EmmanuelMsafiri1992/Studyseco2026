<?php

namespace App\Http\Controllers;

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
        $request->validate([
            'chunk' => 'required|file',
            'chunkNumber' => 'required|integer',
            'totalChunks' => 'required|integer',
        ]);

        $tempDir = "temp/uploads/{$uploadId}";
        $metadataPath = "{$tempDir}/metadata.json";
        
        // Check if upload session exists
        if (!Storage::disk('public')->exists($metadataPath)) {
            return response()->json(['error' => 'Upload session not found'], 404);
        }
        
        $metadata = json_decode(Storage::disk('public')->get($metadataPath), true);
        $chunkNumber = $request->chunkNumber;
        $chunkPath = "{$tempDir}/chunk_{$chunkNumber}";
        
        // Store chunk
        Storage::disk('public')->putFileAs($tempDir, $request->file('chunk'), "chunk_{$chunkNumber}");
        
        // Update metadata
        if (!in_array($chunkNumber, $metadata['uploadedChunks'])) {
            $metadata['uploadedChunks'][] = $chunkNumber;
        }
        
        Storage::disk('public')->put($metadataPath, json_encode($metadata));
        
        $uploadedChunks = count($metadata['uploadedChunks']);
        $totalChunks = $metadata['totalChunks'];
        $progress = ($uploadedChunks / $totalChunks) * 100;
        
        return response()->json([
            'success' => true,
            'chunkNumber' => $chunkNumber,
            'uploadedChunks' => $uploadedChunks,
            'totalChunks' => $totalChunks,
            'progress' => round($progress, 2),
            'isComplete' => $uploadedChunks === $totalChunks,
        ]);
    }

    /**
     * Finalize upload by combining chunks
     */
    public function finalize(Request $request, $uploadId)
    {
        $tempDir = "temp/uploads/{$uploadId}";
        $metadataPath = "{$tempDir}/metadata.json";
        
        if (!Storage::disk('public')->exists($metadataPath)) {
            return response()->json(['error' => 'Upload session not found'], 404);
        }
        
        $metadata = json_decode(Storage::disk('public')->get($metadataPath), true);
        
        // Check if all chunks are uploaded
        if (count($metadata['uploadedChunks']) !== $metadata['totalChunks']) {
            return response()->json(['error' => 'Not all chunks uploaded'], 400);
        }
        
        // Combine chunks
        $finalFileName = 'lessons/videos/' . Str::uuid() . '_' . $metadata['fileName'];
        $finalPath = storage_path('app/public/' . $finalFileName);
        
        // Create directory if it doesn't exist
        $directory = dirname($finalPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Combine chunks in order
        $finalFile = fopen($finalPath, 'wb');
        
        for ($i = 0; $i < $metadata['totalChunks']; $i++) {
            $chunkPath = storage_path("app/public/{$tempDir}/chunk_{$i}");
            if (file_exists($chunkPath)) {
                $chunkData = file_get_contents($chunkPath);
                fwrite($finalFile, $chunkData);
            }
        }
        
        fclose($finalFile);
        
        // Clean up temporary files
        $this->cleanupTempFiles($tempDir);
        
        // Get video duration if possible
        $duration = null;
        try {
            if (class_exists('getID3')) {
                $getID3 = new \getID3();
                $fileInfo = $getID3->analyze($finalPath);
                if (isset($fileInfo['playtime_seconds'])) {
                    $duration = ceil($fileInfo['playtime_seconds'] / 60);
                }
            }
        } catch (\Exception $e) {
            // Continue without duration
        }
        
        return response()->json([
            'success' => true,
            'filePath' => $finalFileName,
            'fileSize' => filesize($finalPath),
            'duration' => $duration,
            'message' => 'Video uploaded successfully!',
        ]);
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