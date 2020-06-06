package com.example.capstone_car;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.viewpager.widget.ViewPager;
import io.socket.client.IO;
import io.socket.client.Socket;

import android.annotation.SuppressLint;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.content.pm.Signature;
import android.net.Uri;
import android.os.Bundle;
import android.util.Base64;
import android.util.Log;
import android.view.View;
import android.view.WindowManager;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import com.google.android.gms.tasks.OnCompleteListener;
import com.google.android.gms.tasks.Task;
import com.google.android.material.tabs.TabItem;
import com.google.android.material.tabs.TabLayout;
import com.google.firebase.iid.FirebaseInstanceId;
import com.google.firebase.iid.InstanceIdResult;

import org.json.JSONException;
import org.json.JSONObject;

import java.net.URISyntaxException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

public class MainActivity extends AppCompatActivity {

    Context mContext;
    Socket mSocket;

    TextView textView_userName;
    TabLayout tablayout;
    VPAdapter vpAdapter;
    ViewPager viewpager;
    Button button_call;

    String user_id, user_name, user_phone;

    long backKeyPressedTime;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        

        mContext = this;

        getAppKeyHash();

        user_id = SharedPreferenceUtil.getString(mContext, "user_id");
        user_name = SharedPreferenceUtil.getString(mContext, "user_name");
        user_phone = SharedPreferenceUtil.getString(mContext, "user_phone");

        Log.d("main id  //////// ", "user_id = " + user_id);

        textView_userName = findViewById(R.id.textView_userName);
        textView_userName.setText(user_name + "님, 반갑습니다!");

