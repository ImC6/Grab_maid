<?php

namespace App\Http\Controllers\Admin;

use App\Models\ExtraCharge;
use App\Models\Zone;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class ExtraChargeController extends Controller
{
   public function addExtra(Request $request){

    $extra = new ExtraCharge;
    $extra->name = $request->name;
    $extra->amount = $request->amount;

    try {
        $extra->save();

    } catch (QueryException $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Database error',
            'errors' => $e
        ]);
    }

    return response()->json([
        'status' => 200,
        'message' => 'create_success',
        'extra' => $extra
    ]);
   }

   public function getExtra(){
    try {
        $extra = ExtraCharge::all();

    } catch (QueryException $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Database error',
            'errors' => $e
        ]);
    }

    return response()->json([
        'status' => 200,
        'extra' => $extra,
    ]);
   }

   public function updateExtra(Request $request, $id)
    {

        if (!$extra = ExtraCharge::find($id)) {
            return response()->json([
                'status' => 404,
                'message' => 'zone_not_found'
            ]);
        }

        $update = ExtraCharge::where('id', $id)->first();
        $update->name = $request->name;
        $update->amount = $request->amount;


        try {
            $update->save();

            if (!$update) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Database error'
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'update_success',
            'updated_data' => $extra
        ]);
    }
}
