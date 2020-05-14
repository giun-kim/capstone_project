<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
// 웹 관제 페이지 컨트롤러
class WebControlController extends Controller
{
    
    # 관제 페이지 로드 시 모든 정보 뿌려주기
    public function index(){
        # 지난 주 날짜
        $dt=date('Y-m-d');
        $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400); 
        $week_end = $week_first + (6*86400); 
        $last_week_start = date('Y-m-d', $week_first - (86400 * (7)));
        $last_week_end = date('Y-m-d', $week_end - (86400 * (7)));
        #지난 달 날짜
        $d = mktime(0,0,0, date("m"), 1, date("Y")); //이번달 1일
        $prev_month = strtotime("-1 month", $d); //한달전
        $last_month_start = date("Y-m-01", $prev_month); // 해당 달 1일
        $last_month_end = date("Y-m-t", $prev_month);   // 해당 달 마지막 일
        $month_count = date("t",$prev_month);   // 달 수
       
        // 2. 실시간 운행 상태
                # 구현 완료
        # 운행 중
        $proceeding_rc = DB::table('car')   
                        ->select('car_num')
                        ->where('car_status', '호출중')
                        ->orwhere('car_status', '배달중')
                        ->count();   
        # 운행 가능
        $waiting_rc = DB::table('car')   
                        ->select('car_num')
                        ->where('car_status', '배달대기')
                        ->count();
        # 에러
        $error_rc = DB::table('car')    
                        ->select('car_num')
                        ->where('car_status', '오류')
                        ->count();
        
        // 3. 실시간 배달 현황
                #구현 완료
        # 지난 달 총 호출 수
        $call_count_month_ago = DB::table('dlvy')
                        ->whereBetween('dlvy_date', [$last_month_start,$last_month_end])
                        ->count('dlvy_num');
        # 지난 달 평균 호출 수
        $call_avg_month_ago = $call_count_month_ago / $month_count;

        # 당일 총 호출 수
        $entire_call = DB::table('dlvy')   
                        ->select('dlvy_num')
                        ->whereRaw('dlvy_date >= curdate()')
                        ->count();
        # 당일 완료 수
        $complete_call = DB::table('dlvy')  
                        ->select('dlvy_num')
                        ->whereRaw('dlvy_date >= curdate()')
                        ->where('dlvy_status','=',  '완료')
                        ->count();
                        
        // 4. 대기 취소 현황       
                #구현 완료
        # 당일 대기완료 수 + 현재 대기중인 수 (전체 대기 건 수)
        $complete_waiting = DB::table('dlvy')  
                        ->select('dlvy_num')
                        ->whereRaw('dlvy_date >= curdate()')
                        ->where('dlvy_wait_time','>','0')
                        ->count();
        $now_waiting = DB::table('dlvy')  
                        ->select('dlvy_num')
                        ->whereRaw('dlvy_date >= curdate()')
                        ->where('dlvy_status', '=', '대기중')
                        ->count();

        # 당일 대기 취소 수
        $canceled_waiting = DB::table('dlvy')   
                        ->select('dlvy_num')
                        ->whereRaw('dlvy_date >= curdate()')
                        ->where('dlvy_status', '=','대기취소')
                        ->count();
                        
        // 5. 지난 주 호출 건물 순위 
                # 구현 완료
        $build_rank_and_count = DB::table('dlvy')
                        ->select('station.station_name as station')
                        ->selectRaw('count(*) as call_count')
                        ->leftJoin('station', 'station.station_name', '=','dlvy.dlvy_start_point')
                        ->whereNotNull('dlvy_start_point')
                        ->whereBetween('dlvy_date', [$last_week_start,$last_week_end])
                        ->groupBy('station')
                        ->orderBy('call_count', 'desc')
                        ->limit(3)
                        ->get();
        $build_rank = [];
        for($i=0; $i<count($build_rank_and_count); $i++){
            $build_rank[$i] = $build_rank_and_count[$i]->station;
        }
        // 6. 실시간 평균 대기 시간
            # 구현 완료
        # 당일 평균 대기 시간
        $avg_waiting_time = DB::table('dlvy')  
                        ->whereRaw('dlvy_date >= curdate()')
                        ->where('dlvy_wait_time','>','0')
                        ->avg('dlvy_wait_time');
        # 지난 달 대기 시간 합계
        $sum_waiting_time_month_ago = DB::table('dlvy')
                        ->whereBetween('dlvy_date', [$last_month_start,$last_month_end])
                        ->sum('dlvy_wait_time');
        # 지난 달 대기 총 수
        $count_waiting_time_month_ago = DB::table('dlvy')
                        ->whereBetween('dlvy_date', [$last_month_start,$last_month_end])
                        ->whereNotNull('dlvy_wait_time')
                        ->count();   

        # 지난달 평균 대기 시간
        $avg_waiting_time_month_ago = $sum_waiting_time_month_ago / $count_waiting_time_month_ago;
        


