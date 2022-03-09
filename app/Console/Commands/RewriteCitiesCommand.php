<?php

namespace App\Console\Commands;

use App\Models\GeneralData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RewriteCitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cities:rewrite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $counter = 0;
        $allCountItems = GeneralData::where('city', 'ЗелАО')->orWhere('city', 'СЗАО')->orWhere('city', 'САО')->orWhere('city', 'СВАО')
            ->orWhere('city', 'ВАО')->orWhere('city', 'ЮВАО')->orWhere('city', 'ЮАО')->orWhere('city', 'ЮЗАО')->orWhere('city', 'ЗАО')
            ->orWhere('city', 'ЦАО')->count();

        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/RewriteCities.log'),
        ])->info("В " . Carbon::now() . " найдено и исправлено " . $allCountItems . " записей");

        GeneralData::where('city', 'ЗелАО')->orWhere('city', 'СЗАО')->orWhere('city', 'САО')->orWhere('city', 'СВАО')
            ->orWhere('city', 'ВАО')->orWhere('city', 'ЮВАО')->orWhere('city', 'ЮАО')->orWhere('city', 'ЮЗАО')->orWhere('city', 'ЗАО')
            ->orWhere('city', 'ЦАО')->chunk(100, function($items) use(&$counter){
                foreach ($items as $item) {
                    $counter++;

                    GeneralData::where('id', $item->id)->update(['city' => 'Москва']);
                }
            });

        return 0;
    }
}
