<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Allows multiple submissions per student per viva so the lecturer can add
     * the same student again for a re-do (one-time participation after close).
     */
    public function up(): void
    {
        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->dropUnique(['viva_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->unique(['viva_id', 'student_id']);
        });
    }
};
