<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('custom_name');
            }
        });

        // Backfill: hanapin sa messages kung sino ang gumawa ng lumang manual GC
        $groups = DB::table('users')->where('username', 'like', 'group_%')->get();
        foreach ($groups as $group) {
            $creatorMsg = DB::table('messages')
                ->where('receiver_id', $group->user_id)
                ->where('content', 'like', '%created the group:%')
                ->orderBy('id', 'asc')
                ->first();
            if ($creatorMsg) {
                DB::table('users')->where('user_id', $group->user_id)
                    ->update(['created_by' => $creatorMsg->sender_id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};
