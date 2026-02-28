<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\ReportedIssue;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionReportedIssuesController extends Controller
{
    protected function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('institution');
        if (! $institution) {
            abort(403, 'Institution context required.');
        }

        return $institution;
    }

    protected function authorizeInstitution(?User $user, ?Institution $institution): void
    {
        if (! $user) {
            abort(403);
        }
        if ($user->role === 'admin') {
            return;
        }
        if ($user->role !== 'institution' || $user->institution_id !== $institution->id) {
            abort(403, 'You do not have access to this institution.');
        }
    }

    /**
     * List all reported issues for the institution.
     */
    public function index(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $query = ReportedIssue::where('institution_id', $institution->id)
            ->with(['user:id,name,email,role'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $issues = $query->get()->map(fn (ReportedIssue $i) => [
            'id' => $i->id,
            'subject' => $i->subject,
            'status' => $i->status,
            'reporter_name' => $i->user?->name,
            'reporter_role' => $i->user?->role,
            'created_at' => $i->created_at->toIso8601String(),
            'support_ticket_id' => $i->support_ticket_id,
        ]);

        return Inertia::render('institution/ReportedIssues', [
            'issues' => $issues,
            'filters' => [
                'status' => $request->status,
            ],
        ]);
    }

    /**
     * Show a single reported issue.
     */
    public function show(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $issue = ReportedIssue::where('institution_id', $institution->id)
            ->with(['user:id,name,email,role'])
            ->findOrFail($id);

        return Inertia::render('institution/ReportedIssueShow', [
            'issue' => [
                'id' => $issue->id,
                'subject' => $issue->subject,
                'body' => $issue->body,
                'status' => $issue->status,
                'reporter_name' => $issue->user?->name,
                'reporter_email' => $issue->user?->email,
                'reporter_role' => $issue->user?->role,
                'created_at' => $issue->created_at->toIso8601String(),
                'support_ticket_id' => $issue->support_ticket_id,
            ],
        ]);
    }

    /**
     * Mark issue as reviewed.
     */
    public function markReviewed(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $issue = ReportedIssue::where('institution_id', $institution->id)->findOrFail($id);
        $issue->update(['status' => 'reviewed']);

        return redirect()
            ->route('institution.reported-issues.show', $id)
            ->with('success', 'Issue marked as reviewed.');
    }

    /**
     * Escalate issue to TalentTune admin (create support ticket).
     */
    public function escalate(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $issue = ReportedIssue::where('institution_id', $institution->id)->findOrFail($id);

        if ($issue->support_ticket_id) {
            return redirect()
                ->route('institution.support.show', $issue->support_ticket_id)
                ->with('info', 'This issue was already escalated.');
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $ticket = SupportTicket::create([
            'institution_id' => $institution->id,
            'user_id' => $request->user()->id,
            'subject' => '[Escalated] '.$issue->subject,
            'body' => $issue->body,
            'status' => 'open',
            'institution_note' => $validated['message'] ?? null,
        ]);

        $issue->update([
            'status' => 'escalated',
            'support_ticket_id' => $ticket->id,
        ]);

        return redirect()
            ->route('institution.support.show', $ticket->id)
            ->with('success', 'Issue escalated to TalentTune support. You can track it in Support.');
    }
}
