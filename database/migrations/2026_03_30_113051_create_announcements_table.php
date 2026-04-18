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
    Schema::create('announcements', function (Blueprint $table) {
        $table->id('announcement_id'); // Announcement ID
        $table->string('title');
        $table->text('content');

        // UPDATE THIS LINE:
        // Change references('id') to references('user_id')
        $table->foreignId('posted_by')->constrained('users', 'user_id')->onDelete('cascade');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
