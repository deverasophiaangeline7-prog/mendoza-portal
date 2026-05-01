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
    Schema::create('event_participants', function (Blueprint $table) {
        $table->id();
        
        // Links to the students table
        $table->foreignId('student_id')->constrained('students', 'student_id')->onDelete('cascade');
        
        // The event_id as a string (to match your school calendar logic)
        $table->string('event_id'); 
        
        // The role, set to nullable so it's not required
        $table->string('role')->nullable(); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
