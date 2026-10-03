<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // if (config('app.env') == 'local') {
        //     URL::forceScheme('https');
        // }

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('karyawan')) {
                \Illuminate\Support\Facades\DB::table('karyawan')
                    ->where(function ($q) {
                        $q->where('nama_lengkap', 'like', '%CINDY%')
                          ->orWhere('nama_lengkap', 'like', '%MAGDALENA%')
                          ->orWhere('nama_lengkap', 'like', '%MAHDALENA%')
                          ->orWhere('nama_lengkap', 'like', '%TITIN%')
                          ->orWhere('nama_lengkap', 'like', '%ROSHELLA%');
                    })
                    ->where('status_location', '!=', 0)
                    ->update(['status_location' => 0]);
            }
        } catch (\Throwable $e) {
            // Silently continue if database is not reachable during early bootstrap / cli
        }
    }
}