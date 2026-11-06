<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $primaryKey = 'section_id';
    protected $fillable = [
    'section_name',
    'grade_level',
    'teacher_id', // Add this line!
];

    // A section has many students
    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    public function teacher() 
    {
    return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    public function adviser()
    {
        return $this->hasOne(Teacher::class, 'advisory', 'section_id');
    }

    /**
     * Sync the Group Chat for this Section
     */

    public static function syncGC($section_id)
    {
        $section = self::find($section_id);
        if (!$section) return;

        $groupUsername = 'gc_section_' . $section_id;

        
        $groupUser = \App\Models\User::firstOrCreate(
            ['username' => $groupUsername],
            [
                'email' => $groupUsername . '@mendoza.edu',
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                'role' => 'admin', // Bypass rules
                'section_id' => $section_id,
                'customer_name' => 'SECTION_GC',
                'status' => 'active',
            ]
        );

        $groupUser->section_id = $section_id;
        $groupUser->custom_name = 'SECTION_GC';
        $groupUser->save();
        
        $memberIds = [];

        
        $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->pluck('user_id')->toArray();
        $memberIds = array_merge($memberIds, $admins);

        
        if ($section->teacher_id) {
            $memberIds[] = $section->teacher_id;
        }

        
        $parents = \App\Models\User::where('role', 'parent')
            ->whereHas('student', function($q) use ($section_id) {
                $q->where('section_id', $section_id);
            })->pluck('user_id')->toArray();
        $memberIds = array_merge($memberIds, $parents);

        $memberIds = array_unique($memberIds); 

       
        $existingMembers = \Illuminate\Support\Facades\DB::table('group_members')
            ->where('group_id', $groupUser->user_id)
            ->pluck('is_archived', 'user_id')->toArray();

        \Illuminate\Support\Facades\DB::table('group_members')->where('group_id', $groupUser->user_id)->delete();

        $insertData = [];
        foreach ($memberIds as $uid) {
            $insertData[] = [
                'group_id' => $groupUser->user_id,
                'user_id' => $uid,
                'is_archived' => $existingMembers[$uid] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            \Illuminate\Support\Facades\DB::table('group_members')->insert($insertData);
        }
    }
}