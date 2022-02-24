<?php

namespace App\Providers;

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
            $location = collect(debug_backtrace())->filter(function ($trace) {
                return !str_contains($trace['file'], 'vendor/');
            })->first(); // берем первый элемент не из каталога вендора
            $bindings = implode(", ", $query->bindings); // форматируем привязку как строку

            $text = "
               ------------
               Sql: $query->sql
               Bindings: $bindings
               Time: $query->time
               File: ${location['file']}
               Line: ${location['line']}
               ------------
            ";

            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/databaseRequests.log'),
            ])->info($text);
        });
    }
}
