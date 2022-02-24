<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralData extends Model
{
    use HasFactory;

    protected $table = 'general_data';

    public $timestamps = false;

    // переопределение данных
    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        static::retrieved(function($model){
            $model->price_actual = round(($model->price_actual/1000000), 2);
            $model->price_actual_to_current_day = round(($model->price_actual/1000000), 2);
            $value = (($model->price_actual-$model->auction)-$model->price_sale);
            if($value > 0)
                $model->percentage_income = round(($model->price_actual - $model->auction) / $value);
            else
                $model->percentage_income = 0;

            if($model->price_start){
                $model->_izmen = round(((($model->price_actual/$model->price_start)-1)*100)) . "%";
            } else {
                $model->_izmen = "";
            }

            $model->price_start = round(($model->price_start/1000000), 2);
            $model->square_meter_price = round(($model->square_meter_price/1000), 2);

            $sootn_sign = strpos($model->sootn, '-') === 0 ? '' : '+';
            $model->_soot = $sootn_sign . round($model->sootn) . '%';

            $sootn_analog_sign = strpos($model->sootn_analog, '-') === 0 ? '' : '+';
            $model->sootn_analog = $sootn_analog_sign . round($model->sootn_analog) . '%';

            if(empty($model->notRemovedAnalogAdsAvgPrice)){
                $model->notRemovedAnalogAdsCount = "";
            }else{
                if ($model->square_meter_price) {
                    $sootn_square_meter_price_to_analogs = (100-round(($model->notRemovedAnalogAdsAvgPrice*100)/$model->square_meter_price));
                } else {
                    $sootn_square_meter_price_to_analogs = 0;
                }


                $notRemovedAnalogAdsAvgPriceSign = strpos($sootn_square_meter_price_to_analogs, '-') === 0 ? "" : "+";
                $model->notRemovedAnalogAdsAvgPrice = $notRemovedAnalogAdsAvgPriceSign . "." . $sootn_square_meter_price_to_analogs . "%";
            }

            $model->ad_added = Carbon::createFromTimestamp($model->ad_added)->format('Y-m-d');
            $model->ad_published = Carbon::createFromTimestamp($model->ad_published)->format('Y-m-d');
            $model->ad_remove = Carbon::createFromTimestamp($model->ad_remove)->format('Y-m-d');

            $model->have_doubles = $model->have_doubles ? "Нет" : "Да";
        });
    }
}
