<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AppDlvyInfoController extends Controller
{
    // 보낸 배달 
    public function send_dlvy($id){
        debug($id);
        $dlvy_info = array();
        $rc_gps =array();
        $station_start_gps = array();
        $station_end_gps = array();

        $send_info = DB::table('dlvy')
                        ->select('dlvy_num','dlvy_status', 'dlvy_start_point', 'dlvy_end_point','dlvy_car_num')
                        ->where('dlvy_sender', $id)
                        ->whereRaw('dlvy_date >= curdate()')
                        ->get();
        debug($send_info, count($send_info));
        if(count($send_info)>0){
            debug('True');
            for($i=0; $i<count($send_info); $i++){
                if($send_info[$i]->dlvy_status == "배달중" || $send_info[$i]->dlvy_status == "호출중" || $send_info[$i]->dlvy_status == "대기중"){
                    // 배달 번호, 받는 사람 이름, 배달 상태, 대기 순위
                    $dlvy_info_data = DB::table('dlvy')
                                ->select('dlvy_num', 'dlvy_receiver', 'dlvy_status', 'dlvy_wait_num')
                                ->where('dlvy_num',  $send_info[$i]->dlvy_num)
                                ->first();
                    $dlvy_info[$i] = $dlvy_info_data;

                    // RC카 gps
                    $rc_gps_data = DB::table('car')
                                ->select('car_lat', 'car_lon')
                                ->where('car_num', $send_info[$i]->dlvy_car_num)
                                ->first();
                    $rc_gps[$i] = $rc_gps_data;
                
                    // 출발지, 출발지 gps
                    $station_start_data = DB::table('station')
                                ->select('station_name', 'station_lat', 'station_lon')
                                ->where('station_name', $send_info[$i]->dlvy_start_point)
                                ->first();
                    $station_start[$i] = $station_start_data;

                    // 도착지, 도착지 gps
                    $station_end_data = DB::table('station')
                                ->select('station_name','station_lat', 'station_lon')
                                ->where('station_name', $send_info[$i]->dlvy_end_point)
                                ->first();
                    $station_end[$i] = $station_end_data;
                }
            }
            return response()->json([
                'dlvy_info'=> $dlvy_info,
                'rc_gps' => $rc_gps,
                'station_start' => $station_start,
                'station_end' =>$station_end,
            ]);        
        }
        else{
            debug('False');
        }
       
    }

    // 받는 배달
    public function receiv_dlvy($id){
        debug($id);
        $dlvy_info = array();
        $rc_gps =array();
        $station_start_gps = array();
        $station_end_gps = array();

        $send_info = DB::table('dlvy')
                        ->select('dlvy_num','dlvy_status', 'dlvy_start_point', 'dlvy_end_point','dlvy_car_num')
                        ->where('dlvy_receiver', $id)
                        ->whereRaw('dlvy_date >= curdate()')
                        ->get();
        debug($send_info, count($send_info));
        if(count($send_info)>0){
            debug('True');
            for($i=0; $i<count($send_info); $i++){
                if($send_info[$i]->dlvy_status == "배달중" || $send_info[$i]->dlvy_status == "호출중" || $send_info[$i]->dlvy_status == "대기중"){
                    // 받는 사람 이름, 배달 상태, 대기 순위
                    $dlvy_info_data = DB::table('dlvy')
                                ->select('dlvy_num', 'dlvy_sender', 'dlvy_status', 'dlvy_wait_num')
                                ->where('dlvy_num',  $send_info[$i]->dlvy_num)
                                ->first();
                    $dlvy_info[$i] = $dlvy_info_data;

                    // RC카 gps
                    $rc_gps_data = DB::table('car')
                                ->select('car_lat', 'car_lon')
                                ->where('car_num', $send_info[$i]->dlvy_car_num)
                                ->first();
                    $rc_gps[$i] = $rc_gps_data;
                
                    // 출발지, 출발지 gps
                    $station_start_data = DB::table('station')
                                ->select('station_name', 'station_lat', 'station_lon')
                                ->where('station_name', $send_info[$i]->dlvy_start_point)
                                ->first();
                    $station_start[$i] = $station_start_data;

                    // 도착지, 도착지 gps
                    $station_end_data = DB::table('station')
                                ->select('station_name','station_lat', 'station_lon')
                                ->where('station_name', $send_info[$i]->dlvy_end_point)
                                ->first();
                    $station_end[$i] = $station_end_data;
                }
            }
            return response()->json([
                'dlvy_info'=> $dlvy_info,
                'rc_gps' => $rc_gps,
                'station_start' => $station_start,
                'station_end' =>$station_end,
            ]);        
        }
        else{
            debug('False');
        }
    }
}
