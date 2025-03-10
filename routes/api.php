<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;

Route::post('/register', [APIController::class, 'register']);
Route::post('/login', [APIController::class, 'login']);
Route::get('/test', [APIController::class, 'test']);

Route::get('/users', [APIController::class, 'users'])->middleware('auth:sanctum');
Route::get('/departments', [APIController::class, 'allDepartments'])->name('departments');

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

    });
});