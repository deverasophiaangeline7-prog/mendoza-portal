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
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id('log_id');
        
        // The ID of the admin/user who performed the action. 
        // nullable() just in case the system itself does something automatically.
        $table->unsignedBigInteger('user_id')->nullable(); 
        
        // A short category (e.g., "Archive", "Create", "Restore")
        $table->string('action'); 
        
        // A detailed sentence (e.g., "Admin archived Teacher account ID 5")
        $table->text('description'); 
        
        // Automatically stores the exact date and time it happened (created_at & updated_at)
        $table->timestamps(); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
