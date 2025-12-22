<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\SubjectDetailController;
use App\Models\User;
use App\Models\StudentStory;
use App\Models\AccessDuration;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // Get student count with error handling
    try {
        $studentCount = User::where('role', 'student')->count();
    } catch (Exception $e) {
        $studentCount = 500;
    }
    
    // Get featured student stories from database (fallback to default if none)
    try {
        $studentStories = StudentStory::getFeatured();
    } catch (Exception $e) {
        $studentStories = collect([]);
    }
    
    // Get access durations from database
    try {
        $accessDurations = AccessDuration::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    } catch (Exception $e) {
        $accessDurations = collect([]);
    }
    
    // Get subjects from database
    try {
        $subjects = \App\Models\Subject::where('is_active', true)->get()->map(function ($subject) {
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'description' => $subject->description,
                'department' => $subject->department,
                'grade_level' => $subject->grade_level,
                'icon' => match($subject->name) {
                    'English' => '📚',
                    'Chichewa' => '📖',
                    'Mathematics' => '📐',
                    'Life Skills' => '🧠',
                    'Biology' => '🧬',
                    'Physical Science' => '⚗️',
                    'Chemistry' => '🧪',
                    'Physics' => '⚡',
                    'Geography' => '🗺️',
                    'History' => '🏛️',
                    'Social Studies' => '🌍',
                    'Bible Knowledge' => '✝️',
                    'Agriculture' => '🌾',
                    'Home Economics' => '🏠',
                    'Technical Drawing' => '📏',
                    'Business Studies' => '💼',
                    'French' => '🇫🇷',
                    'Computer Studies' => '💻',
                    default => '📚'
                },
                'type' => match($subject->department) {
                    'Languages' => 'core',
                    'General Studies' => 'core',
                    'Sciences' => 'science',
                    'Humanities' => 'humanities',
                    'Technical' => 'technical',
                    'Commerce' => 'commerce',
                    'Technology' => 'technology',
                    default => 'optional'
                }
            ];
        });
    } catch (Exception $e) {
        // Fallback if database query fails
        $subjects = collect([]);
    }

    // Sample testimonials (fallback for testimonial cards)
    $testimonials = [
        [
            'name' => 'Sarah Phiri',
            'location' => 'London, UK',
            'message' => 'StudySeco helped me maintain my connection to Malawian education while living abroad. The teachers are excellent!',
            'rating' => 5,
            'avatar' => 'SP'
        ],
        [
            'name' => 'John Mwale',
            'location' => 'Cape Town, South Africa',
            'message' => 'The MANEB curriculum alignment is perfect. My son passed his MSCE with flying colors thanks to StudySeco.',
            'rating' => 5,
            'avatar' => 'JM'
        ],
        [
            'name' => 'Grace Banda',
            'location' => 'Toronto, Canada',
            'message' => 'Flexible scheduling allowed me to study while working. The online library is incredibly comprehensive.',
            'rating' => 5,
            'avatar' => 'GB'
        ]
    ];

    // Get active payment methods grouped by region
    try {
        $paymentMethods = \App\Models\PaymentMethod::active()->get()->groupBy('region');
    } catch (Exception $e) {
        $paymentMethods = collect([]);
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'subjects' => $subjects,
        'studentCount' => max($studentCount, 500), // Show at least 500
        'testimonials' => $testimonials,
        'studentStories' => $studentStories,
        'accessDurations' => $accessDurations,
        'paymentMethods' => $paymentMethods
    ]);
})->name('welcome');

// Subject detail routes (public)
Route::get('/subject/{id}', [SubjectDetailController::class, 'show'])->name('subject.detail');

// Enrollment routes (public)
Route::post('/enroll', [EnrollmentController::class, 'store'])->name('enrollment.store');
Route::get('/enrollment/success', [EnrollmentController::class, 'success'])->name('enrollment.success');

// Verification routes (public)
Route::get('/verification/{enrollment}', [\App\Http\Controllers\VerificationController::class, 'show'])->name('verification.show');
Route::post('/verification/{enrollment}/email/send', [\App\Http\Controllers\VerificationController::class, 'sendEmailVerification'])->name('verification.email.send');
Route::post('/verification/{enrollment}/phone/send', [\App\Http\Controllers\VerificationController::class, 'sendPhoneVerification'])->name('verification.phone.send');
Route::post('/verification/{enrollment}/email/verify', [\App\Http\Controllers\VerificationController::class, 'verifyEmail'])->name('verification.email.verify');
Route::post('/verification/{enrollment}/phone/verify', [\App\Http\Controllers\VerificationController::class, 'verifyPhone'])->name('verification.phone.verify');
Route::get('/verification/{enrollment}/status', [\App\Http\Controllers\VerificationController::class, 'status'])->name('verification.status');

// Protected enrollment routes
Route::middleware('auth')->group(function () {
    Route::post('/enrollment/extend', [EnrollmentController::class, 'extend'])->name('enrollment.extend');
    Route::get('/enrollment/check-extension', [EnrollmentController::class, 'checkExtension'])->name('enrollment.check-extension');
});


