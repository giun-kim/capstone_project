<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Station;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(['station'=>Station::get()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $station = new Station();
        if($request->id == 1) {
            $station->station_name = $request->station_name;
            $station->station_lat = $request->station_lat;
            $station->station_lon = $request->station_lon;

            $station->save();
        } else if($request->id == 2) { 
            debug($request->station_name);
            $station_update = $station->where('station_name', $request->old_station_name)->first();// 배열 형태
            $station_update->station_name = $request->station_name; // 정류장 이름
            $station_update->station_lat = $request->station_lat; // 정류장 위도
            $station_update->station_lon = $request->station_lon; // 정류장 경도

            $station_update->save();
        }

        if($request->id == 3) {
            return response()->json('cancel');
        } else {
            return response()->json(['station'=>Station::get()]);
        }
    }

    public function destroy($id)
    {
        $station = new Station();

        $station->where('station_name', $id)->delete();

        return response()->json('success');
    }
}
