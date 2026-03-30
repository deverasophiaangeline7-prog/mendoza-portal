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
    Schema::create('announcement_approvals', function (Blueprint $table) {
        $table->id('approval_id');
        $table->foreignId('announcement_id')->constrained('announcements', 'announcement_id')->onDelete('cascade');
        $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
        $table->enum('action', ['approved', 'rejected'])->default('approved');
        $table->text('remarks')->nullable();
        $table->timestamp('acted_at')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_approvals');
    }
};
