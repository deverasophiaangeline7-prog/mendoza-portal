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

        // 3. Your existing unread messages logic remains untouched
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