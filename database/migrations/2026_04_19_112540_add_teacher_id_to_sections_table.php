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
        Schema::table('sections', function (Blueprint $table) {
        // This connects the section to a specific teacher user
        $table->unsignedBigInteger('teacher_id')->nullable()->after('grade_level');
        
        // Set up the relationship and foreign key
        $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('set null');
        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            //
        });
    }
};
