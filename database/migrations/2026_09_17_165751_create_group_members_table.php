<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('group_members', function (Blueprint $table) {
        $table->id();
        
        // 1. Create the column as a Big Integer (which matches $table->id())
        $table->unsignedBigInteger('user_id');
        
        // 2. Point the foreign key to 'user_id' on the users table instead of 'id'
        $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        
        $table->unsignedBigInteger('group_id'); 
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};