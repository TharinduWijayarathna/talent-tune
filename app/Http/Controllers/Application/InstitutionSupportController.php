<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InstitutionSupportController extends Controller
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
     * List support tickets for the current institution (submitted by current user).
     */
    public function index(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $tickets = SupportTicket::where('institution_id', $institution->id)
            ->where('user_id', $request->user()->id)
            ->withCount('replies')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (SupportTicket $t) => [
                'id' => $t->id,
                'subject' => $t->subject,
                'status' => $t->status,
                'replies_count' => $t->replies_count,
                'created_at' => $t->created_at->toIso8601String(),
                'updated_at' => $t->updated_at->toIso8601String(),
            ]);

        return Inertia::render('institution/Support', [
            'tickets' => $tickets,
        ]);
    }

    /**
     * Show the form to create a new support ticket.
     */
    public function create(Request $request)
    {
        $this->institution($request);
        $this->authorizeInstitution($request->user(), $request->attributes->get('institution'));

        return Inertia::render('institution/SupportCreate');
    }

    /**
     * Store a new support ticket.
     */
    public function store(Request $request)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        SupportTicket::create([
            'institution_id' => $institution->id,
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'open',
        ]);

        return redirect()->route('institution.support')->with('success', 'Support ticket submitted successfully.');
    }

    /**
     * Show a single ticket and its replies (institution admin view).
     */
    public function show(Request $request, int $id)
    {
        $institution = $this->institution($request);
        $this->authorizeInstitution($request->user(), $institution);

        $ticket = SupportTicket::where('institution_id', $institution->id)
            ->where('user_id', $request->user()->id)
            ->with(['replies' => fn ($q) => $q->with('user:id,name,role')])
            ->findOrFail($id);

        $replies = $ticket->replies->map(fn ($r) => [
            'id' => $r->id,
            'body' => $r->body,
            'user_name' => $r->user->name,
            'is_staff' => $r->user->role === 'admin',
            'created_at' => $r->created_at->toIso8601String(),
        ]);

        return Inertia::render('institution/SupportShow', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'body' => $ticket->body,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at->toIso8601String(),
                'updated_at' => $ticket->updated_at->toIso8601String(),
            ],
            'replies' => $replies,
        ]);
    }
}
