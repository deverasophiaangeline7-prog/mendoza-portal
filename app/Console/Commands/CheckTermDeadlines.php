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
        $activeYear = SchoolYear::where('status', 'active')->first();
        if (!$activeYear) return;

        $targetDate = Carbon::today()->addDays(7)->toDateString();
        $termEnding = null;

        if ($activeYear->term1_end === $targetDate) $termEnding = 'Term 1';
        elseif ($activeYear->term2_end === $targetDate) $termEnding = 'Term 2';
        elseif ($activeYear->term3_end === $targetDate) $termEnding = 'Term 3';

        if ($termEnding) {
            $teachers = User::where('role', 'teacher')->get();
            foreach ($teachers as $teacher) {
                Notification::create([
                    'user_id'    => $teacher->user_id,
                    'title'      => 'Grade Submission Reminder',
                    'message'    => "Reminder: {$termEnding} ends in exactly 1 week. Please finalize your grade sheets.",
                    'type'       => 'deadline_alert',
                    'is_read'    => 0,
                    'created_at' => now(),
                ]);
            }
        }
    }
}