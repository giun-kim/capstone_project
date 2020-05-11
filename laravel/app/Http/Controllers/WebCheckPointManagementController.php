<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Checkpoint;

class WebCheckPointManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['checkpoint'=>Checkpoint::get()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $checkpoint = new Checkpoint();
        if($request->id == 1) { // 등록
            debug($request);
            $checkpoint->checkpoint_lat = $request->checkpoint_lat;
            $checkpoint->checkpoint_lon = $request->checkpoint_lon;

            $checkpoint->save();
        } 

        if($request->id == 3) { // 취소
            return response()->json('cancel');
        } else {
            return response()->json(['checkpoint'=>checkpoint::get()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $checkpoints = new Checkpoint();

        $checkpoint = $checkpoints->find($id);

        debug($checkpoint);

        $checkpoint->checkpoint_lat = $request->checkpoint_lat; // 정류장 위도
        $checkpoint->checkpoint_lon = $request->checkpoint_lon; // 정류장 경도

        $checkpoint->save();

        return response()->json('success');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $checkpoint = new Checkpoint();

        $checkpoint->where('checkpoint_id', $id)->delete();

        return response()->json('success');
    }
}
