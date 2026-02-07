<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('viva_student_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viva_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('document_path')->nullable(); // Student uploaded document
            $table->json('answers')->nullable(); // Store Q&A pairs
            $table->integer('total_score')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();

            $table->unique(['viva_id', 'student_id']); // One submission per student per viva
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viva_student_submissions');
    }
};
