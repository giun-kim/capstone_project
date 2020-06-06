package com.example.capstone_car;

import net.daum.mf.map.api.MapPOIItem;
import net.daum.mf.map.api.MapPoint;
import net.daum.mf.map.api.MapPolyline;
import net.daum.mf.map.api.MapView;

import androidx.appcompat.app.AppCompatActivity;
import io.socket.client.IO;
import io.socket.client.Socket;

import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.AsyncTask;
import android.os.Bundle;

import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URISyntaxException;
import java.net.URL;
import java.util.ArrayList;

public class CallActivity extends AppCompatActivity implements MapView.POIItemEventListener, View.OnClickListener {

    private ArrayList<Double> station_latitude;  //정류장 위도 배열
    private ArrayList<Double> station_longitude; //정류장 경도 배열
    private ArrayList<String> station_name;      //정류장 이름 배열
    private ArrayList<Double> check_latitude;    //체크포인트 위도 배열
    private ArrayList<Double> check_longitude;   //체크포인트 경도 배열
    private TextView notice, selected_start, selected_end, predicted_time;  //notice : 안내문, selected_start : 선택한 출발지, selected_end : 선택한 목적지
    private MapView mapView;            //카카오 지도 띄울 View
    private String startPoint = "";     //출발지
    private String endPoint = "";       //목적지
    private ArrayList<MapPOIItem> markers;  //마커들
    private ViewGroup mapViewContainer;     //맵 뷰 컨테이너
    private EditText input_receiver;        //받는 사람 입력할 EditText

    private String sender_id = "";               //보내는 사람 ID -> SharedPreference에서 가져옴
    private String sender_name = "";             //보내는 사람 이름 -> SharedPreference에서 가져옴
    private String receiver_id = "";             //사용자가 입력한 받는 사람 ID
    private String receiver_name = "";           //사용자가 입력한 받는 사람 이름

    Context mContext;

    private JSONArray station_JsonArray;    //서버로부터 가져온 정류장 정보 받을 JSONArray
    private JSONArray check_JsonArray;      //체크포인트 JSONArray
    private Socket socket;

    private ProgressDialog pd;              //polyline과 예상 소요 시간 계산할 때 띄워줄 ProgressDialog

