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
 Schema::create('messages', function (Blueprint $table) {
    $table->id();
    
    // This column will hold the sender's ID
    $table->unsignedBigInteger('sender_id');
    
    // LINK: This must reference 'user_id' to match the users table exactly
    $table->foreign('sender_id')
          ->references('user_id') 
          ->on('users')
          ->onDelete('cascade');

    $table->text('message');
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
