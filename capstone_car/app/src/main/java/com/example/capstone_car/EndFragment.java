package com.example.capstone_car;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.AsyncTask;
import android.os.Bundle;

import androidx.core.util.Pair;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.DefaultItemAnimator;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.Spinner;

import com.google.android.material.datepicker.CalendarConstraints;
import com.google.android.material.datepicker.CompositeDateValidator;
import com.google.android.material.datepicker.DateValidatorPointBackward;
import com.google.android.material.datepicker.MaterialDatePicker;
import com.google.android.material.datepicker.MaterialPickerOnPositiveButtonClickListener;

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
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;

public class EndFragment extends Fragment {
    private static final String[] spinner_list = {"전체", "보낸 배달", "받은 배달"};
    ArrayList<List> list = new ArrayList<>();
    private RecyclerView endList;
    private EndListAdapter mAdapter;

    Button button_weak, button_month, button_sixmonth, button_check, textView_startDate, button_refresh;
    LinearLayout select_date;
    Spinner spinner;

    String first_date, second_date;

    // 서버에 연결 기본 값
    String user_id;
    String term;
    String date_start;
    String date_end;

    Context mContext;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View v = inflater.inflate(R.layout.fragment_end, container, false);

        mContext = container.getContext();      // 프래그먼트는 일반 액티비티와 다르게 context 구할 때 this 사용 불가.

        user_id = SharedPreferenceUtil.getString(mContext, "user_id");
        term = "all";
        date_start = "0";
        date_end = "0";

        //RecyclerView
        endList = (RecyclerView) v.findViewById(R.id.endList);
        endList.setHasFixedSize(true);
        mAdapter = new EndListAdapter(list);

        RecyclerView.LayoutManager mLayoutManager = new LinearLayoutManager(getActivity());
        endList.setLayoutManager(mLayoutManager);
        endList.setItemAnimator(new DefaultItemAnimator());;
        endList.setAdapter(mAdapter);

        button_weak = (Button)v.findViewById(R.id.button_weak);
        button_month = (Button)v.findViewById(R.id.button_month);
        button_sixmonth = (Button)v.findViewById(R.id.button_sixmonth);
        button_check = (Button)v.findViewById(R.id.button_check);
        select_date = v.findViewById(R.id.select_date);              // gone
        textView_startDate = (Button)v.findViewById(R.id.textView_startDate);
        button_refresh = (Button)v.findViewById( R.id.button_refresh );

        button_refresh.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                term = "all";
                date_start = "0";
                date_end = "0";

                termCheck(user_id, term, date_end, date_start);

