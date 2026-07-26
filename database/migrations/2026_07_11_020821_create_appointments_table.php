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
            
            // 1. CREATE THE COLUMNS FIRST
            $table->unsignedBigInteger('teacher_id');
            $table->unsignedBigInteger('parent_id');
            
            // ... (Your other columns like discussion_topic, appointment_date, status, etc.)
            $table->string('discussion_topic');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'booked', 'reschedule', 'declined'])->default('pending');
            $table->string('reschedule_reason')->nullable();
            
            $table->timestamps();

            // 2. THEN DEFINE THE FOREIGN KEYS
            $table->foreign('teacher_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('user_id')->on('users')->onDelete('cascade');
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