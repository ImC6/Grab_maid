<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CommunicationSetting;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class CommunicationSettingController extends Controller {

    public function getSettings(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $comSettings = $user->communicationSetting;
            if (!$comSettings) {
                $returnData = [
                    'email' => 0,
                    'sms' => 0,
                    'call' => 0,
                ];
            } else {
                $settings = json_decode($comSettings->setting, true);
                $returnData = [
                    'email' => $settings['email'] ?? 0,
                    'sms' => $settings['sms'] ?? 0,
                    'call' => $settings['call'] ?? 0,
                ];
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
            'settings' => $returnData
        ]);
    }

    public function updateSetting(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        $email = $request->get('email');
        $sms = $request->get('sms');
        $call = $request->get('call');
        $comSettings = [
            'email' => $email ?? 0,
            'sms' => $sms ?? 0,
            'call' => $call ?? 0
        ];

        try {
            $settingModel = $user->communicationSetting;
            if (!$settingModel) {
                $settingModel = new CommunicationSetting();
            }

            $settingModel->setting = json_encode($comSettings);
            $user->communicationSetting()->save($settingModel);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Settings is updated!',
            'settings' =>  $comSettings
        ]);
    }
}
