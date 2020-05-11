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
// 관제
Route::get('/dlvy/control', 'WebControlController@index');
Route::get('/dlvy/control/show/{id}', 'WebControlController@run_status');

// 배달 완료
Route::get('dlvy/statistics/complete/{mode}/{term}/{date}', 'WebStatisticsController@divy_complete');
// 대기 완료 / 취소
Route::get('dlvy/statistics/waitcancel/{mode}/{term}/{date}', 'WebStatisticsController@wait_and_cancle');
// 평균 대기 시간
Route::get('dlvy/statistics/waittimeavg/{term}/{date}', 'WebStatisticsController@wait_time_avg');
# mode = acc(누적), avg(평균)
# term = day(일간), week(주간), month(월간)
# date = 지정한 날짜 2020-05-09 형식

// 정류장 관리
Route::resource('dlvy/management/station', 'WebStationManagementController');
// 체크포인트 관리
Route::resource('dlvy/management/checkpoint', 'WebCheckPointManagementController');
// RC카 관리
Route::resource('dlvy/management/car', 'WebCarManagementController');
// 경로 관리


///////////// APP /////////////
// 메인

// 호출하기
Route::get('dlvy/call', 'AppCallController@call');
// 동명이인 체크
Route::post('dlvy/check_user/{id}', 'AppCallController@check_user');

// 받는 배달
Route::get('dlvy/send/{id}', 'AppDlvyInfoController@send_dlvy');
// 보낸 배달

// 완료된 배달
