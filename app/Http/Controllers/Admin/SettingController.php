<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function getSetting(Request $request)
    {
        try {
            $zone = Setting::all();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'zones' => $zone,
        ]);
    }

    public function createSetting(Request $request)
    {
        $zone = new Setting;
        $zone->name = $request->get('name');
        $zone->number = $request->get('number');

        // $zone->promo_code = "ab";
        // $zone->description = "sas";
        // $zone->percentage = "20";
        // $zone->discount_type = "1";
        // $zone->total = "1";
        // $zone->status = "1";
        // $zone->start_date = "2019/10/11";
        // $zone->end_date = "2019/10/11";

        // $zone->save();
        try {
            $zone->save();

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
            'service' => $zone
        ]);
    }

    public function updateSetting(Request $request, $id)
    {

        if (!$update = Setting::find($id)) {
            return response()->json([
                'status' => 404,
                'message' => 'zone_not_found'
            ]);
        }

        $update = Setting::where('id', $id)->first();
        $update->name = $request->get('name');
        $update->number = $request->get('number');

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
            'updated_data' => $update
        ]);
    }

    public function deleteSetting(Request $request, $id){
        {
            try {
                $delete = Setting::destroy($id);
    
            } catch (QueryException $e) {
                return response()->json([
                    'status' => 500,
                    'message' => 'database_error',
                    'errors' => $e
                ]);
            }
    
            return response()->json([
                'status' => 200,
                'message' => 'delete_success',
                'updated_data' => [
                    'id' => $id
                ]
            ]);
        }
    }

   
}
