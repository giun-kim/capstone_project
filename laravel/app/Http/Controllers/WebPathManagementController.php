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
        debug($request);
        // path 등록
        $path_id = DB::table('path')->insertGetId(
            ['path_start_point' => $request->path_start_point, 'path_end_point'=>$request->path_end_point],
        );
        // 반전 등록
        $reverse_path_id =DB::table('path')->insertGetId(
            ['path_start_point'=>$request->path_end_point , 'path_end_point'=>$request->path_start_point ]   
        );

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
        // 순서 초기화
        $sequence = 1;
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
        debug($id);
        return response(DB::table('path')->where('path_start_point', $id)->get());
    }

    // 리스트 클릭(경로 아이디 받음)
    public function show_path_check($id){
        debug($id);
        return response(DB::table('path_check')->select('check_id','sequence')->where('path_col_id', $id)->orderBy('sequence','asc')->get());

    }

    // 수정 (경로 아이디, 채크포인트 아이디)
    public function update(Request $request, $id)
    {
        debug($id, $request->checkpoint_id);


        $path_data = DB::table('path')
                        ->select('path_start_point', 'path_end_point')
                        ->where('path_id', $id)
                        ->first();
        // 반전된 path_id
        $reverse_path_id = DB::table('path')
                        ->where('path_start_point', $path_data->path_end_point)
                        ->where('path_end_point', $path_data->path_start_point)
                        ->value('path_id');
        // 기존 웨이포인트 삭제
        DB::table('path_check')->where('path_col_id', $id)->delete();
        DB::table('path_check')->where('path_col_id', $reverse_path_id)->delete();
        
        // 순서
        $sequence = 1;
        // path_check 등록
        for($i=0;$i<count($request->checkpoint_id); $i++){
            DB::table('path_check')->insert(
                ['path_col_id'=>$id, 'check_id'=>$request->checkpoint_id[$i], 'sequence'=>$sequence]
            );
            $sequence = $sequence+1;
        }
        // checkpoint 순서 반전 시키기
        $reverse_checkpoint_id = array_reverse($request->checkpoint_id);
        // 순서 초기화
         $sequence = 1;
        // path_check 반전 등록
        for($i=0;$i<count($request->checkpoint_id); $i++){
            DB::table('path_check')->insert(
                ['path_col_id'=>$reverse_path_id, 'check_id'=>$reverse_checkpoint_id[$i], 'sequence'=>$sequence]
            );
            $sequence= $sequence+1;
        }

        $return_path_id = DB::table('path')
                        ->where('path_id', $id)
                        ->value('path_start_point');
        return response(DB::table('path')->where('path_start_point',$return_path_id)->get());
    }

    // 삭제
    public function destroy($id)
    {
        $path_data = DB::table('path')
                        ->select('path_start_point', 'path_end_point')
                        ->where('path_id', $id)
                        ->first();

        $reverse_path_id = DB::table('path')
                        ->where('path_start_point', $path_data->path_end_point)
                        ->where('path_end_point', $path_data->path_start_point)
                        ->value('path_id');
        DB::table('station')
        ->where('station_name',$id)
        ->delete();


        DB::table('path')->where('path_id', $id)->delete();
        DB::table('path')->where('path_id', $reverse_path_id)->delete();


        return response(DB::table('path')->where('path_start_point',$path_data->path_start_point)->get());
    }
}
