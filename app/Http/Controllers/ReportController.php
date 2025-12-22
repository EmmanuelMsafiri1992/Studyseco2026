<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\EnrollmentPayment;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // Check admin permission
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        // Get real statistics
        $stats = $this->getReportStats();
        
        // Get chart data
        $chartData = $this->getChartData();

        return Inertia::render('Reports/Index', [
            'stats' => $stats,
            'chartData' => $chartData,
            'reports' => []
        ]);
    }

    private function getReportStats()
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $activeEnrollments = Enrollment::where('status', 'approved')->count();
        $pendingEnrollments = Enrollment::where('status', 'pending')->count();

        // Calculate month-over-month growth
        $lastMonthStudents = User::where('role', 'student')
            ->where('created_at', '<', now()->subMonth())
            ->count();
        $studentGrowth = $lastMonthStudents > 0 
            ? round((($totalStudents - $lastMonthStudents) / $lastMonthStudents) * 100, 1)
            : 0;

        $lastMonthTeachers = User::where('role', 'teacher')
            ->where('created_at', '<', now()->subMonth())
            ->count();
        $teacherGrowth = $lastMonthTeachers > 0 
            ? round((($totalTeachers - $lastMonthTeachers) / $lastMonthTeachers) * 100, 1)
            : 0;

        return [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'active_enrollments' => $activeEnrollments,
            'pending_enrollments' => $pendingEnrollments,
            'student_growth' => $studentGrowth,
            'teacher_growth' => $teacherGrowth,
        ];
    }

    private function getChartData()
    {
        // Get enrollment data for last 6 months
        $enrollmentData = $this->getMonthlyEnrollmentData();
        
        // Get payment data for financial trends
        $paymentData = $this->getMonthlyPaymentData();
        
        // Get enrollment status distribution
        $enrollmentStatusData = $this->getEnrollmentStatusData();

        return [
            'enrollment' => $enrollmentData,
            'payment' => $paymentData,
            'enrollmentStatus' => $enrollmentStatusData,
        ];
    }

    private function getMonthlyEnrollmentData()
    {
        $months = [];
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            $count = Enrollment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    private function getMonthlyPaymentData()
    {
        $months = [];
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            $total = EnrollmentPayment::where('status', 'approved')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            $data[] = round($total / 1000, 1); // Convert to thousands for better chart display
        }

        return [
            'labels' => $months,
            'data' => $data
        ];
    }

    private function getEnrollmentStatusData()
    {
        $approved = Enrollment::where('status', 'approved')->count();
        $pending = Enrollment::where('status', 'pending')->count();
        $rejected = Enrollment::where('status', 'rejected')->count();
        
        $total = $approved + $pending + $rejected;
        
        if ($total === 0) {
            return [
                'labels' => ['No Data'],
                'data' => [1]
            ];
        }

        return [
            'labels' => ['Approved', 'Pending', 'Rejected'],
            'data' => [$approved, $pending, $rejected]
        ];
    }
}
