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
        // We drop the old integer column and add a string one
        $table->string('event_id')->change(); 
    });
}

public function down()
{
    Schema::table('event_participants', function (Blueprint $table) {
        $table->unsignedBigInteger('event_id')->change();
    });
}
};
