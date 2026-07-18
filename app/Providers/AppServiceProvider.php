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
        $this->app->singleton(
            \Filament\Http\Responses\Auth\Contracts\LoginResponse::class,
            \App\Filament\Responses\CustomLoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Self-healing: langsung bersihkan outlet duplikat saat halaman diakses jika masih ada lebih dari 3 outlet
        if (! $this->app->runningInConsole()) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('outlets') && \Illuminate\Support\Facades\DB::table('outlets')->count() > 3) {
                    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    $tables = ['inventories', 'products', 'orders', 'order_items', 'shifts', 'cashier_sessions'];
                    foreach ($tables as $tbl) {
                        if (\Illuminate\Support\Facades\Schema::hasTable($tbl) && \Illuminate\Support\Facades\Schema::hasColumn($tbl, 'outlet_id')) {
                            \Illuminate\Support\Facades\DB::table($tbl)->whereNotIn('outlet_id', [1, 2, 4])->delete();
                        }
                    }
                    \Illuminate\Support\Facades\DB::table('outlets')->whereNotIn('id', [1, 2, 4])->delete();
                    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
            } catch (\Throwable $e) {
                try { \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Throwable $e2) {}
            }
        }
    }
}
