<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradesTable extends Migration
{
    public function up()
{
    Schema::create('grades', function (Blueprint $table) {
        $table->id('grade_id');
        $table->unsignedBigInteger('student_id');
        $table->string('subject_name'); 
        
        // Changed to integer so JavaScript and PHP can calculate averages
        $table->decimal('q1', 5, 2)->nullable();
        $table->decimal('q2', 5, 2)->nullable();
        $table->decimal('q3', 5, 2)->nullable();
        $table->decimal('q4', 5, 2)->nullable();
        $table->float('final_grade')->nullable(); 
        
        $table->string('remarks', 50)->nullable();
        $table->timestamps();
        
        // Safely links to your custom student_id column
        $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
    });
}

    public function down()
    {
        Schema::dropIfExists('grades');
    }
}