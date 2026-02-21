<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VivaStudentSubmission extends Model
{
    protected $table = 'viva_student_submissions';

    protected $fillable = [
        'viva_id',
        'student_id',
        'document_path',
        'answers',
        'total_score',
        'grade',
        'feedback',
        'status',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function viva(): BelongsTo
    {
        return $this->belongsTo(Viva::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
