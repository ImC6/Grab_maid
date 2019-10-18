<?php

namespace App\Http\Controllers\Customer;

use App\Models\Tax;
use App\Models\VendorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function getTaxesByBookingNumber(Request $request, $vendorServiceId)
    {
        $taxes = json_decode(Tax::first()->tax, true);
        $vendorService = VendorService::select('price')->where('id', $vendorServiceId)->first();

        $taxes['insurance'] = number_format((float)$taxes['insurance'], 2, '.', '');
        $taxes['service_tax'] = number_format((float)(0 * $taxes['service_tax'] / 100), 2, '.', '');
        $taxes['shipping_fee'] = number_format((float)$taxes['shipping_fee'], 2, '.', '');

        if (!$vendorService) {
            return response()->json([
                'status' => 404,
                'message' => 'Vendor service not found',
                'taxes' => $taxes,
            ]);
        }

        $taxes['service_tax'] = number_format((float)($vendorService->price * $taxes['service_tax'] / 100), 2, '.', '');

        return response()->json([
            'status' => 200,
            'taxes' => $taxes,
        ]);
    }
}
