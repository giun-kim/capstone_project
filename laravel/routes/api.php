<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('/dlvy/management/station', 'WebStationManagementController'); // 정류장 페이지
Route::resource('/dlvy/management/checkpoint', 'WebCheckPointManagementController'); // 체크포인트 페이지
Route::resource('/dlvy/management/car', 'WebCarManagementController'); // RC카 페이지
Route::resource('/dlvy/management/path', 'WebPathManagementController'); // 경로 페이지