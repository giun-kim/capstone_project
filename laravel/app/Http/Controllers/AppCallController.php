<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class AppCallController extends Controller
{
    // 메인페이지에서 호출하기 클릭
    public function call(){
        $station_all = DB::table('station')
                        ->get();
        
        debug($station_all);
        return response($station_all);
    }

    // 유저확인(동명이인)
    public function check_user($id){
        debug($id);
        $same_user = DB::table('user')
                        ->select('user_name','user_phone')
                        ->get();
                        
        debug($same_user);
        return response($same_user);
    }
}
