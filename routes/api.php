<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('user', function (Request $request) {
//     return $request->user();
// });


Route::post('/login', 'Customer\UserController@authenticate');
Route::post('/fb/auth', 'Customer\UserController@fbAuth');
Route::post('/admin/login', 'Admin\UserController@adminAuthenticate');
Route::post('/vendor/login', 'Admin\UserController@vendorAuthenticate');
Route::post('/cleaner/login', 'Admin\UserController@cleanerAuthenticate');

Route::post('/register', 'Customer\UserController@register');
Route::post('/vendor/register', 'Admin\UserController@vendorRegister');

Route::post('/password/retrieve', 'Customer\UserController@forgotPassword');

Route::get('/vendor-services', 'Customer\BookingController@getServiceSessions');
Route::get('/sessions', function() {
    return response()->json([
        'status' => 200,
        'session' => config('grabmaid.session')
    ]);
});

Route::get('/receipt/{bookingNumber}', 'Customer\BookingController@downloadReceipt')->name('receipt');
Route::post('/promocode/validate', 'Customer\PromotionController@validateCode');
Route::get('/services', 'Admin\ServiceController@getAllServices');

Route::get('/export', 'MyController@export')->name('export');
Route::get('/importExportView', 'MyController@importExportView');
Route::post('/import', 'MyController@import')->name('import');

Route::get('/ewallet','Admin\EwalletController@getEwallet');
Route::post('/ewallet', 'Admin\EwalletController@createEwallet');
Route::delete('/ewallet/{id}','Admin\EwalletController@deleteEwallet');



Route::group(['middleware' => ['jwt']], function() {
    Route::get('/logout', 'Admin\UserController@logout');

    Route::get('/locations', 'Admin\ZoneController@getAllLocations');
    Route::get('/locations-line', 'Admin\ZoneController@getLocationInString');
    Route::get('/ipaybanks', 'PaymentController@getIPayBankList');
    Route::get('/topups', 'PaymentController@getTopupList');
    Route::get('/taxes/{vendorServiceId}', 'Customer\TaxController@getTaxesByBookingNumber');

    Route::group(['middleware' => ['role:admin']], function() {



        // USER SETTINGS
        Route::get('/users', 'Admin\UserController@getAllUsers');
        Route::delete('/users/{guid}', 'Admin\UserController@deleteUser');
        Route::post('/users/{role}', 'Admin\UserController@createUser');
        Route::post('/users/update/{id}', 'Admin\UserController@updateUser');
        Route::get('/user/addresses/{guid}', 'Admin\AddressController@getUserAddress');
        Route::post('/user/addresses/{guid}', 'Admin\AddressController@createUserAddress');
        Route::put('/user/addresses/{id}', 'Admin\AddressController@updateUserAddressById');
        Route::delete('/user/addresses/{id}', 'Admin\AddressController@deleteUserAddressById');
        Route::post('/user/profile-update', 'Admin\UserController@updateUserProfile');

        // GRABMAID SETTINGS
        Route::get('/zones', 'Admin\ZoneController@getZones');
        Route::post('/zones', 'Admin\ZoneController@createZone');
        Route::post('/zones/update/{id}', 'Admin\ZoneController@updateZone');
        Route::delete('/zones/{id}', 'Admin\ZoneController@deleteZone');

        Route::get('/promotion', 'Admin\PromotionController@getPromotion');
        Route::post('/promotion', 'Admin\PromotionController@createPromotion');
        Route::post('/promotion/update/{id}', 'Admin\PromotionController@updatePromotion');
        Route::delete('/promotion/{id}', 'Admin\PromotionController@deletePromotion');

        Route::get('/feedback', 'Admin\FeedbackController@getFeedback');
        Route::post('/feedback', 'Admin\FeedbackController@createFeedback');
        Route::post('/feedback/update/{id}', 'Admin\FeedbackController@updateFeedback');
        Route::delete('/feedback/{id}', 'Admin\FeedbackController@deleteFeedback');

        Route::get('/settings', 'Admin\SettingController@getSetting');
        Route::post('/settings', 'Admin\SettingController@createSetting');
        Route::post('/settings/update/{id}', 'Admin\SettingController@updateSetting');
        Route::delete('/settings/{id}', 'Admin\SettingController@deleteSetting');

        Route::get('/extra', 'Admin\ExtraChargeController@getExtra');
        Route::post('/extra', 'Admin\ExtraChargeController@addExtra');
        Route::post('/extra/update/{id}', 'Admin\ExtraChargeController@updateExtra');




        Route::get('/services/{id}', 'Admin\ServiceController@getServiceById');
        Route::post('/services', 'Admin\ServiceController@createService');
        Route::post('/services/update/{id}', 'Admin\ServiceController@updateService');
        Route::delete('/services/{id}', 'Admin\ServiceController@deleteService');



        Route::get('/holidays', 'Admin\HolidayController@getAllHolidays');
        Route::post('/holidays', 'Admin\HolidayController@createHolidays');
        Route::delete('/holidays/{id}', 'Admin\HolidayController@deleteHoliday');

        // VENDOR SETTINGS
        Route::get('/vendors/{guid}', 'Admin\UserController@getVendorById');
        Route::get('/vendor-services/{companyId}', 'Admin\ServiceController@getVendorService');
        Route::post('/vendor-services/{companyId}', 'Admin\ServiceController@createVendorService');
        Route::put('/vendor-services/{id}', 'Admin\ServiceController@updateVendorService');

        Route::get('/vendor-cleaner/{vendorGuid}', 'Admin\UserController@getCleanerByVendorGuid');

        Route::get('/vendor-companies/{vendorGuid}', 'Admin\CompanyController@getCompaniesByVendorId');
        Route::post('/vendor-companies/{vendorGuid}', 'Admin\CompanyController@createCompanyByVendorId');
        Route::post('/vendor-companies/update/{companyId}', 'Admin\CompanyController@updateCompanyById');
        Route::post('/vendor-bank/{companyId}', 'Admin\CompanyController@createBankByCompanyId');
        Route::put('/vendor-bank/{bankId}', 'Admin\CompanyController@updateBankById');

        Route::get('/bookings', 'Admin\BookingController@getBookings');
        Route::get('/user-bookings/{guid}', 'Admin\BookingController@getBookingsByUser');
        Route::post('/user-bookings/{guid}', 'Admin\BookingController@createBookingForUser');
        Route::put('/bookings/status/{bookingId}', 'Admin\BookingController@updateBookingStatus');

        // LOCATION SETTINGS
        Route::post('/states', 'Admin\ZoneController@createState');
        Route::put('/states/{stateId}', 'Admin\ZoneController@updateState');
        Route::delete('/states/{stateId}', 'Admin\ZoneController@deleteState');

        Route::post('/cities', 'Admin\ZoneController@createCity');
        Route::put('/cities/{cityId}', 'Admin\ZoneController@updateCity');
        Route::delete('/cities/{cityId}', 'Admin\ZoneController@deleteCity');

        Route::post('/areas', 'Admin\ZoneController@createArea');
        Route::put('/areas/{areaId}', 'Admin\ZoneController@updateArea');
        Route::delete('/areas/{areaId}', 'Admin\ZoneController@deleteArea');


    });

    Route::prefix('me')->group(function() {
        Route::get('/', 'Admin\UserController@getAuthenticatedUser');

        Route::group(['middleware' => ['role:user']], function() {
            Route::post('/addresses', 'Customer\AddressController@postAddress');
            Route::post('/addresses/update/{id}', 'Customer\AddressController@updateAddress');
            Route::delete('/addresses/{id}', 'Customer\AddressController@deleteAddress');

            Route::get('/bookings', 'Customer\BookingController@getBookings');
            Route::get('/bookings/{bookingNumber}', 'Customer\BookingController@getBookingByBookingNumber');
            Route::post('/bookings', 'Customer\BookingController@createBooking');
            Route::post('/bookings/update/{bookingNumber}', 'Customer\BookingController@updateBooking');
            Route::post('/bookings/cancel/{bookingNumber}', 'Customer\BookingController@cancelBooking');
            Route::get('/booking/repayment/{bookingNumber}', 'Customer\BookingController@checkBookingRepayment');
            Route::post('/booking/payment/{bookingNumber}', 'Customer\BookingController@coinPayment');
            Route::post('/booking/review/{bookingNumber}', 'Customer\ReviewController@makeReview');
            Route::post('/booking/lock', 'Customer\BookingController@lockBooking');
            Route::post('/booking/release', 'Customer\BookingController@releaseBooking');

            Route::post('/profile/update', 'Customer\ProfileController@updateProfile');
            Route::post('/profile/picture', 'Customer\ProfileController@updateProfilePicture');

            Route::get('/wallet', 'Customer\WalletController@getBalance');
            Route::post('/wallet', 'Customer\WalletController@createWallet');
            Route::get('/wallet-history', 'Customer\WalletController@getWalletHistory');

            Route::get('/payment/{refNo}', 'PaymentController@getPaymentByRefNo');
            Route::post('/payment/topup', 'PaymentController@createTopupPayment');
            Route::get('/payment-history', 'PaymentController@trxHistory');

            Route::get('/communication-setting', 'Customer\CommunicationSettingController@getSettings');
            Route::post('/communication-setting/update', 'Customer\CommunicationSettingController@updateSetting');
        });
    });


});

Route::get('/ewallet','Admin\EwalletController@getEwallet');
Route::get('/editUser','Admin\UserController@selectAllUser')->name('editUser');
