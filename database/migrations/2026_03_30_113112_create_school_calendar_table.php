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
    Schema::create('school_calendar', function (Blueprint $table) {
        $table->id('calendar_id');
        $table->string('event_title', 255);
        $table->text('description')->nullable();
        $table->dateTime('start_date');
        $table->dateTime('end_date')->nullable();
        $table->enum('event_type', ['holiday', 'exam', 'activity', 'meeting', 'other']);
        $table->tinyInteger('is_global')->default(1);
        $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
        $table->enum('status', ['active', 'archived'])->default('active');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_calendar');
    }
};
