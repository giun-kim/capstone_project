package com.example.capstone_car;

import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;

import com.google.zxing.BarcodeFormat;
import com.google.zxing.MultiFormatWriter;
import com.google.zxing.common.BitMatrix;
import com.journeyapps.barcodescanner.BarcodeEncoder;

import org.json.JSONException;
import org.json.JSONObject;

import java.net.URISyntaxException;

import androidx.appcompat.app.AppCompatActivity;
import io.socket.client.IO;
import io.socket.client.Socket;

public class QRCodeActivity extends AppCompatActivity {

    private ImageView qrcode;       //QR코드 이미지
    private String delivery_num;    //인텐트로 받을 배달 번호
    private String car_num;         //인텐트로 받을 차 번호
    private Button control_button;  //물품 보내기, 수령 완료 버튼

    Context mContext;

    private String user;

    private Socket socket;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_qrcode);

        mContext = this;
        Intent intent = getIntent();
        delivery_num = intent.getStringExtra("dlvy_num");
        car_num = intent.getStringExtra("car_num");
        user = intent.getStringExtra("user");

        control_button = findViewById(R.id.control_button);
        if(user.equals("sender")){
            control_button.setText("물품보내기");
        }else if(user.equals("receiver")){
            control_button.setText("수령완료");
        }

        qrcode = findViewById(R.id.qrcode);

        MultiFormatWriter multiFormatWriter = new MultiFormatWriter();
        try{    //QR코드 생성하는 부분
            BitMatrix bitMatrix = multiFormatWriter.encode(delivery_num, BarcodeFormat.QR_CODE,1000,1000);
            BarcodeEncoder barcodeEncoder = new BarcodeEncoder();
            Bitmap bitmap = barcodeEncoder.createBitmap(bitMatrix);
            qrcode.setImageBitmap(bitmap);
        }catch (Exception e){}
    }

    public void onClick(View view){
        if(view.getId() == R.id.control_button){    //물품 보내기 혹은 수령 완료 눌렀을 떄.
            try{
                socket = IO.socket("https://d141df9db1cc.ngrok.io");
            }catch(URISyntaxException e){
                throw new RuntimeException(e);
            }
            socket.connect();
            JSONObject data = new JSONObject();

            if(control_button.getText() == "물품보내기"){
                try{
                    data.put("departure", control_button.getText());
                    data.put("car_num", car_num);
                    data.put("dlvy_num", delivery_num);
                }catch(JSONException e){
                    e.printStackTrace();
                }
                socket.emit("dlvy_departure", data);
                finish();

            }else if(control_button.getText() == "수령완료"){
                try{
                    data.put("complete", control_button.getText());
                    data.put("car_num", car_num);
                    data.put("dlvy_num", delivery_num);
                }catch(JSONException e){
                    e.printStackTrace();
                }
                socket.emit("dlvy_complete", data);
                finish();
            }
        }
    }
}
