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
    Schema::create('announcements', function (Blueprint $table) {
        $table->id('announcement_id');
        $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
        $table->string('title', 255);
        $table->text('content');
        $table->enum('scope', ['classroom', 'school-wide'])->default('classroom');
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamp('approved_at')->nullable();
        $table->timestamp('date_posted')->useCurrent();
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
