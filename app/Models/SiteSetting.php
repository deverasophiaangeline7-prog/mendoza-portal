<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    const CACHE_KEY = 'site_settings_all';

    /**
     * Default values used as fallbacks and for the "Reset to Default" button.
     */
    public static function defaults(): array
    {
        return [
            // Branding
            'school_name'  => 'Mendoza Academy, Inc.',
            'logo_path'    => 'images/MAILogo.png',
            'favicon_path' => 'images/MAILogo.png',

            // Theme Colors
            'primary_color' => '#6d0101',
            'accent_color'  => '#fb923c', // Matches your orange-400 active/icon accent
            'navbar_bg'     => '#6d0101',
            'navbar_text'   => '#ffffff',

            // Sidebar Labels & FontAwesome 6 Icons
            'nav_items' => [
                'dashboard'          => ['label' => 'Dashboard',              'icon' => 'fa-solid fa-chart-line'],
                'admin_info'         => ['label' => 'Admin Information',      'icon' => 'fa-solid fa-user-shield'],
                'student_info'       => ['label' => 'Student Information',    'icon' => 'fa-solid fa-user-graduate'],
                'teacher_info'       => ['label' => 'Teacher Information',    'icon' => 'fa-solid fa-user-tie'],
                'advisory_class'     => ['label' => 'Advisory Class',         'icon' => 'fa-solid fa-chalkboard-user'],
                'student_calendar'   => ['label' => 'Student Calendar',       'icon' => 'fa-solid fa-calendar-days'],
                'report_card'        => ['label' => 'Report Card',            'icon' => 'fa-solid fa-star'],
                'attendance'         => ['label' => 'Attendance',             'icon' => 'fa-solid fa-calendar-check'],
                'appointments'       => ['label' => 'Appointment Scheduling', 'icon' => 'fa-solid fa-user-group'],
                'account_management' => ['label' => 'Account Management',     'icon' => 'fa-solid fa-users-gear'],
            ],
        ];
    }

    /**
     * Fetch all settings (cached), merged with defaults so nothing is ever null.
     */
    public static function getAll(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $defaults = self::defaults();
            $stored = self::pluck('value', 'key')->toArray();

            // Decode nav_items JSON if it exists in the database
            if (isset($stored['nav_items'])) {
                $decoded = json_decode($stored['nav_items'], true);
                if (is_array($decoded)) {
                    $stored['nav_items'] = array_replace_recursive($defaults['nav_items'], $decoded);
                } else {
                    unset($stored['nav_items']);
                }
            }

            return array_merge($defaults, $stored);
        });
    }

    /**
     * Save or update a single setting and clear the cache.
     */
    public static function setValue(string $key, $value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        self::clearCache();
    }

    /**
     * Wipe custom settings to restore defaults.
     */
    public static function resetToDefaults(): void
    {
        self::truncate();
        self::clearCache();
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}