    private double distance;                //사용자가 선택한 출발지에서 목적지까지 가는데의 총 거리 담을 변수
    private int time;                       //예상 소요 시간 담을 변수

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_call);

        new GetStation().execute("https://b157d2719683.ngrok.io/api/dlvy/call");  //첫 액티비티 실행 시, 지도에 띄울 정류장 가져오는 통신 시작

        notice = findViewById(R.id.notice);
        selected_start = findViewById(R.id.selected_start);
        selected_end = findViewById(R.id.selected_end);
        input_receiver = findViewById(R.id.input_receiver);
        predicted_time = findViewById(R.id.predicted_time);

        mContext = this;
        sender_id = SharedPreferenceUtil.getString(mContext, "user_id");    //SharedPreference로부터 현재 로그인되어있는 유저 정보 가져옴
        sender_name = SharedPreferenceUtil.getString(mContext,  "user_name");

        markers = new ArrayList<MapPOIItem>();  //마커 배열 생성

        mapView = new MapView(this);    //맵 생성
        mapView.setMapCenterPoint(MapPoint.mapPointWithGeoCoord(35.8962, 128.6220), true);
        mapView.setZoomLevel(1, true);

        mapViewContainer = findViewById(R.id.map_view);

        mapView.setPOIItemEventListener(this);      //마커 클릭 이벤트
        mapViewContainer.addView(mapView);          //컨테이너에 맵 붙힘
    }

    public void makeMarker() {      //마커 생성 함수
        for (int i = 0; i < station_name.size(); i++) {
            MapPOIItem marker = new MapPOIItem();
            marker.setItemName(station_name.get(i));
            marker.setTag(i);
            marker.setMapPoint(MapPoint.mapPointWithGeoCoord(station_latitude.get(i), station_longitude.get(i)));
            marker.setMarkerType(MapPOIItem.MarkerType.BluePin);
            marker.setSelectedMarkerType(MapPOIItem.MarkerType.RedPin);

            mapView.addPOIItem(marker);

            markers.add(marker);
        }
    }

    @Override
    public void onPOIItemSelected(MapView mapView, MapPOIItem mapPOIItem) { //마커 클릭 이벤트
        if (startPoint.equals("")) { //출발지가 아직 클릭되지 않았을 때
            startPoint = mapPOIItem.getItemName();
            selected_start.setText(mapPOIItem.getItemName());
            notice.setText("목적지를 클릭해주세요");
        } else if (endPoint.equals("")) {    //출발지가 정해졌고, 목적지가 정해지지 않았을 때
            if (mapPOIItem.getItemName().equals(startPoint)) {  //사용자가 출발지와 동일한 정류장을 목적지로 선택했을 때
                Toast.makeText(getApplicationContext(), "출발지와 목적지는 겹칠 수 없습니다", Toast.LENGTH_SHORT).show();
                return;
            }
            endPoint = mapPOIItem.getItemName();
            selected_end.setText(mapPOIItem.getItemName());

            new GetCheck().execute("https://b157d2719683.ngrok.io/api/dlvy/checkpoint/"+startPoint+"/"+endPoint);   //출발지와 목적지 사이의 체크포인트 가져올 통신 실행
            pd = ProgressDialog.show(this, "로딩중", "경로 탐색 중입니다...");                 //progressdialog 실행

            for(int i = 0 ; i < markers.size(); i++){   //출발지와 목적지를 다 입력하면 그 두 정류장 빼고는 다 지워줌
                if(!markers.get(i).getItemName().equals(startPoint) && !markers.get(i).getItemName().equals(endPoint)){
                    mapView.removePOIItem(markers.get(i));
                }
            }
            if(receiver_name != ""){
                notice.setText("모든 정보를 입력하셨습니다.");
            }else {
                notice.setText("받는 사람을 입력해주세요");
            }
        }
    }

    @Override
    public void onCalloutBalloonOfPOIItemTouched(MapView mapView, MapPOIItem mapPOIItem) { }

    @Override   //말풍선 클릭 시 호출
    public void onCalloutBalloonOfPOIItemTouched(MapView mapView, MapPOIItem mapPOIItem, MapPOIItem.CalloutBalloonButtonType calloutBalloonButtonType) { }

    @Override   //마커 드래그 시 호출
    public void onDraggablePOIItemMoved(MapView mapView, MapPOIItem mapPOIItem, MapPoint mapPoint) { }

    public void onClick(View view) {
        if (view.getId() == R.id.againButton) { //초기화 버튼
            markers.clear();                    //마커, polyline, startPoint, endPoint, distance, time, predicted_time을 전부 초기 값으로 돌림.
            mapView.removeAllPOIItems();
            mapView.removeAllPolylines();
            startPoint = "";
            endPoint = "";
            distance = 0;
            time = 0;
            predicted_time.setVisibility(View.INVISIBLE);
            notice.setText("출발지를 클릭해주세요");
            selected_start.setText("클릭해주세요");
            selected_end.setText("클릭해주세요");
            makeMarker();  //마커를 다시 그림
        } else if (view.getId() == R.id.search) {   //받는 사람 입력 후 검색 눌렀을 때
            if (input_receiver.getText().toString().length() == 0) {
                Toast.makeText(this, "받는 사람의 이름을 입력하지 않았습니다.", Toast.LENGTH_SHORT).show();
            } else {
                Intent intent = new Intent(this, com.example.capstone_car.Search_popup.class);
                intent.putExtra("receiver_name", input_receiver.getText().toString());
                startActivityForResult(intent, 1);
            }
        } else if (view.getId() == R.id.call) { //주문하기 버튼 눌렀을 때
            if (startPoint.equals("")) {
                Toast.makeText(this, "출발지를 정해주세요", Toast.LENGTH_SHORT).show();
            } else if (endPoint.equals("")) {
                Toast.makeText(this, "목적지를 정해주세요", Toast.LENGTH_SHORT).show();
            } else if (receiver_name.equals("")) {
                Toast.makeText(this, "받는 사람을 정해주세요", Toast.LENGTH_SHORT).show();
            } else {
                JSONObject data = new JSONObject(); //socket.io 통신 때 보낼 값 넣을 JSONObject
                try{
                    data.put("sender_id", sender_id);
                    data.put("receiver_id", receiver_id);
                    data.put("start_point", selected_start.getText());
                    data.put("end_point", selected_end.getText());
                    data.put("sender_name", sender_name);
                }catch(JSONException e){
                    e.printStackTrace();
                }

                try{
                    socket = IO.socket("https://d141df9db1cc.ngrok.io");
                }catch(URISyntaxException e){
                    throw new RuntimeException(e);
                }
                socket.connect();

                socket.emit("dlvy_call", data);

                finish();
                Toast.makeText(this, "센더 : " + sender_name + ",리시버 : " + receiver_name + ", 출발지 : " + selected_start.getText() + ", 목적지 : " + selected_end.getText(), Toast.LENGTH_SHORT).show();
            }
        }
    }

    protected void onActivityResult(int requestCode, int resultCode, Intent data) { //받는 사람 검색했을 때 나오는 팝업에서 사용자가 리스트를 클릭했을 때 그 값을 받아옴
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == 1) {
            if (resultCode == RESULT_OK) {
                if (data.getStringExtra("name") != null) {
                    receiver_id = data.getStringExtra("id");
                    receiver_name = data.getStringExtra("name");
                    StringBuilder builder = new StringBuilder(data.getStringExtra("number"));   //전화번호 유출을 막기 위해 끝 두 숫자를 XX로 바꿈
                    builder.setCharAt(11, 'X');
                    builder.setCharAt(12, 'X');

                    input_receiver.setText(data.getStringExtra("name") + "(" + builder.toString() + ")");
                    if(!startPoint.equals("")){
                        notice.setText("모든 정보를 입력하셨습니다.");
                    }else{
                        notice.setText("출발지를 클릭해주세요.");
                    }
                }else{  //사용자가 search_popup에서 취소 혹은 팝업 밖을 클릭하여 껏을 때.
                    input_receiver.setText("");
                }
            }
        }
    }

    public class GetStation extends AsyncTask<String, String, String> {   //정류장 가져오기
        @Override
        protected String doInBackground(String... params) {   //서버랑 접속

            try {
                HttpURLConnection con = null;
                BufferedReader reader = null;

                try {
                    URL url = new URL(params[0]);
                    con = (HttpURLConnection) url.openConnection();
                    con.setRequestMethod("GET");
                    con.setRequestProperty("Cache-Control", "no-cache");
                    con.setRequestProperty("Accept","application/json;characterset=utf-8");
                    con.setDoInput(true);
                    con.connect();

                    InputStream stream = con.getInputStream();
                    reader = new BufferedReader(new InputStreamReader(stream,"UTF-8"));
                    StringBuffer buffer = new StringBuffer();
                    String line = "";
                    while ((line = reader.readLine()) != null) {
                        buffer.append(line);
                    }

                    JSONObject json = null;
                    json = new JSONObject(new String(buffer));          //웹으로 치면 response.data와 같음
                    JSONArray stations = json.getJSONArray("station_all"); //response.data.station_all과 같음 그래서 JSONArray

                    System.out.println("can i get..json..?" + stations.get(0));    //JSONArray의 첫번 쨰 애가 나옴.

                    station_JsonArray = stations;
                    stationParse();

                } catch (MalformedURLException e) {
                    e.printStackTrace();
                } catch (IOException e) {
                    e.printStackTrace();
                } finally {
                    if (con != null) {
                        con.disconnect();
                    }
                    try {
                        if (reader != null) {
                            reader.close();
                        }
                    } catch (IOException e) {
                        e.printStackTrace();
                    }
                }
            } catch (Exception e) {
                e.printStackTrace();
            }

            return null;
        }
    }

    public class GetCheck extends AsyncTask<String, String, String> {  //체크포인트 가져오기
        @Override
        protected String doInBackground(String... params) {   //서버랑 접속

            try {
                HttpURLConnection con = null;
                BufferedReader reader = null;

                try {
                    URL url = new URL(params[0]);
                    con = (HttpURLConnection) url.openConnection();
                    con.setRequestMethod("GET");
                    con.setRequestProperty("Cache-Control", "no-cache");
                    con.setRequestProperty("Accept","application/json;characterset=utf-8");
                    con.setDoInput(true);
                    con.connect();

                    InputStream stream = con.getInputStream();
                    reader = new BufferedReader(new InputStreamReader(stream,"UTF-8"));
                    StringBuffer buffer = new StringBuffer();
                    String line = "";
                    while ((line = reader.readLine()) != null) {
                        buffer.append(line);
                    }
                    JSONArray check = null;
                    check = new JSONArray(new String(buffer));

                    check_JsonArray = check;

                    checkParse();

                } catch (MalformedURLException e) {
                    e.printStackTrace();
                } catch (IOException e) {
                    e.printStackTrace();
                } finally {
                    if (con != null) {
                        con.disconnect();
                    }
                    try {
                        if (reader != null) {
                            reader.close();
                        }
                    } catch (IOException e) {
                        e.printStackTrace();
                    }
                }
            } catch (Exception e) {
                e.printStackTrace();
            }

            return null;
        }
    }

    public void stationParse(){ //정류장 정보 파싱해서 각각의 배열에 넣는 함수
        station_latitude = new ArrayList<Double>();
        station_longitude = new ArrayList<Double>();
        station_name = new ArrayList<String>();
        for(int i=0; i < station_JsonArray.length(); i++){
            try {
                JSONObject jObject = station_JsonArray.getJSONObject(i);
                station_latitude.add(jObject.getDouble("station_lat"));
                station_longitude.add(jObject.getDouble("station_lon"));
                station_name.add(jObject.getString("station_name"));
            } catch (JSONException e) {
                e.printStackTrace();
            }

        }
        makeMarker();
    }

    public void checkParse(){   //체크포인트 정보 파싱해서 각각의 배열에 넣는 함수
        check_latitude = new ArrayList<Double>();
        check_longitude = new ArrayList<Double>();
        for(int i=0; i < check_JsonArray.length(); i++){
            try {
                JSONObject jObject = check_JsonArray.getJSONObject(i);
                check_latitude.add(jObject.getDouble("checkpoint_lat"));
                check_longitude.add(jObject.getDouble("checkpoint_lon"));
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
        makePolyline();
    }

    public void makePolyline(){ //사용자가 클릭한 출발지와, 목적지 사이에 polyline 그려주는 함수
        MapPolyline polyline = new MapPolyline();
        polyline.setLineColor(Color.argb(128, 255, 51, 0)); // Polyline 컬러 지정.
        int start_index = station_name.indexOf(startPoint);
        int end_index = station_name.indexOf(endPoint);
        polyline.addPoint(MapPoint.mapPointWithGeoCoord(station_latitude.get(start_index), station_longitude.get(start_index)));
        for (int i = 0, len = check_JsonArray.length(); i < len; i++){
            polyline.addPoint(MapPoint.mapPointWithGeoCoord(check_latitude.get(i), check_longitude.get(i)));
        }
        polyline.addPoint(MapPoint.mapPointWithGeoCoord(station_latitude.get(end_index), station_longitude.get(end_index)));
        mapView.addPolyline(polyline);
        cal_Distance();

        pd.dismiss();
    }

    public void cal_Distance(){ //체크포인트를 이용해 각 거리 계산
        for(int i = 0, len = check_JsonArray.length(); i < len-1; i++){
            double startLat = (check_latitude.get(i) * Math.PI)/180;
            double startLon = (check_longitude.get(i) * Math.PI)/180;
            double endLat = (check_latitude.get(i+1) * Math.PI)/180;
            double endLon = (check_longitude.get(i+1) * Math.PI)/180;
            distance += Math.acos(Math.sin(startLat) * Math.sin(endLat) + Math.cos(startLat) * Math.cos(endLat) * Math.cos(startLon - endLon)) * 6371;
        }
        time = (int)Math.ceil(distance / (1.0/12));
        new Thread(new Runnable() {    //UI Thread 외부에서 UI 관련 작업을 호출 하기 위해
            @Override
            public void run() {
                runOnUiThread(new Runnable(){
                    @Override
                    public void run() {
                        predicted_time.setVisibility(View.VISIBLE);
                        predicted_time.setText("예상 소요 시간 : " + time + "분!!");
                    }
                });
            }
        }).start();
    }
}