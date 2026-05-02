<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add to Grades
        Schema::table('grades', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->onDelete('cascade');
        });

        // 2. Add to Behaviors
        Schema::table('behavior_reports', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->onDelete('cascade');
        });

        // 3. Add to NKP
        Schema::table('nkp_evaluations', function (Blueprint $table) {
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) { $table->dropForeign(['school_year_id']); $table->dropColumn('school_year_id'); });
        Schema::table('behavior_reports', function (Blueprint $table) { $table->dropForeign(['school_year_id']); $table->dropColumn('school_year_id'); });
        Schema::table('nkp_evaluations', function (Blueprint $table) { $table->dropForeign(['school_year_id']); $table->dropColumn('school_year_id'); });
    }
};