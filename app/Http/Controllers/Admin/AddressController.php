<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Address;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function getUserAddress(Request $request, $guid)
    {
        try {
            $user = User::where('guid', $guid)->first();

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'user_not_found',
                ]);
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        $user = $user->load('addresses');

        return response()->json([
            'status' => 200,
            'user' => $user
        ]);
    }

    public function createUserAddress(Request $request, $guid)
    {
        $validator = Validator::make($request->all(), [
            'house_no' => 'required|string|max:20',
            'address_line' => 'required|string|max:100',
            'postcode' => 'required|digits:5',
            'region' => 'required|string|max:50',
            'city' => 'string|max:50',
            'state' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$user = User::where('guid', $guid)->first()) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ]);
        }

        try {
            $address = new Address([
                'house_no' => $request->get('house_no'),
                'address_line' => $request->get('address_line'),
                'postcode' => $request->get('postcode'),
                'region' => $request->get('region'),
                'city' => $request->get('city'),
                'state' => $request->get('state')
            ]);

            if (!$user->addresses()->save($address)) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Database error',
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
            'message' => 'Address is added',
            'address' => $address
        ]);
    }

    public function updateUserAddressById(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'address_line' => 'string|max:100',
            'postcode' => 'digits:5',
            'region' => 'string|max:50',
            'city' => 'string|max:50',
            'state' => 'string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        if (!$address = Address::find($id)) {
            return response()->json([
                'status' => 400,
                'message' => 'Address not found'
            ]);
        }

        $updateArr = [];

        if ($addressline = $request->get('address_line')) {
            $updateArr['address_line'] = $addressline;
        }

        if ($postcode = $request->get('postcode')) {
            $updateArr['postcode'] = $postcode;
        }

        if ($region = $request->get('region')) {
            $updateArr['region'] = $region;
        }

        if ($city = $request->get('city')) {
            $updateArr['city'] = $city;
        }

        if ($state = $request->get('state')) {
            $updateArr['state'] = $state;
        }

        if (count($updateArr) === 0) {
            return response()->json([
                'status' => 422,
                'message' => 'missing_parameter'
            ]);
        }

        try {
            $update = $address->update($updateArr);

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
            'message' => 'Address is updated',
            'address' => $address
        ]);
    }

    public function deleteUserAddressById(Request $request, $id)
    {
        try {
            if (!$address = Address::find($id)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Could not find address'
                ]);
            }

            $address->delete();
        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Address is deleted'
        ]);
    }
}
