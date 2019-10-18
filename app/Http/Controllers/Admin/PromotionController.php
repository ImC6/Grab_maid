<?php

namespace App\Http\Controllers\Admin;

use App\Models\Promotion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    public function getPromotion(Request $request)
    {
        try {
            $zone = Promotion::all();

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

    public function createPromotion(Request $request)
    {
        $zone = new Promotion;
        $zone->promo_code = $request->get('promo_code');
        $zone->description = $request->get('description');
        $zone->percentage = $request->get('percentage');
        $zone->discount_type = $request->get('discount_type');
        $zone->total = $request->get('total');
        $zone->status = $request->get('status');
        $zone->start_date = $request->get('start_date');
        $zone->end_date = $request->get('end_date');

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

    public function updatePromotion(Request $request, $id)
    {

        if (!$update = Promotion::find($id)) {
            return response()->json([
                'status' => 404,
                'message' => 'zone_not_found'
            ]);
        }

        $update = Promotion::where('id', $id)->first();
        $update->promo_code = $request->get('promo_code');
        $update->description = $request->get('description');
        $update->percentage = $request->get('percentage');
        $update->discount_type = $request->get('discount_type');
        $update->total = $request->get('total');
        $update->status = $request->get('status');
        $update->start_date = $request->get('start_date');
        $update->end_date = $request->get('end_date');

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

    public function deletePromotion(Request $request, $id){
        {
            try {
                $delete = Promotion::destroy($id);
    
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
