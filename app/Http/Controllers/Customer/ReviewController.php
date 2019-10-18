<?php

namespace App\Http\Controllers\Customer;

use App\User;
use App\Models\Review;
use App\Models\VendorService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use JWTAuth;

class ReviewController extends Controller
{
    public function makeReview(Request $request, $bookingNumber)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ]);
        }

        $comment = $request->get('comment');
        $rating = $request->get('rating');

        try {
            if (!$user = JWTAuth::user()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Authorization Token not found'
                ]);
            }

            if (!$booking = $user->bookings()->where('booking_number', $bookingNumber)->first()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'This booking does not belong to you',
                    'error_code' => 4001
                ]);
            }

            if ($review = Review::where('booking_id', $booking->id)->where('user_guid', $user->guid)->first()) {
				$booking->status = config('grabmaid.booking.status.rated');
				$booking->save();				
                return response()->json([
                    'status' => 400,
                    'message' => 'You have already made a review on this booking',
                ]);
            }
			
            if ($booking->isRated()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'This booking is already rated',
                    'error_code' => 4002
                ]);
            }

            if (!$booking->isDone()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'This booking is not ready for rating',
                    'error_code' => 4003
                ]);
            }

            // check review table if booking_id exists

            $review = new Review;
            $review->user_guid = $user->guid;
            $review->booking_id = $booking->id;
            $review->comment = $comment;
            $review->rating = $rating;

            $booking->status = config('grabmaid.booking.status.rated');
            $booking->save();

            if (!$review->save()) {
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
            'review' => $review,
            'message' => 'Thank you for your review'
        ]);

    }
}
