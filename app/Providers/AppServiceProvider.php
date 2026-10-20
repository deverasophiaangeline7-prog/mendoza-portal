<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL; // <-- 1. Added URL facade
use App\Models\Message;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 2. Force HTTPS on Railway
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 3. Recreate Gmail OAuth JSON files on Railway from Environment Variables
        $gmailDir = storage_path('app/gmail');
        if (!is_dir($gmailDir)) {
            mkdir($gmailDir, 0755, true);
        }

        $credentials = $_ENV['GOOGLE_GMAIL_CREDENTIALS']
            ?? $_SERVER['GOOGLE_GMAIL_CREDENTIALS']
            ?? getenv('GOOGLE_GMAIL_CREDENTIALS');

        if ($credentials && !file_exists("$gmailDir/credentials.json")) {
            $decodedCredentials = str_starts_with(trim($credentials), '{')
                ? $credentials
                : base64_decode($credentials);
            file_put_contents("$gmailDir/credentials.json", $decodedCredentials);
        }

        $token = $_ENV['GOOGLE_GMAIL_TOKEN']
            ?? $_SERVER['GOOGLE_GMAIL_TOKEN']
            ?? getenv('GOOGLE_GMAIL_TOKEN');

        if ($token && !file_exists("$gmailDir/token.json")) {
            $decodedToken = str_starts_with(trim($token), '{')
                ? $token
                : base64_decode($token);
            file_put_contents("$gmailDir/token.json", $decodedToken);
        }

        // 4. Your existing unread messages logic remains untouched
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $unreadTotal = Message::where('receiver_id', auth()->id())
                                      ->where('is_read', false)
                                      ->count();
                $view->with('unreadTotal', $unreadTotal);
            } else {
                $view->with('unreadTotal', 0);
            }
        });
    }
}