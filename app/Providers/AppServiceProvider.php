<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        DB::listen(function ($query) {
            $bindings = implode(", ", $query->bindings); // форматируем привязку как строку

            $text = "
               ------------
               Sql: $query->sql
               Bindings: $bindings
               Time: $query->time
               ------------
            ";

            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/database/databaseRequests'. Carbon::now()->format('Y-m-d') .'.log'),
            ])->info($text);
        });
    }
}