        // 7. 지도 - 정류장, RC카 위치, 이름 표시
                #구현완료
        $map_car_status = DB::table('car')
                        ->select('*')
                        ->get();

        $station_info = DB::table('station')
                        ->select('*')
                        ->get();


        // return
        return response()->json([
            # 2번 실시간 운행 상태
            'proceeding_rc'=>$proceeding_rc, # 운행 중
            'waiting_rc'=>$waiting_rc, # 운행 가능
            'error_rc' =>$error_rc, # 에러
            # 3번 실시간 배달 현황
            'call_avg_month_ago' => $call_avg_month_ago, # 지난 달 평균 호출 수
            'entire_call' => $entire_call,    # 당일 총 호출 수
            'complete_call' => $complete_call, # 당일 완료 수
            # 4번 실시간 대기 취소 현황
            'complete_waiting'=> $complete_waiting, #당일 대기완료 수
            'now_waiting' => $now_waiting, #현재 대기중인 수
            'canceled_waiting' => $canceled_waiting,    # 당일 대기 취소 수
            # 5번 지난 주 건물 호출 순위
            'build_rank' => $build_rank, # 지난 주 호출 건물 순위 
            # 6번 실시간 평균 대기 시간
            'avg_waiting_time_month_ago' => $avg_waiting_time_month_ago, # 지난달 평균 대기 시간
            'avg_waiting_time'=>$avg_waiting_time,    # 당일 평균 대기 시간
            # 7번 지도에 띄우는 거
            'map_car_status' => $map_car_status,    # 지도에 표시할 RC카 정보
            'station_info' => $station_info,       # 지도에 표시할 정류장 정보

        ]);
    }
    // 9. RC카 마커 클릭시 운행 정보
    public function run_status($id){

        # RC카 아이디, RC카 이름, RC카 상태, RC카 위도 경도
        $car = DB::table('car') 
                        ->select('*')
                        ->where('car_num', $id)
                        ->first();
        debug($car->car_status);
        debug($id);
        if($car->car_status ==="배달중" || $car->car_status==="호출중" ){
            debug('if문 들어옴');

            # 오류, 호출시간, 출발지에서 도착지로 출발 시간
            $dlvy_status = DB::table('dlvy') 
                            ->select('dlvy_call_start', 'dlvy_start')
                            ->where('dlvy_status', $car->car_status)
                            ->where('dlvy_car_num', $id)
                            ->first();
            #  출발지, 좌표
            $dlvy_start_point = DB::table('station')
                            ->select('station.station_name', 'station.station_lat','station.station_lon')
                            ->join('dlvy', 'station.station_name', '=','dlvy.dlvy_start_point')
                            ->where('dlvy_status', $car->car_status)
                            ->where('dlvy_car_num', $id)
                            ->first();

            # 도착지, 좌표
            $dlvy_end_point = DB::table('station')
                            ->select('station.station_name', 'station.station_lat','station.station_lon')
                            ->join('dlvy', 'station.station_name', '=','dlvy.dlvy_end_point')
                            ->where('dlvy_status', $car->car_status)
                            ->where('dlvy_car_num', $id)
                            ->first();

            # sender, receiver 가져오기
            $dlvy_status_s_r = DB::table('dlvy') 
                            ->select('dlvy_sender', 'dlvy_receiver')
                            ->where('dlvy_status', $car->car_status)
                            ->where('dlvy_car_num', $id)
                            ->first();
            debug($dlvy_status_s_r);
            # sender 이름, 전화번호
            $sender_info = DB::table('user')    
                            ->select('user_name', 'user_phone')
                            ->where('user_id', $dlvy_status_s_r->dlvy_sender)
                            ->first();

            # receiver 이름, 전화번호
            $receiver_info = DB::table('user')  
                            ->select('user_name', 'user_phone')
                            ->where('user_id', $dlvy_status_s_r->dlvy_receiver)
                            ->first();
            

            return response()->json([
                'car' => $car,  # RC카 정보(아이디, 이름, 상태, 위도, 경도)
                'dlvy_status' => $dlvy_status, # 배달 정보('출발지, 도착지, 호출시간, 출발지->도착지 출발시간, 에러)
                'dlvy_start_point' => $dlvy_start_point,    # 출발지, 출발지 좌표
                'dlvy_end_point' => $dlvy_end_point,        # 도착지, 도착지 좌표
                'sender_info' => $sender_info, # sender 정보 (유저 이름, 유저 전화번호)
                'receiver_info'=>$receiver_info # receiver 정보 (유저 이름, 유저 전화번호)
            ]);
        }
        elseif($car->car_status==="운행 대기"){
            return response()->json($car); # RC카 정보(아이디, 이름, 상태, 위도, 경도)
        }
        else{  
            return response()->json($car); # RC카 정보(아이디, 이름, 상태, 위도, 경도)
        }
        
    }
}
