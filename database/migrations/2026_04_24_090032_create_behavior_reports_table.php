<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('behavior_reports', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->string('core_value'); 
        
        // Kept as string for letter markings
        $table->string('q1', 5)->nullable();
        $table->string('q2', 5)->nullable();
        $table->string('q3', 5)->nullable();
        $table->string('q4', 5)->nullable();
        $table->timestamps();

        // Safely links to your custom student_id column
        $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behavior_reports');
    }
};