                textView_startDate.setText( "날짜 선택하기" );
            }
        } );

        ///////////////// Spinner (스피너) /////////////////
        spinner = (Spinner)v.findViewById(R.id.spinner);
        ArrayAdapter<String> adapter = new ArrayAdapter<String>(getContext(), android.R.layout.simple_spinner_item, spinner_list);
        adapter.setDropDownViewResource(android.R.layout.simple_dropdown_item_1line);
        spinner.setAdapter(adapter);
        //spinner.getSelectedItem().toString();

        spinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                if (position >= 0 && position < spinner_list.length) {
                    getSelectedCategory(position);
                }
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {

            }
        });

        /*
        내일 날짜 구하는 이유
        >> 캘린더에서 미래 날짜 선택 막을 때 max date를 오늘로 설정해버리면 오늘 전 날인 어제까지만 선택이 가능해짐.
        >> 오늘까지 선택이 가능하기 위해 max date를 내일 날짜로 설정
         */
        Calendar tomorrow = Calendar.getInstance();
        tomorrow.add(Calendar.DATE, 1);

        //////////// 캘린더 (상세 조회 날짜 선택하기) ////////////
        textView_startDate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                MaterialDatePicker.Builder<Pair<Long, Long>> builder = MaterialDatePicker.Builder.dateRangePicker();
                CalendarConstraints.Builder constraintBuilder = new CalendarConstraints.Builder();

                CalendarConstraints.DateValidator dateValidatorMax = DateValidatorPointBackward.before( tomorrow.getTimeInMillis() );
                ArrayList<CalendarConstraints.DateValidator> listValidators = new ArrayList<CalendarConstraints.DateValidator>();
                listValidators.add(dateValidatorMax);
                CalendarConstraints.DateValidator validator = CompositeDateValidator.allOf( listValidators );
                constraintBuilder.setValidator( validator );

                builder.setCalendarConstraints( constraintBuilder.build() );
                MaterialDatePicker<Pair<Long, Long>> picker = builder.build();

                assert getFragmentManager() != null;
                picker.show(getFragmentManager(), picker.toString());
                picker.addOnPositiveButtonClickListener( new MaterialPickerOnPositiveButtonClickListener<Pair<Long, Long>>() {
                    @SuppressLint("SetTextI18n")
                    @Override
                    public void onPositiveButtonClick(Pair<Long, Long> selection) {

                        @SuppressLint("SimpleDateFormat") SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd");
                        first_date = dateFormat.format( selection.first );       // 첫번째로 선택한 날짜
                        second_date = dateFormat.format( selection.second );     // 두번째로 선택한 날짜

                        textView_startDate.setText( first_date + " - " + second_date );

                        term = "check";

                        termCheck(user_id, term, second_date, first_date);
                    }
                } );
            }
        });

        // 1주일 조회 버튼
        button_weak.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                date_start = "0";
                date_end = "0";
                term = "week";

                termCheck(user_id, term, date_end, date_start);

                select_date.setVisibility(View.GONE);
                textView_startDate.setText( "날짜 선택하기" );
            }
        } );

        // 1개월 조회 버튼
        button_month.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                date_start = "0";
                date_end = "0";
                term = "month";

                termCheck(user_id, term, date_end, date_start);

                select_date.setVisibility(View.GONE);
                textView_startDate.setText( "날짜 선택하기" );
            }
        } );

        // 6개월 조회 버튼
        button_sixmonth.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                date_start = "0";
                date_end = "0";
                term = "6month";

                termCheck(user_id, term, date_end, date_start);

                select_date.setVisibility(View.GONE);
                textView_startDate.setText( "날짜 선택하기" );
            }
        });

        // 상세조회 버튼
        button_check.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (select_date.getVisibility() == View.GONE) {
                    select_date.setVisibility(View.VISIBLE);
                } else {
                    select_date.setVisibility(View.GONE);
                }
            }
        });

        return v;
    }

    @Override
    public void onResume() {
        termCheck(user_id, term, date_end, date_start);

        super.onResume();
    }

    // 데이터 준비
    private void termCheck(String user_id, String term, String date_end, String date_start) {              // 1주일, 1개월, 6개월, 상세조회
        new JSONTask1().execute("https://b157d2719683.ngrok.io/api/dlvy/completedlvy/"+user_id+"/"+term+"/"+date_end+"/"+date_start);
    }

    public class JSONTask1 extends AsyncTask<String, String, String> {
        String user_id = SharedPreferenceUtil.getString(mContext, "user_id");

        @Override
        protected String doInBackground(String... urls) {   // GET 방식
            try {
                JSONObject jsonObject = new JSONObject();
                jsonObject.accumulate( "user_id", user_id );
                jsonObject.accumulate( "term", term );
                jsonObject.accumulate( "date_start", date_start );
                jsonObject.accumulate( "date_end", date_end );
                HttpURLConnection con = null;
                BufferedReader reader = null;

                try {
                    URL url = new URL( urls[0] );
                    con = (HttpURLConnection) url.openConnection();
                    con.connect();
                    InputStream stream = con.getInputStream();
                    reader = new BufferedReader( new InputStreamReader(stream) );
                    StringBuffer buffer = new StringBuffer();
                    String line = "";

                    while((line = reader.readLine()) != null) {
                        buffer.append(line);
                    }

                    return buffer.toString();
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

        @SuppressLint("LongLogTag")
        @Override
        protected void onPostExecute(String result) {
            super.onPostExecute(result);

            jsonObject2(result);
        }
    }

    public void jsonObject2(String data) {      // list.add 할 때 뒤에 번호 추가 (1 - 보낸 배달, 2 - 받은 배달)
        try {
            JSONObject obj = new JSONObject(data);

            JSONArray completed_dlvy= obj.getJSONArray("completed_dlvy");

            Log.d("end result ::::::::: ", "result@@ : " + obj.toString());

            list.clear();

            for (int i = 0; i < completed_dlvy.length(); i++) {
                JSONObject complete = completed_dlvy.getJSONObject( i );

                if (complete.has("receiver_name")) {
                    list.add(new List(complete.getString( "receiver_name")+ "(받은 사람)", complete.getString( "dlvy_start_point" ), complete.getString("dlvy_end_point"), complete.getString("dlvy_status"), complete.getString("dlvy_date"), 1));
                } else if (complete.has("sender_name")) {
                    list.add(new List(complete.getString( "sender_name")+ "(보낸 사람)", complete.getString( "dlvy_start_point" ), complete.getString("dlvy_end_point"), complete.getString("dlvy_status"), complete.getString("dlvy_date"), 2));
                }
            }

            mAdapter.notifyDataSetChanged();
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }

    // 스피너 기능 (전체, 보낸 배달, 받은 배달 구분)
    private void getSelectedCategory(int categoryID) {
        ArrayList<List> lists = new ArrayList<>();

        if (categoryID == 0) {
            mAdapter = new EndListAdapter(list);
        } else {
            for (List adapter : list) {
                if (adapter.getCategory() == categoryID) {
                    lists.add(adapter);
                }
            }
            mAdapter = new EndListAdapter(lists);
        }
        endList.setAdapter(mAdapter);
    }
}