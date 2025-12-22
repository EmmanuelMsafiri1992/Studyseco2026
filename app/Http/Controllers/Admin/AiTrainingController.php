<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTrainingMaterial;
use App\Models\Subject;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AiTrainingController extends Controller
{
    public function index()
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can manage AI training materials.');
        }

        // Get training materials with subjects
        $materials = AiTrainingMaterial::with(['subject', 'uploadedBy'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Get subjects for dropdown
        $subjects = Subject::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get statistics
        $stats = [
            'total_materials' => AiTrainingMaterial::count(),
            'by_type' => AiTrainingMaterial::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_subject' => AiTrainingMaterial::with('subject')
                ->get()
                ->groupBy('subject.name')
                ->map->count()
                ->toArray(),
            'total_size' => AiTrainingMaterial::sum('file_size')
        ];

        return Inertia::render('Admin/AiTraining/Index', [
            'materials' => $materials,
            'subjects' => $subjects,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can upload training materials.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'material_type' => 'required|in:book,past_paper,notes,questions,answers',
            'description' => 'nullable|string|max:1000',
            'file' => 'required|file|mimes:pdf,doc,docx,txt|max:50240', // 50MB max
            'tags' => 'nullable|string'
        ]);

        // Upload file
        $file = $request->file('file');
        $path = $file->store('ai-training-materials', 'public');
        
        // Create training material record
        $material = AiTrainingMaterial::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'type' => $request->material_type,
            'content' => $request->description,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'tags' => $request->tags ? explode(',', trim($request->tags)) : [],
                'uploaded_at' => now()->toISOString()
            ],
            'uploaded_by' => auth()->id(),
            'is_processed' => false,
            'is_active' => true
        ]);

        // Log the upload
        AuditService::log(
            'ai_training_upload',
            $material,
            null,
            [
                'title' => $material->title,
                'subject' => $material->subject->name,
                'type' => $material->material_type,
                'file_size' => $material->file_size
            ],
            "AI training material '{$material->title}' uploaded for {$material->subject->name}"
        );

        return back()->with('success', 'Training material uploaded successfully! AI processing will begin shortly.');
    }

    public function destroy(AiTrainingMaterial $material)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Only administrators can delete training materials.');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        // Log the deletion
        AuditService::log(
            'ai_training_delete',
            $material,
            [
                'title' => $material->title,
                'subject' => $material->subject->name,
                'type' => $material->material_type
            ],
            null,
            "AI training material '{$material->title}' deleted"
        );

        $material->delete();

        return back()->with('success', 'Training material deleted successfully.');
    }

    public function process(AiTrainingMaterial $material)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        // Update processing status
        $material->update([
            'is_processed' => true,
            'metadata' => array_merge($material->metadata ?? [], [
                'processed_at' => now()->toISOString(),
                'processing_notes' => 'Material processed successfully'
            ])
        ]);

        // Log the processing
        AuditService::log(
            'ai_training_process',
            $material,
            ['status' => 'pending'],
            ['status' => 'completed'],
            "AI training material '{$material->title}' processed successfully"
        );

        return back()->with('success', 'Material processing completed successfully.');
    }

    public function bulkProcess(Request $request)
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'material_ids' => 'required|array|min:1',
            'material_ids.*' => 'integer|exists:ai_training_materials,id'
        ]);

        $materials = AiTrainingMaterial::whereIn('id', $request->material_ids)->get();
        $processedCount = 0;

        foreach ($materials as $material) {
            $material->update([
                'is_processed' => true,
                'metadata' => array_merge($material->metadata ?? [], [
                    'processed_at' => now()->toISOString(),
                    'processing_notes' => 'Material bulk processed'
                ])
            ]);

            // Log each processing
            AuditService::log(
                'ai_training_bulk_process',
                $material,
                ['status' => $material->processing_status],
                ['status' => 'completed'],
                "AI training material '{$material->title}' bulk processed"
            );

            $processedCount++;
        }

        return back()->with('success', "Successfully processed {$processedCount} training materials.");
    }

    public function getTrainingStats()
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_materials' => AiTrainingMaterial::count(),
            'processed_materials' => AiTrainingMaterial::where('is_processed', true)->count(),
            'pending_processing' => AiTrainingMaterial::where('is_processed', false)->count(),
            'active_materials' => AiTrainingMaterial::where('is_active', true)->count(),
            'by_subject' => AiTrainingMaterial::with('subject')
                ->get()
                ->groupBy('subject.name')
                ->map->count(),
            'by_type' => AiTrainingMaterial::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type'),
            'total_size_mb' => round(AiTrainingMaterial::sum('file_size') / (1024 * 1024), 2)
        ];

        return response()->json($stats);
    }
}