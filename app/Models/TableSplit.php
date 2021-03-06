<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableSplit extends Model
{
    use HasFactory;

    protected $table = '_table_split';

    public $timestamps = false;


    // переопределение данных
    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub

        static::retrieved(function($model){
            $model->title = $model->name;
            $model->dataIndex = $model->bd_name;
            $model->key = $model->bd_name;
            $model->select = 0;
            $model->select_list = [];

            if($model->bd_name == 'id' || $model->bd_name == 'adsapi_id' || $model->bd_name == 'target_profit' || $model->bd_name == 'percentage_income'
                || $model->bd_name == 'price_actual' || $model->bd_name == 'price_actual_to_current_day' || $model->bd_name == '_izmen' || $model->bd_name == 'price_start'
                || $model->bd_name == 'square_meter_price' || $model->bd_name == '_soot' || $model->bd_name == 'count_sold_objects' || $model->bd_name == '_soot_analog'
                || $model->bd_name == '_soot_analog_to_current' || $model->bd_name == 'count_analog' || $model->bd_name == 'notRemovedAnalogAdsAvgPrice' || $model->bd_name == 'notRemovedAnalogAdsCount'
                || $model->bd_name == 'views_all' || $model->bd_name == 'ad_remove' || $model->bd_name == 'ad_published' || $model->bd_name == 'ad_added'
                || $model->bd_name == 'ad_updated' || $model->bd_name == 'phone_protected' || $model->bd_name == 'count_ads_same_phone' || $model->bd_name == 'agent_id'
                || $model->bd_name == 'coords' || $model->bd_name == 'km_do_metro' || $model->bd_name == 'new_repair' || $model->bd_name == 'building_year'
                || $model->bd_name == 'rooms_count' || $model->bd_name == 'living_area' || $model->bd_name == 'kitchen_area' || $model->bd_name == 'current_floor'
                || $model->bd_name == 'total_floors'){
                $model->textAlign = "right";
            }

            if($model->bd_name == 'city'){
                $model->select = 1;
                $model->select_list = [
                    "Все записи",
                    "Пустые записи",
                    "Видное",
                    "Долгопрудный",
                    "Домодедово",
                    "Зеленогорск",
                    "Зеленоград",
                    "Клин",
                    "Колпино",
                    "Коммунарка",
                    "Королев",
                    "Красное Село",
                    "Кронштадт",
                    "Ломоносов",
                    "Москва",
                    "Мытищи",
                    "Одинцово",
                    "Павловск",
                    "Петергоф",
                    "Пушкин",
                    "Рязань",
                    "Санкт-Петербург",
                    "Сергиево-Посадский",
                    "Сестрорецк",
                    "Солнечногорск",
                    "Химки",
                    "Чехов",
                ];
            }

            if($model->bd_name == 'id_source'){
                $model->select = 1;
                $model->select_list = [
                    "Все записи",
                    "Авито",
                    "Циан"
                ];
            }

            if($model->bd_name == 'km_do_metro'){
                $model->select = 1;
                $model->select_list = [
                    "Все записи",
                    "До 1 км",
                    "От 1 до 2км",
                    "От 2 и более км"
                ];
            }

            if($model->bd_name == 'have_doubles'){
                $model->select = 1;
                $model->select_list = [
                    "Все записи",
                    "Да",
                    "Нет"
                ];
            }

            if($model->bd_name == 'isAdsApi'){
                $model->select = 1;
                $model->select_list = [
                    "Все записи",
                    "Ads Api",
                    "Носов"
                ];
            }

            if($model->bd_name == 'status'){
                $model->select = 1;
                $model->select_list = [
                    "Все записи",
                    "Пустые записи",
                    "1.Оцениваем",
                    "2.Назначаем",
                    "3.На просмотр",
                    "4.Думают",
                    "8.Комитет",
                    "9. На подготовку",
                    "C1",
                    "актуально",
                    "апартаменты",
                    "далеко",
                    "ддк",
                    "ДДУ",
                    "дорого",
                    "ДУБЛЬ",
                    "мало аналогов",
                    "наше",
                    "не актуально",
                    "не берет трубку",
                    "не подходит",
                    "нет адреса",
                    "ошибка",
                    "П1",
                    "П2",
                    "П3",
                    "продали",
                    "реновация",
                    "С1",
                    "С3",
                    "сброс",
                    "сняли",
                    "торги",
                    "упустили",
                    "фейк",
                    "Без дублей и ДДУ",
                    "Исключ. 9 парам."
                ];
            }
        });
    }
}
