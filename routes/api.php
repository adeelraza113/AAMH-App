<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;

Route::post('/register', [APIController::class, 'register']);
Route::post('/login', [APIController::class, 'login']);
Route::get('/test', [APIController::class, 'test']);
Route::post('/hospital-video', [APIController::class, 'store']);

Route::middleware('auth:sanctum')->post('/labtestreport', [APIController::class, 'addLabTestReport']);
Route::middleware('auth:sanctum')->get('/labtestreport', [APIController::class, 'getLabTestReportsByUser']);
Route::post('/healthtips', [APIController::class, 'addHealthTip'])->middleware('auth:sanctum');
Route::get('/healthtips', [APIController::class, 'getAllHealthTips'])->middleware('auth:sanctum');
Route::get('/users', [APIController::class, 'users'])->middleware('auth:sanctum');
Route::get('/departments', [APIController::class, 'allDepartments'])->name('departments');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/addresses', [APIController::class, 'getAddresses']);
    Route::delete('/labtestbooking', [APIController::class, 'deleteLabTestBooking']);
    Route::delete('/pharmacyorder', [APIController::class, 'deleteOrder']);
    Route::delete('/appointment', [APIController::class, 'deleteAppointment']);
    Route::post('/payment', [APIController::class, 'makePayment']);
    Route::post('/approveAppointment', [APIController::class, 'approveAppointment']);
    Route::post('/app-slider', [APIController::class, 'createSlider']);
    Route::get('/app-sliders', [APIController::class, 'getActiveSliders']);
    Route::get('/user-profile', [APIController::class, 'getProfile']);
    Route::put('/user-profile', [APIController::class, 'updateProfile']);
    Route::delete('/delete-user', [APIController::class, 'deleteUser']);
    Route::get('/order-tracking', [APIController::class, 'getOrderTracking']);
    Route::get('/lab-booking-tracking', [APIController::class, 'getLabBookingTracking']);
    
    Route::post('/blood-sugar', [APIController::class, 'addBloodSugarReading']);
    Route::put('/blood-sugar', [APIController::class, 'updateBloodSugarReading']);
    Route::get('/blood-sugar', [APIController::class, 'getAllBloodSugarReadings']);
    Route::delete('/blood-sugar', [APIController::class, 'deleteBloodSugarById']);
    
    Route::post('/blood-pressure', [APIController::class, 'storeBloodPressure']);
    Route::put('/blood-pressure', [APIController::class, 'updateBloodPressure']);
    Route::get('/blood-pressure', [APIController::class, 'getBloodPressures']);
    Route::delete('/blood-pressure', [APIController::class, 'deleteBloodPressure']); 

    Route::post('/body-temperature', [APIController::class, 'addBodyTemperature']);
    Route::put('/body-temperature', [APIController::class, 'updateBodyTemperature']);
    Route::get('/body-temperature', [APIController::class, 'getBodyTemperatures']);
    Route::delete('/body-temperature', [APIController::class, 'deleteBodyTemperature']);
    
    Route::post('/blood-oxygen', [APIController::class, 'addBloodOxygen']);
    Route::put('/blood-oxygen', [APIController::class, 'updateBloodOxygen']);
    Route::get('/blood-oxygen', [APIController::class, 'getBloodOxygen']);
    Route::delete('/blood-oxygen', [APIController::class, 'deleteBloodOxygen']);
    
    Route::post('/hemoglobin', [APIController::class, 'storeHemoglobin']);
    Route::put('/hemoglobin', [APIController::class, 'updateHemoglobin']);
    Route::get('/hemoglobin', [APIController::class, 'getHemoglobin']);
    Route::delete('/hemoglobin', [APIController::class, 'deleteHemoglobin']);

    Route::post('/weight', [APIController::class, 'storeUserWeight']);
    Route::put('/weight', [APIController::class, 'updateUserWeight']);
    Route::get('/weight', [APIController::class, 'getUserWeights']);
    Route::delete('/weight', [APIController::class, 'deleteUserWeight']);
   
   Route::get('/admin-appointments', [APIController::class, 'getAllAppointments']);
   Route::get('/admin-labtests', [APIController::class, 'getAllLabTestBookings']);
   Route::get('/admin-pharmacyorders', [APIController::class, 'getAllOrders']);

  Route::get('/admin-payments', [APIController::class, 'getAllPayments']);
  Route::post('/approveLabTest', [APIController::class, 'approveLabTest']);
  Route::post('/approvePharmacyOrder', [APIController::class, 'approveOrder']);



});

Route::group(['as' => "feedback-", 'prefix' => 'feedback'], function () {
    Route::get('/all', [APIController::class, 'allFeedback'])->name('all');
    Route::get('/rating', [APIController::class, 'getFeedbackRatings'])->name('rating');
    Route::post('/post', [APIController::class, 'postFeedback'])->name('post');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::group(['as' => "membershipcard-", 'prefix' => 'membershipcard'], function () {
        Route::post('/post', [APIController::class, 'postMembershipCard'])->name('post');
    });
    Route::group(['as' => "doctors-", 'prefix' => 'doctors'], function () {
        Route::get('/get-by-department/{id}', [APIController::class, 'getDoctorsByDepartment'])->name('get-by-department');
        Route::get('/get-by-tags', [APIController::class, 'getDoctorsByTags'])->name('get-by-tags');
        Route::get('/appointments/{doctor_id}', [APIController::class, 'getOneDayDoctorAppointment'])->name('appointments');
        Route::post('/create-appointment', [APIController::class, 'createAppointment'])->name('create-appointment');
        Route::get('/lab-tests', [APIController::class, 'getLabTests'])->name('lab-tests');
        Route::get('/pharmacy-products', [APIController::class, 'getPharmacyProducts'])->name('pharmacy-products');
        Route::post('/book-tests', [APIController::class, 'createLabTestBooking']);
        Route::get('/bookings-history', [APIController::class, 'getLabTestBookings']);
        Route::post('/order-medicine', [APIController::class, 'createOrder']);
        Route::get('/order-history', [APIController::class, 'getOrders']);
        Route::get('/appointments', [APIController::class, 'getAppointments']);


    });
});