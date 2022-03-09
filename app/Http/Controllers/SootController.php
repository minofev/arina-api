<?php

namespace App\Http\Controllers;

use App\Models\GeneralData;
use Illuminate\Http\Request;

class SootController extends Controller
{
    public function set(Request $request)
    {
        foreach ($request->all() as $item) {
            if(empty($item->id)) continue;

            GeneralData::where('id', $item->id)->update(['SootSAnSgdn' => $item->SootSAnSgdn]);
        }

        return response([
            'status' => 'ok',
            'request' => $request->all()
        ])->setStatusCode(200);
    }
}
