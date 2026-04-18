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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id('notification_id');
        $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
        $table->string('title', 255);
        $table->text('message');
        $table->string('type', 45)->default('announcement');
        $table->unsignedBigInteger('reference_id')->nullable();
        $table->tinyInteger('is_read')->default(0);
        $table->timestamp('created_at')->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
