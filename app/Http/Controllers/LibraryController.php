<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LibraryController extends Controller
{
    /**
     * Display the digital library.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filters
        $type = $request->get('type', 'all');
        $subject = $request->get('subject');
        $grade = $request->get('grade');
        $year = $request->get('year');
        $search = $request->get('search');

        // Build query
        $query = Resource::active()->with('subject');
        
        // Apply user access control
        $query->where(function($q) use ($user) {
            $q->whereJsonContains('access_permissions->roles', [$user->role])
              ->orWhereJsonContains('access_permissions->users', [$user->id])
              ->orWhereNull('access_permissions');
        });

        // Apply filters
        if ($type !== 'all') {
            $query->ofType($type);
        }
        
        if ($subject) {
            $query->forSubject($subject);
        }
        
        if ($grade) {
            $query->forGrade($grade);
        }
        
        if ($year) {
            $query->where('year', $year);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $resources = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get filter options
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $years = Resource::active()->distinct()->orderBy('year', 'desc')->pluck('year')->filter();
        $grades = Resource::active()->distinct()->orderBy('grade_level')->pluck('grade_level')->filter();
        
        // Get statistics
        $stats = [
            'total_resources' => Resource::active()->count(),
            'books' => Resource::active()->ofType('book')->count(),
            'past_papers' => Resource::active()->ofType('past_paper')->count(),
            'documents' => Resource::active()->ofType('document')->count(),
        ];

        return Inertia::render('Library/Index', [
            'resources' => $resources,
            'subjects' => $subjects,
            'years' => $years,
            'grades' => $grades,
            'stats' => $stats,
            'filters' => [
                'type' => $type,
                'subject' => $subject,
                'grade' => $grade,
                'year' => $year,
                'search' => $search,
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource)
    {
        $user = auth()->user();
        
        // Check access permission
        if (!$resource->canBeAccessedBy($user)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        // Increment view count
        $resource->incrementView();
        
        $resource->load('subject');

        // Get related resources
        $related = Resource::active()
            ->where('id', '!=', $resource->id)
            ->where('subject_id', $resource->subject_id)
            ->limit(6)
            ->get();

        return Inertia::render('Library/Show', [
            'resource' => $resource,
            'related' => $related
        ]);
    }

    /**
     * Stream protected file content
     */
    public function stream(Resource $resource)
    {
        $user = auth()->user();
        
        // Additional security checks
        if (!request()->hasHeader('X-Requested-With') && !request()->ajax()) {
            abort(403, 'Direct access not permitted');
        }
        
        // Check referrer to ensure request comes from our application
        $referer = request()->header('referer');
        $appUrl = config('app.url');
        if (!$referer || !str_starts_with($referer, $appUrl)) {
            abort(403, 'Invalid request origin');
        }
        
        // Check access permission
        if (!$resource->canBeAccessedBy($user)) {
            abort(403, 'Access denied');
        }

        // Log potential download attempt for security
        if (request()->has('download')) {
            $resource->incrementDownloadAttempt();
            return response()->json(['error' => 'Downloads are not permitted'], 403);
        }

        // Verify file exists
        if (!Storage::exists($resource->file_path)) {
            abort(404, 'File not found');
        }

        $file = Storage::get($resource->file_path);
        $mimeType = Storage::mimeType($resource->file_path);

        return response($file)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $resource->title . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, private')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('Referrer-Policy', 'same-origin')
            ->header('Content-Security-Policy', "default-src 'self'; script-src 'none'; object-src 'none'; media-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline';")
            ->header('X-Permitted-Cross-Domain-Policies', 'none')
            ->header('Access-Control-Allow-Origin', request()->getSchemeAndHttpHost())
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex, nocache');
    }

    /**
     * Get books collection
     */
    public function books(Request $request)
    {
        $user = auth()->user();
        
        $query = Resource::active()->ofType('book')->with('subject');
        
        // Apply user access control
        $query->where(function($q) use ($user) {
            $q->whereJsonContains('access_permissions->roles', [$user->role])
              ->orWhereJsonContains('access_permissions->users', [$user->id])
              ->orWhereNull('access_permissions');
        });

        // Apply filters
        if ($request->subject) {
            $query->forSubject($request->subject);
        }
        
        if ($request->category) {
            $query->where('category', $request->category);
        }
        
        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $books = $query->orderBy('title')->paginate(20);
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $categories = Resource::active()->ofType('book')->distinct()->pluck('category')->filter();

        return Inertia::render('Library/Books', [
            'books' => $books,
            'subjects' => $subjects,
            'categories' => $categories,
            'filters' => $request->only(['subject', 'category', 'search'])
        ]);
    }

    /**
     * Get past papers collection
     */
    public function pastPapers(Request $request)
    {
        $user = auth()->user();
        
        $query = Resource::active()->ofType('past_paper')->with('subject');
        
        // Apply user access control
        $query->where(function($q) use ($user) {
            $q->whereJsonContains('access_permissions->roles', [$user->role])
              ->orWhereJsonContains('access_permissions->users', [$user->id])
              ->orWhereNull('access_permissions');
        });

        // Apply filters
        if ($request->subject) {
            $query->forSubject($request->subject);
        }
        
        if ($request->year) {
            $query->where('year', $request->year);
        }
        
        if ($request->exam_board) {
            $query->where('exam_board', $request->exam_board);
        }

        $pastPapers = $query->orderBy('year', 'desc')->orderBy('title')->paginate(20);
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        $years = Resource::active()->ofType('past_paper')->distinct()->orderBy('year', 'desc')->pluck('year')->filter();
        $examBoards = Resource::active()->ofType('past_paper')->distinct()->pluck('exam_board')->filter();

        return Inertia::render('Library/PastPapers', [
            'pastPapers' => $pastPapers,
            'subjects' => $subjects,
            'years' => $years,
            'examBoards' => $examBoards,
            'filters' => $request->only(['subject', 'year', 'exam_board'])
        ]);
    }

    /**
     * Search resources
     */
    public function search(Request $request)
    {
        $user = auth()->user();
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $resources = Resource::active()
            ->with('subject')
            ->where(function($q) use ($user) {
                $q->whereJsonContains('access_permissions->roles', [$user->role])
                  ->orWhereJsonContains('access_permissions->users', [$user->id])
                  ->orWhereNull('access_permissions');
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($resource) {
                return [
                    'id' => $resource->id,
                    'title' => $resource->title,
                    'type' => $resource->type,
                    'subject' => $resource->subject?->name,
                    'url' => route('library.show', $resource)
                ];
            });

        return response()->json($resources);
    }
}