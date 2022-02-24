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

            if($model->bd_name == 'have_doubles'){
                $model->select = 1;
                $model->select_list = [
                    "Да",
                    "Нет"
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
