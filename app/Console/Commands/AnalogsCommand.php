<?php

namespace App\Console\Commands;

use App\Models\GeneralData;
use App\Models\RecalcsPercents;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AnalogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analogs:search';

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
        // получаем все проценты месячных изменений
        $percents = RecalcsPercents::all();
        $iterationsCount = GeneralData::select(['id', 'city', 'LastCheckAnalogsDate'])->orderBy('id', 'desc')->where('city', 'Москва')->where('LastCheckAnalogsDate', NULL)->count();
        $this->error("Всего итераций должно быть: $iterationsCount");
        $iteration = 0;
        GeneralData::orderBy('id', 'desc')->where('LastCheckAnalogsDate', NULL)->chunk(100, function($items) use($percents, &$iteration){
            foreach ($items as $item) {
                $iteration++;
                if(!empty($item->house)){

                    if($item->total_floors >= 6){
                        $analogs = GeneralData::where('objecttype', $item->objecttype)
                            ->where('rooms_count', $item->rooms_count)
                            ->where('total_floors', '>=', '6')
                            ->where('city', $item->city)
                            ->where('street', $item->street)
                            ->where('house', $item->house)->get();
                    } else if($item->total_floors <= 5){
                        $analogs = GeneralData::where('objecttype', $item->objecttype)
                            ->where('rooms_count', $item->rooms_count)
                            ->where('total_floors', '<=', '5')
                            ->where('city', $item->city)
                            ->where('street', $item->street)
                            ->where('house', $item->house)->get();
                    }

                    $analogsCounter = 0;
                    $SquareMeterPriceSum = 0;

                    foreach ($analogs as $analog) {
                        $analogsCounter++;
                        $analog->square_meter_price = $analog->square_meter_price * 1000;

                        if($analog->city == "Москва"){
                            //$this->error("Начальный square_meter_price: " . $analog->square_meter_price);
                            foreach ($percents->where('month', '>', Carbon::parse($analog->ad_added)->format('Y-m-d')) as $percent) {
                                //$this->info($analogsCounter . ") " . "аналог дата: " . Carbon::parse($analog->ad_added) . " проценты " . Carbon::parse($percent->month));
                                $analog->square_meter_price = $analog->square_meter_price + ($analog->square_meter_price / 100 * $percent->manual_value);
                                //$this->info("square meter price: $analog->square_meter_price");
                            }
                        }

                        $SquareMeterPriceSum += $analog->square_meter_price;
                    }

                    $SrArifm = $SquareMeterPriceSum / $analogsCounter;

                    $a = (($item->square_meter_price * 1000 / $SrArifm) - 1) * 100;
                    $a = round($a) . "%";

                    //$item->update(['SootSAnSgdn' => $a, 'LastCheckAnalogsDate' => Carbon::now()]);
                    GeneralData::where('id', $item->id)->update(['SootSAnSgdn' => $a, 'LastCheckAnalogsDate' => Carbon::now()->addHours(3), 'count_analog' => $analogsCounter]);

                    $this->info("$iteration) ID: " . $item->id . "soot: " . $a);
                }
            }
        });

        return true;
    }
}
