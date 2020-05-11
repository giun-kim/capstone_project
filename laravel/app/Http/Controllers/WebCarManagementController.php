<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
class WebCarManagementController extends Controller
{   # RC카 관리 컨트롤러

    // 페이지 로드
    public function index()
    {
        return response(['car_all'=> DB::table('car')->select('car_num','car_name')->get()]);
    }

    // 등록하기
    public function store(Request $request)
    {
        debug("$request->car_num, $request->car_name");
        DB::table('car')
                        ->insert(
                            ['car_num' => $request->car_num, 'car_name'=>$request->car_name, 'car_status'=>'운행 대기', 'car_lat'=>35.896157, 'car_lon'=>128.622522]
                        );
        debug('등록 완료');

        return response(['car_all'=> DB::table('car')->select('car_num','car_name')->get()]);
    }

    //
    // public function show($id)
    // {
    //     debug($id);
    //     $car_info = DB::table('car')
    //                     ->where('car_num', $id)
    //                     ->first();
    //     debug($car_info);
    //     return response(['car__info'=>$car_info]);
    // }

    // 수정하기
    public function update(Request $request, $id)
    {
        debug("$id, $request->car_name");
        DB::table('car')
                        ->where('car_num', $id)
                        ->update(['car_name' => $request->car_name]);


        return response(['car_all'=> DB::table('car')->select('car_num','car_name')->get()]);  
    }  

    // 삭제하기
    public function destroy($id)
    {
        debug($id);
        DB::table('car')
                        ->where('car_num',$id)
                        ->delete();

        return response(['car_all'=> DB::table('car')->select('car_num','car_name')->get()]);   
    }
}
