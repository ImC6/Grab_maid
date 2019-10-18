<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Models\Booking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function getBookings(Request $request)
    {
        $limit = getQueryLimit($request->get('limit'));
        $offset = getQueryOffset($request->get('offset'));

        try {
            $query = Booking::query();
            $total = $query->count();
            $bookings = $query->limit($limit)->offset($offset)->orderBy('created_at', 'desc')->with([
                'user' => function($query) {
                    $query->select('id', 'email');
                },
                'vendorService' => function($query) {
                    $query->select('id', 'company_id', 'service_id');
                },
                'vendorService.company' => function($query) {
                    $query->select('id', 'name', 'company_logo');
                },
                'vendorService.service' => function($query) {
                    $query->select('id', 'name');
                },
                'promotion' => function($query) {
                    $query->select('id', 'promo_code');
                },
            ])->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'bookings' => $bookings,
            'total' => $total
        ]);
    }

    public function getBookingsByUser(Request $request, $guid)
    {
        try {
            $user = User::where('guid', $guid)->customer()->first();

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'User not found'
                ]);
            }

            $bookings = $user->bookings()->orderBy('created_at', 'desc')->with([
                'user' => function($query) {
                    $query->select('id', 'email');
                },
                'vendorService' => function($query) {
                    $query->select('id', 'company_id', 'service_id');
                },
                'vendorService.company' => function($query) {
                    $query->select('id', 'name', 'company_logo');
                },
                'vendorService.service' => function($query) {
                    $query->select('id', 'name');
                },
                'promotion' => function($query) {
                    $query->select('id', 'promo_code');
                },
            ])->get();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'bookings' => $bookings
        ]);
    }

    public function createBookingForUser(Request $request, $guid)
    {
        $validator = Validator::make($request->all(), [
            'vendor_service_id' => 'required|numeric|exists:vendor_service,id',
            'remarks' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $user = User::where('guid', $guid)->customer()->first();

            if (!$user) {
                return response()->json([
                    'status' => 404,
                    'message' => 'user_not_found'
                ]);
            }

            $vendorServiceId = $request->get('vendor_service_id');
            $remarks = $request->get('remarks');

            $lastBooking = Booking::orderBy('id', 'desc')->first();
            $lastBookingId = $lastBooking ? ($lastBooking->id + 1) : 1;

            $booking = new Booking([
                'booking_number' => getBookingNumber($lastBookingId),
                'vendor_service_id' => $vendorServiceId,
                'remarks' => $remarks,
                'status' => 0,
                'created_by' => \Auth::user()->id
            ]);

            $user->bookings()->save($booking);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'database_error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'New booking is created',
            'booking' => $booking
        ]);
    }

    public function updateBookingStatus(Request $request, $bookingId)
    {
        if (!$booking = Booking::find($bookingId)) {
            return response()->json([
                'status' => 404,
                'message' => 'Booking not found'
            ]);
        }

        $status = intval($request->get('status'));
        $availableStatus = array_values(config('grabmaid.booking.status'));

        if (!in_array($status, $availableStatus, true)) {
            return response()->json([
                'status' => 400,
                'message' => 'Booking status is not available'
            ]);
        }

        $booking->status = $status;
        $booking->save();

        return response()->json([
            'status' => 200,
            'booking' => $booking
        ]);
    }



}
