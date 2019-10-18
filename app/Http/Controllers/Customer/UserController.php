<?php

namespace App\Http\Controllers\Customer;

use App\User;
use App\Models\PasswordReset;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Mail\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class UserController extends Controller
{
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            return response()->json([
                'status' => 400,
                'errors' => 'Invalid credentials',
            ]);
        }
        $credentials['role'] = config('grabmaid.role.user');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 400,
                    'errors' => 'Invalid credentials',
                ]);
            }

            $user = JWTAuth::user();
            $deviceToken = $request->get('device_token');
            $setToken = $this->userService->setToken($token, $deviceToken);

            if (!$setToken) {
                return response()->json([
                    'status' => 500,
                    'errors' => 'Database error',
                ]);
            }

            $user = $user->load(['addresses', 'wallet']);

        } catch (JWTException $e) {
            return response()->json([
                'status' => 500,
                'errors' => 'Internal Server Error',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'errors' => 'Database Error',
            ]);
        }

        $returnUser = $user->toArray();
        $returnUser['addresses'] = $this->modifyAddress($user->addresses);

        return response()->json([
            'status' => 200,
            'token' => $token,
            'user' => $returnUser
        ]);
    }

    public function fbAuth(Request $request)
    {
        $fbId = $request->get('fb_id');
        $email = $request->get('email');

        if (!$fbId || !$email) {
            return response()->json([
                'status' => 400,
                'errors' => 'Invalid credentials',
            ]);
        }

        $profilePic = $request->get('profile_pic');

        try {
            if (!$user = User::where('fb_id', $fbId)->first()) {
                if (!$user = User::where('email', $email)->first()) {
                    if (!$name = $request->get('name')) {
                        return response()->json([
                            'status' => 400,
                            'errors' => [
                                'name' => ['Name is required']
                            ],
                        ]);
                    }

                    $user = new User();
                    $user->guid = guidv4();
                    $user->name = $name;
                    $user->email = $email;
                    $user->fb_id = $fbId;
                    $user->google_id = null;
                    $user->mobile_no = null;
                    $user->gender = null;
                    $user->role = config('grabmaid.role.user');
                    $user->cleaner_of = null;
                    $user->profile_pic = null;
                    if ($profilePic) {
                        $user->profile_pic = $profilePic;
                    }
                    $user->status = 1;
                    $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
                    $user->created_at = Carbon::now()->format('Y-m-d H:i:s');
                    $user->updated_at = Carbon::now()->format('Y-m-d H:i:s');
                    $user->save();

                    $this->userService->initiateWalletAndComSetting($user);
                } else {
                    $user->fb_id = $fbId;
                    if ($profilePic) {
                        $user->profile_pic = $profilePic;
                    }
                    $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
                    $user->save();
                }
            } else {
                if ($profilePic) {
                    $user->profile_pic = $profilePic;
                    $user->save();
                }
            }

            $token = JWTAuth::fromUser($user);
            $user->load(['addresses', 'wallet']);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'errors' => 'Database Error',
            ]);
        }

        $returnUser = $user->toArray();
        $returnUser['addresses'] = $this->modifyAddress($user->addresses);

        return response()->json([
            'status' => 200,
            'user' => $returnUser,
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $userDetails = $request->all();

        try {
            $userCreateArr = $this->userService->createUser($userDetails, config('grabmaid.role.user'));

            if (!$userCreateArr[0]) {
                return response()->json($userCreateArr[1]);
            }

            $user = $userCreateArr[1];
            $token = JWTAuth::fromUser($user);
            $user->load(['addresses', 'wallet']);
            $returnUser = $user->toArray();
            $returnUser['addresses'] = $this->modifyAddress($user->addresses);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'errors' => 'Internal Server Error',
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'User is created',
            'user' => $returnUser,
            'token' => $token,
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $email = $request->get('email');

        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        //todo update email verification status

        return response()->json([
            'status' => 200,
            'user' => $user
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $email = $request->get('email');
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 400,
                    'message' => 'This email is not registered in our system'
                ]);
            }

            $resetToken = PasswordReset::where('email', $email)->first();

            if ($resetToken) {
                $resetToken->touch();
            } else {
                $token = generateRandomToken();
                $resetToken = new PasswordReset();
                $resetToken->email = $user->email;
                $resetToken->token = $token;
                $resetToken->save();
            }

            \Mail::to($user->email)->send(new ResetPassword([
                'name' => $user->name,
                'email' => $user->email,
                'token' => $resetToken->token
            ]));

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'error' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Reset Password has been sent to the requested email'
        ]);
    }

    public function passwordReset(Request $request, $token)
    {
        $email = $request->get('email');
        if (!$email) {
            return redirect()->route('password.reset.expired');
        }

        if (!$resetToken = PasswordReset::where('token', $token)->where('email', $email)->first()) {
            return redirect()->route('password.reset.expired');
        }

        $resetTime = $resetToken->updated_at->addHours(24);
        $now = Carbon::now();

        if ($now->gt($resetTime)) {
            return redirect()->route('password.reset.expired');
        }

        return view('web.reset-password', [
            'email' => $email,
            'token' => $token
        ]);
    }

    public function postPasswordReset(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'same:password',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }

        $email = $request->get('email');
        $password = $request->get('password');

        try {
            if (!$resetToken = PasswordReset::where('token', $token)->where('email', $email)->first()) {
                return redirect()->route('password.reset.expired');
            }

            $resetTime = $resetToken->updated_at->addHours(24);
            $now = Carbon::now();

            if ($now->gt($resetTime)) {
                return redirect()->route('password.reset.expired');
            }

            $user = User::where('email', $resetToken->email)->first();
            if (!$user) {
                return redirect()->back()->withErrors([
                    'This user email does not exist'
                ]);
            }

            $user->password = Hash::make($password);
            $user->save();
            $resetToken->delete();

        } catch (QueryException $e) {
            return response('Database error', 500);
        }

        return view('web.reset-password-success');
    }

    public function passwordResetExpired(Request $request)
    {
        return view('web.reset-password-expired');
    }

    public function linkFacebook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fb_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $user->fb_id = $request->get('fb_id');
            $user->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'error' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Your account is now linked with Facebook'
        ]);
    }

    public function linkGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $user->google_id = $request->get('google_id');
            $user->save();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'error' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Your account is now linked with Google'
        ]);
    }

    private function modifyAddress(\Illuminate\Database\Eloquent\Collection $addresses)
    {
        return $addresses->map(function($address) {
            return [
                'id' => $address->id,
                'title' => $address->house_no . ', ' . $address->address_line . ', ' . $address->postcode . ', ' . $address->city . ', ' . $address->state . '.',
                'details' => [
                    [
                        'address_line' => $address->address_line,
                        'postcode' => $address->postcode,
                        'location_id' => $address->location_id,
                        'location_details' => $address->location_details,
                        'region' => $address->region,
                        'city' => $address->city,
                        'state' => $address->state,
                        'house_no' => $address->house_no,
                        'house_type' => $address->house_type,
                        'house_size' => $address->house_size,
                        'bedrooms' => $address->bedrooms,
                        'bathrooms' => $address->bathrooms,
                        'pet' => $address->pet,
                        'created_at' => $address->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $address->updated_at->format('Y-m-d H:i:s')
                    ]
                ],
            ];
        });
    }
}
