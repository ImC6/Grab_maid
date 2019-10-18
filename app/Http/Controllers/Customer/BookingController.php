<?php

namespace App\Http\Controllers\Customer;

use App\User;
use App\Models\Tax;
use App\Models\Zone;
use App\Models\Wallet;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Promotion;
use App\Models\VendorDayOff;
use App\Models\VendorService;
use App\Models\BookingSession;
use App\Models\WalletActivity;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use JWTAuth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function getServiceSessions(Request $request)
    {
        // $requiredParam = [];

        // if (!$region = $request->get('region')) {
        //     $requiredParam['region'] = ['This field is required'];
        // }

        // if (!$serviceId = $request->get('service_id')) {
        //     $requiredParam['service_id'] = ['This field is required'];
        // }

        // if (count($requiredParam) > 0) {
        //     return response()->json([
        //         'status' => 400,
        //         "errors" => $requiredParam,
        //         'message' => 'Missing parameters ' . implode($requiredParam, ', '),
        //     ]);
        // }

        $limit = getQueryLimit($request->get('limit'));
        $offset = getQueryOffset($request->get('offset'));
        // $date = new \DateTime($request->get('date'));
        try {
            $date = Carbon::parse($request->get('date'));
            if (Carbon::now()->diffInHours($date, false) < 72) {
                $date = Carbon::now()->addDays(3);
            }

        } catch (\Exception $e) {
            $date = Carbon::now()->addDays(3);
        }
        $formatedDate = $date->format('Y-m-d');

        try {
            $query = VendorService::with(['service:id,name', 'company:id,name,company_logo'])
            ->where(function($query) use($formatedDate) {
                $query->where('start_date', '<=', $formatedDate)
                ->orWhereNull('start_date');
            })
            ->where(function($query) use($formatedDate) {
                $query->where('end_date', '>=', $formatedDate)
                ->orWhereNull('end_date');
            })
            ->where(function($query) use($date) {
                $query->whereJsonContains('working_day', intval($date->dayOfWeekIso))
                ->orWhereJsonLength('working_day', 0)
                ->orWhereNull('working_day');
            })
            ->where(function($query) use($formatedDate) {
                $query->whereNotIn('id', function($query) use($formatedDate) {
                    $query->select('vendor_service_id')
                        ->from('bookings')
                        ->whereDate('booking_date', $formatedDate)
                        //->where('status', '>=', config('grabmaid.booking.status.paid'));
						
					->whereIn('status', [config('grabmaid.booking.status.paid'), config('grabmaid.booking.status.delivering'), config('grabmaid.booking.status.inProgress')]); // KC Edit - 2019-08-23
	
						
                });
            })
            ->where(function($query) use($formatedDate) {
                $query->whereNotIn('id', function($query) use($formatedDate) {
                    $query->select('vendor_service_id')
                        ->from('booking_session')
                        ->whereDate('booking_date', $formatedDate)
                        ->where('updated_at', '>=', Carbon::now()->subSeconds(config('grabmaid.booking.blocking.seconds')));
                });
            });

            if ($region = $request->get('region')) {
                $query->whereJsonContains('regions', $region);
            }

            if ($serviceId = $request->get('service_id')) {
                $query->where('service_id', $serviceId);
            }

            if ($startTime = $request->get('start_time')) {
                $query->whereTime('start_time', '>=', $startTime);
            }

            if ($duration = $request->get('duration')) {
                $query->where('duration', '=', $duration);
            }

            if ($price = $request->get('price')) {
                $query->where('price', '>=', $price);
            }

			// KC Edit - Sorting 2019-08-26
			/*if ($sorting = $request->get('sorting')) {
				if($sorting != ''){
					$sorting = ($sorting == 'sort_by_time') ? 'start_time' : 'price';
					$query->orderBy($sorting, 'asc');
				}
			}				
			
			$query->orderBy('start_time', 'asc');
			$query->orderBy('price', 'asc');
			*/
			if ($sort_by_time = $request->get('sort_by_time')) {
				if($sort_by_time != ''){
					$query->orderBy('start_time', $sort_by_time);
				}
			}		
			
			if ($sort_by_price = $request->get('sort_by_price')) {
				if($sort_by_price != ''){
					$query->orderBy('price', $sort_by_price);
				}
			}			
			
			// Timezone
			if ($timezone = $request->get('timezone')) {
				if($timezone != ''){
					if($timezone == 'AM'){
						$query->whereTime('start_time', '<', '12:00');
					}else if($timezone == 'PM'){
						$query->whereTime('start_time', '>=', '12:00');
					}
				}
			}	
			
			// Cleaners
			if ($cleaners = $request->get('cleaners')) {
				if($cleaners != ''){
					$query->whereJsonLength('cleaners', $cleaners);
				}
			}			
			
            $count = $query->count();

            $services = $query
                ->offset($offset)
                ->limit($limit)
                ->get();

            // Get Cleaners detials
            $cleaners = $services->map(function($service, $index) {
                return json_decode($service->cleaners);
            })->flatten(1)->unique();

            if ($cleaners->count() > 0) {
                $cleaners = User::select('guid', 'name', 'gender', 'mobile_no')->whereIn('guid', $cleaners->toArray())->get()->keyBy('guid');
            }

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        // Check vendor day off
        // $services->each(function($service, $index) use($services, $formatedDate) {
        //     if ($vendor = $service->vendor) {
        //         if ($vendorDayOff = $vendor->day_off) {
        //             $vendorDayOff->each(function($dayOff) use($services, $formatedDate) {

        //                 if ($dayOff->date === $formatedDate) {
        //                     $services->splice($index, 1);
        //                     return false;
        //                 }
        //             });
        //         }
        //     }
        // });

        $returnData = $services->map(function($service) use($cleaners) {
            $cleanersGuids = json_decode($service->cleaners);
            $cleanerArr = [];
            foreach ($cleanersGuids as $guid) {
                $cleanerArr[] = $cleaners->get($guid);
            }

            $startTime = Carbon::createFromFormat('H:i', $service->start_time);

            return [
                'vendor_service_id' => $service->id,
                'regions' => json_decode($service->regions),
                'start_time' => $startTime->format('H:i A'),
                'start_time_sort' => $startTime->format('G.i'),
                'duration' => $service->duration,
                'end_time' => $startTime->addhours(intval($service->duration))->format('H:i A'),
                'start_date' => $service->start_date ? $service->start_date->format('Y-m-d') : null,
                'end_date' => $service->end_date ? $service->end_date->format('Y-m-d') : null,
                'price' => $service->price,
                'cleaners' => $cleanerArr,
                'service' => $service->service,
                'company' => $service->company,
            ];
        });

        return response()->json([
            'status' => 200,
            'services' => $returnData,
            'total' => $count,
        ]);

    }

    public function getBookings(Request $request)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        $limit = getQueryLimit($request->get('limit'));
        $offset = getQueryOffset($request->get('offset'));
        $date = $request->get('date');
        $sortBy = $request->get('sort_by');

        try {
            $totalQuery = $user->bookings();
            if ($date) {
                $totalQuery->whereDate('created_at', $date);
            }
            $total = $totalQuery->count();

            $user = $user->load([
                'bookings' => function($query) use($limit, $offset, $date, $sortBy) {
                    $query->limit($limit)->offset($offset);

                    if ($date) {
                        $query->whereDate('created_at', $date);
                    }

                    if ($sortBy) {
                        $sortByArr = explode('.', $sortBy);
                        $sortByField = $sortByArr[0];

                        if (in_array($sortByField, config('grabmaid.booking.allowSorting'))) {
                            $sortByDirection = $sortByArr[1] ?? '';
                            if ($sortByDirection !== 'asc' && $sortByDirection !== 'desc') {
                                $sortByDirection = 'asc';
                            }

                            $query->orderBy($sortByField, $sortByDirection);
                        }
                    }
                }
            ]);

            $bookings = $user->bookings;
            $returnBookings = $this->prepareBookings($bookings);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'total' => $total,
            'bookings' => $returnBookings
        ]);
    }

    public function getBookingByBookingNumber(Request $request, $bookingNumber)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $total = $user->bookings()->count();
            $bookings = $user->bookings;
            $booking = $bookings->firstWhere('booking_number', $bookingNumber);

            $returnBooking = $this->prepareBooking($booking);
            $returnBookings = $this->prepareBookings($bookings);

            if (!$booking) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Booking not found'
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
            'booking' => $returnBooking,
            'bookings' => $returnBookings,
        ]);
    }

    public function createBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_service_id' => 'required|exists:vendor_service,id',
            'booking_date' => 'required|date_format:"Y-m-d"',
            'address_id' => 'required|numeric',
            'payment_type' => 'required|in:1,2,3',
            'promo_code' => 'nullable|string',
            'remarks' => 'nullable|string|max:100'
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

        \DB::beginTransaction();

        try {
            $vendorServiceId = $request->get('vendor_service_id');
            $bookingDate = Carbon::createFromFormat('Y-m-d', $request->get('booking_date'));

            // Check vendor service exist
            $vendorService = VendorService::find($vendorServiceId);
            if (!$vendorService) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'vendor_service_id' => ['Vendor service not found']
                    ]
                ]);
            }

            // Check booking_date within vender_service start_end_date
            if ($vendorService->start_date) {
                if ($bookingDate->lt($vendorService->start_date)) {
                    return response()->json([
                        'status' => 400,
                        'errors' => [
                            'booking_date' => [
                                'Booking date is not within the service date'
                            ]
                        ]
                    ]);
                }
            }
            if ($vendorService->end_date) {
                if ($bookingDate->gt($vendorService->end_date)) {
                    return response()->json([
                        'status' => 400,
                        'errors' => [
                            'booking_date' => [
                                'Booking date is not within the service date'
                            ]
                        ]
                    ]);
                }
            }

            // check if booking date is available for booking
            $now = Carbon::now();
            $sessionDate = Carbon::createFromFormat('Y-m-d', $bookingDate->format('Y-m-d'));
            if ($now->diffindays($sessionDate, false) < config('grabmaid.booking.lastChance.days')) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Booking can only be made ' . config('grabmaid.booking.lastChance.days') . ' days before service session',
                ]);
            }

            // Check address belongs to user
            if (!$user->addresses->contains('id', $request->get('address_id'))) {
                return response()->json([
                    'status' => 400,
                    'errors' => [
                        'address_id' => [
                            'This address does not belong to you'
                        ]
                    ]
                ]);
            }

            // check vendor service is taken
            if (!$this->checkBookingAvailable($user, $vendorServiceId, $bookingDate)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'This session is already been taken',
                ]);
            }

            $promoCode = $request->get('promo_code');
            $paymentType = $request->get('payment_type');
            $addressId = $request->get('address_id');
            $remarks = $request->get('remarks');
            $lastBooking = Booking::orderBy('id', 'desc')->first();
            $lastBookingNumber = $lastBooking ? $lastBooking->booking_number : 0;
            $currentBookingNumber = getBookingNumber($lastBookingNumber);

            if ($matchedBooking = $this->checkSessionBelongsToUser($user, $vendorServiceId, $bookingDate)) {
                if ($matchedBooking->isUnpaid()) {
                    $this->applyPromoCode($promoCode, $matchedBooking, $vendorService);
                    $matchedBooking->payment_type = $paymentType;

                    if (floatval($matchedBooking->total_price) === floatval(0)) {
                        $matchedBooking->status = config('grabmaid.booking.status.paid');
                        $matchedBooking->receipt = route('receipt', [
                            $currentBookingNumber,
                            'token' => $user->guid
                        ]);
                    }

                    $matchedBooking->save();
                    $bookings = $user->bookings;
                    $returnBooking = $this->prepareBooking($matchedBooking);
                    $returnBookings = $this->prepareBookings($bookings);

                    return response()->json([
                        'status' => 200,
                        'message' => 'This booking is where you left unpaid',
                        'booking' => $returnBooking,
                        'bookings' => $returnBookings,
                    ]);
                }
            }

            lockTable('bookings');

            $booking = $this->initiateBooking($currentBookingNumber, $vendorServiceId, $bookingDate, $addressId, $remarks, $paymentType, $vendorService->price);

            // $tax = Tax::first();
            // if ($tax) {
            //     $taxArray = json_decode($tax, true);

            //     if (isset($taxArray['insurance'])) {
            //         $insr = floatval($taxArray['insurance']);
            //         $oriPrice = floatval($booking->price);
            //         $totalPrice = $oriPrice + $insr;

            //         $booking->insurance = $insr;
            //         $booking->total_price = $totalPrice;
            //     }
            // }

            $this->applyPromoCode($promoCode, $booking, $vendorService);

            // if ($tax) {
            //     $taxArray = json_decode($tax, true);

            //     if (isset($taxArray['service_tax'])) {
            //         $serviceTax = floatval($taxArray['service_tax']);
            //         $totalPrice = floatval($booking->total_price);
            //         $totalPrice = $totalPrice + floatval($serviceTax / 100);

            //         $booking->service_tax = $serviceTax;
            //         $booking->total_price = $totalPrice;
            //     }
            // }

            if (floatval($booking->total_price) === floatval(0)) {
                $booking->status = config('grabmaid.booking.status.paid');
                $booking->receipt = route('receipt', [
                    $currentBookingNumber,
                    'token' => $user->guid
                ]);
            }

            $user->bookings()->save($booking);
            $bookings = $user->bookings;
            $returnBooking = $this->prepareBooking($booking);
            $returnBookings = $this->prepareBookings($bookings);

        } catch (QueryException $e) {
            \DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        unlockTable();
        \DB::commit();

        return response()->json([
            'status' => 200,
            'message' => 'New booking is created',
            'booking' => $returnBooking,
            'bookings' => $returnBookings,
        ]);
    }

    public function updateBooking(Request $request, $bookingNumber)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => 'in:1,2,3',
            'promo_code' => 'nullable|string',
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
            $booking = $user->bookings()->where('booking_number', $bookingNumber)->first();

            if (!$booking) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Booking not found'
                ]);
            }

            if ($promoCode = $request->get('promo_code')) {
                $promotion = Promotion::checkCode($promoCode)->first();

                if (!is_null($booking->promotion_id)) { // current booking has promotion
                    if (!$promotion) { // new promo code is not valid
                        $promotion = $booking->promotion;
                        if (!$promotion->isAvailable()) { // current promotion is not available
                            $booking->promotion_id = null;
                            $booking->total_price = $booking->price;
                        }
                    } else {
                        if ($booking->promotion_id !== $promotion->id) { // user enter new promo code
                            $oriPrice = $booking->price;
                            $bookingPrice = $oriPrice - ($oriPrice * $promotion->percentage / 100);

                            $booking->promotion_id = $promotion->id;
                            $booking->total_price = $bookingPrice;
                        }
                    }
                } else { // current booking has no promotion
                    $booking->total_price = $booking->price;
                    if ($promotion) {
                        $oriPrice = $booking->price;
                        $bookingPrice = $oriPrice - ($oriPrice * $promotion->percentage / 100);

                        $booking->promotion_id = $promotion->id;
                        $booking->total_price = $bookingPrice;
                    }
                }
            } else { // user is not using new promo code
                if ($promotion = $booking->promotion) {
                    if (!$promotion->isAvailable()) { // current promotion is not available
                        $booking->promotion_id = null;
                        $booking->total_price = $booking->price;
                    }
                } else {
                    $booking->total_price = $booking->price;
                }
            }

            if ($paymentType = $request->get('payment_type')) {
                $booking->payment_type = $paymentType;

                // if (intval($paymentType) === config('grabmaid.payment.method.wallet')) {
                //     if (!$wallet = $user->wallet) {
                //         $wallet = new Wallet();
                //         $wallet->guid = guidv4();
                //         $wallet->amount = 0;
                //         $user->wallet()->save($wallet);
                //     }

                //     $balance = $wallet->amount;

                //     if (floatval($balance) < floatval($booking->total_price)) {
                //         return response()->json([
                //             'status' => 400,
                //             'message' => 'Insuficient balance'
                //         ]);
                //     }

                //     $walletActivity = new WalletActivity([
                //         'amount' => $booking->total_price,
                //         'action' => config('grabmaid.wallet.action.minus'),
                //         'desc' => 'Booking Payment',
                //     ]);
                //     $wallet->amount = $balance - $booking->total_price;
                //     $wallet->save();
                //     $wallet->activities()->save($walletActivity);
                //     $booking->status = config('grabmaid.booking.status.paid');
                //     $booking->receipt = route('receipt', [
                //         $booking->booking_number,
                //         'token' => $booking->user->guid
                //     ]);
                // }
            }

            $booking->save();
            $returnBooking = $this->prepareBooking($booking);

            $user->load(['addresses', 'wallet']);
            $returnUser = $user->toArray();
            $returnUser['addresses'] = $this->modifyAddresses($user->addresses);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error'
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Booking is updated',
            'booking' => $returnBooking,
            'user' => $returnUser
        ]);
    }

    public function cancelBooking(Request $request, $bookingNumber)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $booking = $user->bookings()->where('booking_number', $bookingNumber)->first();

            if (!$booking) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Booking not found'
                ]);
            }

            if ($booking->isCancelled()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Booking is already been cancelled'
                ]);
            }

            if ($booking->isStarted()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You are not allowed to cancel a booking after session started'
                ]);
            }

            if ($booking->isUnpaid()) {
                $booking->status = config('grabmaid.booking.status.cancelled');
                $booking->save();
                $returnBooking = $this->prepareBooking($booking);

                return response()->json([
                    'status' => 200,
                    'message' => 'Booking is cancelled',
                    'booking' => $returnBooking,
                    'refunded' => 0
                ]);
            }

            $date = Carbon::now();
            $bookingDate = $booking->booking_date;
            $bookingTime = $booking->vendorService->start_time;
            $sessionDateTime = Carbon::createFromFormat('Y-m-d H:i', "$bookingDate $bookingTime");

            $hoursDiff = $date->diffInHours($sessionDateTime, false);

            if ($hoursDiff <= config('grabmaid.booking.refund.lastChance.hours')) { // datetime is after last chance
                return response()->json([
                    'status' => 400,
                    'message' => 'You are not allowed to cancel booking less than 24hours prior to service session'
                ]);
            }

            $booking->status = config('grabmaid.booking.status.cancelled');
            $totalPrice = floatval($booking->total_price);

            if ($totalPrice > 0) {
                $priceToReturn = $totalPrice;

                if ($hoursDiff <= config('grabmaid.booking.refund.lastChance.hours', 72)) { // datetime is at last chance
                    $percentage = config('grabmaid.booking.refund.lastChance.percentage', 50);
                } else { // datetime is at first chance
                    $percentage = config('grabmaid.booking.refund.firstChance.percentage', 70);
                }

                $priceToReturn = $priceToReturn * ($percentage / 100);
                $this->userService->refundToWallet($user, $priceToReturn, $booking, $percentage);
                $booking->refunded = $priceToReturn;
            }

            $booking->save();
            $bookings = $user->bookings;
            $returnBooking = $this->prepareBooking($booking);
            $returnBookings = $this->prepareBookings($bookings);
            $user->load(['wallet']);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error'
            ]);
        }



        return response()->json([
            'status' => 200,
            'message' => 'Booking is cancelled',
            'booking' => $returnBooking,
            'bookings' => $returnBookings,
            'wallet' => $user->wallet,
            'refunded' => $priceToReturn
        ]);
    }

    public function checkBookingRepayment(Request $request, $bookingNumber)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $booking = $user->bookings()->where('booking_number', $bookingNumber)->first();

            if (!$booking) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Booking not found'
                ]);
            }

            if ($booking->isAfterPaid()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You have already paid this booking'
                ]);
            }

            if ($booking->isCancelled()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You have already cancelled this booking'
                ]);
            }

            // check if booking date is available for booking
            $now = Carbon::now();
            $bookingDate = Carbon::createFromFormat('Y-m-d', $booking->booking_date);
            if ($now->diffindays($bookingDate, false) < config('grabmaid.booking.lastChance.days')) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Booking can only be made ' . config('grabmaid.booking.lastChance.days') . ' days before service session',
                ]);
            }

            $vendorServiceId = $booking->vendor_service_id;

            //check service still available
            if (!$this->checkBookingAvailable($user, $vendorServiceId, $bookingDate)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'This session is already been taken',
                ]);
            }

            // todo add lock booking
            $resArr = $this->lockBookingBy($user, $vendorServiceId, $bookingDate);
            if ($resArr !== 200) {
                return response()->json($resArr);
            }

            $bookings = $user->bookings;
            $this->prepareBookings($bookings);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'error' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Repayment is available',
            'bookings' => $bookings
        ]);
    }

    public function coinPayment(Request $request, $bookingNumber)
    {
        if (!$user = JWTAuth::user()) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found'
            ]);
        }

        try {
            $booking = $user->bookings()->where('booking_number', $bookingNumber)->first();
            if ($booking->payment_type !== config('grabmaid.payment.method.wallet')) {
                if (!$booking->isUnpaid()) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Wrong payment type'
                    ]);
                }

                $booking->payment_type = config('grabmaid.payment.method.wallet');
            }

            $wallet = $user->wallet;
            if (!$wallet) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Insuficient balance'
                ]);
            }
            $balance = $wallet->amount;
            if (floatval($balance) < floatval($booking->total_price)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Insuficient balance'
                ]);
            }

            $walletActivity = new WalletActivity([
                'amount' => $booking->total_price,
                'action' => config('grabmaid.wallet.action.minus'),
                'desc' => 'Booking Payment',
            ]);
            $wallet->amount = $balance - $booking->total_price;
            $wallet->save();
            $wallet->activities()->save($walletActivity);
            $booking->status = config('grabmaid.booking.status.paid');
            $booking->receipt = route('receipt', [
                $booking->booking_number,
                'token' => $booking->user->guid
            ]);
            $booking->save();

            $bookings = $user->bookings;
            $returnBooking = $this->prepareBooking($booking);
            $returnBookings = $this->prepareBookings($bookings);
            $user->load(['wallet']);

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Coin payment is successful',
            'booking' => $returnBooking,
            'bookings' => $returnBookings,
            'wallet' => $user->wallet,
        ]);
    }

    public function downloadReceipt(Request $request, $bookingNumber)
    {
        // if (!$guid = $request->get('token')) {
        //     return response('Unauthorized', 401);
        // }

        try {
            // if (!$user = User::where('guid', $guid)->first()) {
            //     return response('Unauthorized', 401);
            // }

            // if (!$booking = $user->bookings()->where('booking_number', $bookingNumber)->first()) {
            //     return response('Booking not found', 404);
            // }

            if (!$booking = Booking::where('booking_number', $bookingNumber)->first()) {
                return response('Booking not found', 404);
            }

            $this->loadBooking($booking);

        } catch (QueryException $e) {
            return response('Server Error', 500);
        }

        if($booking->payment_type == "1"){
            $pm_type = 'Credit Card';
        }
        else if($booking->payment_type == "2"){
            $pm_type = 'Online Banking';
        }
        else if($booking->payment_type == "3"){
            $pm_type = 'Wallet Coin';
        }
        return view('pdf.invoice', [
            'title' => 'hello',
            'booking' => $booking,
            'address' => $booking->address,
            'promotion' => $booking->promotion,
            'company' => $booking->vendorService->company,
            'customer' => $booking->user,
            'session' => $booking->vendorService,
            'service' => $booking->vendorService->service,
            'payment_type' =>$pm_type,
        ]);

        if (!File::exists(storage_path('app/public/pdf/'))) {
            mkdir(storage_path('app/public/pdf/'));
        }

        $pdf = \PDF::loadView('pdf.invoice', [
            'title' => '',
            'booking' => $booking,
            'address' => $booking->address,
            'company' => $booking->vendorService->company,
            'customer' => $booking->user,
            'session' => $booking->vendorService,
            'service' => $booking->vendorService->service,
        ])->save(storage_path('app/public/pdf/' . $booking->booking_number . '.pdf'));

        return $pdf->stream();
        // return $pdf->download('invoice.pdf');
    }

    public function lockBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_service_id' => 'required|exists:vendor_service,id',
            'booking_date' => 'required|date_format:"Y-m-d"'
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

        $vendorServiceId = $request->get('vendor_service_id');
        $bookingDate = $request->get('booking_date');

        $resArr = $this->lockBookingBy($user, $vendorServiceId, $bookingDate);
        return response()->json($resArr);
    }

    public function releaseBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_service_id' => 'required|exists:vendor_service,id',
            'booking_date' => 'required|date_format:"Y-m-d"'
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
            $vendorServiceId = $request->get('vendor_service_id');
            $bookingDate = $request->get('booking_date');

            BookingSession::where('user_id', $user->id)
            ->where('vendor_service_id', $vendorServiceId)
            ->where('booking_date', $bookingDate)
            ->delete();

        } catch (QueryException $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Booking is released!',
        ]);
    }

    private function loadBooking($b)
    {
        $b->load('address', 'vendorService.service', 'vendorService.company.vendor', 'promotion');
    }

    private function prepareBooking(Booking $booking)
    {
        $this->loadBooking($booking);
        $cleaners = $this->getCleanersForBooking($booking);
        if ($cleaners) {
            $cleanersGuid = json_decode($booking->vendorService->cleaners);
            $cleanerArr = [];
            foreach ($cleanersGuid as $guid) {
                $cleanerArr[] = $cleaners->get($guid);
            }
        }
        $modifiedTime = $this->modifyStartTime($booking->vendorService);
        $modifiedAddress = $this->modifyAddress($booking->address);

        return [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'vendor_service_id' => $booking->vendor_service_id,
            'price' => $booking->price,
            'total_price' => $booking->total_price,
            'booking_date' => $booking->booking_date,
            'insurance' => $booking->insurance,
            'service_tax' => $booking->service_tax,
            'shipping_fee' => $booking->shipping_fee,
            'payment_type' => $booking->payment_type,
            'status' => $booking->status,
            'rated' => $booking->isRated() ? 1 : 0,
            'receipt' => $booking->receipt,
            'refunded' => $booking->refunded,
            'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
            'refunded' => $booking->refunded,
            'promotion' => [
                'promo_code' => $booking->promotion->promo_code ?? null,
                'desc' => $booking->promotion->desc ?? null,
            ],
            'start_time' => $modifiedTime['start_time'],
            'end_time' => $modifiedTime['end_time'],
            'start_time_sort' => $modifiedTime['start_time_sort'],
            'address' => $modifiedAddress,
            'vendor_service' => [
                'cleaners' => $cleanerArr,
                'service' => [
                    'name' => $booking->vendorService->service->name
                ],
                'company' => [
                    'name' => $booking->vendorService->company->name,
                    'company_logo' => $booking->vendorService->company->company_logo,
                    'vendor' => [
                        'guid' => $booking->vendorService->company->vendor->guid,
                        'email' => $booking->vendorService->company->vendor->email,
                    ],
                ],
            ],
        ];

        if (is_string($booking->vendorService->cleaners)) {
            if ($cleaners) {
                $cleanersGuid = json_decode($booking->vendorService->cleaners);
                $cleanerArr = [];
                foreach ($cleanersGuid as $guid) {
                    $cleanerArr[] = $cleaners->get($guid);
                }
                $booking->vendorService->cleaners = $cleanerArr;
            }
        }
        $this->modifyStartTime($booking->vendorService);
    }

    private function prepareBookings(\Illuminate\Database\Eloquent\Collection $bookings)
    {
        $bookings->load('address', 'vendorService.service', 'vendorService.company.vendor', 'promotion');
        $cleaners = $this->getCleanersForBookings($bookings);

        return $bookings->map(function($booking) use($cleaners) {
            if ($cleaners) {
                $cleanersGuid = json_decode($booking->vendorService->cleaners);
                $cleanerArr = [];
                foreach ($cleanersGuid as $guid) {
                    $cleanerArr[] = $cleaners->get($guid);
                }
            }

            $modifiedTime = $this->modifyStartTime($booking->vendorService);
            $modifiedAddress = $this->modifyAddress($booking->address);

            return [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'vendor_service_id' => $booking->vendor_service_id,
                'price' => $booking->price,
                'total_price' => $booking->total_price,
                'booking_date' => $booking->booking_date,
                'insurance' => $booking->insurance,
                'service_tax' => $booking->service_tax,
                'shipping_fee' => $booking->shipping_fee,
                'payment_type' => $booking->payment_type,
                'status' => $booking->status,
                'rated' => $booking->isRated() ? 1 : 0,
                'receipt' => $booking->receipt,
                'refunded' => $booking->refunded,
                'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                'refunded' => $booking->refunded,
                'promotion' => [
                    'promo_code' => $booking->promotion->promo_code ?? null,
                    'desc' => $booking->promotion->desc ?? null,
                ],
                'start_time' => $modifiedTime['start_time'],
                'end_time' => $modifiedTime['end_time'],
                'start_time_sort' => $modifiedTime['start_time_sort'],
                'address' => $modifiedAddress,
                'vendor_service' => [
                    'cleaners' => $cleanerArr,
                    'service' => [
                        'name' => $booking->vendorService->service->name
                    ],
                    'company' => [
                        'name' => $booking->vendorService->company->name,
                        'company_logo' => $booking->vendorService->company->company_logo,
                        'vendor' => [
                            'guid' => $booking->vendorService->company->vendor->guid,
                            'email' => $booking->vendorService->company->vendor->email,
                        ],
                    ],
                ],
            ];
        });

        // $bookings->each(function($booking, $index) use ($cleaners) {
        //     if (is_string($booking->vendorService->cleaners)) {
        //         if ($cleaners) {
        //             $cleanersGuid = json_decode($booking->vendorService->cleaners);
        //             $cleanerArr = [];
        //             foreach ($cleanersGuid as $guid) {
        //                 $cleanerArr[] = $cleaners->get($guid);
        //             }
        //             $booking->vendorService->cleaners = $cleanerArr;
        //         }
        //     }

        //     $this->modifyStartTime($booking->vendorService);
        //     $this->modifyAddress($booking->address);
        // });
    }

    private function modifyStartTime($vendorService)
    {
        $startTime = Carbon::createFromFormat('H:i', $vendorService->start_time);
        $endTime = Carbon::createFromFormat('H:i', $vendorService->end_time);

        return [
            'start_time' => $startTime->format('h:i A'),
            'end_time' => $endTime->format('h:i A'),
            'start_time_sort' => $startTime->format('G.i'),
        ];
    }

    private function getCleanersForBooking($booking)
    {
        $cleaners = User::select('guid', 'name', 'gender', 'mobile_no')->whereIn('guid', json_decode($booking->vendorService->cleaners))->get()->keyBy('guid');
        return $cleaners;
    }

    private function getCleanersForBookings(\Illuminate\Database\Eloquent\Collection $bookings)
    {
        $cleanersGuids = $bookings->map(function($booking, $index) {
            $cleanerArr = json_decode($booking->vendorService->cleaners);
            return $cleanerArr;
        })->flatten(1)->unique();

        if ($cleanersGuids->count() > 0) {
            return User::select('guid', 'name', 'gender', 'mobile_no')->whereIn('guid', $cleanersGuids->toArray())->get()->keyBy('guid');
        }

        return null;
    }

    private function checkBookingAvailable(User $user, $vendorServiceId, $bookingDate)
    {
        $bookingQuery = Booking::where('vendor_service_id', $vendorServiceId)
        ->whereDate('booking_date', $bookingDate->format('Y-m-d'))
        ->afterPaid();

        if ($bookingQuery->exists()) {
            return false;
        }

        $bookingSessionQuery = BookingSession::where('user_id', '!=', $user->id)
        ->where('vendor_service_id', $vendorServiceId)
        ->whereDate('booking_date', $bookingDate->format('Y-m-d'))
        ->where('updated_at', '>=', Carbon::now()->subSeconds(config('grabmaid.booking.blocking.seconds')));

        if ($bookingSessionQuery->exists()) {
            return false;
        }

        return true;
    }

    private function checkSessionBelongsToUser($user, $vendorServiceId, $bookingDate)
    {
        return $user->bookings()->where('vendor_service_id', $vendorServiceId)
        ->whereDate('booking_date', $bookingDate->format('Y-m-d'))
        ->first();
    }

    private function applyPromoCode($promoCode, Booking &$booking, VendorService $vendorService)
    {
        if ($promoCode) {
            $promotion = Promotion::checkCode($promoCode)->first();
            if ($promotion) {
                $oriPrice = $vendorService->price;
                $bookingPrice = $oriPrice - ($oriPrice * $promotion->percentage / 100);

                $booking->promotion_id = $promotion->id;
                $booking->total_price = $bookingPrice;
            }
        }
    }

    private function modifyAddresses(\Illuminate\Database\Eloquent\Collection $addresses)
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

    private function modifyAddress(Address $address = null)
    {
        if (is_null($address)) {
            return null;
        }

        return [
            'id' => $address->id,
            'title' => $address->house_no . ', ' . $address->address_line . ', ' . $address->postcode . ', ' . $address->city . ', ' . $address->state . '.',
            'details' => [
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
        ];
    }

    private function initiateBooking($currentBookingNumber, $vendorServiceId, $bookingDate, $addressId, $remarks, $paymentType, $price)
    {
        $booking = new Booking();
        $booking->booking_number = $currentBookingNumber;
        $booking->vendor_service_id = $vendorServiceId;
        $booking->booking_date = $bookingDate->format('Y-m-d');
        $booking->address_id = $addressId;
        $booking->remarks = $remarks;
        $booking->payment_type = $paymentType;
        $booking->status = config('grabmaid.booking.status.accepted');
        $booking->price = $price;
        $booking->total_price = $price;
        $booking->created_by = \Auth::user()->id;
        return $booking;
    }

    private function lockBookingBy(User $user, $vendorServiceId, $bookingDate)
    {
        \DB::beginTransaction();

        try {
            lockTable('booking_session');
            $otherUserbookingSession = BookingSession::where('vendor_service_id', $vendorServiceId)
            ->where('booking_date', $bookingDate)->where('user_id', '!=', $user->id)
            ->where('updated_at', '>=', Carbon::now()->subSeconds(config('grabmaid.booking.blocking.seconds')))
            ->count();

            if ($otherUserbookingSession > 0) {
                unlockTable();
                \DB::commit();
                return [
                    'status' => 400,
                    'message' => 'This session is selected by another user'
                ];
            }

            $userbookingSession = BookingSession::where('vendor_service_id', $vendorServiceId)
            ->where('booking_date', $bookingDate)
            ->where('user_id', $user->id)
            ->first();

            if ($userbookingSession) {
                $userbookingSession->touch();
            } else {
                $bookingSession = new BookingSession();
                $bookingSession->user_id = $user->id;
                $bookingSession->vendor_service_id = $vendorServiceId;
                $bookingSession->booking_date = $bookingDate;
                $bookingSession->save();
            }

            unlockTable();
            \DB::commit();

        } catch (QueryException $e) {
            \DB::rollBack();
            return [
                'status' => 500,
                'message' => 'Database error',
                'errors' => $e
            ];
        }

        return [
            'status' => 200,
            'message' => 'Booking is locked!',
        ];
    }
}
