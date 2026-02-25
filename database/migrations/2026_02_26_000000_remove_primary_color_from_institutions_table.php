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
        if (Schema::hasColumn('institutions', 'primary_color')) {
            Schema::table('institutions', function (Blueprint $table) {
                $table->dropColumn('primary_color');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('institutions', 'primary_color')) {
            Schema::table('institutions', function (Blueprint $table) {
                $table->string('primary_color')->nullable()->after('logo_url');
            });
        }
    }
};
