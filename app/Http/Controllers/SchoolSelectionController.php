<?php

namespace App\Http\Controllers;

use App\Models\SecondarySchool;
use App\Models\StudentSchoolSelection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SchoolSelectionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user already has a confirmed school
        $confirmedSchool = $user->confirmedSchool();
        if ($confirmedSchool) {
            // Redirect to a page showing their confirmed school information
            return redirect()->route('school-selections.confirmed')->with('info', 'You already have an approved school. You cannot select additional schools.');
        }
        
        $schools = SecondarySchool::active()->get();
        $userSelections = $user->schoolSelections()->with('secondarySchool')->byPriority()->get();

        return Inertia::render('SchoolSelection/Index', [
            'schools' => $schools,
            'userSelections' => $userSelections,
            'hasRequiredSelections' => $user->hasRequiredSchoolSelections(),
            'confirmedSchool' => $confirmedSchool,
        ]);
    }

    public function confirmed()
    {
        $user = Auth::user();
        $confirmedSchool = $user->confirmedSchool();
        
        if (!$confirmedSchool) {
            return redirect()->route('school-selections.index')->with('error', 'You do not have a confirmed school yet.');
        }

        return Inertia::render('SchoolSelection/Confirmed', [
            'confirmedSchool' => $confirmedSchool,
            'allSelections' => $user->schoolSelections()->with('secondarySchool')->byPriority()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user already has a confirmed school
        $hasConfirmedSchool = $user->schoolSelections()->where('status', 'confirmed')->exists();
        if ($hasConfirmedSchool) {
            return redirect()->back()->withErrors(['error' => 'You already have an approved school. You cannot apply to additional schools once one has been approved.']);
        }

        $request->validate([
            'school_selections' => 'required|array|min:1|max:5',
            'school_selections.*.school_id' => 'required|exists:secondary_schools,id',
            'school_selections.*.priority' => 'required|integer|min:1|max:5',
        ]);

        DB::transaction(function () use ($user, $request) {
            $user->schoolSelections()->delete();

            foreach ($request->school_selections as $selection) {
                StudentSchoolSelection::create([
                    'user_id' => $user->id,
                    'secondary_school_id' => $selection['school_id'],
                    'priority_order' => $selection['priority'],
                    'status' => 'pending',
                ]);
            }
        });

        return redirect()->back()->with('success', 'School selections saved successfully. You can select up to 5 schools. You will be notified once schools respond to your applications.');
    }

    public function update(Request $request, StudentSchoolSelection $selection)
    {
        if ($selection->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'priority_order' => 'required|integer|min:1|max:5',
        ]);

        $selection->update($request->only('priority_order'));

        return redirect()->back()->with('success', 'Selection priority updated successfully.');
    }

    public function destroy(StudentSchoolSelection $selection)
    {
        if ($selection->user_id !== Auth::id() || $selection->status !== 'pending') {
            abort(403);
        }

        $selection->delete();

        return redirect()->back()->with('success', 'School selection removed successfully.');
    }

    public function adminIndex()
    {
        $selections = StudentSchoolSelection::with(['user', 'secondarySchool'])
            ->byStatus('pending')
            ->byPriority()
            ->paginate(20);

        return Inertia::render('Admin/SchoolSelections/Index', [
            'selections' => $selections,
        ]);
    }

    public function adminUpdate(Request $request, StudentSchoolSelection $selection)
    {
        $request->validate([
            'status' => 'required|in:confirmed,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        DB::transaction(function () use ($request, $selection) {
            if ($request->status === 'confirmed') {
                $user = $selection->user;
                
                // Check if user already has a confirmed school
                $existingConfirmed = $user->schoolSelections()->where('status', 'confirmed')->exists();
                if ($existingConfirmed) {
                    throw new \Exception('This student already has an approved school. Only one school can be approved per student.');
                }
                
                $school = $selection->secondarySchool;
                
                if ($school->available_slots <= 0) {
                    throw new \Exception('No available slots in this school.');
                }
                
                // Confirm this selection
                $selection->markAsConfirmed();
                
                // Auto-reject all other pending selections for this user
                $user->schoolSelections()
                    ->where('id', '!=', $selection->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejection_reason' => 'Automatically rejected - another school has been approved for this student'
                    ]);
                    
            } else {
                $selection->markAsRejected($request->rejection_reason);
            }
        });

        return redirect()->back()->with('success', 'Selection status updated successfully. If approved, all other selections for this student have been automatically rejected.');
    }
}
