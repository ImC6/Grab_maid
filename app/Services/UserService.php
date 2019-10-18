<?php

namespace App\Services;

use App\User;
use App\Models\Wallet;
use App\Models\Booking;
use App\Models\UserToken;
use App\Models\WalletActivity;
use App\Models\VerificationCode;
use App\Models\CommunicationSetting;
use App\Mail\RegisterVerify;
use App\Contracts\UserServiceContract;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class UserService implements UserServiceContract {

    public function getAllUsers($role = null)
    {
        if (!$role) {
            $role = 4;
        }

        if (!is_int($role)) {
            $role = intval($role);
        }
        return User::role($role)->get();
    }

    public function setToken(string $token, $deviceToken = null)
    {
        $user = JWTAuth::user();

        try {
            $newUserToken = new UserToken([
                'access_token' => $token,
                'device_token' => $deviceToken
            ]);
            $save = $user->token()->save($newUserToken);

        } catch (QueryException $e) {
            return false;
        }

        return $save;
    }

    public function createUser(array $userDetails, int $role, $cleanerOf = null)
    {
        $validator = Validator::make($userDetails, [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'gender' => 'nullable|in:male,female',
            'mobile_no' => 'nullable|digits_between:9,15',
        ]);

        if ($validator->fails()) {
            return [false, [
                'status' => 400,
                'errors' => $validator->errors(),
                'message' => 'Validation errors'
            ]];
        }

        try {
            $createArr = [
                'guid' => guidv4(),
                'name' => $userDetails['name'],
                'email' => $userDetails['email'],
                'password' => Hash::make($userDetails['password']),
                'role' => $role,
                'status' => 1
            ];

            if (isset($userDetails['mobile_no'])) {
                $createArr['mobile_no'] = $userDetails['mobile_no'];
            }

            if (isset($userDetails['gender'])) {
                $createArr['gender'] = getGender($userDetails['gender']);
            }

            if ($role === config('grabmaid.role.cleaner')) {
                $createArr['cleaner_of'] = $cleanerOf;
            }

            $user = User::create($createArr);
            $this->initiateWalletAndComSetting($user);

            // Generate email verification code
            // $verificationCode = new VerificationCode([
            //     'code' => generateVerificationCode()
            // ]);
            // $user->verificationCode()->save($verificationCode);

        } catch (QueryException $e) {
            return [false, [
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]];
        }

        // Send email verification
        // \Mail::to($user->email)->send(new RegisterVerify([
        //     'name' => $user->name,
        //     'code' => $verificationCode->code
        // ]));

        return [true, $user];
    }

    public function updateUser(string $userGuid, array $userDetails)
    {
        $validator = Validator::make($userDetails, [
            'name' => 'string|max:100',
            'gender' => 'nullable|in:male,female',
            'mobile_no' => 'nullable|digits_between:9,15',
        ]);

        if ($validator->fails()) {
            return [false, [
                'status' => 400,
                'errors' => $validator->errors()
            ]];
        }

        if (!$user = User::where('guid', $userGuid)->first()) {
            return [false, [
                'status' => 404,
                'message' => 'User not found'
            ]];
        }

        try {
            $updateArr = [];

            if (isset($userDetails['name'])) {
                $updateArr['name'] = $userDetails['name'];
            }

            if (isset($userDetails['gender'])) {
                $updateArr['gender'] = getGender($userDetails['gender']);
            }

            if (isset($userDetails['mobile_no'])) {
                $updateArr['mobile_no'] = $userDetails['mobile_no'];
            }

            if (count($updateArr) === 0) {
                return [false, [
                    'status' => 422,
                    'message' => 'missing_parameter',
                ]];
            }

            if (!$user->update($updateArr)) {
                return [false, [
                    'status' => 500,
                    'message' => 'Database error',
                ]];
            }

        } catch (QueryException $e) {
            return [false, [
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]];
        }

        return [true, $user];
    }

    public function updatePassword(string $userGuid, array $userDetails)
    {
        $validator = Validator::make($userDetails, [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return [false, [
                'status' => 400,
                'errors' => $validator->errors(),
                'message' => 'Validation error'
            ]];
        }

        if (!$user = User::where('guid', $userGuid)->first()) {
            return [false, [
                'status' => 404,
                'message' => 'User not found'
            ]];
        }

        try {
            $user->password = Hash::make($userDetails['password']);

            if (!$user->save()) {
                return [false, [
                    'status' => 500,
                    'message' => 'Something went wrong',
                ]];
            }

        } catch (QueryException $e) {
            return [false, [
                'status' => 500,
                'message' => 'Something went wrong',
                'errors' => $e
            ]];
        }

        return [true, $user];
    }

    public function refundToWallet(User $user, float $priceToReturn, Booking $booking, int $percentage)
    {
        if (!$wallet = $user->wallet) {
            $wallet = new Wallet();
            $wallet->guid = guidv4();
            $user->wallet()->save($wallet);
        }

        $walletActivity = new WalletActivity;
        $walletActivity->amount = $priceToReturn;
        $walletActivity->desc = 'Booking Number: ' . $booking->booking_number . ' is cancelled with ' . $percentage . '% refunded';
        $walletActivity->action = config('grabmaid.wallet.action.plus');
        $wallet->activities()->save($walletActivity);
        $wallet->amount = $wallet->amount + $priceToReturn;
        return $wallet->save();
    }

    public function initiateWalletAndComSetting(User $user)
    {
        $wallet = new Wallet();
        $wallet->guid = guidv4();
        $wallet->amount = 0;
        $user->wallet()->save($wallet);

        $comSetting = new CommunicationSetting();
        $comSetting->setting = json_encode([
            'email' => 1,
            'sms' => 1,
            'call' => 1,
        ]);
        $user->communicationSetting()->save($comSetting);
    }
}
