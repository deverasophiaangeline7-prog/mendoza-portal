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
    Schema::create('teacher_schedules', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('teacher_id'); // ID of the teacher
        $table->date('date');                     // The specific day
        $table->string('time_slot');              // e.g., '8AM'
        $table->string('status');                 // 'available', 'booked', 'class_hours', 'on_leave'
        $table->timestamps();
        
        // Foreign key constraint must point to the users table primary key
        $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_schedules');
    }
};
