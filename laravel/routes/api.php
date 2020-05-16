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

// 정류장 관리
Route::resource('dlvy/management/station', 'WebStationManagementController');
// 체크포인트 관리
Route::resource('dlvy/management/checkpoint', 'WebCheckPointManagementController');
// RC카 관리
Route::resource('dlvy/management/car', 'WebCarManagementController');
// 경로 관리
Route::resource('dlvy/management/path', 'WebPathManagementController');
Route::get('dlvy/management/pathcheck/{id}', 'WebPathManagementController@show_path_check');
    