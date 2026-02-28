<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\ReportedIssue;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportedIssueController extends Controller
{
    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (! $institution) {
            abort(403, 'Institution context required.');
        }

        return $institution;
    }

    protected function authorizeReporter(Request $request): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }
        if ($user->role === 'admin') {
            return;
        }
        if (! in_array($user->role, ['student', 'lecturer'], true)) {
            abort(403, 'Only students and lecturers can report issues.');
        }
        $institution = $this->institution($request);
        if ($user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    /**
     * List reported issues for the current user (student or lecturer).
     */
    public function index(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeReporter($request);

        $issues = ReportedIssue::where('institution_id', $institution->id)
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ReportedIssue $i) => [
                'id' => $i->id,
                'subject' => $i->subject,
                'status' => $i->status,
                'created_at' => $i->created_at->toIso8601String(),
            ]);

        $role = $request->user()->role;

        return Inertia::render($role === 'student' ? 'student/ReportIssue' : 'lecturer/ReportIssue', [
            'issues' => $issues,
        ]);
    }

    /**
     * Show the form to create a new reported issue.
     */
    public function create(Request $request)
    {
        $this->institution($request);
        $this->authorizeReporter($request);

        $role = $request->user()->role;

        return Inertia::render($role === 'student' ? 'student/ReportIssueCreate' : 'lecturer/ReportIssueCreate');
    }

    /**
     * Store a new reported issue.
     */
    public function store(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeReporter($request);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        ReportedIssue::create([
            'institution_id' => $institution->id,
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'pending',
        ]);

        $role = $request->user()->role;
        $route = $role === 'student' ? 'student.issues' : 'lecturer.issues';

        return redirect()->route($route)->with('success', 'Issue reported successfully. Your institution admin will review it.');
    }
}
