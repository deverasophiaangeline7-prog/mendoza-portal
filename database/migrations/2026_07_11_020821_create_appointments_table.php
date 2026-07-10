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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys linking to your users table
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            
            // Appointment Details
            $table->string('discussion_topic');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            
            // Status and Rescheduling
            $table->enum('status', ['pending', 'booked', 'reschedule', 'declined'])->default('pending');
            $table->string('reschedule_reason')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};