<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AIChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'subject_id' => 'nullable|integer|exists:subjects,id'
        ]);

        $user = auth()->user();
        $message = $request->message;
        $subjectId = $request->subject_id;

        // Check if user has access
        if (!$this->hasAccess($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You need active enrollment to use the AI assistant.',
                'requires_enrollment' => true
            ]);
        }

        // Generate AI response using the enhanced AIService
        $aiService = new AIService();
        $response = $aiService->generateResponse($message, $subjectId, $user);

        // Save chat history (optional - implement if needed)
        // ChatHistory::create(['user_id' => $user->id, 'message' => $message, 'response' => $response]);

        return response()->json([
            'success' => true,
            'response' => $response,
            'suggestions' => $aiService->generateSuggestions($subjectId),
            'can_book_session' => !$response['confident']
        ]);
    }

    public function getSubjects()
    {
        $user = auth()->user();
        
        // Get subjects from user's enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
            
        if (!$enrollment || !$enrollment->selected_subjects) {
            return response()->json([
                'subjects' => []
            ]);
        }

        $subjects = Subject::whereIn('id', $enrollment->selected_subjects)
            ->where('is_active', true)
            ->get(['id', 'name', 'department']);

        return response()->json([
            'subjects' => $subjects
        ]);
    }

    public function bookSession(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'preferred_time' => 'required|date|after:now',
            'topic' => 'required|string|max:500',
            'description' => 'nullable|string|max:1000'
        ]);

        $user = auth()->user();
        
        // Get assigned tutor for the user
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
            
        if (!$enrollment || !$enrollment->assigned_tutor_id) {
            return response()->json([
                'success' => false,
                'message' => 'No tutor assigned to your account. Please contact support.'
            ]);
        }

        // Here you would create a live session booking
        // For now, return success message
        return response()->json([
            'success' => true,
            'message' => 'Session booking request sent to your tutor. They will contact you soon.',
            'tutor_name' => $enrollment->assignedTutor->name ?? 'Your assigned tutor',
            'tutor_email' => $enrollment->assignedTutor->email ?? null
        ]);
    }

    private function hasAccess($user): bool
    {
        if (!$user) return false;
        
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->where('status', 'approved')
            ->where('access_expires_at', '>', now())
            ->first();
            
        return $enrollment !== null;
    }

}