        textView_userName.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(MainActivity.this, UserInfoActivity.class);
                startActivity(intent);
            }
        });

        viewpager = findViewById(R.id.viewpager);
        vpAdapter = new VPAdapter(getSupportFragmentManager());
        viewpager.setOffscreenPageLimit(2);
        viewpager.setAdapter(vpAdapter);

        tablayout = findViewById(R.id.tablayout);
        tablayout.setupWithViewPager(viewpager);

        //
        tablayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override
            public void onTabSelected(TabLayout.Tab tab) {
                viewpager.setCurrentItem(tab.getPosition());
            }

            @Override
            public void onTabUnselected(TabLayout.Tab tab) {
            }

            @Override
            public void onTabReselected(TabLayout.Tab tab) {

            }
        });

        //

        button_call = findViewById(R.id.button_call);
        button_call.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(MainActivity.this, CallActivity.class);

                startActivity(intent);
            }
        });

        // 현재 토큰 가져오기 && 출력
        // 토큰이 있어야 휴대폰 주인을 특정할 수 있고 서버에서 주는 push 메시지 받을 수 있음
        FirebaseInstanceId.getInstance().getInstanceId().addOnCompleteListener(new OnCompleteListener<InstanceIdResult>() {
            @SuppressLint("LongLogTag")
            @Override
            public void onComplete(@NonNull Task<InstanceIdResult> task) {
                if (!task.isSuccessful()) {
                    Log.w("main", "getInstanceid Failed", task.getException());
                    return;
                }
                String token = task.getResult().getToken();
                Log.d("MAIN-TOKEN : ㅣㅣㅣㅣㅣㅣㅣㅣㅣㅣ", token);
            }
        });
    }

    private void getAppKeyHash() {
        try {
            PackageInfo info = getPackageManager().getPackageInfo( getPackageName(), PackageManager.GET_SIGNATURES );
            for (Signature signature : info.signatures) {
                MessageDigest md;
                md = MessageDigest.getInstance( "SHA" );
                md.update(signature.toByteArray());
                String something = new String( Base64.encode( md.digest(), 0 ) );
                Log.d("yyg", "key : " + something);
            }
        } catch (Exception e) {
            Log.e("name not found", e.toString());
        }
    }

   @Override
    public void onResume() {
       super.onResume();

       Intent intent = getIntent();
       String loginId = intent.getStringExtra("loginId");

       Log.d("onResume  ///   ", "intent : " + intent);

       Log.d("onResume  ///   ", "loginId : " + loginId);

       if (loginId != null && loginId.equals("300")) {
           notiAlertDialog(intent);
       }
    }

    // onNewIntent 호출 시점 : A라는 Activity가 현재 떠 있는 상태에서 A라는 Activity를 다시 호출 했을 때
    // onNewIntent에 넣어야 하는 이유 -> https://knoow.tistory.com/182
    @Override
    protected void onNewIntent(Intent intent) {
        Log.d("onNewIntent  ///   ", "onNewIntent!!!!!!");

        notiAlertDialog(intent);

        super.onNewIntent(intent);
    }

    private void notiAlertDialog(Intent intent) {
        if (intent != null) {
            setIntent(intent);

            // 화면 켜기
            getWindow().addFlags( WindowManager.LayoutParams.FLAG_TURN_SCREEN_ON );

            String title = intent.getStringExtra( "title" );
            String body = intent.getStringExtra( "body" );
            String waiting_num = intent.getStringExtra( "waiting_num" );
            String car_num = intent.getStringExtra( "car_num" );
            String dlvy_num = intent.getStringExtra( "dlvy_num" );
            String sender_name = intent.getStringExtra("sender_name");
            AlertDialog.Builder builder = new AlertDialog.Builder(MainActivity.this);

            if (title.equals( "배달 수락" )) {
                builder.setTitle(sender_name + "님이 물품을 보내려고 합니다.");
                if (waiting_num.equals("0")) {
                    builder.setMessage( "배달을 수락하시겠습니까?" );
                } else {
                    builder.setMessage( "대기 순위는 " +  waiting_num + "번 입니다. 대기 하시겠습니까?");
                }
                builder.setPositiveButton( "예", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        JSONObject data = new JSONObject();

                        try {
                            data.put("accept", "yes");
                            if (waiting_num.equals( "0" )) {
                                data.put("wait", "no");
                            } else {
                                data.put("wait", "yes");
                            }
                            data.put("car_num", car_num);
                            data.put("dlvy_num", dlvy_num);
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                        try {
                            mSocket = IO.socket( "https://d141df9db1cc.ngrok.io" );
                        } catch(URISyntaxException e) {
                            throw new RuntimeException(e);
                        }
                        mSocket.connect();

                        mSocket.emit("accept", data);
                    }
                } );
                builder.setNegativeButton( "아니오", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        JSONObject data = new JSONObject();

                        try {
                            data.put("accept", "no");
                            if (waiting_num.equals( "0" )) {
                                data.put("wait", "no");
                            } else {
                                data.put("wait", "yes");
                            }
                            data.put("car_num", car_num);
                            data.put("dlvy_num", dlvy_num);
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                        try {
                            mSocket = IO.socket( "https://d141df9db1cc.ngrok.io" );
                        } catch(URISyntaxException e) {
                            throw new RuntimeException(e);
                        }
                        mSocket.connect();

                        mSocket.emit("accept", data);
                    }
                } );
                AlertDialog alertDialog = builder.create();
                alertDialog.show();
            }
        }
    }

    // 뒤로가기 2번 누르면 앱 종료
    @Override
    public void onBackPressed() {
        // 1번째 백 버튼 클릭
        if (System.currentTimeMillis()>backKeyPressedTime+2000) {
            backKeyPressedTime = System.currentTimeMillis();
            Toast.makeText(this, "한번 더 누르면 종료됩니다.", Toast.LENGTH_SHORT).show();
        } else { // 2번째 백 버튼 클릭 (종료)
            AppFinish();
        }
    }

    // 앱 종료
    public void AppFinish() {
        finish();
        System.exit(0);
        android.os.Process.killProcess(android.os.Process.myPid());
    }
}
