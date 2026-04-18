<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('students', function (Blueprint $table) {
        $table->id('student_id');
        $table->string('lrn', 20)->unique();
        $table->string('first_name', 100);
        $table->string('middle_name', 100)->nullable();
        $table->string('last_name', 100);
        $table->string('gender', 10);
        $table->date('birth_date');
        $table->string('grade_level', 20); 
        $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
        
        
         $table->foreignId('section_id')->nullable()->constrained('sections', 'section_id');
        
        $table->timestamps();
    });
}
};
