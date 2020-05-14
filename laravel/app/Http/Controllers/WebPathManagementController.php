<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class WebPathManagementController extends Controller
{
    // 페이지 로드
    public function index()
    {
        // 외래키를 어떻게 줄 것인지?
        return response()->json(['station_all'=>DB::table('station')->get(), 'checkpoint_all'=>DB::table('checkpoint')->get()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return response()->json([
            'station_all' => DB::table('station')->get(),
            'checkpoint_all' => DB::table('checkpoint')->get()
        ]);
    }

    // 등록하기
    public function store(Request $request)
    {
        debug('등록하기 도착');
        debug("출발지-> $request->path_start_point, 도착지-> $request->path_end_point");
        debug($request->checkpoint_id);
        // path 등록
        $path_id = DB::table('path')->insertGetId(
            ['path_start_point' => $request->path_start_point, 'path_end_point'=>$request->path_end_point],
        );
        // 반전 등록
        $reverse_path_id =DB::table('path')->insertGetId(
            ['path_start_point'=>$request->path_end_point , 'path_end_point'=>$request->path_start_point ]   
        );
        debug($path_id, $reverse_path_id);
        // 순서
        $sequence = 1;
        // path_check 등록
        for($i=0;$i<count($request->checkpoint_id); $i++){
            DB::table('path_check')->insert(
                ['path_col_id'=>$path_id, 'check_id'=>$request->checkpoint_id[$i], 'sequence'=>$sequence]
            );
            $sequence = $sequence+1;
        }
        // checkpoint 순서 반전 시키기
        $reverse_checkpoint_id = array_reverse($request->checkpoint_id);
        debug($reverse_checkpoint_id);

        // path_check 반전 등록
        for($i=0;$i<count($request->checkpoint_id); $i++){
            DB::table('path_check')->insert(
                ['path_col_id'=>$reverse_path_id, 'check_id'=>$reverse_checkpoint_id[$i], 'sequence'=>$sequence]
            );
            $sequence= $sequence+1;
        }

    }

    // 정류장 클릭(정류장 이름 받음)
    public function show($id)
    {
        return response(DB::table('path')->where('path_start_point', $id)->get());
    }

    // 리스트 클릭(경로 아이디 받음)
    public function show_path_check($id){
        debug($id);
        return response(DB::table('path_check')->where('path_col_id', $id)->get());

    }

    // 수정
    public function update(Request $request, $id)
    {
        //
    }

    // 삭제
    public function destroy($id)
    {
        debug($id);
        $path_data = DB::table('path')
                        ->select('path_start_point', 'path_end_point')
                        ->where('path_id', $id)
                        ->first();

        $reverse_path_id = DB::table('path')
                        ->where('path_start_point', $path_data->path_end_point)
                        ->where('path_end_point', $path_data->path_start_point)
                        ->value('path_id');
        debug($reverse_path_id);
        DB::table('station')
        ->where('station_name',$id)
        ->delete();


        DB::table('path')->where('path_id', $id)->delete();
        DB::table('path')->where('path_id', $reverse_path_id)->delete();

        return response()->json([

        ]);
    }
}
