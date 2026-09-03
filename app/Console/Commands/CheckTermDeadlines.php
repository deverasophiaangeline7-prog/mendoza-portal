<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SchoolYear;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class CheckTermDeadlines extends Command
{
    protected $signature = 'terms:check-deadlines';
    protected $description = 'Notify teachers 1 week before a term ends';

    public function handle()
    {
        // 1. Get the active school year
        $activeYear = SchoolYear::where('status', 'active')->first();
        
        if (!$activeYear) {
            $this->info('No active school year found.');
            return;
        }

        // 2. Set the target date to exactly 7 days from today
        $targetDate = Carbon::today()->addDays(7)->toDateString();
        $termEnding = null;

        // 3. Check if any of the term end dates match the target date
        if ($activeYear->term1_end === $targetDate) $termEnding = 'Term 1';
        elseif ($activeYear->term2_end === $targetDate) $termEnding = 'Term 2';
        elseif ($activeYear->term3_end === $targetDate) $termEnding = 'Term 3';

        // 4. If a term is ending, notify all teachers
        if ($termEnding) {
            $teachers = User::where('role', 'teacher')->get();
            
            foreach ($teachers as $teacher) {
                Notification::create([
                    'user_id'    => $teacher->user_id, // Correctly mapped to your users table PK
                    'title'      => 'Grade Submission Reminder',
                    'message'    => "Reminder: {$termEnding} ends in exactly 1 week. Please finalize your grade sheets.",
                    'type'       => 'deadline_alert',
                    'is_read'    => 0,
                    'created_at' => now(),
                ]);
            }
            
            // Output success message to the terminal
            $this->info("Success: Sent alerts to teachers for {$termEnding}.");
        } else {
            // Output fallback message if no deadlines match today
            $this->info("No terms ending in exactly 7 days. Target date checked: {$targetDate}");
        }
    }
}