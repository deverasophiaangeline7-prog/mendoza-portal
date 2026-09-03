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
    // Update Grades Table
    Schema::table('grades', function (Blueprint $table) {
        $table->dropColumn(['q1', 'q2', 'q3', 'q4']);
        $table->decimal('term1', 5, 2)->nullable()->after('subject_name');
        $table->decimal('term2', 5, 2)->nullable()->after('term1');
        $table->decimal('term3', 5, 2)->nullable()->after('term2');
    });

    // Update Behavior Reports Table
    Schema::table('behavior_reports', function (Blueprint $table) {
        $table->dropColumn(['q1', 'q2', 'q3', 'q4']);
        $table->string('term1', 10)->nullable()->after('core_value');
        $table->string('term2', 10)->nullable()->after('term1');
        $table->string('term3', 10)->nullable()->after('term2');
    });

    // Add Date Locks to School Year
    Schema::table('school_years', function (Blueprint $table) {
        $table->date('term1_start')->nullable();
        $table->date('term1_end')->nullable();
        $table->date('term2_start')->nullable();
        $table->date('term2_end')->nullable();
        $table->date('term3_start')->nullable();
        $table->date('term3_end')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
