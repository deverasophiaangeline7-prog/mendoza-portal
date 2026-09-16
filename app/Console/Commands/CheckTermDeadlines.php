<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SchoolYear;
use App\Models\User;
use Carbon\Carbon;

class CheckTermDeadlines extends Command
{
    protected $signature = 'app:check-term-deadlines';
    protected $description = 'Checks if a term ends in exactly 1 week and alerts teachers.';

    public function handle()
    {
        // 1. Calculate the exact date 7 days from today
        $targetDate = Carbon::now()->addDays(7)->format('Y-m-d');

        // 2. Look for an active school year where ANY term ends on that exact target date
        $activeYear = SchoolYear::where('status', 'active')
            ->where(function ($query) use ($targetDate) {
                $query->whereDate('term1_end', $targetDate)
                      ->orWhereDate('term2_end', $targetDate)
                      ->orWhereDate('term3_end', $targetDate);
            })->first();

        if ($activeYear) {
            $termName = '';
            if (Carbon::parse($activeYear->term1_end)->format('Y-m-d') === $targetDate) $termName = 'Term 1';
            if (Carbon::parse($activeYear->term2_end)->format('Y-m-d') === $targetDate) $termName = 'Term 2';
            if (Carbon::parse($activeYear->term3_end)->format('Y-m-d') === $targetDate) $termName = 'Term 3';

            // 3. Get all teachers
            $teachers = User::where('role', 'teacher')->get();

            // 4. Use your User model's built-in notifyUser method!
            foreach ($teachers as $teacher) {
                $teacher->notifyUser(
                    "$termName Deadline Approaching", 
                    "Reminder: $termName ends in exactly 1 week! Please ensure all student grade sheets are finalized and submitted.", 
                    'deadline_alert'
                );
            }

            $this->info("Deadline alerts sent successfully for $termName.");
        } else {
            $this->info('No terms ending in exactly 1 week.');
        }
    }
}