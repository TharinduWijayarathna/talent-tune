<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Viva extends Model
{
    protected $fillable = [
        'institution_id',
        'lecturer_id',
        'title',
        'description',
        'batch',
        'scheduled_at',
        'instructions',
        'lecture_materials',
        'viva_background',
        'base_prompt',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'lecture_materials' => 'array',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }
}
