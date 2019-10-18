<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Topup;
use App\Models\Wallet;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\BookingSession;
use App\Models\IPayBank;
use App\Models\WalletActivity;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

class PaymentController extends Controller
{
    public function getIPayBankList(Request $request)
    {
        try {
            //$bankList = IPayBank::all('payment_id', 'name', 'icon');
			$bankList = IPayBank::select('sort', 'payment_id', 'name', 'icon')->orderBy('sort')->get();
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'ipay_bank_list' => $bankList
        ]);
    }

    public function getTopupList(Request $request)
    {
        try {
            $topupList = Topup::all('id', 'amount', 'value', 'desc');
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'topup_list' => $topupList
        ]);
    }

    public function getPaymentByRefNo(Request $request, $refNo)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            if (!$payment = $user->payments()->where('ref_no', $refNo)->first()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Payment not found'
                ]);
            }

            $payment->load(['user.wallet']);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'payment' => $payment
        ]);
    }

    public function createTopupPayment(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'topup_id' => 'required|numeric',
            'payment_type' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors(),
                'message' => 'Missing parameters'
            ]);
        }

        $topupId = $request->get('topup_id');
        $paymentType = $request->get('payment_type');

        try {
            $topup = Topup::find($topupId);
            if (!$topup) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Top up not found'
                ]);
            }

            $lastTopup = Payment::isTopup()->orderBy('id', 'desc')->first();
            $lastTopupRefNo = $lastTopup ? $lastTopup->ref_no : 0;
            $refNo = getTopupNumber($lastTopupRefNo);

            $payment = new Payment();
            $payment->ref_no = $refNo;
            $payment->type = config('grabmaid.payment.topup');
            $payment->amount = $topup->amount;
            // $payment->desc = 'Topup payment for ' . $refNo;
            $payment->topup_id = $topup->id;
            $payment->status = 0;
            $payment->payment_type = $paymentType;
            $user->payments()->save($payment);


            if (!$payment = $user->payments()->where('ref_no', $refNo)->first()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Payment not found'
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
            'message' => 'Payment is created',
            'payment' => $payment
        ]);
    }

    public function booking(Request $request)
    {
        // $refNo = 'GM00000001';
        // $amount = 1.00;
        // $amountStr = number_format($amount, 2, '', '');
        // $currency = 'MYR';
        // $signatureStr = env('IPAY_MERCHANT_KEY') . env('IPAY_MERCHANT_CODE') . $refNo . $amountStr . $currency;
        // $username = 'Keng Shen';
        // $email = 'kengshen92@gmail.com';
        // $contact = '0199360536';

        $hasRequiredFiled = true;

        if (!$refNo = $request->get('ref_no')) {
            $hasRequiredFiled = false;
        }
        if (!$paymentId = $request->get('payment_id')) {
            $hasRequiredFiled = false;
        }
        if (!$currency = $request->get('currency')) {
            $hasRequiredFiled = false;
        }
        if (!$username = $request->get('username')) {
            $hasRequiredFiled = false;
        }
        if (!$email = $request->get('email')) {
            $hasRequiredFiled = false;
        }
        if (!$contact = $request->get('contact')) {
            $hasRequiredFiled = false;
        }

        if (!$user = User::where('email', $email)->first()) {
            $hasRequiredFiled = false;
        }

        $data = [
            'refNo' => $refNo,
            'paymentId' => $paymentId,
            'currency' => $currency,
            'username' => $username,
            'email' => $email,
            'contact' => $contact,
            'responseUrl' => route('payment.booking.response'),
            'backendUrl' => route('payment.booking.backend')
        ];

        if ($hasRequiredFiled) {

            try {
                $booking = Booking::where('booking_number', $refNo)->first();

                if (is_null($user->mobile_no)) {
                    $user->mobile_no = $contact;
                    $user->save();
                }

                if (!$booking) {
                    $data['bookingFound'] = false;
                } else {
                    if (!$payment = Payment::where('ref_no', $refNo)->first()) {
                        $payment = new Payment();
                        $payment->ref_no = $refNo;
                        $payment->type = config('grabmaid.payment.booking');
                        $payment->amount = $booking->total_price;
                        $payment->desc = 'Booking payment for ' . $refNo;
                        $payment->status = 0;
                        $payment->payment_type = $booking->payment_type;

                        $user->payments()->save($payment);
                    }

                    if (floatval($booking->total_price) === floatval(0)) {
                        $booking->status = 1;
                        $payment->status = 1;

                        $booking->save();
                        $user->payments()->save($payment);

                        return redirect()->route('payment.back-to-app');
                    }
                }

            } catch (QueryException $e) {
                // Log::alert('Database error: ' . $e->getMessage());
                abort(500);
            }

            $amount = floatval($booking->total_price);
            $amount = floatval(1.00); // remove when go live
            $amountStr = number_format($amount, 2, '', '');
            $signatureStr = env('IPAY_MERCHANT_KEY') . env('IPAY_MERCHANT_CODE') . $refNo . $amountStr . $currency;

            $data['bookingFound'] = true;
            $data['amount'] = $amount;
            $data['amountStr'] = $amountStr;
            $data['signatureStr'] = $signatureStr;
            $data['signature'] = iPay88_signature($signatureStr);
        }

        $data['hasRequiredFiled'] = $hasRequiredFiled;

        return view('web.payment', [
            'data' => $data
        ]);
    }

    public function bookingPaymentReponse(Request $request)
    {
        $merchantCode = $request->get('MerchantCode');
        $paymentId = $request->get('PaymentId');
        $refNo = $request->get('RefNo');
        $amount = $request->get('Amount');
        $currency = $request->get('Currency');
        $remark = $request->get('Remark');
        $transId = $request->get('TransId');
        $authCode = $request->get('AuthCode');
        $eStatus = $request->get('Status');
        $errDesc = $request->get('ErrDesc');
        $signature = $request->get('Signature');
        $ccName = $request->get('CCName');
        $ccNo = $request->get('CCNo');
        $sBankname = $request->get('S_bankname');
        $sCountry = $request->get('S_country');

        $data = [
            'payment_status' => $eStatus,
        ];

        if ($eStatus == 1) {
            $amountStr = number_format($amount, 2, '', '');
            $responseSignatureStr = env('IPAY_MERCHANT_KEY') . env('IPAY_MERCHANT_CODE') . $paymentId . $refNo . $amountStr . $currency . $eStatus;
            $localGenerateResSignature = iPay88_signature($responseSignatureStr);

            if ($signature === $localGenerateResSignature) {
                try {
                    $booking = Booking::where('booking_number', $refNo)->first();

                    if (!$booking) {
                        $data['payment_status'] = 0;
                    } else {
                        if ($booking->status === config('grabmaid.booking.status.accepted')) {
                            $booking->status = config('grabmaid.booking.status.paid');
                            $booking->receipt = route('receipt', [
                                $booking->booking_number,
                                'token' => $booking->user->guid
                            ]);
                            $booking->save();

                            BookingSession::where('user_id', $booking->user->id)
                            ->where('vendor_service_id', $booking->vendor_service_id)
                            ->where('booking_date', $booking->booking_date)
                            ->delete();
                        }

                        if ($payment = Payment::where('ref_no', $refNo)->first()) {
                            $payment->status = 1;
                            $payment->save();
                        }
                    }

                } catch(QueryException $e) {
                    $data['payment_status'] = 0;
                    dump($e);
                    die;
                }
            } else {
                $data['payment_status'] = 0;
            }
        }

        return view('web.payment-response', [
            'data' => $data
        ]);
    }

    public function bookingPaymentBackend(Request $request)
    {
        $merchantCode = $request->get('MerchantCode');
        $paymentId = $request->get('PaymentId');
        $refNo = $request->get('RefNo');
        $amount = $request->get('Amount');
        $currency = $request->get('Currency');
        $remark = $request->get('Remark');
        $transId = $request->get('TransId');
        $authCode = $request->get('AuthCode');
        $eStatus = $request->get('Status');
        $errDesc = $request->get('ErrDesc');
        $signature = $request->get('Signature');
        $ccName = $request->get('CCName');
        $ccNo = $request->get('CCNo');
        $sBankname = $request->get('S_bankname');
        $sCountry = $request->get('S_country');

        if ($eStatus == 1) {
            $amountStr = number_format($amount, 2, '', '');
            $responseSignatureStr = env('IPAY_MERCHANT_KEY') . env('IPAY_MERCHANT_CODE') . $paymentId . $refNo . $amountStr . $currency . $eStatus;
            $localGenerateResSignature = iPay88_signature($responseSignatureStr);

            if ($signature === $localGenerateResSignature) {
                try {
                    $booking = Booking::where('booking_number', $refNo)->first();

                    if (!$booking) {
                        $data['payment_status'] = 0;
                    } else {
                        if ($booking->status === config('grabmaid.booking.status.accepted')) {
                            $booking->status = config('grabmaid.booking.status.paid');
                            $booking->receipt = route('receipt', [
                                $booking->booking_number,
                                'token' => $booking->user->guid
                            ]);
                            $booking->save();

                            BookingSession::where('user_id', $booking->user->id)
                            ->where('vendor_service_id', $booking->vendor_service_id)
                            ->where('booking_date', $booking->booking_date)
                            ->delete();
                        }

                        if ($payment = Payment::where('ref_no', $refNo)->first()) {
                            $payment->status = 1;
                            $payment->save();
                        }
                    }

                } catch(QueryException $e) {
                    $data['payment_status'] = 0;
                    dump($e);
                    die;
                }

                die('RECEIVEOK');
            } else {
                $data['payment_status'] = 0;
            }
        }
    }

    public function topup(Request $request)
    {
        $hasRequiredFiled = true;

        if (!$refNo = $request->get('ref_no')) {
            $hasRequiredFiled = false;
        }
        if (!$paymentId = $request->get('payment_id')) {
            $hasRequiredFiled = false;
        }
        if (!$currency = $request->get('currency')) {
            $hasRequiredFiled = false;
        }
        if (!$username = $request->get('username')) {
            $hasRequiredFiled = false;
        }
        if (!$email = $request->get('email')) {
            $hasRequiredFiled = false;
        }
        if (!$contact = $request->get('contact')) {
            $hasRequiredFiled = false;
        }

        try {
            if (!$user = User::where('email', $email)->first()) {
                $hasRequiredFiled = false;

                return view('web.payment', [
                    'data' => [
                        'hasRequiredFiled' => $hasRequiredFiled,
                        'errorMessage' => 'User email not found'
                    ]
                ]);
            }

            if (!$payment = $user->payments()->where('ref_no', $refNo)->first()) {
                $hasRequiredFiled = false;

                return view('web.payment', [
                    'data' => [
                        'hasRequiredFiled' => $hasRequiredFiled,
                        'errorMessage' => 'Payment not found'
                    ]
                ]);
            }

            if (!$topup = $payment->topup) {
                $hasRequiredFiled = false;

                return view('web.payment', [
                    'data' => [
                        'hasRequiredFiled' => $hasRequiredFiled,
                        'errorMessage' => 'Topup not found'
                    ]
                ]);
            }

            $data = [
                'refNo' => $refNo,
                'paymentId' => $paymentId,
                'currency' => $currency,
                'username' => $username,
                'email' => $email,
                'contact' => $contact,
                'responseUrl' => route('payment.topup.response'),
                'backendUrl' => route('payment.topup.backend')
            ];

            if ($hasRequiredFiled) {
                if (is_null($user->mobile_no)) {
                    $user->mobile_no = $contact;
                    $user->save();
                }
                $amount = floatval($topup->amount);
                $amount = floatval(1.00); // remove when go live
                $amountStr = number_format($amount, 2, '', '');
                $signatureStr = env('IPAY_MERCHANT_KEY') . env('IPAY_MERCHANT_CODE') . $refNo . $amountStr . $currency;

                $data['bookingFound'] = true;
                $data['amount'] = $amount;
                $data['amountStr'] = $amountStr;
                $data['signatureStr'] = $signatureStr;
                $data['signature'] = iPay88_signature($signatureStr);
            }

        } catch (QueryException $e) {
            dump($e);
            die;
            abort(500);
        }

        $data['hasRequiredFiled'] = $hasRequiredFiled;

        return view('web.payment', [
            'data' => $data
        ]);
    }

    public function topupPaymentReponse(Request $request)
    {
        $merchantCode = $request->get('MerchantCode');
        $paymentId = $request->get('PaymentId');
        $refNo = $request->get('RefNo');
        $amount = $request->get('Amount');
        $currency = $request->get('Currency');
        $remark = $request->get('Remark');
        $transId = $request->get('TransId');
        $authCode = $request->get('AuthCode');
        $eStatus = $request->get('Status');
        $errDesc = $request->get('ErrDesc');
        $signature = $request->get('Signature');
        $ccName = $request->get('CCName');
        $ccNo = $request->get('CCNo');
        $sBankname = $request->get('S_bankname');
        $sCountry = $request->get('S_country');

        $data = [
            'payment_status' => $eStatus,
        ];

        if ($eStatus == 1) {
            $amountStr = number_format($amount, 2, '', '');
            $responseSignatureStr = env('IPAY_MERCHANT_KEY') . env('IPAY_MERCHANT_CODE') . $paymentId . $refNo . $amountStr . $currency . $eStatus;
            $localGenerateResSignature = iPay88_signature($responseSignatureStr);

            if ($signature === $localGenerateResSignature) {
                try {
                    $payment = Payment::where('ref_no', $refNo)->first();

                    if (!$payment) {
                        $data['payment_status'] = 0;
                    } else {
                        if ($payment->status === 0) {
                            $payment->status = 1;
                            $payment->save();

                            $user = $payment->user;

                            if (!$wallet = $user->wallet) {
                                $wallet = new Wallet;
                                $wallet->guid = guidv4();
                            }

                            if (!$topup = $payment->topup) {
                                $data['payment_status'] = 0;
                            } else {
                                $shouldAddAmount = $topup->value;
                                $wallet->amount = floatval($wallet->amount ?? 0) + $shouldAddAmount;
                                $user->wallet()->save($wallet);

                                $walletActivity = new WalletActivity;
                                $walletActivity->topup_number = $refNo;
                                $walletActivity->amount = $shouldAddAmount;
                                $walletActivity->desc = 'Topup ' . $payment->amount . ' and got ' . $shouldAddAmount;
                                $walletActivity->action = 1;
                                $wallet->activities()->save($walletActivity);
                            }
                        }
                    }

                } catch(QueryException $e) {
                    $data['payment_status'] = 0;
                    dump($e);
                    die;
                }
            } else {
                $data['payment_status'] = 0;
            }
        }

        return view('web.payment-response', [
            'data' => $data
        ]);
    }

    public function topupPaymentBackend(Request $request)
    {
        $merchantCode = $request->get('MerchantCode');
        $paymentId = $request->get('PaymentId');
        $refNo = $request->get('RefNo');
        $amount = $request->get('Amount');
        $currency = $request->get('Currency');
        $remark = $request->get('Remark');
        $transId = $request->get('TransId');
        $authCode = $request->get('AuthCode');
        $eStatus = $request->get('Status');
        $errDesc = $request->get('ErrDesc');
        $signature = $request->get('Signature');
        $ccName = $request->get('CCName');
        $ccNo = $request->get('CCNo');
        $sBankname = $request->get('S_bankname');
        $sCountry = $request->get('S_country');

        if ($eStatus == 1) {
            $amountStr = number_format($amount, 2, '', '');
            $responseSignatureStr = env('IPAY_MERCHANT_KEY') . env('IPAY_MERCHANT_CODE') . $paymentId . $refNo . $amountStr . $currency . $eStatus;
            $localGenerateResSignature = iPay88_signature($responseSignatureStr);

            if ($signature === $localGenerateResSignature) {
                try {
                    $payment = Payment::where('ref_no', $refNo)->first();

                    if (!$payment) {
                        $data['payment_status'] = 0;
                    } else {
                        if ($payment->status === 0) {
                            $payment->status = 1;
                            $payment->save();

                            $user = $payment->user;

                            if (!$wallet = $payment->user->wallet) {
                                $wallet = new Wallet;
                                $wallet->guid = guidv4();
                            }

                            if (!$topup = $payment->topup) {
                                $data['payment_status'] = 0;
                            } else {
                                $shouldAddAmount = $topup->value;
                                $wallet->amount = floatval($wallet->amount ?? 0) + $shouldAddAmount;
                                $user->wallet()->save($wallet);

                                $walletActivity = new WalletActivity;
                                $walletActivity->topup_number = $refNo;
                                $walletActivity->amount = $shouldAddAmount;
                                $walletActivity->action = 1;
                                $walletActivity->desc = 'Topup ' . $payment->amount . ' and got ' . $shouldAddAmount;
                                $wallet->activities()->save($walletActivity);
                            }
                        }
                    }
                } catch(QueryException $e) {
                    $data['payment_status'] = 0;
                }

                die('RECEIVEOK');
            } else {
                $data['payment_status'] = 0;
            }
        }
    }

    public function backToApp(Request $request)
    {
        echo 'Close this page if you are not redirecting back to application.';
        die;
    }

    public function trxHistory(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        $payments = $user->payments()->orderBy('id', 'desc')->get();

        $returnData = $payments->map(function($payment) {
            return [
                'ref_no' => $payment->ref_no,
                'type' => config('grabmaid.payment.type_tostr.' . $payment->type, $payment->type),
                'payment_type' => config('grabmaid.payment.method_tostr.' . $payment->payment_type, $payment->payment_type),
                'amount' => $payment->amount,
                'desc' => $payment->desc,
                'status' => $payment->status,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'status' => 200,
            'payment_history' => $returnData
        ]);
    }
}
