<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\VerifyController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SurveyReportController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', function(Request $request){
        return $request->user();
    });

    Route::post('/survey', [SurveyController::class, 'saveSurvey']);
    Route::post('/survey/questions', [SurveyController::class, 'saveQuestionsToSurvey']);
    Route::get('/survey', [SurveyController::class, 'getSurveys']);
    Route::get('/survey/{slug}', [SurveyController::class, 'getSurvey']);

    Route::post('/response', [ResponseController::class, 'saveResponse']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/survey-report/{slug}', [SurveyReportController::class, 'getSurvey']);

});


// User Registration
Route::post('/register', [RegisterController::class, 'register']);

// User Login
Route::post('/login', [LoginController::class, 'login']);

// Verify the email
Route::post('/verify-email', [VerifyController::class, 'verify']);

Route::get("/users/{id}", [UserController::class, 'getUser']);


