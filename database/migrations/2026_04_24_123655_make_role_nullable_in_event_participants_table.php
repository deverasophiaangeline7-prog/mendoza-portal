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
    Schema::table('event_participants', function (Blueprint $table) {
        // This makes the existing column nullable
        $table->string('role')->nullable()->change();
    });
}

public function down()
{
    Schema::table('event_participants', function (Blueprint $table) {
        $table->string('role')->nullable(false)->change();
    });
}
};
