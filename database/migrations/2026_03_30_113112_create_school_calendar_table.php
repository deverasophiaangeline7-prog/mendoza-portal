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
    Schema::create('school_calendar', function (Blueprint $table) {
        $table->id('calendar_id');
        $table->string('start_date')->unique(); // This acts as your event key
        $table->string('event_title');
        $table->text('description')->nullable(); // Your "PS" field
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
