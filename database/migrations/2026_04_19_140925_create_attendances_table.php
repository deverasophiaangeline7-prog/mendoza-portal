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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->unsignedBigInteger('student_id');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'holiday']);
            $table->timestamps();

            // Links this record directly to the student
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
