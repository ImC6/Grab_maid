<?php

namespace App\Http\Controllers\Customer;

use App\User;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Zone;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use JWTAuth;

class AddressController extends Controller
{
    public function postAddress(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'address_line' => 'required|string|max:100',
            'postcode' => 'required|digits:5',
            // 'region' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'state' => 'required|string|max:50',
            'location_id' => 'required|numeric',
            'house_no' => 'required|string|max:20',
            'house_type' => 'required|string|max:50',
            'bedrooms' => 'required|numeric',
            'bathrooms' => 'required|numeric',
            'house_size' => 'required|string|max:50',
            'pet' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $locationId = $request->get('location_id');
            $location = Zone::where('id', $locationId)->enabled()->first();

            if (!$location) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'location_id' => ['Location is not found']
                    ]
                ]);
            }

            $address = new Address([
                'address_line' => $request->get('address_line'),
                'postcode' => $request->get('postcode'),
                'location_id' => $locationId,
                'location_details' => $location->region . ', ' . $location->city . ', ' . $location->state,
                'region' => $location->region,
                'city' => $request->get('city'),
                'state' => $request->get('state'),
                // 'region' => $request->get('region'),
                // 'city' => $location->city,
                // 'state' => $location->state,
                'house_no' => $request->get('house_no'),
                'house_type' => $request->get('house_type'),
                'house_size' => $request->get('house_size'),
                'bedrooms' => $request->get('bedrooms'),
                'bathrooms' => $request->get('bathrooms'),
                'pet' => $request->get('pet')
            ]);

            if (!$user->addresses()->save($address)) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Database error',
                ]);
            }

            $returnData = $this->modifyAddress($user->addresses);

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
            'addresses' => $returnData
        ]);
    }

    public function updateAddress(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'address_line' => 'required|string|max:100',
            'postcode' => 'required|digits:5',
            'city' => 'required|string|max:50',
            'state' => 'required|string|max:50',
            'location_id' => 'required|numeric',
            'house_no' => 'required|string|max:20',
            'house_type' => 'required|string|max:50',
            'bedrooms' => 'required|numeric',
            'bathrooms' => 'required|numeric',
            'house_size' => 'required|string|max:50',
            'pet' => 'required|string|max:50',
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
            if (Booking::where('address_id', $id)->paid()->exists()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You are not allowed to modify address when you have a booking session with it'
                ]);
            }

            if (!$address = $user->addresses()->where('id', $id)->first()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Address not found'
                ]);
            }

            $locationId = $request->get('location_id');
            $location = Zone::where('id', $locationId)->enabled()->first();

            if (!$location) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'location_id' => ['Location is not found']
                    ]
                ]);
            }

            $address->address_line = $request->get('address_line');
            $address->postcode = $request->get('postcode');
            $address->state = $request->get('state');
            $address->city = $request->get('city');
            $address->region = $location->region;
            $address->location_id = $locationId;
            $address->location_details = $location->region . ', ' . $location->city . ', ' . $location->state;
            // $address->city = $location->city;
            // $address->state = $location->state;
            $address->house_no = $request->get('house_no');
            $address->house_type = $request->get('house_type');
            $address->house_size = $request->get('house_size');
            $address->bedrooms = $request->get('bedrooms');
            $address->bathrooms = $request->get('bathrooms');
            $address->pet = $request->get('pet');

            if (!$address->save()) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Database error',
                ]);
            }

            $returnData = $this->modifyAddress($user->addresses);

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
            'addresses' => $returnData,
        ]);
    }

    public function deleteAddress(Request $request, $id)
    {
        try {
            if (!$address = Address::find($id)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Could not find address'
                ]);
            }

            $user = \Auth::user();

            if ($address->user_id !== $user->id) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Could not find address'
                ]);
            }

            $address->delete();
            $user->load('addresses');
            $returnData = $this->modifyAddress($user->addresses);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Address is deleted',
            'addresses' => $returnData
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
