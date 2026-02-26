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
     *
     * On MySQL the unique index is used to support the foreign keys, so we must
     * drop FKs first, then the unique, then re-add FKs (which get their own indexes).
     */
    public function up(): void
    {
        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->dropForeign(['viva_id']);
            $table->dropForeign(['student_id']);
        });

        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->dropUnique(['viva_id', 'student_id']);
        });

        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->foreign('viva_id')->references('id')->on('vivas')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->dropForeign(['viva_id']);
            $table->dropForeign(['student_id']);
        });

        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->unique(['viva_id', 'student_id']);
        });

        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->foreign('viva_id')->references('id')->on('vivas')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
