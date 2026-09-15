<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // --- 1. BEHAVIOR REPORTS TABLE ---
        
        // If q1 exists, drop the old quarters
        if (Schema::hasColumn('behavior_reports', 'q1')) {
            Schema::table('behavior_reports', function (Blueprint $table) {
                $table->dropColumn(['q1', 'q2', 'q3', 'q4']);
            });
        }
        
        // If term1 DOES NOT exist, add the new terms
        if (!Schema::hasColumn('behavior_reports', 'term1')) {
            Schema::table('behavior_reports', function (Blueprint $table) {
                $table->string('term1')->nullable();
                $table->string('term2')->nullable();
                $table->string('term3')->nullable();
            });
        }


        // --- 2. NKP EVALUATIONS TABLE ---
        
        // If q1 exists, drop the old quarters
        if (Schema::hasColumn('nkp_evaluations', 'q1')) {
            Schema::table('nkp_evaluations', function (Blueprint $table) {
                $table->dropColumn(['q1', 'q2', 'q3', 'q4']);
            });
        }
        
        // If term1 DOES NOT exist, add the new terms
        if (!Schema::hasColumn('nkp_evaluations', 'term1')) {
            Schema::table('nkp_evaluations', function (Blueprint $table) {
                $table->string('term1')->nullable();
                $table->string('term2')->nullable();
                $table->string('term3')->nullable();
            });
        }
    }

    public function down()
    {
        // Reverse Behavior Reports
        if (Schema::hasColumn('behavior_reports', 'term1')) {
            Schema::table('behavior_reports', function (Blueprint $table) {
                $table->dropColumn(['term1', 'term2', 'term3']);
            });
        }
        if (!Schema::hasColumn('behavior_reports', 'q1')) {
            Schema::table('behavior_reports', function (Blueprint $table) {
                $table->string('q1')->nullable();
                $table->string('q2')->nullable();
                $table->string('q3')->nullable();
                $table->string('q4')->nullable();
            });
        }

        // Reverse NKP Evaluations
        if (Schema::hasColumn('nkp_evaluations', 'term1')) {
            Schema::table('nkp_evaluations', function (Blueprint $table) {
                $table->dropColumn(['term1', 'term2', 'term3']);
            });
        }
        if (!Schema::hasColumn('nkp_evaluations', 'q1')) {
            Schema::table('nkp_evaluations', function (Blueprint $table) {
                $table->string('q1')->nullable();
                $table->string('q2')->nullable();
                $table->string('q3')->nullable();
                $table->string('q4')->nullable();
            });
        }
    }
};