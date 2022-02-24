<?php

namespace App\Http\Controllers;

use App\Models\GeneralData;
use App\Models\TableSplit;
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

            if($item === 'page') continue;

            if($foreachCounter == 1){
                $whereRaw = $item . " = '" . $key . "'";
            }else{
                $whereRaw = $whereRaw . " AND " . $item . " = '" . $key . "'";
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

        GeneralData::where('id', $request->id)->update([$request->name => $request->value]);

        return response()->json([
            'message' => 'successfull',
            'code' => 200
        ])->setStatusCode(200);
    }
}
