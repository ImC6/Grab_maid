<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('testBigFile', function() {
//     $path = __DIR__ . '/../public/dearnestle.zip';
//     return response()->download($path);
// });

// Route::get('update-address', function() {
//     $addresses = \App\Models\Address::all();

//     $addresses->each(function($address) {
//         $location = \App\Models\Zone::where('region', $address->region)->first();
//         if ($location) {
//             $address->location_id = $location->id;
//             $address->location_details = $location->region . ', ' . $location->city . ', ' . $location->state;
//             $address->save();
//         }
//     });

//     echo 'done';
//     die;
// });

Route::get('password-reset/{token}', 'Customer\UserController@passwordReset')->name('password.reset');
Route::post('password-reset/update/{token}', 'Customer\UserController@postPasswordReset')->name('password.reset.update');
Route::get('password-expired', 'Customer\UserController@passwordResetExpired')->name('password.reset.expired');
Route::get('password-success', 'Customer\UserController@passwordResetSuccess');

Route::get('storage/images/{filename}', function ($filename) {
    $path = storage_path('public/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::prefix('payment')->group(function () {
    Route::get('back-to-app', 'PaymentController@backToApp')->name('payment.back-to-app');

    Route::get('booking', 'PaymentController@booking')->name('payment.booking');
    Route::post('booking/response', 'PaymentController@bookingPaymentReponse')->name('payment.booking.response');
    Route::post('booking/backend', 'PaymentController@bookingPaymentBackend')->name('payment.booking.backend');

    Route::get('topup', 'PaymentController@topup')->name('payment.topup');
    Route::post('topup/response', 'PaymentController@topupPaymentReponse')->name('payment.topup.response');
    Route::post('topup/backend', 'PaymentController@topupPaymentBackend')->name('payment.topup.backend');
});

Route::prefix('admin')->group(function () {
    Route::get('/{any?}', function () {
        return view('admin.admin');
    })->where('any', '.*');
});

Route::get('storage_link', function () {
Artisan::call('storage:link');
});

Route::get('clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return "Cache is cleared";
});

// Route::get('/ewallet','Admin\EwalletController@getEwallet');
Route::post('/ewallet','Admin\EwalletController@createEwallet')->name('createEwallet');
Route::get('/editUser','Admin\UserController@selectAllUser');
Route::POST('/editUser','Admin\UserController@updateUser')->name('editUser');
Route::POST('/deleteUser', 'Admin\UserController@deleteUser')->name('deleteUser');
Route::get('/deleteEwallet', 'Admin\EwalletController@deleteEwallet');

Route::get('/region', 'Admin\ZoneController@getRegions');
// Route::get('/state', 'Admin\ZoneController@createStates');
// Route::get('/promotion', 'Admin\PromotionController@createPromotion');


