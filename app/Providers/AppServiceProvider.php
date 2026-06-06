<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin bypass
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Dynamic Permissions registration
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                $permissions = \App\Models\Permission::all();
                foreach ($permissions as $permission) {
                    \Illuminate\Support\Facades\Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                }
            }
        } catch (\Exception $e) {
            // Fail silently if DB is not setup yet during deployments/migrations
        }

        // Log logins
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function (\Illuminate\Auth\Events\Login $event) {
                \App\Models\ActivityLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'login',
                    'description' => "{$event->user->name} logged in successfully",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        );

        // Log logouts
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            function (\Illuminate\Auth\Events\Logout $event) {
                if ($event->user) {
                    \App\Models\ActivityLog::create([
                        'user_id' => $event->user->id,
                        'action' => 'logout',
                        'description' => "{$event->user->name} logged out successfully",
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                }
            }
        );
    }
}
