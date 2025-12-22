<?php

namespace App\Services;

use App\Models\AiTrainingMaterial;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIService
{
    /**
     * Generate AI response using training materials and built-in knowledge
     */
    public function generateResponse(string $message, ?int $subjectId, $user): array
    {
        $message = strtolower(trim($message));
        $subject = $subjectId ? Subject::find($subjectId) : null;
        
        try {
            // First, try to find answer in training materials
            $trainingResponse = $this->searchTrainingMaterials($message, $subject);
            
            if ($trainingResponse['confident']) {
                return $trainingResponse;
            }
            
            // Fallback to built-in knowledge
            return $this->generateBuiltInResponse($message, $subject);
            
        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            
            return [
                'message' => "I'm having trouble processing your question right now. Please try again or contact your tutor for assistance.",
                'confident' => false,
                'subject' => $subject?->name,
                'error' => true
            ];
        }
    }

    /**
     * Search through training materials for relevant content
     */
    private function searchTrainingMaterials(string $message, ?Subject $subject): array
    {
        $query = AiTrainingMaterial::where('is_processed', true)
            ->where('is_active', true);
            
        if ($subject) {
            $query->where('subject_id', $subject->id);
        }
        
        // Get relevant materials based on title and content matching
        $materials = $query->where(function ($q) use ($message) {
            $q->where('title', 'like', "%{$message}%")
              ->orWhere('content', 'like', "%{$message}%");
        })->get();
        
        // Look for keyword matches in metadata tags
        $keywordMatches = $query->where(function ($q) use ($message) {
            $keywords = $this->extractKeywords($message);
            foreach ($keywords as $keyword) {
                $q->orWhereJsonContains('metadata->tags', $keyword);
            }
        })->get();
        
        $allMaterials = $materials->merge($keywordMatches)->unique('id');
        
        if ($allMaterials->isEmpty()) {
            return [
                'message' => '',
                'confident' => false,
                'materials_found' => 0
            ];
        }
        
        // Generate response based on found materials
        $response = $this->generateMaterialBasedResponse($message, $allMaterials, $subject);
        
        return [
            'message' => $response,
            'confident' => true,
            'subject' => $subject?->name,
            'materials_found' => $allMaterials->count(),
            'source_materials' => $allMaterials->take(3)->map(fn($m) => [
                'title' => $m->title,
                'type' => $m->type
            ])->toArray()
        ];
    }

    /**
     * Generate response based on found training materials
     */
    private function generateMaterialBasedResponse(string $message, $materials, ?Subject $subject): string
    {
        $subjectName = $subject?->name ?? 'your studies';
        
        // Categorize materials by type
        $materialsByType = $materials->groupBy('type');
        
        $response = "Based on the training materials available for {$subjectName}, here's what I found:\n\n";
        
        // Check for specific question patterns
        if ($this->isDefinitionQuestion($message)) {
            $response .= $this->generateDefinitionResponse($message, $materials, $subject);
        } elseif ($this->isExampleQuestion($message)) {
            $response .= $this->generateExampleResponse($message, $materials, $subject);
        } elseif ($this->isHowToQuestion($message)) {
            $response .= $this->generateHowToResponse($message, $materials, $subject);
        } else {
            $response .= $this->generateGeneralResponse($message, $materials, $subject);
        }
        
        // Add reference to materials
        $response .= "\n\n📚 This information comes from ";
        if ($materials->count() === 1) {
            $response .= "the training material: \"{$materials->first()->title}\"";
        } else {
            $response .= "{$materials->count()} training materials in our knowledge base";
        }
        
        $response .= "\n\nFor more detailed information, I recommend reviewing the complete materials or discussing with your assigned tutor.";
        
        return $response;
    }

    /**
     * Fallback to built-in responses when no training materials match
     */
    private function generateBuiltInResponse(string $message, ?Subject $subject): array
    {
        $responses = $this->getBuiltInResponses($subject);
        
        // Check for keyword matches
        foreach ($responses as $pattern => $response) {
            if (str_contains($message, $pattern)) {
                return [
                    'message' => $response,
                    'confident' => true,
                    'subject' => $subject?->name,
                    'source' => 'built-in'
                ];
            }
        }
        
        // Check if it's a question
        if ($this->isQuestion($message)) {
            return [
                'message' => $this->getHelpfulFallback($subject),
                'confident' => false,
                'subject' => $subject?->name,
                'suggestion' => 'Would you like to book a live session with your tutor for detailed explanation?'
            ];
        }
        
        return [
            'message' => "I understand you're asking about " . ($subject?->name ?? 'your studies') . 
                        ". Could you please be more specific with your question? I'm here to help with " .
                        ($subject ? "topics related to {$subject->name}" : "your enrolled subjects") . ".",
            'confident' => false,
            'subject' => $subject?->name
        ];
    }

    /**
     * Generate helpful suggestions based on subject and available materials
     */
    public function generateSuggestions(?int $subjectId): array
    {
        if (!$subjectId) {
            return [
                "What subjects am I enrolled in?",
                "Help me prepare for exams",
                "Show me study tips"
            ];
        }

        $subject = Subject::find($subjectId);
        if (!$subject) return [];

        // Get suggestions based on available training materials
        $materials = AiTrainingMaterial::where('subject_id', $subjectId)
            ->where('is_processed', true)
            ->where('is_active', true)
            ->take(5)
            ->get();

        $suggestions = [];
        
        // Generate suggestions based on material titles
        foreach ($materials as $material) {
            if ($material->type === 'questions') {
                $suggestions[] = "Practice questions from {$material->title}";
            } elseif ($material->type === 'book') {
                $suggestions[] = "Explain concepts from {$material->title}";
            } elseif ($material->type === 'past_paper') {
                $suggestions[] = "Help with {$material->title}";
            }
        }
        
        // Add subject-specific fallback suggestions
        $fallbackSuggestions = match($subject->name) {
            'Mathematics' => [
                "Explain quadratic equations",
                "Help with trigonometry",
                "Practice algebra problems"
            ],
            'English' => [
                "Essay writing tips",
                "Grammar rules",
                "Improve vocabulary"
            ],
            'Biology' => [
                "Explain photosynthesis",
                "Cell structure differences",
                "Genetics basics"
            ],
            'Physics' => [
                "Newton's laws of motion",
                "Electricity concepts",
                "Wave properties"
            ],
            'Chemistry' => [
                "Periodic table trends",
                "Chemical reactions",
                "Acid and base properties"
            ],
            default => [
                "Key concepts in {$subject->name}",
                "Study tips for {$subject->name}",
                "Exam preparation for {$subject->name}"
            ]
        };
        
        // Merge and limit to 4 suggestions
        return array_slice(array_merge($suggestions, $fallbackSuggestions), 0, 4);
    }

    // Helper methods
    private function extractKeywords(string $message): array
    {
        $commonWords = ['the', 'is', 'at', 'which', 'on', 'what', 'how', 'why', 'when', 'where', 'can', 'you', 'help', 'me', 'with', 'about'];
        $words = explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $message));
        return array_filter($words, fn($word) => strlen($word) > 2 && !in_array(strtolower($word), $commonWords));
    }

    private function isQuestion(string $message): bool
    {
        return str_contains($message, '?') || 
               str_contains($message, 'what') || 
               str_contains($message, 'how') ||
               str_contains($message, 'why') ||
               str_contains($message, 'when') ||
               str_contains($message, 'where') ||
               str_contains($message, 'explain') ||
               str_contains($message, 'help');
    }

    private function isDefinitionQuestion(string $message): bool
    {
        return str_contains($message, 'what is') || 
               str_contains($message, 'define') ||
               str_contains($message, 'meaning of') ||
               str_contains($message, 'definition');
    }

    private function isExampleQuestion(string $message): bool
    {
        return str_contains($message, 'example') || 
               str_contains($message, 'give me') ||
               str_contains($message, 'show me');
    }

    private function isHowToQuestion(string $message): bool
    {
        return str_contains($message, 'how to') || 
               str_contains($message, 'how do') ||
               str_contains($message, 'steps to');
    }

    private function generateDefinitionResponse(string $message, $materials, ?Subject $subject): string
    {
        return "Let me explain this concept for you. According to the materials in our knowledge base, this topic is covered comprehensively. I recommend reviewing the specific sections that address your question.";
    }

    private function generateExampleResponse(string $message, $materials, ?Subject $subject): string
    {
        return "Here are some examples related to your question. Our training materials contain several practice problems and real-world applications that demonstrate these concepts clearly.";
    }

    private function generateHowToResponse(string $message, $materials, ?Subject $subject): string
    {
        return "Here's a step-by-step approach to solve this type of problem. The training materials include detailed procedures and worked examples that you can follow.";
    }

    private function generateGeneralResponse(string $message, $materials, ?Subject $subject): string
    {
        return "I found relevant information about your question in our knowledge base. The materials cover this topic with explanations, examples, and practice exercises.";
    }

    private function getHelpfulFallback(?Subject $subject): string
    {
        if (!$subject) {
            return "I'm not sure about that specific question. Could you please select a subject and provide more details? I'm here to help with your studies!";
        }

        return "That's a great question about {$subject->name}! While I don't have a specific answer in my current knowledge base, " .
               "I'd recommend reviewing your textbook or notes on this topic. Your assigned tutor would be the best " .
               "person to provide detailed explanation for this specific question.";
    }

    private function getBuiltInResponses(?Subject $subject): array
    {
        if (!$subject) {
            return [
                'hello' => 'Hello! I\'m your AI study assistant. Which subject would you like help with today?',
                'help' => 'I can help you with questions about your enrolled subjects. Please select a subject and ask me anything!',
                'exam' => 'For exam preparation, I recommend reviewing past papers and practicing regularly. Which specific topic needs attention?'
            ];
        }

        return match($subject->name) {
            'Mathematics' => [
                'algebra' => 'Algebra involves working with variables and equations. Key topics include linear equations, quadratic equations, and factorization.',
                'geometry' => 'Geometry deals with shapes, angles, and spatial relationships. Remember the basic formulas for area and perimeter.',
                'calculus' => 'Calculus is about rates of change and accumulation. Start with limits, then derivatives and integrals.',
                'trigonometry' => 'Trigonometry involves relationships between angles and sides in triangles. Remember SOHCAHTOA!',
                'equation' => 'To solve equations, isolate the variable by performing the same operations on both sides.',
            ],
            'English' => [
                'grammar' => 'Good grammar is essential for clear communication. Focus on subject-verb agreement and proper punctuation.',
                'essay' => 'For essay writing, structure your work with an introduction, body paragraphs, and conclusion.',
                'comprehension' => 'For comprehension, read the passage carefully and identify key themes and main ideas.',
                'vocabulary' => 'Build vocabulary by reading widely and learning word roots, prefixes, and suffixes.',
            ],
            'Biology' => [
                'cell' => 'Cells are the basic units of life. Plant cells have cell walls and chloroplasts, animal cells don\'t.',
                'genetics' => 'Genetics studies heredity. Remember that DNA contains genes that determine traits.',
                'photosynthesis' => 'Photosynthesis: 6CO₂ + 6H₂O + light energy → C₆H₁₂O₆ + 6O₂',
                'evolution' => 'Evolution occurs through natural selection, where favorable traits increase survival chances.',
            ],
            'Physics' => [
                'force' => 'Force equals mass times acceleration (F = ma). Forces cause changes in motion.',
                'energy' => 'Energy cannot be created or destroyed, only transferred. Kinetic energy = ½mv²',
                'wave' => 'Waves transfer energy without transferring matter. Speed = frequency × wavelength',
                'electricity' => 'Current is the flow of electric charge. Voltage is the electrical potential difference.',
            ],
            'Chemistry' => [
                'atom' => 'Atoms consist of protons, neutrons, and electrons. The periodic table organizes elements by atomic number.',
                'reaction' => 'Chemical reactions involve breaking and forming bonds. Balance equations by conserving atoms.',
                'acid' => 'Acids have pH < 7, bases have pH > 7. They neutralize each other to form salt and water.',
                'molecule' => 'Molecules are formed when atoms bond together. Covalent bonds share electrons.',
            ],
            default => [
                'concept' => 'This is an important concept in ' . $subject->name . '. Let me help you understand it better.',
                'study' => 'For effective studying in ' . $subject->name . ', create a study schedule and practice regularly.',
                'exam' => 'For ' . $subject->name . ' exams, review key concepts and practice past papers.',
            ]
        };
    }
}