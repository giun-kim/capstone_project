<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

// 웹 통계 페이지 컨트롤러
class WebStatisticsController extends Controller
{
        ////////////    # 날짜 받아서 해당 날짜 전날 표시
        // $dt = date('Y-m-d');
        // $dayy = date('Y-m-d', strtotime("$dt -1 day"));
        // debug($dt);
        ////////////    # 주 받아서 해당 주 전 주 표시
        // $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400);  // 해당 주 일요일
        // $week_end = $week_first + (6*86400);    // 해당 주 토요일
        // $last_week_start = date('Y-m-d', $week_first - (86400 * 7));    // 지난 주 일요일
        // $last_week_end = date('Y-m-d', $week_end - (86400 * 7));    // 지난 주 토요일
        // debug($week_first, $week_end, $last_week_start, $last_week_end);
        ////////////    # 달 받아서 해당 달 전달 표시
        // $d = mktime(0,0,0, date("m"), 1, date("Y")); //해당 달 1일
        // $prev_month = strtotime("$dt",$d); //해당 달
        // $last_month_start = date("Y-m-01", $prev_month);    //해당 달 1일
        // $last_month_end = date("Y-m-t", $prev_month);   // 해당 달 마지막 일
        // debug($last_month_start, $last_month_end);
        ////////////

    
    #배달 완료 건 수 배달 완료 누적/평균
    public function divy_complete($mode="acc", $term="day", $date='0'){
        debug($mode, $term, $date);
        $statis_info =array();
        $date_info = array();
        // mode = acc, avg
        // term = day, week, month
        // if($date == '0'){
        //     $dt = date('Y-m-d', strtotime('-1 day'));    // 하루 전
        //     $mt = date('Y-m', strtotime('-1 month'));   // 지난 달
        //     $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400 + (86400*7));  // 지난 주 일요일
        //     $week_end = $week_first + (6*86400);    // 지난 주 토요일
        // }else{}
            $dt = date('Y-m-d', strtotime($date));    // 해당 날짜
            $mt = date('Y-m', strtotime($date));    // 해당 달
            $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400);  // $dt에 해당하는 주의 일요일
            $week_end = $week_first + (6*86400);    // $dt에 해당하는 주의 토요일
        

       
        if($mode == "acc"){     # 누적 클릭 시
            if($term == 'day'){         # 하루별 총 배달 건 수
                for($i=0; $i<9; $i++){ 
                    $day = date('Y-m-d', strtotime("$dt -$i day"));

                    $statis_data = DB::table('dlvy')
                                    ->where('dlvy_date', $day)
                                    ->count('dlvy_num');
                    $date_info[$i] = $day;
                    $statis_info[$i] = $statis_data;        
                }
            }elseif($term == 'week'){   # 주차별 총 배달 건 수
                for($i =0; $i <9; $i++)
                {
                    $last_week_start = date('Y-m-d', $week_first - (86400 * (7*$i)));
                    $last_week_end = date('Y-m-d', $week_end - (86400 * (7*$i)));

                    $statis_data = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_week_start, $last_week_end])
                                ->count('dlvy_num');
                    $date_info[$i] = "$last_week_start~$last_week_end";
                    $statis_info[$i] = $statis_data;
                }
            }elseif($term=='month'){    # 달별 총 배달 건 수
                for($i =0; $i <9; $i++)
                {
                    $d = mktime(0,0,0, date("m"), 1, date("Y")); //해당 달 1일
                    $prev_month = strtotime("$mt -$i month",$d); //해당 달
                    $last_month_start = date("Y-m-01", $prev_month);    //해당 달 1일
                    $last_month_end = date("Y-m-t", $prev_month);   // 해당 달 마지막 일
        
                    $statis_data = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_month_start, $last_month_end])
                                ->count('dlvy_num');
                    $date_info[$i] = date('Y-m', $prev_month);
                    $statis_info[$i] = $statis_data;
                }
            }
        }elseif($mode == "avg"){    # 평균 클릭 시
            if($term == 'week'){        # 주차별 평균 배달 건 수
                for($i =0; $i <9; $i++)
                {
                    $last_week_start = date('Y-m-d', $week_first - (86400 * (7*$i)));
                    $last_week_end = date('Y-m-d', $week_end - (86400 * (7*$i)));

                    $statis_data = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_week_start, $last_week_end])
                                ->count('dlvy_num');
                    $date_info[$i] = "$last_week_start~$last_week_end";
                    $statis_info[$i] = round($statis_data / 7, 2);
                }
            }elseif($term=='month'){    # 달별 평균 배달 건 수
                for($i =0; $i <9; $i++)
                {
                    $d = mktime(0,0,0, date("m"), 1, date("Y")); //해당 달 1일
                    $prev_month = strtotime("$mt -$i month",$d); //해당 달
                    $last_month_start = date("Y-m-01", $prev_month);    //해당 달 1일
                    $last_month_end = date("Y-m-t", $prev_month);   // 해당 달 마지막 일
                    $month_count = date("t",$prev_month);

                    $statis_data = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_month_start, $last_month_end])
                                ->count('dlvy_num');

                    $date_info[$i] = date('Y-m', $prev_month);
                    $statis_info[$i] = round($statis_data / $month_count, 2);
                } 
            }
        }else{  // 잘못된 요청
            return response(['msg'=>'잘못된 요청']);
        }

        debug($statis_info, $date_info);
        
        return response([
            'date_info'=>$date_info,        // 날짜
            'statis_info'=>$statis_info,    // 배달 완료 건 수(누적 or 평균)
            
        ]);
    }


    # 대기 완료/취소 건 수
    public function wait_and_cancle($mode="acc", $term="day", $date='0'){
        // 대기 완료/취소
        debug($mode, $term, $date);
        $wait_count =array();
        $wait_cancel = array();
        $date_info = array();
        // mode = acc, avg
        // term = day, week, month
        if($date == '0'){   // 날짜 선택x 디폴트 (어제, 지난 주, 지난 달 기준)
            $dt = date('Y-m-d', strtotime('-1 day'));    // 하루 전
            $mt = date('Y-m', strtotime('-1 month'));    // 지난 달
            $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400 + (86400*7));  // 지난 주 일요일
            $week_end = $week_first + (6*86400);    // 지난 주 토요일
        }else{  // 날짜 선택o 해당 일, 해당 일의 주, 해당 일의 달
            $dt = date('Y-m-d', strtotime($date));    // 해당 날짜
            $mt = date('Y-m', strtotime($date));    // 해당 달
            $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400);  // $dt에 해당하는 주의 일요일
            $week_end = $week_first + (6*86400);    // $dt에 해당하는 주의 토요일
        }


        if($mode == "acc"){     # 누적 클릭 시
            if($term == 'day'){         # 하루별 대기/취소 건 수
                for($i=0; $i<9; $i++){ 
                    $day = date('Y-m-d', strtotime("$dt -$i day"));

                    $statis_data_count = DB::table('dlvy')
                                    ->where('dlvy_date', $day)
                                    ->count('dlvy_wait_time');
                    $statis_data_cancel = DB::table('dlvy')
                                    ->where('dlvy_date', $day)
                                    ->where('dlvy_status', '대기취소')
                                    ->count('dlvy_status');
                    $date_info[$i] = $day;
                    $wait_count[$i] = $statis_data_count;  
                    $wait_cancel[$i] = $statis_data_cancel;
                }
            }elseif($term == 'week'){   # 주차별 대기/취소 건 수
                for($i =0; $i <9; $i++)
                {
                    $last_week_start = date('Y-m-d', $week_first - (86400 * (7*$i)));
                    $last_week_end = date('Y-m-d', $week_end - (86400 * (7*$i)));

                    $statis_data_count = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_week_start, $last_week_end])
                                ->count('dlvy_wait_time');
                    $statis_data_cancel = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_week_start, $last_week_end])
                                ->where('dlvy_status', '대기취소')
                                ->count('dlvy_status');
                    $date_info[$i] = "$last_week_start~$last_week_end";
                    $wait_count[$i] = $statis_data_count;  
                    $wait_cancel[$i] = $statis_data_cancel;
                }
            }elseif($term=='month'){    # 달별 대기/취소 건 수
                for($i =0; $i <9; $i++)
                {
                    $d = mktime(0,0,0, date("m"), 1, date("Y")); //해당 달 1일
                    $prev_month = strtotime("$mt -$i month",$d); //해당 달
                    $last_month_start = date("Y-m-01", $prev_month);    //해당 달 1일
                    $last_month_end = date("Y-m-t", $prev_month);   // 해당 달 마지막 일
        
                    $statis_data_count = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_month_start, $last_month_end])
                                ->count('dlvy_wait_time');
                    $statis_data_cancel = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_month_start, $last_month_end])
                                ->where('dlvy_status', '대기취소')
                                ->count('dlvy_status');
                    $date_info[$i] = date('Y-m', $prev_month);
                    $wait_count[$i] = $statis_data_count;  
                    $wait_cancel[$i] = $statis_data_cancel;
                }
            }
        }elseif($mode == "avg"){    # 평균 클릭 시
            if($term == 'week'){        # 주차별 평균 대기/취소 건 수
                for($i =0; $i <9; $i++)
                {
                    $last_week_start = date('Y-m-d', $week_first - (86400 * (7*$i)));
                    $last_week_end = date('Y-m-d', $week_end - (86400 * (7*$i)));

                    $statis_data_count = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_week_start, $last_week_end])
                                ->count('dlvy_wait_time');
                    $statis_data_cancel = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_week_start, $last_week_end])
                                ->where('dlvy_status', '대기취소')
                                ->count('dlvy_status');
                    $date_info[$i] = "$last_week_start~$last_week_end";
                    $wait_count[$i] = round($statis_data_count / 7, 2);  
                    $wait_cancel[$i] = round($statis_data_cancel / 7, 2);
                }
            }elseif($term=='month'){    # 달별 평균 대기/취소 건 수
                for($i =0; $i <9; $i++)
                {
                    $d = mktime(0,0,0, date("m"), 1, date("Y")); //해당 달 1일
                    $prev_month = strtotime("$mt -$i month",$d); //해당 달
                    $last_month_start = date("Y-m-01", $prev_month);    //해당 달 1일
                    $last_month_end = date("Y-m-t", $prev_month);   // 해당 달 마지막 일
                    $month_count = date("t",$prev_month);

                    $statis_data_count = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_month_start, $last_month_end])
                                ->count('dlvy_wait_time');
                    $statis_data_cancel = DB::table('dlvy')
                                ->whereBetween('dlvy_date', [$last_month_start, $last_month_end])
                                ->where('dlvy_status', '대기취소')
                                ->count('dlvy_status');
                    $date_info[$i] = date('Y-m', $prev_month);
                    $wait_count[$i] = round($statis_data_count / 7, 2);  
                    $wait_cancel[$i] = round($statis_data_cancel / 7, 2);
                } 
            }
        }else{  // 잘못된 요청
            return response(['msg'=>'잘못된 요청']);
        }
        
        debug($date_info, $wait_count, $wait_cancel);

        return response([
            'date_info'=>$date_info,        // 날짜
            'wait_count'=>$wait_count,      // 대기 수(누적 or 평균)
            'wait_cancel'=>$wait_cancel,    // 취소 수(누적 or 평균)
        ]);
    }

    # 평균 대기 시간
    public function wait_time_avg($term="day", $date='0'){
        debug($term, $date);
        $statis_info =array();
        $date_info = array();
        // mode = acc, avg
        // term = day, week, month
        if($date == '0'){
            $dt = date('Y-m-d', strtotime('-1 day'));    // 하루 전
            $mt = date('Y-m', strtotime('-1 month'));   // 지난 달
            $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400 + (86400*7));  // 지난 주 일요일
            $week_end = $week_first + (6*86400);    // 지난 주 토요일
        }else{
            $dt = date('Y-m-d', strtotime($date));    // 해당 날짜
            $mt = date('Y-m', strtotime($date));    // 해당 달
            $week_first = strtotime($dt) - (date('w',strtotime($dt)) * 86400);  // $dt에 해당하는 주의 일요일
            $week_end = $week_first + (6*86400);    // $dt에 해당하는 주의 토요일
        }

        #  평균 대기 시간
        if($term == 'day'){         # 하루별 평균 대기 시간
            for($i=0; $i<9; $i++){ 
                $day = date('Y-m-d', strtotime("$dt -$i day"));

                $statis_data = DB::table('dlvy')
                                ->selectRaw('sum(dlvy_wait_time) as sum, count(dlvy_wait_time) as count')
                                ->where('dlvy_date', $day)
                                ->first();
                $date_info[$i] = $day;
                $statis_info[$i] = $statis_data->count >0 ? $statis_data->sum / $statis_data->count : 0;          
            }
        }elseif($term == 'week'){   # 주차별 평균 대기 시간
            for($i =0; $i <9; $i++)
            {
                $last_week_start = date('Y-m-d', $week_first - (86400 * (7*$i)));
                $last_week_end = date('Y-m-d', $week_end - (86400 * (7*$i)));

                $statis_data = DB::table('dlvy')
                            ->selectRaw('sum(dlvy_wait_time) as sum, count(dlvy_wait_time) as count')
                            ->whereBetween('dlvy_date', [$last_week_start, $last_week_end])
                            ->first();
                $date_info[$i] = "$last_week_start~$last_week_end";
                $statis_info[$i] = $statis_data->count >0 ? $statis_data->sum / $statis_data->count : 0;   
            }
        }elseif($term=='month'){    # 달별 평균 대기 시간
            for($i =0; $i <9; $i++)
            {
                $d = mktime(0,0,0, date("m"), 1, date("Y")); //해당 달 1일
                $prev_month = strtotime("$mt -$i month",$d); //해당 달
                $last_month_start = date("Y-m-01", $prev_month);    //해당 달 1일
                $last_month_end = date("Y-m-t", $prev_month);   // 해당 달 마지막 일
        
                $statis_data = DB::table('dlvy')
                            ->selectRaw('sum(dlvy_wait_time) as sum, count(dlvy_wait_time) as count')
                            ->whereBetween('dlvy_date', [$last_month_start, $last_month_end])
                            ->first();
                $date_info[$i] = date('Y-m', $prev_month);
                $statis_info[$i] = $statis_data->count >0 ? $statis_data->sum / $statis_data->count : 0;   
            }
        }

        debug($statis_info, $date_info);
        
        return response([
            'date_info'=>$date_info,        // 날짜
            'statis_info'=>$statis_info,    // 평균 대기 시간
            
        ]);
    }
    
}
