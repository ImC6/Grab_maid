<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\UserToken;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function adminAuthenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['role'] = config('grabmaid.role.admin');

        return $this->authenticate($credentials);

        // try {
        //     if (!$token = JWTAuth::attempt($credentials)) {
        //         return response()->json([
        //             'status' => 400,
        //             'errors' => 'invalid_credentials'
        //         ]);
        //     }
        // } catch (JWTException $e) {
        //     return response()->json([
        //         'status' => 500,
        //         'errors' => 'internal_server_error'
        //     ]);
        // }

        // $setToken = $this->userService->setToken($token);

        // if (!$setToken) {
        //     return response()->json([
        //         'status' => 500,
        //         'errors' => 'database_error'
        //     ]);
        // }

        // return response()->json([
        //     'status' => 200,
        //     'token' => $token
        // ]);
    }

    public function vendorAuthenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['role'] = config('grabmaid.role.vendor');

        return $this->authenticate($credentials);

        // try {
        //     if (!$token = JWTAuth::attempt($credentials)) {
        //         return response()->json([
        //             'status' => 400,
        //             'errors' => 'invalid_credentials'
        //         ]);
        //     }
        // } catch (JWTException $e) {
        //     return response()->json([
        //         'status' => 500,
        //         'errors' => 'internal_server_error'
        //     ]);
        // }

        // $deviceToken = $request->get('device_token');
        // $setToken = $this->userService->setToken($token, $deviceToken);

        // if (!$setToken) {
        //     return response()->json([
        //         'status' => 500,
        //         'errors' => 'database_error'
        //     ]);
        // }

        // return response()->json([
        //     'status' => 200,
        //     'token' => $token
        // ]);
    }

    public function vendorRegister(Request $request)
    {
        $userDetails = $request->all();
        $userCreateArr = $this->userService->createUser($userDetails, config('grabmaid.role.vendor'));

        if (!$userCreateArr[0]) {
            return response()->json($userCreateArr[1]);
        }

        $vendor = $userCreateArr[1];
        $token = JWTAuth::fromUser($vendor);

        return response()->json([
            'status' => 200,
            'user' => $vendor,
            'token' => $token,
        ]);
    } 

    public function cleanerAuthenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $credentials['role'] = config('grabmaid.role.cleaner');

        return $this->authenticate($credentials);

        // try {
        //     if (!$token = JWTAuth::attempt($credentials)) {
        //         return response()->json([
        //             'status' => 400,
        //             'errors' => 'invalid_credentials'
        //         ]);
        //     }
        // } catch (JWTException $e) {
        //     return response()->json([
        //         'status' => 500,
        //         'errors' => 'internal_server_error'
        //     ]);
        // }

        // $deviceToken = $request->get('device_token');
        // $setToken = $this->userService->setToken($token, $deviceToken);

        // if (!$setToken) {
        //     return response()->json([
        //         'status' => 500,
        //         'errors' => 'database_error'
        //     ]);
        // }

        // return response()->json([
        //     'status' => 200,
        //     'token' => $token
        // ]);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'status' => 404,
                    'errors' => 'user_not_found'
                ]);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => $e->getStatusCode(),
                'errors' => 'token_expired'
            ]);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => $e->getStatusCode(),
                'errors' => 'token_invalid'
            ]);
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => $e->getStatusCode(),
                'errors' => 'token_absent'
            ]);
        }

        $user->load(['addresses', 'wallet']);
        $returnData = $this->modifyAddress($user->addresses);

        return response()->json([
            'status' => 200,
            'user' => [
                'id' => $user->id,
                'guid' => $user->guid,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'gender' => $user->gender,
                'mobile_no' => $user->mobile_no,
                'status' => $user->status,
                'role' => $user->role,
                'profile_pic' => $user->profile_pic,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                'addresses' => $returnData,
                'wallet' => $user->wallet
            ],
        ]);
    }

    public function getAllUsers(Request $request)
    {
        $role = $request->query('role') ?? 4;
        $users = $this->userService->getAllUsers($role);

        return response()->json([
            'status' => 200,
            'users' => $users
        ]);
    }

    public function getVendorById(Request $request, $guid)
    {
        try {
            $vendor = User::where('guid', $guid)->vendor()->first();

            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User not found',
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'vendor' => $vendor
        ]);
    }

    public function getCleanerByVendorGuid(Request $request, $vendorGuid)
    {
        try {
            $vendor = User::where('guid', $vendorGuid)->vendor()->first();

            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'user_not_found',
                ]);
            }

            $limit = getQueryLimit($request->get('limit'));
            $offset = getQueryOffset($request->get('offset'));

            $totalCleaners = $vendor->cleaners->count();
            $vendor->load(['cleaners' => function($query) use($limit, $offset) {
                $query->offset($offset)->limit($limit);
            }]);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'vendor' => $vendor,
            'cleaners' => $vendor->cleaners,
            'total' => $totalCleaners
        ]);
    }

    public function createUser(Request $request, $role)
    {
        $userRoles = config('grabmaid.role');

        if (!isset($userRoles[$role])) {
            return response()->json([
                'status' => 400,
                'message' => 'Undefined user role'
            ]);
        }

        $cleanerOf = null;
        if ($role === 'cleaner') {
            if (is_null($request->get('vendor_guid'))) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Missing vendor'
                ]);
            }

            $vendor = User::where('guid', $request->get('vendor_guid'))->vendor()->first();

            if (!$vendor) {
                return response()->json([
                    'status' => 404,
                    'message' => 'vendor_not_found'
                ]);
            }

            $cleanerOf = $vendor->id;
        }

        $userDetails = $request->all();
        $userCreateArr = $this->userService->createUser($userDetails, $userRoles[$role], $cleanerOf);

        if (!$userCreateArr[0]) {
            return response()->json($userCreateArr[1]);
        }

        return response()->json([
            'status' => 200,
            'user' => $userCreateArr[1],
        ]);
    }

    public function updateUserProfile(Request $request)
    {
        if (!$userGuid = $request->get('user_guid')) {
            return response()->json([
                'status' => 422,
                'message' => 'Missing Parameter'
            ]);
        }

        $userDetails = $request->all();
        $userUpdateArr = $this->userService->updateUser($userGuid, $userDetails);

        if (!$userUpdateArr[0]) {
            return response()->json($userUpdateArr[1]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'User is updated',
            'user' => $userUpdateArr[1],
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        if (!$user = User::find($id)) {
            return response()->json([
                'status' => 404,
                'message' => 'zone_not_found'
            ]);
        }

        $update = User::where('id', $id)->first();
        $update->name = $request->name;
        $update->email = $request->email;
        $update->password = $request->password;
        $update->mobile_no = $request->mobile_no;
        $update->gender = $request->gender;
       


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
            'updated_data' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);
        UserToken::where('access_token', $token)->delete();

        return response()->json([
            'status' => 200,
            'logged_out' => 1
        ]);
    }

    private function authenticate(array $credentials)
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 400,
                    'errors' => 'invalid_credentials'
                ]);
            }
        } catch (JWTException $e) {
            return response()->json([
                'status' => 500,
                'errors' => 'internal_server_error'
            ]);
        }

        $setToken = $this->userService->setToken($token);

        if (!$setToken) {
            return response()->json([
                'status' => 500,
                'errors' => 'database_error'
            ]);
        }

        return response()->json([
            'status' => 200,
            'token' => $token
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


    public function selectAllUser(){
        $user = User::where('role', '!=', 1)->paginate(15);

        return view('admin/editUser',['users' => $user]);
    }



    public function deleteUser(Request $request, $id){
        try {
            $delete = User::destroy($id);

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

