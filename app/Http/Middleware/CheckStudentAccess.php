<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Enrollment;

class CheckStudentAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Only check access for students
        if (!$user || $user->role !== 'student') {
            return $next($request);
        }
        
        // Get student's enrollment
        $enrollment = Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
            
        // If no enrollment found, block access to protected content
        if (!$enrollment) {
            if ($this->isProtectedRoute($request)) {
                return redirect()->route('dashboard')->with('error', 'You need to enroll in subjects to access this content.');
            }
            return $next($request);
        }
        
        // Check if enrollment is approved
        if ($enrollment->status !== 'approved') {
            if ($this->isProtectedRoute($request)) {
                return redirect()->route('dashboard')->with('warning', 'Your enrollment is pending approval. You will gain access once approved.');
            }
            return $next($request);
        }
        
        // Check if access has expired
        if ($enrollment->is_access_expired) {
            if ($this->isProtectedRoute($request)) {
                return redirect()->route('student.access-expired')->with('error', 'Your subject access has expired. Please extend your enrollment to continue learning.');
            }
            return $next($request);
        }
        
        // Check if in grace period (last 7 days)
        $daysRemaining = $enrollment->access_days_remaining;
        if ($daysRemaining <= 7 && $daysRemaining > 0) {
            session()->flash('warning', "Your subject access expires in {$daysRemaining} days. Consider extending to avoid interruption.");
        }
        
        return $next($request);
    }
    
    /**
     * Check if the current route requires active enrollment
     */
    private function isProtectedRoute(Request $request): bool
    {
        $currentRoute = $request->route()->getName();

        // Exclude public index routes that are accessible without enrollment
        $publicRoutes = [
            'subjects.index',
            'library.index',
            'chat.index',
        ];

        if (in_array($currentRoute, $publicRoutes)) {
            return false;
        }

        $protectedRoutes = [
            'subjects.*',
            'lessons.*',
            'library.*',
            'community.*',
            'chat.*',
            'ai.*'
        ];

        foreach ($protectedRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }

        return false;
    }
}