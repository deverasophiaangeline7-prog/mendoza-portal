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
    Schema::table('students', function (Blueprint $table) {
        $table->string('promotion_status')->default('none'); // Can be: none, pending, promoted
        $table->string('next_grade_level')->nullable(); // Stores the calculated next grade
    });
}

public function down(): void
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropColumn(['promotion_status', 'next_grade_level']);
    });
}
};
