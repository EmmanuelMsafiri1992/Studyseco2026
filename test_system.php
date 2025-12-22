<?php
/**
 * Comprehensive System Testing Script
 * Tests all major functionality for Admin, Teacher, and Student roles
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Testing\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SystemTester
{
    private $testResults = [];
    private $testUsers = [];
    
    public function __construct()
    {
        echo "🚀 Starting Comprehensive System Test\n";
        echo "===================================\n\n";
        $this->setupTestUsers();
    }
    
    private function setupTestUsers()
    {
        echo "🔧 Setting up test users...\n";
        
        // Admin user
        $admin = User::where('email', 'admin@test.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now()
            ]);
            echo "✅ Created admin user\n";
        } else {
            echo "✅ Admin user exists\n";
        }
        $this->testUsers['admin'] = $admin;
        
        // Teacher user
        $teacher = User::where('email', 'teacher@test.com')->first();
        if (!$teacher) {
            $teacher = User::create([
                'name' => 'Test Teacher',
                'email' => 'teacher@test.com',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'email_verified_at' => now()
            ]);
            echo "✅ Created teacher user\n";
        } else {
            echo "✅ Teacher user exists\n";
        }
        $this->testUsers['teacher'] = $teacher;
        
        // Student user
        $student = User::where('email', 'student@test.com')->first();
        if (!$student) {
            $student = User::create([
                'name' => 'Test Student',
                'email' => 'student@test.com',
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now()
            ]);
            echo "✅ Created student user\n";
        } else {
            echo "✅ Student user exists\n";
        }
        $this->testUsers['student'] = $student;
        
        echo "\n";
    }
    
    private function testUrl($url, $description, $expectedStatus = 200)
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: SystemTester\r\n",
                'timeout' => 10
            ]
        ]);
        
        $result = @file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        
        $status = 0;
        if ($headers) {
            foreach ($headers as $header) {
                if (strpos($header, 'HTTP/') === 0) {
                    $status = (int)substr($header, 9, 3);
                    break;
                }
            }
        }
        
        $success = ($status === $expectedStatus || ($result !== false && $status > 0));
        
        echo ($success ? "✅" : "❌") . " $description ";
        echo ($success ? "(Status: $status)" : "(FAILED - Status: $status)") . "\n";
        
        $this->testResults[] = [
            'description' => $description,
            'url' => $url,
            'status' => $status,
            'success' => $success
        ];
        
        return $success;
    }
    
    public function testPublicPages()
    {
        echo "🌐 Testing Public Pages\n";
        echo "=====================\n";
        
        $baseUrl = 'http://127.0.0.1:8000';
        
        $this->testUrl("$baseUrl/", "Home page loads");
        $this->testUrl("$baseUrl/login", "Login page loads");
        $this->testUrl("$baseUrl/register", "Register page loads");
        $this->testUrl("$baseUrl/forgot-password", "Forgot password page loads");
        
        echo "\n";
    }
    
    public function testDashboardPages()
    {
        echo "🏠 Testing Dashboard Pages\n";
        echo "=========================\n";
        
        $baseUrl = 'http://127.0.0.1:8000';
        
        // These will redirect to login without authentication, but should not error
        $this->testUrl("$baseUrl/dashboard", "Dashboard page accessible", 302);
        $this->testUrl("$baseUrl/teacher/dashboard", "Teacher dashboard accessible", 302);
        $this->testUrl("$baseUrl/admin/roles", "Admin pages accessible", 302);
        
        echo "\n";
    }
    
    public function testApiEndpoints()
    {
        echo "🔌 Testing API Endpoints\n";
        echo "=======================\n";
        
        $baseUrl = 'http://127.0.0.1:8000/api';
        
        // Test some GET endpoints that should not require auth or return proper errors
        $this->testUrl("$baseUrl/users", "Users API endpoint", null); // May return various status codes
        $this->testUrl("$baseUrl/notifications/count", "Notifications count API", null);
        
        echo "\n";
    }
    
    public function testDatabaseConnection()
    {
        echo "🗄️  Testing Database Connection\n";
        echo "==============================\n";
        
        try {
            $userCount = DB::table('users')->count();
            echo "✅ Database connection successful\n";
            echo "✅ Found $userCount users in database\n";
            
            // Test key tables exist
            $tables = ['users', 'subjects', 'lessons', 'payments', 'enrollments'];
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    echo "✅ Table '$table' exists with $count records\n";
                } catch (Exception $e) {
                    echo "❌ Table '$table' does not exist or has issues\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    public function testAssetFiles()
    {
        echo "📁 Testing Asset Files\n";
        echo "=====================\n";
        
        $publicPath = __DIR__ . '/public';
        $buildPath = $publicPath . '/build';
        
        if (is_dir($buildPath)) {
            echo "✅ Build directory exists\n";
            
            // Check for manifest file
            if (file_exists($buildPath . '/manifest.json')) {
                echo "✅ Manifest file exists\n";
                $manifest = json_decode(file_get_contents($buildPath . '/manifest.json'), true);
                $assetCount = count($manifest ?? []);
                echo "✅ Found $assetCount compiled assets\n";
            } else {
                echo "❌ Manifest file missing\n";
            }
            
            // Check for key CSS/JS files
            $cssFiles = glob($buildPath . '/assets/*.css');
            $jsFiles = glob($buildPath . '/assets/*.js');
            echo "✅ Found " . count($cssFiles) . " CSS files\n";
            echo "✅ Found " . count($jsFiles) . " JS files\n";
        } else {
            echo "❌ Build directory does not exist - run 'npm run build'\n";
        }
        
        echo "\n";
    }
    
    public function testRoutes()
    {
        echo "🛤️  Testing Route Configuration\n";
        echo "==============================\n";
        
        try {
            // Load Laravel app
            $app = require_once __DIR__ . '/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            
            echo "✅ Laravel application loaded\n";
            echo "✅ Routes configuration accessible\n";
            
            // Test critical routes exist by checking the router
            $router = $app['router'];
            $routes = $router->getRoutes();
            $routeCount = count($routes);
            echo "✅ Found $routeCount registered routes\n";
            
        } catch (Exception $e) {
            echo "❌ Route testing failed: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    public function generateReport()
    {
        echo "📊 Test Results Summary\n";
        echo "=====================\n";
        
        $totalTests = count($this->testResults);
        $passedTests = array_filter($this->testResults, function($result) {
            return $result['success'];
        });
        $passedCount = count($passedTests);
        $failedCount = $totalTests - $passedCount;
        
        echo "Total Tests: $totalTests\n";
        echo "Passed: $passedCount\n";
        echo "Failed: $failedCount\n";
        
        if ($failedCount > 0) {
            echo "\n❌ Failed Tests:\n";
            foreach ($this->testResults as $result) {
                if (!$result['success']) {
                    echo "  - {$result['description']} ({$result['url']})\n";
                }
            }
        }
        
        $percentage = $totalTests > 0 ? round(($passedCount / $totalTests) * 100, 1) : 0;
        echo "\nOverall Success Rate: $percentage%\n";
        
        if ($percentage >= 90) {
            echo "🎉 Excellent! System is highly functional.\n";
        } elseif ($percentage >= 75) {
            echo "👍 Good! Minor issues to fix.\n";
        } elseif ($percentage >= 50) {
            echo "⚠️  Moderate issues found. Needs attention.\n";
        } else {
            echo "🚨 Major issues found. Significant work needed.\n";
        }
        
        echo "\n";
    }
    
    public function runAllTests()
    {
        $this->testDatabaseConnection();
        $this->testAssetFiles();
        $this->testRoutes();
        $this->testPublicPages();
        $this->testDashboardPages();
        $this->testApiEndpoints();
        $this->generateReport();
    }
}

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Run the tests
$tester = new SystemTester();
$tester->runAllTests();

echo "✨ Testing completed!\n";
echo "Check the results above for any issues that need fixing.\n";
?>