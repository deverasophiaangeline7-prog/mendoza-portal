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
        Schema::create('nkp_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('category'); 
            $table->text('skill'); 
            
            // Storing the B, D, C markings for the 3 terms
            $table->string('term1', 2)->nullable();
            $table->string('term2', 2)->nullable();
            $table->string('term3', 2)->nullable();
            
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nkp_evaluations');
    }
};