<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class WebStationManagementController extends Controller 
{    # 정류장 관리 컨트롤러
    
    // 페이지 로드
    public function index()
    {
        return response(['station_all'=> DB::table('station')->get()]);
    }

    // 등록하기
    public function store(Request $request)
    {
        debug("$request->station_name, $request->station_lat, $request->station_lon");
        DB::table('station')
                        ->insert(
                            ['station_name' => $request->station_name, 'station_lat'=>$request->station_lat, 'station_lon'=>$request->station_lon ]
                        );
        debug('등록 완료');
        return response(['station_all'=> DB::table('station')->get()]);
    }

    // 정류장 마커 클릭
    // public function show($id)
    // {
    //     debug($id);
    //     $station_info = DB::table('station')
    //                     ->where('station_name', $id)
    //                     ->first();
    //     debug($station_info);
    //     return response(['station_info'=>$station_info]);
    // }

    // 수정하기
    public function update(Request $request, $id)
    {
        debug("$id, $request->station_name, $request->station_lat, $request->station_lon");
        DB::table('station')
                        ->where('station_name', $id)
                        ->update(['station_name'=>$request->station_name, 'station_lat' => $request->station_lat, 'station_lon' => $request->station_lon]);

        return response(['station_all'=> DB::table('station')->get()]);  
    }

    // 삭제하기
    public function destroy($id)
    {
        debug($id);
        DB::table('station')
                        ->where('station_name',$id)
                        ->delete();
        
        return response(['station_all'=> DB::table('station')->get()]);  
    }
}
