<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeMailNotificationsTo(config('horizon.email_monitor'));
    }

    /**
     * Register the Horizon gate.
     *
     * Dashboard Horizon hanya boleh diakses super-admin. Bila
     * spatie/laravel-permission sudah terpasang dan peran tersebut ada,
     * pemeriksaan memakai permission; jika belum, akses ditolak kecuali
     * pada environment lokal.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function (?\Illuminate\Contracts\Auth\Authenticatable $user = null): bool {
            if ($user === null) {
                return false;
            }

            if (app()->isLocal()) {
                return true;
            }

            $canCheck = method_exists($user, 'hasRole');

            return $canCheck && $user->hasRole(\App\Enums\RoleName::SUPER_ADMIN->value);
        });
    }
}
