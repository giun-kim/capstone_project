<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class WebCheckPointManagementController extends Controller
{   # 채크포인트 관리 컨트롤러

    // 페이지 로드
    public function index()
    {
        return response(['checkpoint_all' => DB::table('checkpoint')->get()]);
    }

    // 등록하기
    public function store(Request $request)
    {
        debug("$request->checkpoint_lat, $request->checkpoint_lon");
        DB::table('checkpoint')
                        ->insert(
                            ['checkpoint_lat'=>$request->checkpoint_lat, 'checkpoint_lon'=>$request->checkpoint_lon ]
                        );
        debug('등록 완료');

        return response(['checkpoint_all' => DB::table('checkpoint')->get()]);
    }

    // 채크포인트 마커 클릭
    // public function show($id)
    // {
    //     debug($id);
    //     $checkpoint_info = DB::table('checkpoint')
    //                     ->where('checkpoint_id', $id)
    //                     ->first();
    //     debug($checkpoint_info);

    //     return response(['checkpoint_info'=>$checkpoint_info]);
    // }

    // 수정하기
    public function update(Request $request, $id)
    {
        debug("$id, $request->checkpoint_lat, $request->checkpoint_lon");
        DB::table('checkpoint')
                        ->where('checkpoint_id', $id)
                        ->update(['checkpoint_lat' => $request->checkpoint_lat, 'checkpoint_lon' => $request->checkpoint_lon]);

        return response(['checkpoint_all' => DB::table('checkpoint')->get()]);
    }

    // 삭제하기
    public function destroy($id)
    {
        debug($id);
        DB::table('checkpoint')
                        ->where('checkpoint_id',$id)
                        ->delete();
        
        return response(['checkpoint_all' => DB::table('checkpoint')->get()]);
    }
}
