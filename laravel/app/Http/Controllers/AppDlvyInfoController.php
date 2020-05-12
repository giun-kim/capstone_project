<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AppDlvyInfoController extends Controller
{
    // 보낸 배달 
    public function send_dlvy($id){
        debug($id);
        debug('send');
        $send_data=array();
        $dlvy_info = array();
        $rc_gps =array();
        $station_start_gps = array();
        $station_end_gps = array();
        $j =0;
        $send_info = DB::table('dlvy')
                        ->select('dlvy_num','dlvy_status','dlvy_receiver', 'dlvy_start_point', 'dlvy_end_point','dlvy_car_num')
                        ->where('dlvy_sender', $id)
                        ->whereRaw('dlvy_date >= curdate()')
                        ->get();
        debug($send_info);
        if(count($send_info)>0){
            debug('True');
            for($i=0; $i<count($send_info); $i++){
                if($send_info[$i]->dlvy_status == "배달중" || $send_info[$i]->dlvy_status == "호출중" || $send_info[$i]->dlvy_status == "대기중"){
                    $send_data[$j] = $send_info[$i];
                    $j = $j+1;
                }
            }
            debug($send_data);

            for($i=0; $i<count($send_data); $i++){
            
                // 배달 번호, 받는 사람 이름, 배달 상태, 대기 순위
                $dlvy_info_data = DB::table('dlvy')
                            ->select('dlvy_num', 'dlvy_status', 'dlvy_wait_num')
                            ->where('dlvy_num',  $send_data[$i]->dlvy_num)
                            ->first();
                $dlvy_info[$i] = $dlvy_info_data;

                // 받는 사람 이름
                $user_name_data = DB::table('user')
                            ->where('user_id', $send_data[$i]->dlvy_receiver)
                            ->value('user_name');
                $user_name[$i] = $user_name_data;
               
                // RC카 gps
                $rc_gps_data = DB::table('car')
                            ->select('car_lat', 'car_lon')
                            ->where('car_num', $send_data[$i]->dlvy_car_num)
                            ->first();
                $rc_gps[$i] = $rc_gps_data;
            
                // 출발지, 출발지 gps
                $station_start_data = DB::table('station')
                            ->select('station_name', 'station_lat', 'station_lon')
                            ->where('station_name', $send_data[$i]->dlvy_start_point)
                            ->first();
                $station_start[$i] = $station_start_data;
                // 도착지, 도착지 gps
                $station_end_data = DB::table('station')
                            ->select('station_name','station_lat', 'station_lon')
                            ->where('station_name', $send_data[$i]->dlvy_end_point)
                            ->first();
                $station_end[$i] = $station_end_data;
            }
            debug("Data-Test");
            debug($dlvy_info);
            return response()->json([
                'dlvy_info'=> $dlvy_info,   // 배달번호, 받는사람 이름, 배달 상태, 대기 순위(없으면 null)
                'user_name' => $user_name,  // 받는 사람 이름
                'rc_gps' => $rc_gps,        // RC카 GPS 
                'station_start' => $station_start, // 출발지, 출발지 gps
                'station_end' =>$station_end,      // 도착지, 도착지 gps

            ]);    
        }else{
            debug('False');
            return response()->json([
                'value' => 'null'
            ]);
        }  
       
    }

    // 받는 배달
    public function receiv_dlvy($id){
        debug($id);
        $receive_data=array();
        $dlvy_info = array();
        $rc_gps =array();
        $station_start_gps = array();
        $station_end_gps = array();
        $user_name = array();
        $j =0;
        $receive_info = DB::table('dlvy')
                        ->select('dlvy_num','dlvy_status', 'dlvy_sender', 'dlvy_start_point', 'dlvy_end_point','dlvy_car_num')
                        ->where('dlvy_receiver', $id)
                        ->whereRaw('dlvy_date >= curdate()')
                        ->get();
        debug($receive_info, count($receive_info));
        if(count($receive_info)>0){
            debug('True');
            for($i=0; $i<count($receive_info); $i++){
                if($receive_info[$i]->dlvy_status == "배달중" || $receive_info[$i]->dlvy_status == "호출중" || $receive_info[$i]->dlvy_status == "대기중"){
                    $receive_data[$j] = $receive_info[$i];
                    $j = $j+1;
                }
            }
            for($i=0; $i<count($receive_data); $i++){
                // 받는 사람 이름, 배달 상태, 대기 순위
                $dlvy_info_data = DB::table('dlvy')
                            ->select('dlvy_num', 'dlvy_status', 'dlvy_wait_num')
                            ->where('dlvy_num',  $receive_data[$i]->dlvy_num)
                            ->first();
                $dlvy_info[$i] = $dlvy_info_data;

                //보내는 사람 이름
                $user_name_data = DB::table('user')
                            ->where('user_id', $receive_data[$i]->dlvy_sender)
                            ->value('user_name');
                $user_name[$i] = $user_name_data;

                // RC카 gps
                $rc_gps_data = DB::table('car')
                            ->select('car_lat', 'car_lon')
                            ->where('car_num', $receive_data[$i]->dlvy_car_num)
                            ->first();
                $rc_gps[$i] = $rc_gps_data;
                
                // 출발지, 출발지 gps
                $station_start_data = DB::table('station')
                            ->select('station_name', 'station_lat', 'station_lon')
                            ->where('station_name', $receive_data[$i]->dlvy_start_point)
                            ->first();
                $station_start[$i] = $station_start_data;

                // 도착지, 도착지 gps
                $station_end_data = DB::table('station')
                            ->select('station_name','station_lat', 'station_lon')
                            ->where('station_name', $receive_data[$i]->dlvy_end_point)
                            ->first();
                $station_end[$i] = $station_end_data;
            }
            debug($user_name);
            return response()->json([
                'dlvy_info'=> $dlvy_info,   // 배달번호, 받는사람 이름, 배달 상태, 대기 순위(없으면 null)
                'user_name' => $user_name,  // 보내는 사람 이름
                'rc_gps' => $rc_gps,        // RC카 GPS
                'station_start' => $station_start,  // 출발지, 출발지 gps
                'station_end' =>$station_end,       // 도착지, 도착지 gps
            ]);        
        }
        else{
            debug('False');
            return response()->json([
                'value' => 'null'
            ]);
        }
    }
}
