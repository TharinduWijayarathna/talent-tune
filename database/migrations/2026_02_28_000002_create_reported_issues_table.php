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
        Schema::create('reported_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // reporter (student or lecturer)
            $table->string('subject');
            $table->text('body');
            $table->string('status')->default('pending'); // pending, reviewed, escalated
            $table->foreignId('support_ticket_id')->nullable()->constrained()->nullOnDelete(); // when escalated
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reported_issues');
    }
};
