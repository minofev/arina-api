<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecalcsPercents extends Model
{
    use HasFactory;

    protected $table = "recalcs_for_special_tables_percents";

    public $timestamps = false;
}
