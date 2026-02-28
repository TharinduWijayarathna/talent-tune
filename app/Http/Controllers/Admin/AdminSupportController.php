<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSupportController extends Controller
{
    /**
     * List all support tickets (admin only).
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['institution:id,name,slug', 'user:id,name,email'])
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $tickets = $query->get()->map(fn (SupportTicket $t) => [
            'id' => $t->id,
            'subject' => $t->subject,
            'status' => $t->status,
            'institution' => $t->institution ? [
                'id' => $t->institution->id,
                'name' => $t->institution->name,
                'slug' => $t->institution->slug,
            ] : null,
            'user_name' => $t->user?->name,
            'user_email' => $t->user?->email,
            'replies_count' => $t->replies()->count(),
            'created_at' => $t->created_at->toIso8601String(),
            'updated_at' => $t->updated_at->toIso8601String(),
        ]);

        return Inertia::render('admin/SupportTickets', [
            'tickets' => $tickets,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    /**
     * Show a single ticket and its replies; allow admin to reply.
     */
    public function show(int $id)
    {
        $ticket = SupportTicket::with(['institution:id,name,slug', 'user:id,name,email'])
            ->with(['replies' => fn ($q) => $q->with('user:id,name,role')])
            ->findOrFail($id);

        $replies = $ticket->replies->map(fn ($r) => [
            'id' => $r->id,
            'body' => $r->body,
            'user_name' => $r->user->name,
            'is_staff' => $r->user->role === 'admin',
            'created_at' => $r->created_at->toIso8601String(),
        ]);

        return Inertia::render('admin/SupportTicketDetail', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'body' => $ticket->body,
                'status' => $ticket->status,
                'institution' => $ticket->institution ? [
                    'id' => $ticket->institution->id,
                    'name' => $ticket->institution->name,
                    'slug' => $ticket->institution->slug,
                ] : null,
                'user_name' => $ticket->user?->name,
                'user_email' => $ticket->user?->email,
                'created_at' => $ticket->created_at->toIso8601String(),
                'updated_at' => $ticket->updated_at->toIso8601String(),
            ],
            'replies' => $replies,
        ]);
    }

    /**
     * Store a reply from admin and mark ticket as answered.
     */
    public function reply(Request $request, int $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $ticket->update(['status' => 'answered']);

        return redirect()
            ->route('admin.support.show', $id)
            ->with('success', 'Reply sent.');
    }

    /**
     * Update ticket status (e.g. close).
     */
    public function updateStatus(Request $request, int $id)
    {
        $ticket = SupportTicket::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,answered,closed'],
        ]);

        $ticket->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.support.show', $id)
            ->with('success', 'Ticket status updated.');
    }
}
