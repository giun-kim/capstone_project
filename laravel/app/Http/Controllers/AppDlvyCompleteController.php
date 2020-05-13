<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AppDlvyCompleteController extends Controller
{
    // 완료된 배달 App
    // 엑티비티 로드 => 당일 전체 완료 내역 (보낸배달, 받은 배달)
    // 카테고리 설정 => 앱에서 해결
    // 1주일 클릭 => 오늘부터 1주일 치 전체 완료 내역
    // 1개월 클릭 => 오늘부터 1개월 치 전체 완료 내역
    // 6개월 클릭 => 오늘부터 6개월 치 전체 완료 내역
    // 상세조회 => input(시작날짜, 끝나는 날짜) 시작날짜부터 끝나는 날짜 까지 전체 완료 내역


    public function completed_dlvy($id, $term='all', $date_start='0', $date_end='0'){
        debug("$id, $term, $date_start, $date_end");
        if($date_start == '0'){
            $date_start= date('Y-m-d');
            $date_end = date('Y-m-d');;
            if($term == 'day'){
                $date_end = date('Y-m-d');;
            }elseif($term == 'week'){
                $date_end = date('Y-m-d', strtotime("$date_start-6 day"));
            }elseif($term == 'month'){
                $date_end = date('Y-m-d', strtotime("$date_start-1 month"));
            }elseif($term == '6month'){
                $date_end = date('Y-m-d', strtotime("$date_start-6 month"));
            }
        }else{
            $date_start = $date_start;
            $date_end = $date_end;
        }
        if($term=='all'){
            debug($date_start, $date_end);
            $completed_send_dlvy = DB::table('dlvy')
                            ->select('dlvy_receiver', 'dlvy_start_point','dlvy_end_point', 'dlvy_status', 'dlvy_date')
                            ->where('dlvy_sender', $id)
                            ->where('dlvy_status', '완료')
                            ->orderBy('dlvy_date', 'desc')
                            ->get();
            $completed_receive_dlvy = DB::table('dlvy')
                            ->select('dlvy_sender', 'dlvy_start_point','dlvy_end_point', 'dlvy_status', 'dlvy_date')
                            ->where('dlvy_receiver', $id)
                            ->where('dlvy_status', '완료')
                            ->orderBy('dlvy_date', 'desc')
                            ->get();
            debug($completed_send_dlvy, $completed_receive_dlvy);
        }else{    
            $completed_send_dlvy = DB::table('dlvy')
                            ->select('dlvy_receiver', 'dlvy_start_point','dlvy_end_point', 'dlvy_status', 'dlvy_date')
                            ->where('dlvy_sender', $id)
                            ->where('dlvy_status', '완료')
                            ->whereBetween('dlvy_date', [$date_end, $date_start])
                            ->orderBy('dlvy_date', 'desc')
                            ->get();
            $completed_receive_dlvy = DB::table('dlvy')
                            ->select('dlvy_sender', 'dlvy_start_point','dlvy_end_point', 'dlvy_status', 'dlvy_date')
                            ->where('dlvy_receiver', $id)
                            ->where('dlvy_status', '완료')
                            ->whereBetween('dlvy_date', [$date_end, $date_start])
                            ->orderBy('dlvy_date', 'desc')
                            ->get();
            
        }

        return response()->json([
            'completed_send_dlvy' => $completed_send_dlvy,
            'completed_receive_dlvy' => $completed_receive_dlvy,
        ]);
    }
}
