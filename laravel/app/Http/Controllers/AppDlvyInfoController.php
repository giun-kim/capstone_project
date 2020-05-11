<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AppDlvyInfoController extends Controller
{
    // 보낸 배달 
    public function send_dlvy($id){
        debug($id);
        $rc_gps =array();
        $station_start_gps = array();
        $station_end_gps = array();
//받는 사람 이름, 출발지, 도착지,  배달 상태, 대기 순위, RC카 GPS, 출발지 GPS, 도착지 GPS
        $send_info = DB::table('dlvy')
                        ->select('dlvy_receiver', 'dlvy_status', 'dlvy_start_point', 'dlvy_end_point', 'dlvy_wait_num', 'dlvy_car_num')
                        ->where('dlvy_sender', $id)
                        ->get();

        debug($send_info[0]->dlvy_car_num, count($send_info));
        // RC카 gps
        for($i=0; $i<count($send_info); $i++){
            $rc_gps_data = DB::table('car')
                        ->select('car_lat', 'car_lon')
                        ->where('car_num', $send_info[$i]->dlvy_car_num)
                        ->first();
            $rc_gps[$i] = $rc_gps_data;
        }
        // 출발지
        for($i=0; $i<count($send_info); $i++){
            $station_start_gps_data = DB::table('station')
                        ->select('station_lat', 'station_lon')
                        ->where('station_name', $send_info[$i]->dlvy_start_point)
                        ->first();
            $station_start_gps[$i] = $station_start_gps_data;
        }
        // 도착지

        debug($rc_gps, $station_start_gps);
        
    }

    // 받는 배달
    public function receiv_dlvy($id){

    }
}
