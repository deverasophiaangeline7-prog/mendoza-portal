<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('teachers', function (Blueprint $table) {
        $table->id('teacher_id');
        $table->string('first_name', 100);
        $table->string('last_name', 100);
        $table->string('advisory')->unique()->nullable(); 
        $table->string('cv_path')->nullable(); 
        $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
        $table->timestamps();
    });
}
};