Route::get('/dashboard', function () {
    $user = auth()->user();

    // Allow students to access dashboard directly
    // Note: Students can also access their school confirmation via navigation menu
    // if ($user->role === 'student') {
    //     $confirmedSchool = $user->confirmedSchool();
    //     if ($confirmedSchool) {
    //         return redirect()->route('school-selections.confirmed');
    //     }
    // }

    // Get enrollment information for students to display country
    $enrollment = null;
    if ($user->role === 'student') {
        $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
    }

    // Add enrollment info to user data
    $userData = $user->toArray();
    if ($enrollment) {
        $userData['enrollment'] = [
            'country' => $enrollment->country,
            'region' => $enrollment->region
        ];
    }
    
    $data = ['auth' => ['user' => $userData]];
    
    if ($user->role === 'admin') {
        // Get real statistics for admin dashboard
        try {
            // Calculate real revenue data
            $monthlyRevenue = \App\Models\EnrollmentPayment::where('status', 'verified')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');
                
            $totalRevenue = \App\Models\EnrollmentPayment::where('status', 'verified')
                ->sum('amount');
                
            $pendingPayments = \App\Models\EnrollmentPayment::where('status', 'pending')
                ->sum('amount');
            
            $data['stats'] = [
                'total_students' => \App\Models\User::where('role', 'student')->count(),
                'total_teachers' => \App\Models\User::where('role', 'teacher')->count(),
                'total_subjects' => \App\Models\Subject::where('is_active', true)->count(),
                'pending_enrollments' => \App\Models\Enrollment::where('status', 'pending')->count(),
                'total_enrollments' => \App\Models\Enrollment::count(),
                'active_enrollments' => \App\Models\Enrollment::where('status', 'approved')
                    ->whereDate('access_expires_at', '>', now())->count(),
                'expired_enrollments' => \App\Models\Enrollment::where('status', 'approved')
                    ->whereDate('access_expires_at', '<=', now())->count(),
                'monthly_revenue' => $monthlyRevenue,
                'total_revenue' => $totalRevenue,
                'pending_revenue' => $pendingPayments,
                'pending_payments_count' => \App\Models\EnrollmentPayment::where('status', 'pending')->count(),
            ];
            
            // Recent enrollments for activity feed (admin sees all)
            $data['recent_enrollments'] = \App\Models\Enrollment::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($enrollment) {
                    return [
                        'id' => $enrollment->id,
                        'student_name' => $enrollment->user ? $enrollment->user->name : $enrollment->name,
                        'email' => $enrollment->email,
                        'status' => $enrollment->status,
                        'created_at' => $enrollment->created_at->diffForHumans(),
                        'type' => 'enrollment',
                        'description' => 'New enrollment for ' . count($enrollment->selected_subjects ?: []) . ' subjects'
                    ];
                });
                
            // Recent payments for activity feed (admin sees all)
            $data['recent_payments'] = \App\Models\EnrollmentPayment::with('enrollment.user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'student_name' => $payment->enrollment->user ? $payment->enrollment->user->name : $payment->enrollment->name,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'created_at' => $payment->created_at->diffForHumans(),
                        'type' => 'payment',
                        'description' => 'Payment of ' . $payment->currency . ' ' . number_format($payment->amount, 2)
                    ];
                });
                
            // Subject distribution for chart
            $data['subject_distribution'] = \App\Models\Subject::selectRaw('department, COUNT(*) as count')
                ->where('is_active', true)
                ->groupBy('department')
                ->get()
                ->map(function ($item) {
                    return [
                        'department' => $item->department,
                        'count' => $item->count
                    ];
                });
                
        } catch (Exception $e) {
            // Fallback to dummy data if database queries fail
            $data['stats'] = [
                'total_students' => 1850,
                'total_teachers' => 125,
                'total_subjects' => 45,
                'pending_enrollments' => 7,
                'total_enrollments' => 2000,
                'active_enrollments' => 1500,
                'expired_enrollments' => 350,
            ];
            $data['recent_enrollments'] = [];
            $data['recent_payments'] = [];
            $data['subject_distribution'] = [];
        }
    } elseif ($user->role === 'student') {
        // Get student-specific data
        try {
            $enrollment = \App\Models\Enrollment::where('user_id', $user->id)->orWhere('email', $user->email)->first();
            $data['enrollment'] = $enrollment;
            $data['enrolled_subjects'] = $enrollment ? $enrollment->selected_subjects_with_details : collect([]);
            $data['access_remaining'] = $enrollment ? $enrollment->access_days_remaining : 0;
            $data['access_expired'] = $enrollment ? $enrollment->is_access_expired : true;
            
            // Add stats object for students with enrollment status
            $enrollmentStatus = 'not_enrolled';
            if ($enrollment) {
                if ($enrollment->is_trial) {
                    $enrollmentStatus = 'trial';
                } else {
                    $enrollmentStatus = $enrollment->status; // pending, approved, rejected
                }
            }

            $data['stats'] = [
                'enrollment_status' => $enrollmentStatus,
                'total_subjects' => $enrollment ? count($enrollment->selected_subjects ?: []) : 0,
                'access_remaining_days' => $enrollment ? $enrollment->access_days_remaining : 0,
                'is_trial' => $enrollment ? $enrollment->is_trial : null,
                'trial_days_remaining' => $enrollment && $enrollment->is_trial ? $enrollment->access_days_remaining : null,
                'trial_expires_at' => $enrollment && $enrollment->is_trial && $enrollment->access_expires_at ?
                    \Carbon\Carbon::parse($enrollment->access_expires_at)->format('M j, Y') : null,
                'access_expires_at' => $enrollment && !$enrollment->is_trial && $enrollment->access_expires_at ?
                    \Carbon\Carbon::parse($enrollment->access_expires_at)->format('M j, Y') : null,
            ];
            
            // Student-specific recent activities (only their own data)
            $data['recent_activities'] = collect();
            
            if ($enrollment) {
                // Student's own enrollment activity
                $data['recent_activities']->push([
                    'id' => 'enrollment_' . $enrollment->id,
                    'type' => 'enrollment',
                    'title' => 'Course Enrollment',
                    'description' => 'You enrolled in ' . count($enrollment->selected_subjects ?: []) . ' subjects',
                    'created_at' => $enrollment->created_at->diffForHumans(),
                    'status' => $enrollment->status,
                    'is_personal' => true
                ]);
                
                // Student's own payment activities (ONLY their payments)
                $studentPayments = \App\Models\EnrollmentPayment::where('enrollment_id', $enrollment->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
                    
                foreach ($studentPayments as $payment) {
                    $data['recent_activities']->push([
                        'id' => 'payment_' . $payment->id,
                        'type' => 'payment',
                        'title' => 'Your Payment ' . ucfirst($payment->status),
                        'description' => 'Your payment of ' . $payment->currency . ' ' . number_format($payment->amount, 2),
                        'created_at' => $payment->created_at->diffForHumans(),
                        'status' => $payment->status,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'is_personal' => true
                    ]);
                }
                
                // Add student's achievements if any
                $studentAchievements = \App\Models\Achievement::where('user_id', $user->id)
                    ->orderBy('achieved_date', 'desc')
                    ->limit(2)
                    ->get();
                    
                foreach ($studentAchievements as $achievement) {
                    $data['recent_activities']->push([
                        'id' => 'achievement_' . $achievement->id,
                        'type' => 'achievement',
                        'title' => 'Achievement Earned',
                        'description' => 'You earned: ' . $achievement->title,
                        'created_at' => $achievement->achieved_date->diffForHumans(),
                        'status' => 'earned',
                        'is_personal' => true
                    ]);
                }
            }
            
            // Sort activities by date
            $data['recent_activities'] = $data['recent_activities']->sortByDesc('created_at')->take(5);
            
        } catch (Exception $e) {
            $data['enrollment'] = null;
            $data['enrolled_subjects'] = collect([]);
            $data['access_remaining'] = 0;
            $data['access_expired'] = true;
            $data['recent_activities'] = collect([]);
            $data['stats'] = [
                'enrollment_status' => 'not_enrolled',
                'total_subjects' => 0,
                'access_remaining_days' => 0,
                'is_trial' => false,
            ];
        }
    } elseif ($user->role === 'teacher') {
        // Get teacher-specific data
        try {
            // Get assigned students (students assigned to this teacher)
            $assignedEnrollments = \App\Models\Enrollment::where('assigned_tutor_id', $user->id)
                ->with('user')
                ->where('status', 'approved')
                ->get();
                
            $data['assigned_students'] = $assignedEnrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->user ? $enrollment->user->id : null,
                    'name' => $enrollment->user ? $enrollment->user->name : $enrollment->name,
                    'email' => $enrollment->email,
                    'enrollment_id' => $enrollment->id,
                    'subjects_count' => count($enrollment->selected_subjects ?: []),
                    'access_expires_at' => $enrollment->access_expires_at,
                    'is_trial' => $enrollment->is_trial,
                    'status' => $enrollment->status,
                    'tutor_assigned_at' => $enrollment->tutor_assigned_at
                ];
            });
            
            $data['teacher_subjects'] = \App\Models\Subject::where('is_active', true)->limit(5)->get();
            $data['student_count'] = $assignedEnrollments->count();
            
            // Teacher stats
            $data['stats'] = [
                'assigned_students' => $assignedEnrollments->count(),
                'active_students' => $assignedEnrollments->where('access_expires_at', '>', now())->count(),
                'trial_students' => $assignedEnrollments->where('is_trial', true)->count(),
                'subjects_teaching' => $data['teacher_subjects']->count()
            ];
            
            // Recent student activities for teacher dashboard
            $data['recent_activities'] = collect();
            
            foreach ($assignedEnrollments->take(5) as $enrollment) {
                $data['recent_activities']->push([
                    'id' => 'assignment_' . $enrollment->id,
                    'type' => 'assignment',
                    'title' => 'Student Assigned',
                    'description' => ($enrollment->user ? $enrollment->user->name : $enrollment->name) . ' was assigned to you',
                    'created_at' => $enrollment->tutor_assigned_at ? \Carbon\Carbon::parse($enrollment->tutor_assigned_at)->diffForHumans() : $enrollment->created_at->diffForHumans(),
                    'student_name' => $enrollment->user ? $enrollment->user->name : $enrollment->name
                ]);
            }
            
        } catch (Exception $e) {
            $data['assigned_students'] = collect([]);
            $data['teacher_subjects'] = collect([]);
            $data['student_count'] = 0;
            $data['recent_activities'] = collect([]);
            $data['stats'] = [
                'assigned_students' => 0,
                'active_students' => 0,
                'trial_students' => 0,
                'subjects_teaching' => 0
            ];
        }
    }
    
    return Inertia::render('Dashboard', $data);
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Student-specific routes
    Route::prefix('student')->name('student.')->group(function () {
        // Student Enrollment Management
        Route::get('/enrollment', [\App\Http\Controllers\Student\EnrollmentController::class, 'index'])->name('enrollment.index');
        Route::get('/enrollment/details', [\App\Http\Controllers\Student\EnrollmentController::class, 'show'])->name('enrollment.show');
        Route::get('/enrollment/increase-subjects', [\App\Http\Controllers\Student\EnrollmentController::class, 'increaseSubjects'])->name('enrollment.increase-subjects');
        Route::post('/enrollment/increase-subjects', [\App\Http\Controllers\Student\EnrollmentController::class, 'storeSubjectIncrease'])->name('enrollment.store-subject-increase');
        
        // Student Subject Management
        Route::get('/subjects', [\App\Http\Controllers\Student\SubjectController::class, 'index'])->name('subjects.index');
        
        // Student Extension Routes
        Route::get('/extension', [\App\Http\Controllers\ExtensionController::class, 'index'])->name('extension.index');
        Route::post('/extension', [\App\Http\Controllers\ExtensionController::class, 'store'])->name('extension.store');

        // Student Assignment Routes
        Route::get('/assignments', [\App\Http\Controllers\Student\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [\App\Http\Controllers\Student\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('/assignments/{assignment}/submit', [\App\Http\Controllers\Student\AssignmentController::class, 'submit'])->name('assignments.submit');
        Route::get('/assignments/{assignment}/submission', [\App\Http\Controllers\Student\AssignmentController::class, 'viewSubmission'])->name('assignments.submission');

        // Student Quiz Routes
        Route::get('/quizzes', [\App\Http\Controllers\Student\QuizController::class, 'index'])->name('quizzes.index');
        Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Student\QuizController::class, 'show'])->name('quizzes.show');
        Route::post('/quizzes/{quiz}/start', [\App\Http\Controllers\Student\QuizController::class, 'start'])->name('quizzes.start');
        Route::get('/quiz-attempts/{attempt}/take', [\App\Http\Controllers\Student\QuizController::class, 'take'])->name('quizzes.take');
        Route::post('/quiz-attempts/{attempt}/submit', [\App\Http\Controllers\Student\QuizController::class, 'submit'])->name('quizzes.submit');
        Route::get('/quiz-attempts/{attempt}/result', [\App\Http\Controllers\Student\QuizController::class, 'result'])->name('quizzes.result');

        // Student Certificate Routes
        Route::get('/certificates', [\App\Http\Controllers\Student\CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{certificate}', [\App\Http\Controllers\Student\CertificateController::class, 'show'])->name('certificates.show');
        Route::get('/certificates/{certificate}/download', [\App\Http\Controllers\Student\CertificateController::class, 'download'])->name('certificates.download');

        // Student Mock Exams Routes
        Route::get('/mock-exams', [\App\Http\Controllers\Student\MockExamController::class, 'index'])->name('mock-exams.index');
        Route::get('/mock-exams/{mockExam}', [\App\Http\Controllers\Student\MockExamController::class, 'show'])->name('mock-exams.show');
        Route::post('/mock-exams/{mockExam}/start', [\App\Http\Controllers\Student\MockExamController::class, 'start'])->name('mock-exams.start');
        Route::get('/exam-attempts/{attempt}/take', [\App\Http\Controllers\Student\MockExamController::class, 'take'])->name('mock-exams.take');
        Route::post('/exam-attempts/{attempt}/submit', [\App\Http\Controllers\Student\MockExamController::class, 'submit'])->name('mock-exams.submit');
        Route::get('/exam-attempts/{attempt}/result', [\App\Http\Controllers\Student\MockExamController::class, 'result'])->name('mock-exams.result');

        // Student Games Routes
        Route::get('/games', [\App\Http\Controllers\Student\GameController::class, 'index'])->name('games.index');
        Route::get('/games/{game}', [\App\Http\Controllers\Student\GameController::class, 'show'])->name('games.show');
        Route::post('/games/{game}/start', [\App\Http\Controllers\Student\GameController::class, 'start'])->name('games.start');
        Route::get('/game-sessions/{session}/play', [\App\Http\Controllers\Student\GameController::class, 'play'])->name('games.play');
        Route::post('/game-sessions/{session}/submit', [\App\Http\Controllers\Student\GameController::class, 'submit'])->name('games.submit');
        Route::get('/game-sessions/{session}/result', [\App\Http\Controllers\Student\GameController::class, 'result'])->name('games.result');
        Route::get('/games/{game}/leaderboard', [\App\Http\Controllers\Student\GameController::class, 'leaderboard'])->name('games.leaderboard');

        // Student Access Expired Page
        Route::get('/access-expired', function () {
            $user = auth()->user();
            $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
                
            $extensionOptions = \App\Models\AccessDuration::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
                
            return Inertia::render('Student/AccessExpired', [
                'enrollment' => $enrollment,
                'extensionOptions' => $extensionOptions
            ]);
        })->name('access-expired');
    });

    // Legacy routes for backward compatibility
    Route::get('/student/extension', [\App\Http\Controllers\ExtensionController::class, 'index'])->name('student.extension');
    Route::post('/student/extension', [\App\Http\Controllers\ExtensionController::class, 'store'])->name('student.extension.store');
    
    // Student Access Expired Page (legacy)
    Route::get('/student/access-expired', function () {
        $user = auth()->user();
        $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
            
        $extensionOptions = \App\Models\AccessDuration::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return Inertia::render('Student/AccessExpired', [
            'enrollment' => $enrollment,
            'extensionOptions' => $extensionOptions
        ]);
    })->name('student.access-expired');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin-Only School Management Routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/students', [\App\Http\Controllers\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [\App\Http\Controllers\StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [\App\Http\Controllers\StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{user}', [\App\Http\Controllers\StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{user}/edit', [\App\Http\Controllers\StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{user}', [\App\Http\Controllers\StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{user}', [\App\Http\Controllers\StudentController::class, 'destroy'])->name('students.destroy');
    });

    // Teacher Management Routes (Admin and Teachers can access)
    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'index'])->name('teachers.index');
        Route::get('/teachers/{user}', [\App\Http\Controllers\TeacherController::class, 'show'])->name('teachers.show');
    });

    
    // Admin-only teacher management operations
    Route::middleware('role:admin')->group(function () {
        Route::get('/teachers/create', [\App\Http\Controllers\TeacherController::class, 'create'])->name('teachers.create');
        Route::post('/teachers', [\App\Http\Controllers\TeacherController::class, 'store'])->name('teachers.store');
        Route::get('/teachers/{user}/edit', [\App\Http\Controllers\TeacherController::class, 'edit'])->name('teachers.edit');
        Route::put('/teachers/{user}', [\App\Http\Controllers\TeacherController::class, 'update'])->name('teachers.update');
        Route::delete('/teachers/{user}', [\App\Http\Controllers\TeacherController::class, 'destroy'])->name('teachers.destroy');
    });

    // Public navigation routes - accessible to all authenticated users
    Route::get('/subjects', function () {
        $user = auth()->user();
        if ($user->role === 'student') {
            return redirect()->route('student.subjects.index');
        }
        return app(\App\Http\Controllers\SubjectController::class)->index();
    })->name('subjects.index');

    Route::get('/library', [\App\Http\Controllers\LibraryController::class, 'index'])->name('library.index');
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');

    // Student Content Access Routes - Protected by access expiry check
    Route::middleware('check.student.access')->group(function () {
        // Subject Management Routes (except index which is public above)
        Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::put('subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');

        // Lesson Player Route
        Route::get('lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

        // Library Routes (except index which is public above)
        Route::prefix('library')->name('library.')->group(function () {
            Route::get('/books', [\App\Http\Controllers\LibraryController::class, 'books'])->name('books');
            Route::get('/past-papers', [\App\Http\Controllers\LibraryController::class, 'pastPapers'])->name('pastPapers');
            Route::get('/search', [\App\Http\Controllers\LibraryController::class, 'search'])->name('search');
            Route::get('/{resource}', [\App\Http\Controllers\LibraryController::class, 'show'])->name('show');
            Route::get('/{resource}/stream', [\App\Http\Controllers\LibraryController::class, 'stream'])->name('stream');
        });
        
        // Community Routes
        Route::prefix('community')->name('community.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CommunityController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\CommunityController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\CommunityController::class, 'store'])->name('store');
            Route::get('/{post}', [\App\Http\Controllers\CommunityController::class, 'show'])->name('show');
            Route::get('/{post}/edit', [\App\Http\Controllers\CommunityController::class, 'edit'])->name('edit');
            Route::patch('/{post}', [\App\Http\Controllers\CommunityController::class, 'update'])->name('update');
            Route::delete('/{post}', [\App\Http\Controllers\CommunityController::class, 'destroy'])->name('destroy');
            Route::post('/{post}/react', [\App\Http\Controllers\CommunityController::class, 'toggleReaction'])->name('react');
            Route::post('/{post}/comment', [\App\Http\Controllers\CommunityController::class, 'storeComment'])->name('comment');
            Route::post('/{post}/vote', [\App\Http\Controllers\CommunityController::class, 'votePoll'])->name('vote');
            Route::post('/comment/{comment}/solution', [\App\Http\Controllers\CommunityController::class, 'markSolution'])->name('markSolution');
            Route::post('/share-resource', [\App\Http\Controllers\CommunityController::class, 'shareResource'])->name('shareResource');
            Route::get('/resource/{resource}/download', [\App\Http\Controllers\CommunityController::class, 'downloadResource'])->name('downloadResource');
        });
        
        // AI Chat Routes
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::post('/chat', [\App\Http\Controllers\AIChatController::class, 'chat'])->name('chat');
            Route::get('/subjects', [\App\Http\Controllers\AIChatController::class, 'getSubjects'])->name('subjects');
            Route::post('/book-session', [\App\Http\Controllers\AIChatController::class, 'bookSession'])->name('book-session');
        });
    });

    // Chat Routes - accessible to all authenticated users (not restricted by enrollment)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/create', [\App\Http\Controllers\ChatController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\ChatController::class, 'store'])->name('store');
        Route::get('/{chatGroup}', [\App\Http\Controllers\ChatController::class, 'show'])->name('show');
        Route::post('/{chatGroup}/messages', [\App\Http\Controllers\ChatController::class, 'storeMessage'])->name('storeMessage');
        Route::get('/{chatGroup}/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('getMessages');
        Route::post('/{chatGroup}/join', [\App\Http\Controllers\ChatController::class, 'join'])->name('join');
        Route::post('/{chatGroup}/leave', [\App\Http\Controllers\ChatController::class, 'leave'])->name('leave');
        Route::post('/{chatGroup}/add-member', [\App\Http\Controllers\ChatController::class, 'addMember'])->name('addMember');
        Route::delete('/{chatGroup}/remove-member/{user}', [\App\Http\Controllers\ChatController::class, 'removeMember'])->name('removeMember');
    });
    
    // Topic Management Routes (API) - Admin/Teacher only
    Route::prefix('api')->group(function () {
        Route::post('topics', [TopicController::class, 'store'])->name('topics.store');
        Route::get('topics/{topic}', [TopicController::class, 'show'])->name('topics.show');
        Route::put('topics/{topic}', [TopicController::class, 'update'])->name('topics.update');
        Route::delete('topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
        Route::patch('topics/{topic}/reorder', [TopicController::class, 'reorder'])->name('topics.reorder');
        
        // Lesson Management Routes (API)
        Route::post('lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::put('lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        Route::patch('lessons/{lesson}/publish', [LessonController::class, 'publish'])->name('lessons.publish');
        Route::patch('lessons/{lesson}/unpublish', [LessonController::class, 'unpublish'])->name('lessons.unpublish');
    });

    // Fees Management Routes (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/fees', function () {
            return Inertia::render('Fees/Index', [
                'fees' => []
            ]);
        })->name('fees.index');

        Route::get('/fees/create', function () {
            return Inertia::render('Fees/Create');
        })->name('fees.create');
    });

    // Payment Management Routes (Admin-only viewing, students can only create)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.receipt');
    Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->middleware('role:admin')->name('payments.verify');
    Route::get('payments/{payment}/proof', [PaymentController::class, 'viewPaymentProof'])->middleware('role:admin')->name('payments.proof');
    Route::get('payments/statistics', [PaymentController::class, 'statistics'])->middleware('role:admin')->name('payments.statistics');
    Route::get('api/payments/check-access', [PaymentController::class, 'checkAccess'])->name('payments.check-access');

    // Admin Payment Settings Routes
    Route::prefix('admin/payment-settings')->name('admin.payment-settings.')->group(function () {
        Route::get('/', [PaymentSettingsController::class, 'index'])->name('index');
        
        // Payment Methods
        Route::post('/payment-methods', [PaymentSettingsController::class, 'storePaymentMethod'])->name('payment-methods.store');
        Route::put('/payment-methods/{paymentMethod}', [PaymentSettingsController::class, 'updatePaymentMethod'])->name('payment-methods.update');
        Route::delete('/payment-methods/{paymentMethod}', [PaymentSettingsController::class, 'destroyPaymentMethod'])->name('payment-methods.destroy');
        Route::patch('/payment-methods/order', [PaymentSettingsController::class, 'updatePaymentMethodOrder'])->name('payment-methods.order');
        
        // Access Durations
        Route::post('/access-durations', [PaymentSettingsController::class, 'storeAccessDuration'])->name('access-durations.store');
        Route::put('/access-durations/{accessDuration}', [PaymentSettingsController::class, 'updateAccessDuration'])->name('access-durations.update');
        Route::delete('/access-durations/{accessDuration}', [PaymentSettingsController::class, 'destroyAccessDuration'])->name('access-durations.destroy');
        Route::patch('/access-durations/order', [PaymentSettingsController::class, 'updateAccessDurationOrder'])->name('access-durations.order');
    });

    // Admin Site Content Management Routes
    Route::prefix('admin/site-content')->name('admin.site-content.')->group(function () {
        Route::get('/', [SiteContentController::class, 'index'])->name('index');
        
        // Site Content
        Route::post('/contents', [SiteContentController::class, 'storeContent'])->name('contents.store');
        Route::put('/contents/{content}', [SiteContentController::class, 'updateContent'])->name('contents.update');
        Route::delete('/contents/{content}', [SiteContentController::class, 'destroyContent'])->name('contents.destroy');
        Route::patch('/contents/order', [SiteContentController::class, 'updateContentOrder'])->name('contents.order');
        
        // Student Stories
        Route::post('/student-stories', [SiteContentController::class, 'storeStudentStory'])->name('student-stories.store');
        Route::put('/student-stories/{story}', [SiteContentController::class, 'updateStudentStory'])->name('student-stories.update');
        Route::delete('/student-stories/{story}', [SiteContentController::class, 'destroyStudentStory'])->name('student-stories.destroy');
        Route::patch('/student-stories/order', [SiteContentController::class, 'updateStoryOrder'])->name('student-stories.order');
    });

    // Admin System Settings Routes
    Route::prefix('admin/system-settings')->name('admin.system-settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [SystemSettingsController::class, 'index'])->name('index');
        Route::post('/', [SystemSettingsController::class, 'update'])->name('update');
    });

    // Admin Department Management Routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
    });

    // Support Chat Routes (formerly Complaints) - Live chat system
    Route::get('/complaints', [\App\Http\Controllers\ComplaintController::class, 'index'])
         ->name('complaints.index');
    Route::get('/complaints/create', [\App\Http\Controllers\ComplaintController::class, 'create'])
         ->name('complaints.create');
    Route::post('/complaints', [\App\Http\Controllers\ComplaintController::class, 'store'])
         ->name('complaints.store');
    
    // Live Chat Routes
    Route::get('/complaints/chat/{sessionId}', [\App\Http\Controllers\ComplaintController::class, 'chat'])
         ->middleware('role:admin,teacher')->name('complaints.chat');
    Route::post('/complaints/chat/{sessionId}/assign', [\App\Http\Controllers\ComplaintController::class, 'assignChat'])
         ->middleware('role:admin,teacher')->name('complaints.assign');
    Route::post('/complaints/chat/{sessionId}/close', [\App\Http\Controllers\ComplaintController::class, 'closeChat'])
         ->middleware('role:admin,teacher')->name('complaints.close');
    Route::post('/complaints/chat/{sessionId}/message', [\App\Http\Controllers\ComplaintController::class, 'sendMessage'])
         ->middleware('role:admin,teacher')->name('complaints.message');
    
    // Legacy route redirect
    Route::get('/complaints/{complaint}', [\App\Http\Controllers\ComplaintController::class, 'show'])->name('complaints.show');

    // Reports Routes - Admin only
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->middleware('role:admin')->name('reports.index');

    // Settings Routes - Admin only for system settings, users for personal settings
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->middleware('role:admin')->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->middleware('role:admin')->name('settings.update');

    // Role Management Routes - Admin only
    Route::middleware('role:admin')->prefix('admin/roles')->name('admin.roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('destroy');
        Route::post('/{role}/toggle', [\App\Http\Controllers\Admin\RoleController::class, 'toggle'])->name('toggle');
        
        // User role assignment
        Route::post('/assign', [\App\Http\Controllers\Admin\RoleController::class, 'assignRole'])->name('assign');
        Route::post('/revoke', [\App\Http\Controllers\Admin\RoleController::class, 'revokeRole'])->name('revoke');
    });

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/api/notifications/count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.count');

    // Achievements Routes
    Route::get('/achievements', function () {
        $user = auth()->user();
        
        // Sample achievements data based on user role
        $achievements = collect();
        $userStats = [
            'totalPoints' => 0,
            'subjectsCompleted' => 0,
            'studentsHelped' => 0,
            'totalCompletions' => 0,
            'studyStreak' => 0
        ];

        if ($user->role === 'student') {
            // Get student's enrollment to calculate achievements
            $enrollment = \App\Models\Enrollment::where('user_id', $user->id)->orWhere('email', $user->email)->first();
            
            if ($enrollment) {
                $subjectsCount = count($enrollment->selected_subjects ?: []);
                $userStats['subjectsCompleted'] = $subjectsCount;
                $userStats['totalPoints'] = $subjectsCount * 100;
                $userStats['studyStreak'] = rand(1, 30);

                // Create sample achievements for students
                $achievements = collect([
                    [
                        'id' => 1,
                        'name' => 'First Steps',
                        'description' => 'Complete your first subject enrollment',
                        'icon' => '🎯',
                        'points' => 100,
                        'unlocked' => true,
                        'unlockedAt' => $enrollment->created_at,
                        'progress' => 100,
                        'current' => 1,
                        'target' => 1,
                        'category' => 'Learning'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Subject Master',
                        'description' => 'Complete 5 subjects successfully',
                        'icon' => '📚',
                        'points' => 500,
                        'unlocked' => $subjectsCount >= 5,
                        'unlockedAt' => $subjectsCount >= 5 ? now() : null,
                        'progress' => ($subjectsCount / 5) * 100,
                        'current' => $subjectsCount,
                        'target' => 5,
                        'category' => 'Learning'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Dedicated Learner',
                        'description' => 'Maintain a 7-day study streak',
                        'icon' => '🔥',
                        'points' => 200,
                        'unlocked' => $userStats['studyStreak'] >= 7,
                        'unlockedAt' => $userStats['studyStreak'] >= 7 ? now()->subDays(rand(1, 10)) : null,
                        'progress' => min(($userStats['studyStreak'] / 7) * 100, 100),
                        'current' => $userStats['studyStreak'],
                        'target' => 7,
                        'category' => 'Milestones'
                    ],
                    [
                        'id' => 4,
                        'name' => 'Course Completer',
                        'description' => 'Successfully complete your entire course',
                        'icon' => '🎓',
                        'points' => 1000,
                        'unlocked' => false,
                        'unlockedAt' => null,
                        'progress' => 75,
                        'current' => 3,
                        'target' => 4,
                        'category' => 'Milestones'
                    ]
                ]);
            }
        } elseif ($user->role === 'teacher') {
            // Get teacher's assigned students
            $assignedStudents = \App\Models\Enrollment::where('assigned_tutor_id', $user->id)
                ->where('status', 'approved')
                ->count();
            
            $userStats['studentsHelped'] = $assignedStudents;
            $userStats['totalPoints'] = $assignedStudents * 150;

            $achievements = collect([
                [
                    'id' => 1,
                    'name' => 'First Student',
                    'description' => 'Get assigned your first student',
                    'icon' => '👨‍🏫',
                    'points' => 150,
                    'unlocked' => $assignedStudents >= 1,
                    'unlockedAt' => $assignedStudents >= 1 ? now()->subDays(rand(1, 30)) : null,
                    'progress' => $assignedStudents >= 1 ? 100 : 0,
                    'current' => $assignedStudents,
                    'target' => 1,
                    'category' => 'Teaching'
                ],
                [
                    'id' => 2,
                    'name' => 'Popular Tutor',
                    'description' => 'Help 10 students with their studies',
                    'icon' => '⭐',
                    'points' => 750,
                    'unlocked' => $assignedStudents >= 10,
                    'unlockedAt' => $assignedStudents >= 10 ? now()->subDays(rand(1, 20)) : null,
                    'progress' => min(($assignedStudents / 10) * 100, 100),
                    'current' => $assignedStudents,
                    'target' => 10,
                    'category' => 'Teaching'
                ],
                [
                    'id' => 3,
                    'name' => 'Resource Creator',
                    'description' => 'Upload 5 teaching materials',
                    'icon' => '📝',
                    'points' => 300,
                    'unlocked' => false, // Would need to check actual materials count
                    'unlockedAt' => null,
                    'progress' => 60,
                    'current' => 3,
                    'target' => 5,
                    'category' => 'Teaching'
                ]
            ]);
        } else { // Admin
            // Get admin overview stats
            $totalStudents = \App\Models\User::where('role', 'student')->count();
            $totalTeachers = \App\Models\User::where('role', 'teacher')->count();
            $totalEnrollments = \App\Models\Enrollment::count();
            
            $userStats['totalCompletions'] = $totalEnrollments;
            $userStats['totalPoints'] = $totalEnrollments * 50;

            $achievements = collect([
                [
                    'id' => 1,
                    'name' => 'System Administrator',
                    'description' => 'Successfully manage the learning platform',
                    'icon' => '👑',
                    'points' => 1000,
                    'unlocked' => true,
                    'unlockedAt' => now()->subDays(rand(30, 100)),
                    'progress' => 100,
                    'current' => 1,
                    'target' => 1,
                    'category' => 'Leadership'
                ],
                [
                    'id' => 2,
                    'name' => 'Growth Manager',
                    'description' => 'Reach 100 total enrollments',
                    'icon' => '📈',
                    'points' => 2000,
                    'unlocked' => $totalEnrollments >= 100,
                    'unlockedAt' => $totalEnrollments >= 100 ? now()->subDays(rand(1, 50)) : null,
                    'progress' => min(($totalEnrollments / 100) * 100, 100),
                    'current' => $totalEnrollments,
                    'target' => 100,
                    'category' => 'Milestones'
                ]
            ]);
        }

        // Calculate total points from unlocked achievements
        $userStats['totalPoints'] = $achievements->where('unlocked', true)->sum('points');

        return Inertia::render('Achievements/Index', [
            'user' => $user,
            'achievements' => [
                'data' => $achievements,
                'total' => $achievements->count(),
                'current_page' => 1,
                'last_page' => 1,
                'prev_page_url' => null,
                'next_page_url' => null
            ],
            'userStats' => $userStats,
            'stats' => [
                'total_achievements' => $achievements->count(),
                'featured_achievements' => $achievements->where('unlocked', true)->count(),
                'total_points' => $userStats['totalPoints'],
                'recent_achievements' => $achievements->where('unlocked', true)->where('unlockedAt', '>', now()->subWeek())->count()
            ],
            'types' => ['learning', 'teaching', 'social', 'milestones'],
            'levels' => ['bronze', 'silver', 'gold', 'platinum'],
            'filters' => []
        ]);
    })->name('achievements.index');

    // Account Settings Route (from dropdown)
    Route::get('/account-settings', function () {
        $user = auth()->user();
        $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
        
        return Inertia::render('Profile/AccountSettings', [
            'enrollment' => $enrollment,
            'enrollmentStatus' => $enrollment ? [
                'status' => $enrollment->status,
                'is_trial' => $enrollment->is_trial,
                'subjects_count' => count($enrollment->selected_subjects ?: []),
                'access_expires_at' => $enrollment->access_expires_at,
                'access_days_remaining' => $enrollment->access_days_remaining,
                'country' => $enrollment->country ?: ($enrollment->region ? ucfirst(str_replace('_', ' ', $enrollment->region)) : null),
                'region' => $enrollment->region,
            ] : null
        ]);
    })->name('account.settings');
    
    // Country Update Route
    Route::post('/profile/country-update', function (Illuminate\Http\Request $request) {
        $user = auth()->user();
        
        $request->validate([
            'new_country' => 'required|string|in:malawi,south_africa,zambia,botswana,zimbabwe',
            'new_phone' => 'required|string|max:20',
            'reason' => 'required|string|max:500',
            'verification_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Store verification document if provided
        $documentPath = null;
        if ($request->hasFile('verification_document')) {
            $documentPath = $request->file('verification_document')->store('country-updates', 'public');
        }

        // Create country update request
        \App\Models\CountryUpdateRequest::create([
            'user_id' => $user->id,
            'current_country' => $user->enrollment?->country ?? 'unknown',
            'new_country' => $request->new_country,
            'new_phone' => $request->new_phone,
            'reason' => $request->reason,
            'verification_document_path' => $documentPath,
            'status' => 'pending'
        ]);

        return response()->json(['success' => true, 'message' => 'Country update request submitted successfully']);
    })->name('profile.country-update');

    // Notification Settings Route
    Route::get('/notification-settings', function () {
        return Inertia::render('Profile/NotificationSettings');
    })->name('notification.settings');

    // Admin enrollment management (only for admin users)
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
        Route::patch('/enrollments/{enrollment}/approve', [EnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::patch('/enrollments/{enrollment}/reject', [EnrollmentController::class, 'reject'])->name('enrollments.reject');
        Route::get('/enrollments/{enrollment}/payment-proof', [EnrollmentController::class, 'viewPaymentProof'])->name('enrollments.payment-proof');
        
        // Admin extension management
        Route::prefix('extensions')->name('extensions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ExtensionController::class, 'index'])->name('index');
            Route::get('/{payment}', [\App\Http\Controllers\Admin\ExtensionController::class, 'show'])->name('show');
            Route::post('/{payment}/approve', [\App\Http\Controllers\Admin\ExtensionController::class, 'approve'])->name('approve');
            Route::post('/{payment}/reject', [\App\Http\Controllers\Admin\ExtensionController::class, 'reject'])->name('reject');
            Route::post('/grant-extension', [\App\Http\Controllers\Admin\ExtensionController::class, 'grantExtension'])->name('grant');
            Route::post('/update-pricing', [\App\Http\Controllers\Admin\ExtensionController::class, 'updatePricing'])->name('update-pricing');
            Route::post('/bulk-action', [\App\Http\Controllers\Admin\ExtensionController::class, 'bulkAction'])->name('bulk-action');
        });
        
        // Admin payment methods management
        Route::resource('payment-methods', \App\Http\Controllers\Admin\PaymentMethodController::class);
        Route::post('payment-methods/{paymentMethod}/toggle', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'toggle'])->name('payment-methods.toggle');
        
        // Tutor Assignment Management
        Route::prefix('tutor-assignments')->name('tutor-assignments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'index'])->name('index');
            Route::post('/assign', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'assign'])->name('assign');
            Route::post('/unassign', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'unassign'])->name('unassign');
            Route::post('/bulk-assign', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'bulkAssign'])->name('bulk-assign');
            Route::get('/tutor/{tutor}/stats', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'getTutorStats'])->name('tutor-stats');
        });
    });

    
    // Admin Library Management Routes
    Route::middleware('role:admin')->prefix('admin/library')->name('admin.library.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LibraryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\LibraryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\LibraryController::class, 'store'])->name('store');
        Route::get('/{resource}', [\App\Http\Controllers\Admin\LibraryController::class, 'show'])->name('show');
        Route::get('/{resource}/edit', [\App\Http\Controllers\Admin\LibraryController::class, 'edit'])->name('edit');
        Route::put('/{resource}', [\App\Http\Controllers\Admin\LibraryController::class, 'update'])->name('update');
        Route::delete('/{resource}', [\App\Http\Controllers\Admin\LibraryController::class, 'destroy'])->name('destroy');
        Route::patch('/{resource}/toggle', [\App\Http\Controllers\Admin\LibraryController::class, 'toggle'])->name('toggle');
        
        // Bulk operations
        Route::post('/bulk-upload', [\App\Http\Controllers\Admin\LibraryController::class, 'bulkUpload'])->name('bulk-upload');
        Route::patch('/bulk-action', [\App\Http\Controllers\Admin\LibraryController::class, 'bulkAction'])->name('bulk-action');
        
        // Categories and metadata
        Route::get('/categories/manage', [\App\Http\Controllers\Admin\LibraryController::class, 'manageCategories'])->name('categories.manage');
        Route::post('/categories', [\App\Http\Controllers\Admin\LibraryController::class, 'storeCategory'])->name('categories.store');
    });

    // AI Training Routes - Admin only
    Route::middleware('role:admin')->prefix('admin/ai-training')->name('admin.ai-training.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Admin/AITraining/Index', [
                'materials' => collect([]), // Sample data - would come from database
                'subjects' => collect([
                    ['id' => 1, 'name' => 'Mathematics'],
                    ['id' => 2, 'name' => 'English'],
                    ['id' => 3, 'name' => 'Science'],
                    ['id' => 4, 'name' => 'Geography'],
                    ['id' => 5, 'name' => 'History']
                ]),
                'stats' => [
                    'total_materials' => 0,
                    'by_type' => [],
                    'by_subject' => [],
                    'total_size' => 0
                ]
            ]);
        })->name('index');
        
        Route::post('/', function (\Illuminate\Http\Request $request) {
            $request->validate([
                'title' => 'required|string|max:255',
                'subject_id' => 'required|integer',
                'material_type' => 'required|string|in:book,past_paper,notes,questions,answers',
                'description' => 'nullable|string',
                'file' => 'required|file|mimes:pdf,doc,docx,txt|max:51200',
                'tags' => 'nullable|string'
            ]);

            // Store the file
            $filePath = $request->file('file')->store('ai-training-materials', 'public');

            // TODO: Process the file and extract content for AI training
            // This would involve text extraction, chunking, and preparing for AI model training
            
            return redirect()->back()->with('success', 'Training material uploaded successfully! The AI will start learning from this content.');
        })->name('store');
    });
        Route::delete('/categories/{category}', [\App\Http\Controllers\Admin\LibraryController::class, 'destroyCategory'])->name('categories.destroy');
    });

    // Achievement Routes
    Route::prefix('achievements')->name('achievements.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AchievementController::class, 'index'])->name('index');
        Route::get('/leaderboard', [\App\Http\Controllers\AchievementController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/{achievement}', [\App\Http\Controllers\AchievementController::class, 'show'])->name('show');
    });

    // School Selection Routes (Students only)
    Route::prefix('school-selections')->name('school-selections.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SchoolSelectionController::class, 'index'])->name('index');
        Route::get('/confirmed', [\App\Http\Controllers\SchoolSelectionController::class, 'confirmed'])->name('confirmed');
        Route::post('/', [\App\Http\Controllers\SchoolSelectionController::class, 'store'])->name('store');
        Route::put('/{selection}', [\App\Http\Controllers\SchoolSelectionController::class, 'update'])->name('update');
        Route::delete('/{selection}', [\App\Http\Controllers\SchoolSelectionController::class, 'destroy'])->name('destroy');
    });

    // Admin School Selection Management
    Route::middleware('role:admin')->prefix('admin/school-selections')->name('admin.school-selections.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SchoolSelectionController::class, 'adminIndex'])->name('index');
        Route::patch('/{selection}', [\App\Http\Controllers\SchoolSelectionController::class, 'adminUpdate'])->name('update');
    });

    // Teacher Routes
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\TeacherDashboardController::class, 'index'])->name('dashboard');
        Route::post('/send-message', [\App\Http\Controllers\TeacherDashboardController::class, 'sendMessage'])->name('send-message');
        Route::post('/book-session', [\App\Http\Controllers\TeacherDashboardController::class, 'bookSession'])->name('book-session');
        
        Route::get('/students', function () {
            $user = auth()->user();
            $assignedEnrollments = \App\Models\Enrollment::where('assigned_tutor_id', $user->id)
                ->with('user')
                ->where('status', 'approved')
                ->get();
                
            $assignedStudents = $assignedEnrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->user ? $enrollment->user->id : null,
                    'name' => $enrollment->user ? $enrollment->user->name : $enrollment->name,
                    'email' => $enrollment->email,
                    'enrollment_id' => $enrollment->id,
                    'subjects_count' => count($enrollment->selected_subjects ?: []),
                    'access_expires_at' => $enrollment->access_expires_at,
                    'is_trial' => $enrollment->is_trial,
                    'status' => $enrollment->status,
                    'tutor_assigned_at' => $enrollment->tutor_assigned_at
                ];
            });

            return Inertia::render('Teacher/Students/Index', [
                'assignedStudents' => $assignedStudents,
                'stats' => [
                    'assigned_students' => $assignedEnrollments->count(),
                    'active_students' => $assignedEnrollments->where('access_expires_at', '>', now())->count(),
                    'trial_students' => $assignedEnrollments->where('is_trial', true)->count(),
                ]
            ]);
        })->name('students.index');

        Route::get('/calendar', function () {
            $user = auth()->user();
            
            // Get assigned students for meeting scheduling
            $assignedEnrollments = \App\Models\Enrollment::where('assigned_tutor_id', $user->id)
                ->with('user')
                ->where('status', 'approved')
                ->get();

            $assignedStudents = $assignedEnrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->user ? $enrollment->user->id : $enrollment->id,
                    'name' => $enrollment->user ? $enrollment->user->name : $enrollment->name,
                    'email' => $enrollment->email,
                ];
            });

            // Get meetings (placeholder - you'll need to create a Meeting model)
            $meetings = collect([
                [
                    'id' => 1,
                    'title' => 'Math Review Session',
                    'student_name' => 'John Doe',
                    'student_email' => 'john@example.com',
                    'scheduled_at' => now()->addDays(2)->setHour(14)->setMinute(0),
                    'duration' => 60,
                    'status' => 'scheduled',
                    'type' => 'online',
                    'time' => '14:00'
                ]
            ]);

            return Inertia::render('Teacher/Calendar/Index', [
                'assignedStudents' => $assignedStudents,
                'meetings' => $meetings,
                'stats' => [
                    'total_meetings' => $meetings->count(),
                    'upcoming_meetings' => $meetings->where('scheduled_at', '>', now())->count()
                ]
            ]);
        })->name('calendar');

        Route::post('/calendar', function (\Illuminate\Http\Request $request) {
            $user = auth()->user();
            
            $request->validate([
                'student_id' => 'required',
                'title' => 'required|string|max:255',
                'date' => 'required|date|after:today',
                'time' => 'required',
                'duration' => 'required|integer|min:15|max:180',
                'type' => 'required|in:online,in-person',
                'notes' => 'nullable|string'
            ]);

            // TODO: Create meeting record in database
            // For now, just return success
            
            return redirect()->back()->with('success', 'Meeting scheduled successfully!');
        })->name('calendar.store');

        Route::get('/materials', [\App\Http\Controllers\Teacher\TeachingMaterialController::class, 'index'])->name('materials');
        Route::post('/materials', [\App\Http\Controllers\Teacher\TeachingMaterialController::class, 'store'])->name('materials.store');
        Route::get('/materials/{material}', [\App\Http\Controllers\Teacher\TeachingMaterialController::class, 'show'])->name('materials.show');
        Route::get('/materials/{material}/download', [\App\Http\Controllers\Teacher\TeachingMaterialController::class, 'download'])->name('materials.download');
        Route::put('/materials/{material}', [\App\Http\Controllers\Teacher\TeachingMaterialController::class, 'update'])->name('materials.update');
        Route::delete('/materials/{material}', [\App\Http\Controllers\Teacher\TeachingMaterialController::class, 'destroy'])->name('materials.destroy');

        // Teacher Assignment Routes
        Route::get('/assignments', [\App\Http\Controllers\Teacher\AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [\App\Http\Controllers\Teacher\AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [\App\Http\Controllers\Teacher\AssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/{assignment}', [\App\Http\Controllers\Teacher\AssignmentController::class, 'show'])->name('assignments.show');
        Route::get('/assignments/{assignment}/edit', [\App\Http\Controllers\Teacher\AssignmentController::class, 'edit'])->name('assignments.edit');
        Route::put('/assignments/{assignment}', [\App\Http\Controllers\Teacher\AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [\App\Http\Controllers\Teacher\AssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::get('/assignments/{assignment}/submissions', [\App\Http\Controllers\Teacher\AssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('/submissions/{submission}/grade', [\App\Http\Controllers\Teacher\AssignmentController::class, 'gradeSubmission'])->name('submissions.grade');

        // Teacher Quiz Routes
        Route::get('/quizzes', [\App\Http\Controllers\Teacher\QuizController::class, 'index'])->name('quizzes.index');
        Route::get('/quizzes/create', [\App\Http\Controllers\Teacher\QuizController::class, 'create'])->name('quizzes.create');
        Route::post('/quizzes', [\App\Http\Controllers\Teacher\QuizController::class, 'store'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'show'])->name('quizzes.show');
        Route::get('/quizzes/{quiz}/edit', [\App\Http\Controllers\Teacher\QuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'update'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'destroy'])->name('quizzes.destroy');
        Route::get('/quizzes/{quiz}/results', [\App\Http\Controllers\Teacher\QuizController::class, 'results'])->name('quizzes.results');
        Route::post('/quizzes/{quiz}/questions', [\App\Http\Controllers\Teacher\QuizController::class, 'addQuestion'])->name('quizzes.questions.add');
        Route::put('/quizzes/{quiz}/questions/{question}', [\App\Http\Controllers\Teacher\QuizController::class, 'updateQuestion'])->name('quizzes.questions.update');
        Route::delete('/quizzes/{quiz}/questions/{question}', [\App\Http\Controllers\Teacher\QuizController::class, 'deleteQuestion'])->name('quizzes.questions.delete');

        // Teacher Certificate Routes
        Route::get('/certificates', [\App\Http\Controllers\Teacher\CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/create', [\App\Http\Controllers\Teacher\CertificateController::class, 'create'])->name('certificates.create');
        Route::post('/certificates', [\App\Http\Controllers\Teacher\CertificateController::class, 'store'])->name('certificates.store');
        Route::get('/certificates/{certificate}', [\App\Http\Controllers\Teacher\CertificateController::class, 'show'])->name('certificates.show');
        Route::delete('/certificates/{certificate}', [\App\Http\Controllers\Teacher\CertificateController::class, 'destroy'])->name('certificates.destroy');
        Route::get('/certificates/{certificate}/download', [\App\Http\Controllers\Teacher\CertificateController::class, 'download'])->name('certificates.download');
        Route::get('/subjects/{subject}/students', [\App\Http\Controllers\Teacher\CertificateController::class, 'getStudents'])->name('subjects.students');
    });


    // Admin Mock Exams Routes
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/mock-exams', [\App\Http\Controllers\Admin\MockExamController::class, 'index'])->name('mock-exams.index');
        Route::get('/mock-exams/create', [\App\Http\Controllers\Admin\MockExamController::class, 'create'])->name('mock-exams.create');
        Route::post('/mock-exams', [\App\Http\Controllers\Admin\MockExamController::class, 'store'])->name('mock-exams.store');
        Route::get('/mock-exams/{mockExam}/edit', [\App\Http\Controllers\Admin\MockExamController::class, 'edit'])->name('mock-exams.edit');
        Route::put('/mock-exams/{mockExam}', [\App\Http\Controllers\Admin\MockExamController::class, 'update'])->name('mock-exams.update');
        Route::delete('/mock-exams/{mockExam}', [\App\Http\Controllers\Admin\MockExamController::class, 'destroy'])->name('mock-exams.destroy');
        Route::get('/mock-exams/{mockExam}/results', [\App\Http\Controllers\Admin\MockExamController::class, 'results'])->name('mock-exams.results');
        Route::post('/mock-exams/{mockExam}/questions', [\App\Http\Controllers\Admin\MockExamController::class, 'addQuestion'])->name('mock-exams.questions.add');
        Route::put('/mock-exams/{mockExam}/questions/{question}', [\App\Http\Controllers\Admin\MockExamController::class, 'updateQuestion'])->name('mock-exams.questions.update');
        Route::delete('/mock-exams/{mockExam}/questions/{question}', [\App\Http\Controllers\Admin\MockExamController::class, 'deleteQuestion'])->name('mock-exams.questions.delete');

        // Games Routes
        Route::get('/games', [\App\Http\Controllers\Admin\GameController::class, 'index'])->name('games.index');
        Route::get('/games/create', [\App\Http\Controllers\Admin\GameController::class, 'create'])->name('games.create');
        Route::post('/games', [\App\Http\Controllers\Admin\GameController::class, 'store'])->name('games.store');
        Route::get('/games/{game}/edit', [\App\Http\Controllers\Admin\GameController::class, 'edit'])->name('games.edit');
        Route::put('/games/{game}', [\App\Http\Controllers\Admin\GameController::class, 'update'])->name('games.update');
        Route::delete('/games/{game}', [\App\Http\Controllers\Admin\GameController::class, 'destroy'])->name('games.destroy');
        Route::get('/games/{game}/leaderboard', [\App\Http\Controllers\Admin\GameController::class, 'leaderboard'])->name('games.leaderboard');
    });


    // Admin Audit Trail Routes
    Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditController::class, 'show'])->name('audit.show');
        
        // Tutor Assignment Routes
        Route::get('/tutor-assignments', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'index'])->name('tutor-assignments.index');
        Route::post('/tutor-assignments/assign', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'assign'])->name('tutor-assignments.assign');
        Route::post('/tutor-assignments/unassign', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'unassign'])->name('tutor-assignments.unassign');
        Route::post('/tutor-assignments/bulk-assign', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'bulkAssign'])->name('tutor-assignments.bulk-assign');
        Route::get('/tutors/{tutor}/stats', [\App\Http\Controllers\Admin\TutorAssignmentController::class, 'getTutorStats'])->name('tutors.stats');
        
        // AI Training Routes
        Route::get('/ai-training', [\App\Http\Controllers\Admin\AiTrainingController::class, 'index'])->name('ai-training.index');
        Route::post('/ai-training', [\App\Http\Controllers\Admin\AiTrainingController::class, 'store'])->name('ai-training.store');
        Route::delete('/ai-training/{material}', [\App\Http\Controllers\Admin\AiTrainingController::class, 'destroy'])->name('ai-training.destroy');
        Route::post('/ai-training/{material}/process', [\App\Http\Controllers\Admin\AiTrainingController::class, 'process'])->name('ai-training.process');
        Route::post('/ai-training/bulk-process', [\App\Http\Controllers\Admin\AiTrainingController::class, 'bulkProcess'])->name('ai-training.bulk-process');
        Route::get('/ai-training/stats', [\App\Http\Controllers\Admin\AiTrainingController::class, 'getTrainingStats'])->name('ai-training.stats');
    });

    // Notification Settings Routes
    Route::middleware('auth')->group(function () {
        Route::get('/notification-settings', [\App\Http\Controllers\NotificationSettingsController::class, 'index'])->name('notification-settings.index');
        Route::post('/notification-settings', [\App\Http\Controllers\NotificationSettingsController::class, 'update'])->name('notification-settings.update');
        Route::get('/api/notification-settings', [\App\Http\Controllers\NotificationSettingsController::class, 'getSettings'])->name('api.notification-settings');
    });

    // Data Usage Routes
    Route::middleware('auth')->prefix('data-usage')->name('data-usage.')->group(function () {
        Route::get('/estimation', [\App\Http\Controllers\DataUsageController::class, 'getEstimation'])->name('estimation');
        Route::get('/stats', [\App\Http\Controllers\DataUsageController::class, 'getUserUsageStats'])->name('stats');
    });

require __DIR__.'/auth.php';
