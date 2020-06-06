package com.example.capstone_car;

import android.app.Activity;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

import androidx.recyclerview.widget.DividerItemDecoration;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;


public class Search_popup extends Activity implements ReceiverClickListener{

    private TextView popup_notice;
    private RecyclerView recyclerView;
    private LinearLayoutManager linearLayoutManager;
    private RecyclerViewAdapter recyclerViewAdapter;
    private TextView search_failed;
    private ProgressBar loading_user;

    private JSONArray jsonArray;

    private List<Receiver> receiver;

    @Override
    protected void onCreate(Bundle savedInstanceState){
        super.onCreate(savedInstanceState);
        setContentView(R.layout.search_popup);

        popup_notice = findViewById(R.id.popup_notice);
        loading_user = findViewById(R.id.loading_user);

        Intent intent = getIntent();
        String data = intent.getStringExtra("receiver_name");

        receiver = new ArrayList<>();    //나중에 디비에서 가져와야됨

        new JSONTask().execute("https://b157d2719683.ngrok.io/api/dlvy/check_user/"+data); //비동기라서 receiver에 바로 적용이 안됨

        popup_notice.setText(data+"님을 검색한 결과입니다.");
    }



    public void onClick(View view){
        if(view.getId() == R.id.cancel_button){
            Intent intent = new Intent();
            setResult(RESULT_OK, intent);
            finish();
        }
    }
    @Override
    public void onReceiverClick(View v, Receiver data){
        Intent intent = new Intent();
        intent.putExtra("name", data.getName());
        intent.putExtra("number", data.getNumber());
        intent.putExtra("id", data.getId());
        setResult(RESULT_OK, intent);
        finish();
    }

    public class JSONTask extends AsyncTask<String, String, String> {
        @Override
        protected String doInBackground(String... params) {   //서버랑 접속
            String result;

            try {
                JSONObject jsonObject = new JSONObject();           //JSON 데이터를 보내기 위해 JSONObject 객체 생성
                HttpURLConnection con = null;
                BufferedReader reader = null;

                try {
                    URL url = new URL(params[0]);
                    con = (HttpURLConnection) url.openConnection();
                    con.setRequestMethod("POST");
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

                    return new String(buffer);

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
        protected void onPostExecute(String result) {       //서버에서 값 받는거    비동기인 AsyncTask를 동기화 형식으로 값을 리턴 받기 위해
            super.onPostExecute(result);

            JSONArray json = null;
            try {
                json = new JSONArray(result);
            } catch (JSONException e) {
                e.printStackTrace();
            }
//                    JSONObject stations = json.getJSONObject(0); //response.data.station_all과 같음 그래서 JSONArray

            jsonArray = json;

            jsonArrayParse();
        }
    }

    public void jsonArrayParse(){
        if(jsonArray.length() != 0){
            for(int i = 0 ; i < jsonArray.length(); i++){ //이게 비동기라서 receiver가 제대로 안됨
                try {
                    JSONObject jObject = jsonArray.getJSONObject(i);
                    receiver.add(new Receiver(jObject.getString("user_name"), jObject.getString("user_phone"), jObject.getString("user_id")));
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
            //받는 사람 리스트를 동기화 형식으로 받아서 recyclerView를 갱신해주기 위해 여기다가 recyclerView 설정 적어줌. UI 갱신은 다른 스레드에서는 되지 않는다
            recyclerView = findViewById(R.id.receiver_list);
            recyclerView.setVisibility(View.VISIBLE);
            loading_user.setVisibility(View.GONE);

            linearLayoutManager = new LinearLayoutManager(this);

            recyclerView.addItemDecoration(
                    new DividerItemDecoration(this,linearLayoutManager.getOrientation()));
            recyclerView.setLayoutManager(linearLayoutManager);

            recyclerViewAdapter = new RecyclerViewAdapter(this, receiver, this);
            recyclerView.setAdapter(recyclerViewAdapter);
        }else{
            search_failed = findViewById(R.id.search_failed);
            search_failed.setVisibility(View.VISIBLE);
            loading_user.setVisibility(View.GONE);
        }
    }
}