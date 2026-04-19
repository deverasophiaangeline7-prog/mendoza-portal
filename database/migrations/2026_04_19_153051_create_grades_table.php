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
            
            $table->string('q1', 10)->nullable();
            $table->string('q2', 10)->nullable();
            $table->string('q3', 10)->nullable();
            $table->string('q4', 10)->nullable();
            $table->string('final_grade', 10)->nullable();
            $table->string('remarks', 50)->nullable();
            
            $table->timestamps();
            
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grades');
    }
}