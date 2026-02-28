<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'institution_id',
        'user_id',
        'subject',
        'body',
        'status',
        'institution_note',
    ];

    /**
     * Get the institution that owns the ticket.
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the user who submitted the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reported issue when this ticket was escalated from one.
     */
    public function reportedIssue(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReportedIssue::class);
    }

    /**
     * Get all replies for the ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class)->orderBy('created_at');
    }
}
