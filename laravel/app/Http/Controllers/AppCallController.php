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
        return response()->json(['station_all'=>$station_all]);
    }

    // 유저확인(동명이인)
    public function check_user($id){
        debug($id);
        $same_user = DB::table('user')
                        ->select('user_id','user_name','user_phone')
                        ->where('user_name', $id)
                        ->get();
                        
        debug($same_user);
        return response($same_user);
    }

    // qr_code
    public function qr_user_check($id){
        $qr_user = DB::table('dlvy')
                        ->select('dlvy_sender','dlvy_receiver')
                        ->where('dlvy_num', $id)
                        ->first();

        return response()->json([
            'qr_receiver' => $qr_user->dlvy_receiver,
            'qr_sender' => $qr_user->dlvy_sender
        ]);
    }


}
