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
        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->boolean('allowed_after_close')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('viva_student_submissions', function (Blueprint $table) {
            $table->dropColumn('allowed_after_close');
        });
    }
};
