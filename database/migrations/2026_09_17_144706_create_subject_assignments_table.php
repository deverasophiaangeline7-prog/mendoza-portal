<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id'); // Links to users/teachers table
            $table->unsignedBigInteger('section_id'); // Links to sections table
            $table->string('subject_name');           // e.g., 'GMRC', 'Makabansa'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_assignments');
    }
};