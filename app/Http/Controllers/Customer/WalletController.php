<?php

namespace App\Http\Controllers\Customer;

use App\User;
use App\Models\Wallet;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;

class WalletController extends Controller
{
    public function getBalance(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $wallet = $user->wallet;

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'wallet' => $wallet
        ]);
    }

    public function getWalletHistory(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $activities = $user->wallet->activities()
                // ->whereDate('created_at', date('Y-m-d'))
                ->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'wallet_history' => $activities
        ]);
    }

    public function createWallet(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        // If user has already enable wallet, return wallet
        if ($wallet = $user->wallet) {
            return response()->json([
                'status' => 200,
                'wallet' => $wallet
            ]);
        }

        try {
            $wallet = new Wallet();
            $wallet->guid = guidv4();

            $user->wallet()->save($wallet);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'wallet' => $wallet
        ]);
    }
}
