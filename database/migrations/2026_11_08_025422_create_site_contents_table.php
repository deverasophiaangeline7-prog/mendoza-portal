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
    Schema::create('site_contents', function (Blueprint $table) {
        $table->id();
        $table->string('page'); // 'home', 'about', 'courses', 'faqs', 'login'
        $table->string('key');  // e.g., 'hero_title', 'mission_text', 'faq_1_q'
        $table->text('value')->nullable(); // text or image path
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
