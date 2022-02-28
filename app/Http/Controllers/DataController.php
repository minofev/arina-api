<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Models\GeneralData;
use App\Models\TableSplit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * метод получения первых данных для отрисовки страницы
     * дергается сразу при загрузке таблицы
     */
    public function get()
    {
        // данные
        $data = GeneralData::orderBy('id', 'desc')->paginate(50);

        return response()->json([
            'data' => $data
        ])->setStatusCode(200);
    }

    public function columns()
    {
        // плохо от одного вида этого огромного массива
        // нужно его куда то деть
        $orderedColumns = array(
            'id',
            'id_source',
            'adsapi_id',
            'status',
            'have_doubles',
            'comments',
            'auction',
            'bonus',
            'owners',
            'registered',
            'check_out',
            'constitutive',
            'new_coefficient',
            'price_sale',
            'target_profit',
            'percentage_income',
            'grand_yandk',
            'grand_real',
            'grand_cian',
            'grand_avit',
            'grand_pik',
            'grand_irn',
            'grand_dmk',
            'standard_deviation',
            'house_characteristics',
            'legal_aspects',
            'link',
            '_fakt',
            'price_actual',
            'price_actual_to_current_day',
            '_izmen',
            'price_start',
            'square_meter_price',
            'square_meter_price2',
            //'square_meter_price3',
            //'square_meter_price4',
            '_soot',
            'accuracy',
            'count_sold_objects',
            'square_meter_price2_analog',
            '_soot_analog',
            '_soot_analog_to_current',
            'accuracy_analog',
            'count_analog',
            'notRemovedAnalogAdsAvgPrice',
            'notRemovedAnalogAdsCount',
            'views_all',
            'ad_remove',
            'ad_published',
            'ad_added',
            'ad_updated',
            'responsible_realtor_partner',
            'offer_date',
            'purchase_advance_date',
            'date_of_purchase_transaction',
            'sale_advertising_date',
            'date_of_sale_advance',
            'date_of_transaction_sale',
            'region',
            'address',
            'city',
            'street_prefix',
            'street',
            'house',
            'phones',
            'direct_phone',
            'agent_type',
            'phone_protected',
            'phone_region',
            'phone_operator',
            'count_ads_same_phone',
            'person_type',
            'person',
            'agent_id',
            'contactname',
            'coords',
            'metro',
            'km_do_metro',
            'complex_name',
            'house_rating',
            // убираем
            //'repair',
            'new_repair',
            'balconies',
            'restroom',
            'ceiling_height',
            'objecttype',
            'property_type',
            'house_type',
            'building_year',
            'rooms_count',
            'total_area',
            'living_area',
            'kitchen_area',
            'current_floor',
            'total_floors',
            'with_photos',
            'title',
            'description',
            'demand_test_results',
            'nechaev',
            'shavnev',
            'dyakonov'
        );

        // колонки
        $columnNames = TableSplit::all();
        return response()->json([
            'columnNames' => $columnNames,
            'orderedColumns' => $orderedColumns
        ])->setStatusCode(200);
    }

    /**
     * @param Request $request
     * метод сортировки данных
     * принимает $request
     * в котором содержатся поле => метод сортировки
     *
     * example:
     * "id" => "desc"
     */
    public function sort(Request $request)
    {
        $items = $request->all();
        $orderRaw = "";

        // чтобы узнать первую итерацию цикла
        $foreachCounter = 0;

        foreach ($items as $item=>$key) {
            $foreachCounter++;

            if($item === 'page') continue;

            if($foreachCounter == 1){
                $orderRaw = $item . " " . strtoupper($key);
            }else{
                $orderRaw = $orderRaw . ", " . $item . " " . strtoupper($key);
            }
        }

        $data = GeneralData::orderByRaw($orderRaw)->paginate(50);

        return response()->json([
            'data' => $data
        ])->setStatusCode(200);
    }

    public function take(Request $request)
    {
        $items = $request->all();
        $whereRaw = "";

        // чтобы узнать первую итерацию цикла
        $foreachCounter = 0;

        foreach ($items as $item=>$key) {
            $foreachCounter++;

            // чтобы с пагинацией багов не было
            if($item === 'page') continue;

            // если больше одной итерации - добавляем оператор AND
            if($foreachCounter > 1){
                $whereRaw = $whereRaw . " AND ";
            }

            if($item == 'ad_added' || $item == 'ad_published' || $item == 'ad_remove'){
                // при условии что дата всегда в формате день-месяц-год
                // поочередность конкретно, на разделитель не смотрим
                // делим строку по разделителю
                // который узнаем через mb_substr
                $date = explode(mb_substr($key, 2, 1), $key);
                // далее год записываем в $year
                $year = $date[2];

                // если в "году" всего 2 символа, добавляем в начало 20 (чтобы получился 2022 напирмер)
                if(iconv_strlen($year) == 2){
                    $year = "20" . $year;
                }

                // формируем полную дату
                $key = $date[0] . "-" . $date[1] . "-" . $year;

                // переводим в unix метку
                $key = Carbon::parse($key)->timestamp;

                // формируем запрос
                $whereRaw = $whereRaw . " ($item >= $key and $item <= ". (intval($key) + 3600 * 24) .")";
            } else if($item == 'have_doubles') {
                if($key == 'Да'){$key = 1;} else if($key == 'Нет'){$key = 0;}else{$key = "ERROR";}

                $whereRaw = $whereRaw . $item . " = '" . $key . "'";
            } else if($item == 'status'){
                if($key == 'Все записи'){
                    $key = "IS NOT NULL";
                }

                $whereRaw = $whereRaw . $item . " " . $key . "";
            }
            else{

                $whereRaw = $whereRaw . $item . " = '" . $key . "'";
            }

        }


        $data = GeneralData::whereRaw($whereRaw)->paginate(50);

        return response()->json([
            'data' => $data,
            'request' => $whereRaw
        ])->setStatusCode(200);
    }

    public function change(Request $request)
    {
        // если это какие то сокращенные (приведенные) значения
        if($request->name == "price_actual" || $request->name == "price_actual_to_current_day" || $request->name == "price_start"){
            $request->value = $request->value * 1000000;
        }
        if($request->name == "square_meter_price"){
            $request->value = $request->value * 1000;
        }

        if($request->name == "comments" && $request->value == ""){
            $request->value = 'пусто';
        }

        GeneralData::where('id', $request->id)->update([$request->name => $request->value]);

        return response()->json([
            'message' => 'successfull',
            'code' => 200
        ])->setStatusCode(200);
    }

    public function v2__get(Request $request)
    {
        // select - выборка
        // sort - сортировка

        // сюда будем записывать sql выборки
        $whereRaw = "";

        // сюда будем записываться sql сортировки
        $sortRaw = "";

        // сначала делаем выборку
        // чтобы при сортировке уменьшить колво записей в коллекции
        if(!empty($request->select)){
            // счетчик цикла
            $foreachCounter = 0;
            foreach ($request->select as $item=>$value) {
                $foreachCounter++;

                // если больше одной итерации - добавляем оператор AND
                if($foreachCounter > 1){
                    $whereRaw = $whereRaw . " AND ";
                }

                if($item == 'ad_added' || $item == 'ad_published' || $item == 'ad_remove'){
                    if(iconv_strlen($value) > 12){
                        // если указано от и до
                    }else{
                        // если всего одна дата
                        $operators = ['>', '<', '=', '>=', '<='];

                        // значение с оператором
                        $valueWithOperator = $value;

                        // значение без оператора
                        $value = str_replace($operators, '', $valueWithOperator);

                        // оператор
                        $operatorValue = str_replace($value, '', $valueWithOperator);

                        if(!$operatorValue || $operatorValue == '='){
                            // если оператора нет
                            // или в качестве оператора жеское сравнение
                            // что по сути одно и то же

                            // переводим в unix метку
                            $value = Carbon::parse($value)->timestamp;

                            // формируем запрос
                            // и берем в охват целые сутки (с 00:00 до 24:00)
                            $whereRaw = $whereRaw . " ($item >= ". (intval($value) - 3600 * 21) ." and $item <= ". (intval($value) + 3600 * 3) .")";
                        }

                        if($operatorValue && $operatorValue != '='){
                            // переводим в unix метку
                            $value = Carbon::parse($value)->timestamp;

                            $whereRaw = $whereRaw . " $item " . $operatorValue . " " . (intval($value) - 3600 * 21);
                        }
                    }
                } else if($item == 'have_doubles') {
                    if($value == 'Да'){$value = 1;} else if($value == 'Нет'){$value = 0;}else{$value = "ERROR";}

                    $whereRaw = $whereRaw . $item . " = '" . $value . "'";
                } else if($item == 'status'){
                    if($value == 'Все записи'){
                        $value = "IS NOT NULL";

                        $whereRaw = $whereRaw . $item . " = '" . $value . "'";
                    }else if($value == 'Пустые записи'){
                        $value = "";

                        $whereRaw = $whereRaw . $item . " = '" . $value . "'";
                    }else if($value == 'Без дублей и ДДУ'){
                        $value = "";

                        $whereRaw = $whereRaw . $item . " != 'дубль' and ". $item . " != 'ДДУ'";
                    }else if($value == 'Исключ. 9 парам.'){
                        $value = "";

                        $whereRaw = $whereRaw . $item . " != '9. На подготовку'";
                    }
                } else if($item == 'price_actual'){
                    $value = intval($value * 1000000);

                    $whereRaw = $whereRaw . $item . " = " . $value;
                } else if($item == 'km_do_metro') {
                    if ($value == 'До 1 км') {
                        $whereRaw = $whereRaw . $item . " <= 1";
                    } else if ($value == 'От 1 до 2км') {
                        $whereRaw = $whereRaw . $item . " >= 1 and " . $item . " <= 2";
                    } else if ($value == 'От 2 и более км') {
                        $whereRaw = $whereRaw . $item . "> 2";
                    }
                } else if($item == 'id_source'){
                    if($value == 'Авито'){
                        $value = 2;
                    }else if($value == 'Циан'){
                        $value = 1;
                    }

                    $whereRaw = $whereRaw . "$item = $value";
                } else{

                    $whereRaw = $whereRaw . "$item = '$value'";
                }
            }
        }

        if(!empty($request->sort)){
            // счетчик цикла
            $foreachCounter = 0;
            foreach ($request->sort as $item=>$value) {
                $foreachCounter++;

                if($foreachCounter > 1){
                    $sortRaw = $sortRaw . " ";
                }
                $sortRaw = $sortRaw . "$item " . strtoupper($value);
            }
        }

        if(empty($sortRaw) && !empty($whereRaw)){
            // если пустая сортировка и не пустая выборка
            $data = GeneralData::whereRaw($whereRaw)->paginate($request->itemscount);
        }else if(empty($whereRaw) && !empty($sortRaw)){
            // если пустая выборка и не пустая сортировка
            $data = GeneralData::orderByRaw($sortRaw)->paginate($request->itemscount);
        }else if(empty($whereRaw) && empty($sortRaw)){
            // если пустая выборка и сортировка
            $data = GeneralData::paginate($request->itemscount);
        }else{
            // если есть и сортировка и выборка вместе
            $data = GeneralData::orderByRaw($sortRaw)->whereRaw($whereRaw)->paginate($request->itemscount);
        }

        return response()->json([
            'data' => $data,
            'code' => 200
        ])->setStatusCode(200);
    }
}
