<?php

namespace App\Jobs;

use App\Models\GeneralData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DuplicatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $timerStart = microtime(true);

        $foreachCounter = 0;

        $counterOfCount = GeneralData::orderBy('id', 'desc')->where('id', '>=', 1893536893)->count();
        $text = "Предположительное колво итераций: $counterOfCount";
        //$this->info($text);
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/searchDuplicates_stream-rabbit1.log'),
        ])->info($text);

        GeneralData::orderBy('id', 'desc')->where('id', '>=', 1893536893)->chunk(1000, function($items) use(&$foreachCounter, &$timerStart, &$counterOfCount){

            foreach ($items as $item) {
                $foreachCounter++;

                $text = $foreachCounter . ') Начинаем сверять запись ID: '. $item->id .' от ' . date("d-m-Y", $item->ad_added) . ' (memory usage: ' . memory_get_usage() / 1048576 . ' mb)';
                //$this->info($text);
                Log::build([
                    'driver' => 'single',
                    'path' => storage_path('logs/searchDuplicates_stream-rabbit1.log'),
                ])->info($text);

                // have_doubles обнуляем (ведь ищем все дубли заново, старые нам не подходят)
                $item->have_doubles = 0;

                // только если статус равен "ДУБЛЬ" - убираем
                if($item->status == "ДУБЛЬ"){
                    $item->status = "";
                }

                // сохраняем изменения в коллекции
                $item->save();

                // высчитываем максимальную и минимальную площадь
                // относительно конкретного элемента +-5% (10% разброс в площади)
                $total_area_max = $item->total_area + (($item->total_area / 100) * 5);
                $total_area_min = $item->total_area - (($item->total_area / 100) * 5);

                // высчитываем максимальную и минимальую цену
                // относительно конкретного элемента +-5% (10% разброс площади)
                $price_actual_min = $item->price_actual - (($item->price_actual / 100) * 5);
                $price_actual_max = $item->price_actual + (($item->price_actual / 100) * 5);

                //опубликовано
                $add_published_compare = 0;

                if ($item->ad_published) {
                    $add_published_compare = $item->ad_published - (35*24*60*60);
                } else {
                    $add_published_compare = 0;
                }

                // ищем дубли для $item по определенным параметрам
                $duplicatesData = GeneralData::where('city', $item->city)
                    ->where('street', 'like', '%'.$item->street.'%')
                    ->where('house', $item->house)
                    ->where('current_floor', $item->current_floor)
                    ->where('rooms_count', $item->rooms_count)
                    ->where('objecttype', $item->objecttype)
                    ->where('id', '!=', $item->id)
                    ->where('total_area', '>', 0)
                    ->where('total_area', '>=', $total_area_min)
                    ->where('total_area', '<=', $total_area_max)
                    ->where('price_actual', '>=', $price_actual_min)
                    ->where('price_actual', '<=', $price_actual_max)
                    ->where('ad_published', '<=', $add_published_compare)
                    ->get();

                if($duplicatesData->count() !== 0) {

                    // выводим
                    $logText = "Для ID: " . $item->id . ' найдено '.$duplicatesData->count().' дублей. Начинаем сверять.';
                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/searchDuplicates_stream-rabbit1.log'),
                    ])->info($logText);
                    //$this->error($logText);

                    // массив со свеми дублями
                    $doubles = [];

                    // сразу в $doubles кладем коллекцию, по которой ищем
                    $doubles[] = $item;

                    // теперь перебираем все найденные дубли
                    // и тоже кладем их в $doubles
                    foreach ($duplicatesData as $double) {
                        $doubles[] = $double;
                    }

                    // последняя дата публикации
                    $published_time = 0;
                    // id последней публикации
                    $collection_id = 0;

                    // перебираем дубли
                    // находим последний опубликованный
                    foreach ($doubles as $double) {

                        // выводим
                        $logText = "---------Вероятный дубль ID: " . $double->id;
                        Log::build([
                            'driver' => 'single',
                            'path' => storage_path('logs/searchDuplicates_stream-rabbit1.log'),
                        ])->info($logText);
                        //$this->error($logText);

                        if($double->ad_published > $published_time){
                            // записываем последний опубликованный элемент
                            $published_time     = $double->ad_published;
                            $collection_id      = $double->id;
                            $collection_double  = $double;
                        }
                    }

                    // мы нашли последний опубликованный элемент
                    // теперь надо проверить
                    // если разница по дням публикации более 5 дней
                    // то оставляем этот элемент как самый актуальный
                    // если разница по дням публикации менее 5 дней
                    // то ищем самый актуальный по самой дешевой цене

                    foreach ($doubles as $double) {
                        // чтобы не сравнивать даты одной и той же записи
                        // то при нахождении этой записи просто пропускаем итерацию
                        if($collection_id == $double->id){
                            continue;
                        }

                        // ищем объявления с разницей в 5 дней (86400с = 1 день)
                        if(($published_time - (86400 * 5)) > $double->ad_published){
                            // если разница объявлений более 5 дней
                        }else{
                            // если разница объявлений менее или равна 5 дням
                            // сравниваем их цены
                            if($double->price_actual == $collection_double->price_actual){
                                // если цены одинаковые
                                // сравниваем цены за квадратный метр
                                // если цена за квадратный метр у дубликата меньше
                                // то дубликат становится актуальным
                                if($double->square_meter_price < $collection_double->square_meter_price){
                                    $collection_id      = $double->id;
                                    $published_time     = $double->ad_published;
                                    $collection_double  = $double;
                                }
                            }else if($double->price_actual < $collection_double->price_actual){
                                // если у дубликата цена ниже выбранного элемента (актуального)
                                // то дубликат становится выбранным элементом (актуальным)
                                $collection_id      = $double->id;
                                $published_time     = $double->ad_published;
                                $collection_double  = $double;
                            }

                        }

                        foreach ($doubles as $double) {
                            $double->have_doubles = 1;

                            if($double->status != "сняли"
                                & $double->status != "продали"
                                & $double->status != "актуально"
                                & $double->status != "мало аналогов"
                                & $double->status != "не подходит"
                                & $double->status != "сброс"
                                & $double->status != "нет ответа"
                                & $double->status != "упустили"
                                & $double->status != "ошибка"
                                & $double->status != "фейк"
                                & $double->status != "П"
                                & $double->status != "С"
                                & $double->status != "Оцениваем"
                                & $double->status != "назначаем"
                                & $double->status != "На просмотр"
                                & $double->status != "думают"
                                & $double->status != "на юр. пре-скоринг"
                                & $double->status != "на аванс"
                                & $double->status != "на фин. юр. скоринг"
                                & $double->status != "на комитет"
                                & $double->status != "на подготовку сделки"
                                & $double->status != "на сделку"
                                & $double->status != "отклонено комитетом"
                                & $double->status != "купили"
                                & $double->status != "наше"){
                                $double->status = "";
                            }

                            if($double->id != $collection_id){
                                if($double->status != "сняли"
                                    & $double->status != "продали"
                                    & $double->status != "актуально"
                                    & $double->status != "мало аналогов"
                                    & $double->status != "не подходит"
                                    & $double->status != "сброс"
                                    & $double->status != "нет ответа"
                                    & $double->status != "упустили"
                                    & $double->status != "ошибка"
                                    & $double->status != "фейк"
                                    & $double->status != "П"
                                    & $double->status != "С"
                                    & $double->status != "Оцениваем"
                                    & $double->status != "назначаем"
                                    & $double->status != "На просмотр"
                                    & $double->status != "думают"
                                    & $double->status != "на юр. пре-скоринг"
                                    & $double->status != "на аванс"
                                    & $double->status != "на фин. юр. скоринг"
                                    & $double->status != "на комитет"
                                    & $double->status != "на подготовку сделки"
                                    & $double->status != "на сделку"
                                    & $double->status != "отклонено комитетом"
                                    & $double->status != "купили"
                                    & $double->status != "наше"){
                                    $double->status = "ДУБЛЬ";
                                }
                            }

                            $double->save();
                        }
                    }
                }

                // удаляем из памяти
                // сокращение времени в 10 раз
                // (пару раз дало буст к скорости работы скрипта в 10х)
                unset($duplicatesData);
                unset($duplicatesDatum);
                unset($doubles);
                unset($double);

                // каждые 10 запросов делаем пузу в 3 секунды
                // можно уменьшить, но надо тестить
                // а то без таймера выскакивало "Connection refused" с БД
                // (но 5 секунд наверное все таки много)
                // (примерно 1000 итераций за 5 минут)
                // (10.000 итераций примерно за 50 минут)
                // (100.000 итераций примерно за 8.3 часов)
                // (1.000.000 итераций примерно за 83 часа)
                if($foreachCounter % 10000 == 0){

                    $timerEnd = 'Время выполнения скрипта за 10.000 итераций: '.round(microtime(true) - $timerStart, 4).' сек.';
                    // логгируем время выполнения скрипта
                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/searchDuplicates_stream-rabbit1.log'),
                    ])->info($timerEnd);
                    //$this->error($timerEnd);

                    $text = "Итерация $foreachCounter из $counterOfCount";
                    // логгируем время выполнения скрипта
                    Log::build([
                        'driver' => 'single',
                        'path' => storage_path('logs/searchDuplicates_stream-rabbit1.log'),
                    ])->info($timerEnd);
                    //$this->error($timerEnd);

                    sleep(3);
                }
            }

        });

        $timerEnd = 'Время выполнения скрипта: '.round(microtime(true) - $timerStart, 4).' сек.';

        // логгируем время выполнения скрипта
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/searchDuplicates_stream-rabbit1.log'),
        ])->info($timerEnd);
        //$this->error($timerEnd);
    }
}
