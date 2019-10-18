<?php

namespace App\Http\Controllers\Customer;

use App\Models\Promotion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function validateCode(Request $request)
    {
        if (!$promoCode = $request->get('promo_code')) {
            return response()->json([
                'status' => 400,
                'message' => 'Promotion code is required'
            ]);
        }

        $promotion = Promotion::checkCode($promoCode)->first();

        if (!$promotion) {
            return response()->json([
                'status' => 400,
                'percentage' => 0,
                'message' => 'Invalid promo code'
            ]);
        }

        if ($promotion->isExp()) {
            return response()->json([
                'status' => 400,
                'percentage' => 0,
                'message' => 'Promo code is expired'
            ]);
        }

        return response()->json([
            'status' => 200,
            'percentage' => $promotion->percentage,
            'message' => 'Promo code is valid'
        ]);
    }
}
