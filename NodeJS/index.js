var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var admin = require('firebase-admin');
var db_config = require('./database.json');
var mysql = require('mysql');

var connection = mysql.createConnection({
    host: db_config.host,
    user: db_config.user,
    password: db_config.password,
    port: db_config.port,
    database: db_config.database,
    multipleStatements: true,
});
connection.connect();

// fcm 프로젝트 특정
var serviceAccount = require('./capstone-car-firebase-adminsdk-zqm0k-2248c7ebc5.json');
 
// 해당 토큰으로 휴대폰에 설치된 앱을 특정함
var sender_token = "frds82R9RguVKXCuTHQIkK:APA91bHFHq4olZB6TJdOdvGJL7te798PyXyfM6pR3GP3_chQ1XZDZRZ69QZJ29T9-LoXgXm2h6RqI3Fx3vaYHhpM27e-G6MKSbHRnN5UKg9-9aOWOPsdBT5lGBaTrxAFIUG1LynUyE16";
// var receiver_token = "eot9UMWkRuWHP0oxph6qfk:APA91bFNN2JLoWXn9WKCHI09OsyvFpj8a2K_5cXlCPcTpqofxX3onqUbNgXk4FM2gsdfk34vD-P1bnKmt-KPqdXO_g9NLF6grICP4HfkYJHPi9v0d4Zw9_5s1039Y6876k9ytE4WZfVi";
var receiver_token = "fOQ-jf71RIaCYZM2en0eXQ:APA91bGztZWmN-LutegBOVqPf9hOBssRIEkl1CloptCHhlnTAioXcSc5c4t6HtOaeQ3aNwhaGKD15_veNLuuprKYMMQDlJrL5HI6LaSo9c72_Bb1C66RY2AOQpWeTOzRhCY198pNshvL";
//receiver_token 지금은 sender_token이랑 똑같은거 써서 테스트 하는데 나중에 바꿔야됨

admin.initializeApp({
    credential: admin.credential.cert(serviceAccount)
});

// 출발, 도착, 완료 알림
var user; // 리시버, 센더
var title_msg; // 제목
var body_msg; // 내용

io.on('connection', (socket) => {
    console.log('one user connection // ' + socket.id);

    // 센더 호출하기 클릭
    socket.on('dlvy_call', (data) => { // 센더id, 리시버id, 목적지 이름, 출발지 이름

        var {sender_id, receiver_id, start_point, end_point, sender_name} = data;
        var wait; // 대기 여부
        var waiting_num; // 대기 순번
        var car_num; 
        
        // 사용대기인 rc카 검색
        connection.query('select car_num from car where car_status = "배달대기"', (err, rows, fields) => {
            if(err) console.log("err : " + err); // err 표시
            
            // undefined을 console.log(rows[0].car_num)으로 출력하면 없는 데이터를 찾으려고 해서 에러 발생

            if(rows.length >= 1) { // 사용 가능 o 일 때 리시버에게 전달
                car_num = rows[0].car_num;
                wait = false;
                waiting_num = 0;

                //db 저장 -> 비동기 순서
                dlvy_call_db_insert(car_num, wait, waiting_num, sender_id, receiver_id, start_point, end_point, sender_name);
                connection.query(`select count(*) as count from dlvy where dlvy_date = curdate()`, (err, rows, fields) => {
                    console.log(rows);
                    io.emit("call_count", rows);
                });
            }
            else if(rows.length == 0) { // 사용가능 x 일 때 대기 순위 리시버 전달
                // 대기 순번 가져오기 +1 로 호출한 사용자 대기순번 표시
                connection.query('select dlvy_num from dlvy where dlvy_status = "대기중" and dlvy_date = curdate()', (err, rows, fields) => {
                    if(err) console.log("err : " + err); // err 표시
                    car_num = 0;
                    wait = true;
                    waiting_num = rows.length + 1;
                    //db 저장 -> 비동기 순서
                    dlvy_call_db_insert(car_num, wait, waiting_num, sender_id, receiver_id, start_point, end_point, sender_name);
                    connection.query(`select count(*) as count from dlvy where dlvy_date = curdate()`, (err, rows, fields) => {
                        console.log(rows);
                        io.emit("call_count", rows);
                    });
                });
            }
        });
    });

    // 배달 수락 여부
    socket.on('accept', (data) => { // accept, wait, car_num, dlvy_num
        if(data.accept == 'yes') { // 배달 수락 
            if(data.wait == 'no') { // 대기 없음

                // rc 운행상태 전달
                connection.query(`select car_status, count(*) as cnt from car group by car_status`, (err, rows, fields) => {
                    var dd = new Object();
                    dd.call = 0;
                    dd.dlvy = 0;
                    dd.err = 0;
                    dd.wait = 0;
                    for(var i = 0; i < rows.length; i++){
                        if(rows[i].car_status=="호출중"){
                            dd.call = rows[i].cnt;
                        }else if(rows[i].car_status == "배달중"){
                            dd.dlvy = rows[i].cnt;
                        }else if(rows[i].car_status == "배달대기"){
                            dd.wait = rows[i].cnt;
                        }else if(rows[i].car_status == "오류"){
                            dd.err = rows[i].cnt;
                        }
                    }
                    io.emit("rc_status", dd);
                });
                
                var dlvy_data = new Object();
                dlvy_data.dlvy_num = data.dlvy_num; // 작업번호
                dlvy_data.car_num = data.car_num; // RC번호

                // rc카에게 정보 전달, 체크포인트, 배달번호
                connection.query(`select dlvy_start_point, dlvy_end_point from dlvy where dlvy_num = ${data.dlvy_num}`, (err, rows, fields) => {
                    // 출발지, 목적지로 체크포인트 순서대로 gps값 가져와서 rc카에 전달
                    connection.query(`SELECT c.checkpoint_lat, c.checkpoint_lon 
                                    FROM path_check as pc left join checkpoint as c on pc.check_id = c.checkpoint_id 
                                    where pc.path_col_id in 
                                        ( select path_id from path where path_start_point = "${rows[0].dlvy_start_point}" and path_end_point = "${rows[0].dlvy_end_point}") 
                                    order by sequence asc;`, (err, rows, fields) => {                        
                        
                        dlvy_data.gps = rows  // 검색된 gps들 담기

                        var jsonData = JSON.stringify(dlvy_data);

                        // 체크포인트, 배달번호 전달 ( 센더id, 리시버id는 보류), car_num으로 작업rc카 분류
                        // socket.emit('dlvy_start_data', dlvy_data);
                    });
                });    
             
            }else { // 대기 있음
                // 대기 수락 시 웹에 대기 수 전달
                // 대기수락 , 대기현황 web 전달
                var wait_data = new Object();

                connection.query(`select dlvy_num from dlvy where dlvy_wait_time is not null and dlvy_date = curdate()`, (err, rows, fields) => {
                    wait_data.wait_complete = rows.length; // 대기 완료
                    connection.query(`select dlvy_num from dlvy where dlvy_status = "대기중" and dlvy_date = curdate()`, (err, rows, fields) => {
                        wait_data.wait_now = rows.length; // 대기중
                        connection.query(`select dlvy_num from dlvy where dlvy_status = "대기취소" and dlvy_date = curdate()`, (err, rows, fields) => {
                            wait_data.wait_cancel = rows.length; // 대기 취소
                            console.log(wait_data);
                            // 대기 현황 web전달
                            io.emit('wait_data', wait_data);
                        });
                    });
                });
            }
        }else { // 배달 캔슬, 해당 배달 삭제하려면 배달 번호 잇어야됨
            if(data.wait == 'no') { // 배달 캔슬,  
                // rc카 상태 배달 대기로 변경
                connection.query(`update car set car_status = '배달대기' where (car_num = ${data.car_num})`, (err, rows, fields) => {
                    if(err) console.log("err : " + err); // err 표시
                    else console.log('대기중 변경 Successfully');
                })
            }else { // 대기 캔슬, 작업 삭제

            }
            // 배달 삭제
            connection.query(`delete from dlvy where (dlvy_num = ${data.dlvy_num})`, (err, rows, fields) => {
                if(err) console.log("err : " + err); // err 표시
                else console.log('작업 삭제 완료');
            });
        }
    });

    // RC 출발 알림 ( 배달시작_호출중, 배달시작_배달중 )
    socket.on('dlvy_departure', (data) => { // 출발지, 차번호, 작업번호 
        if(data.departure == '출발지'){  // 출발지로 출발(호출) , 센더 , rc카
            user = sender_token;
            title_msg = '호출 시작';
            body_msg = '출발지로 가고 있습니다';
        }else if(data.departure == '물품보내기') { // 목적지로 출발 (배달) , 사용자(app), dlvy_start
            user = receiver_token;
            title_msg = '배달 시작';
            body_msg = '목적지로 배달이 시작되었습니다';

            // RC카 상태 배달중 변경
            connection.query(`update car set car_status = "배달중" where car_num=${data.car_num}`, (err, rows, fields) => {
                if(err) console.log(err);
                else console.log('배달중 변경 Successfully');
            });

            // 작업상태 상태 배달중 변경, 배달 시작 시간
            connection.query(`update dlvy set dlvy_status = "배달중", dlvy_start = curtime() where dlvy_num=${data.dlvy_num}`, (err, rows, fields) => {
                if(err) console.log(err);
                else console.log('배달중 변경, 배달시작 시간 저장 Successfully');
            });

            // RC카에게 출발 신호 보내기
            // socket.emit('dlvy_start', {start: 'start'});
        }
        notification_message(user, title_msg, body_msg);
    });

    // RC 도착 알림 ( 출발지_도착, 목적지_도착)
    socket.on('dlvy_arrival', (data) => { // 도착지, 차번호, 작업번호
        if(data.arrival == '출발지') { // 출발지 도착, 센더
            user = sender_token;
            title_msg = '자동차 도착';
            body_msg = '물품을 적재 해주십시오';
        }else if(data.arrival == '목적지') { // 목적지 도착, 리시버
            user = receiver_token;
            title_msg = '배달 도착';
            body_msg = '물품을 수령 해주십시오';
        }

        notification_message(user, title_msg, body_msg);
    });

    // RC 배달 완료
    socket.on('dlvy_complete', (data) => { // 차번호, 배달번호
        // 작업상태-> 배달완료, 배달완료시간
        connection.query(`update dlvy set dlvy_status = "배달완료", dlvy_end = curtime() where dlvy_num=${data.dlvy_num}`, (err, rows, fields) => {
            if(err) console.log(err);
            else console.log('배달완료 변경, 배달완료 시간 저장 Successfully');
        });

        if(data.complete == "수령완료"){
            user = sender_token;
            title_msg = "배달 완료"
            body_msg = "배달의 완료되었습니다."
        }
        notification_message(user, title_msg, body_msg);

        // 실시간 배달현황, 완료 건수
        connection.query(`select count(*) as count from dlvy where dlvy_date = curdate() and dlvy_status = "배달완료"`, (err, rows, fields) => {
            io.emit("complete_dlvy_count", rows);
        })
        connection.query(`select dlvy_num from dlvy where dlvy_status = "대기중" and dlvy_date = curdate()`, (err, rows, fields) => {
            if(rows.length > 0){ // 대기작업 o,
                // 작업완료 한 RC카 상태 호출중 변경
                connection.query(`update car set car_status = "호출중" where car_num = ${data.car_num}`, (err, rows, fields) => {
                    if(err) console.log(err);
                    else console.log('RC카 상태 호출중 Successfully');
                });
                // 대기 작업 -> 작업 시작
                connection.query(`update dlvy set dlvy_car_num = ${data.car_num}, dlvy_status = "호출중", dlvy_wait_time = timestampdiff(minute, dlvy_wait_start, curtime()), dlvy_call_start = curtime() where dlvy_num = ${rows[0].dlvy_num}`, (err, rows, fields) => {
                    if(err) console.log(err);
                    else console.log('작업 상태 대기중->호출중 Successfully');
                });

                var dlvy_data = new Object();
                dlvy_data.dlvy_num = rows[0].dlvy_num; // 대기 중이었던 작업번호
                dlvy_data.car_num = data.car_num; // 차번호

                // rc카에게 정보 전달, 체크포인트, 배달번호
                connection.query(`select dlvy_start_point, dlvy_end_point from dlvy where dlvy_num = ${rows[0].dlvy_num}`, (err, rows, fields) => {
                    // 출발지, 목적지로 체크포인트 순서대로 gps값 가져와서 rc카에 전달
                    connection.query(`SELECT c.checkpoint_lat, c.checkpoint_lon 
                                    FROM path_check as pc left join checkpoint as c on pc.check_id = c.checkpoint_id 
                                    where pc.path_col_id in 
                                        ( select path_id from path where path_start_point = "${rows[0].dlvy_start_point}" and path_end_point = "${rows[0].dlvy_end_point}") 
                                    order by sequence asc;`, (err, rows, fields) => {                        
                        
                        dlvy_data.gps = rows  // 검색된 gps들 담기

                        var jsonData = JSON.stringify(dlvy_data);
                        console.log(jsonData);
                        
                        // 체크포인트, 배달번호 전달 ( 센더id, 리시버id는 보류), car_num으로 작업rc카 분류
                        // socket.emit('dlvy_start_data', dlvy_data);
                    });
                });   

                // 대기중 작업 -> 대기 완료 대기 현황, 평균 대기시간 web전달 
                var wait_data = new Object();
                
                // 대기현황
                connection.query(`select dlvy_num from dlvy where dlvy_wait_time is not null and dlvy_date = curdate()`, (err, rows, fields) => {
                    wait_data.wait_complete = rows.length; // 대기 완료
                    connection.query(`select dlvy_num from dlvy where dlvy_status = "대기중" and dlvy_date = curdate()`, (err, rows, fields) => {
                        wait_data.wait_now = rows.length; // 대기중
                        connection.query(`select dlvy_num from dlvy where dlvy_status = "대기취소" and dlvy_date = curdate()`, (err, rows, fields) => {
                            wait_data.wait_cancel = rows.length; // 대기 취소
                            
                            // 평균 대기시간 
                            connection.query(`select floor(avg(dlvy_wait_time)) as time from dlvy where dlvy_date = curdate() group by dlvy_date`, (err, rows, fields) => {
                                wait_data.wait_avg_time = rows[0].time;

                                // web전달
                                io.emit('wait_data', wait_data);
                            });
                        });
                    });
                });

                // rc 운행상태 전달
                connection.query(`select car_status, count(*) as cnt from car group by car_status`, (err, rows, fields) => {
                    var dd = new Object();
                    dd.call = 0;
                    dd.dlvy = 0;
                    dd.err = 0;
                    dd.wait = 0;
                    console.log(rows[0].cnt);
                    for(var i = 0; i < rows.length; i++){
                        if(rows[i].car_status=="호출중"){
                            dd.call = rows[i].cnt;
                        }else if(rows[i].car_status == "배달중"){
                            dd.dlvy = rows[i].cnt;
                        }else if(rows[i].car_status == "배달대기"){
                            dd.wait = rows[i].cnt;
                        }else if(rows[i].car_status == "오류"){
                            dd.err = rows[i].cnt;
                        }
                    }
                    io.emit("rc_status", dd);
                });
            }else if(rows.length == 0) { // 대기작업 x , 차상태 배달대기 변경 
                connection.query(`update car set car_status = "배달대기" where car_num = ${data.car_num}`, (err, rows, fields) => {
                    if(err) console.log(err);

                    // rc 운행상태 전달
                    connection.query(`select car_status, count(*) as cnt from car group by car_status`, (err, rows, fields) => {
                        var dd = new Object();
                        dd.call = 0;
                        dd.dlvy = 0;
                        dd.err = 0;
                        dd.wait = 0;
                        console.log(rows[0].cnt);
                        for(var i = 0; i < rows.length; i++){
                            if(rows[i].car_status=="호출중"){
                                dd.call = rows[i].cnt;
                            }else if(rows[i].car_status == "배달중"){
                                dd.dlvy = rows[i].cnt;
                            }else if(rows[i].car_status == "배달대기"){
                                dd.wait = rows[i].cnt;
                            }else if(rows[i].car_status == "오류"){
                                dd.err = rows[i].cnt;
                            }
                        }
                        io.emit("rc_status", dd);
                    });
                })
            }
        });
    });

    // 배달 대기 취소
    socket.on('dlvy_wait_cancel', (data) => { // 배달번호, 

        connection.query(`update dlvy set dlvy_status = "대기취소" where (dlvy_num = ${data.dlvy_num})`, (err, rows, fields) => {
            if(err) console.log(err);
            // 대기 현황 web전달
            var wait_data = new Object();
            console.log("들어옴")
            connection.query(`select dlvy_num from dlvy where dlvy_wait_time is not null and dlvy_date = curdate()`, (err, rows, fields) => {
                wait_data.wait_complete = rows.length; // 대기 완료
                connection.query(`select dlvy_num from dlvy where dlvy_status = "대기중" and dlvy_date = curdate()`, (err, rows, fields) => {
                    wait_data.wait_now = rows.length; // 대기중
                    connection.query(`select dlvy_num from dlvy where dlvy_status = "대기취소" and dlvy_date = curdate()`, (err, rows, fields) => {
                        wait_data.wait_cancel = rows.length; // 대기 취소
                                    
                        io.emit('wait_data', wait_data);
                    });
                });
            });
        });

    });

    // RC카 실시간 gps 정보
    socket.on('rc_gps', (data) => { // 실시간 lat, lon , 차 번호 -> db저장, 웹 전달

        var car_data = new Object();
        car_data.car_num = data.car_num;
        car_data.car_lat = data.car_lat;
        car_data.car_lon = data.car_lon;
        console.log(car_data.car_num, car_data.car_lat, car_data.car_lon);
        // lat, lon 저장
        connection.query(`update car set car_lat = ${data.car_lat}, car_lon = ${data.car_lon} where car_num = ${data.car_num}`, (err, rows, fields) => {
            if(err) console.log(err);
            // 상태 , 차 번호, lat, lon을 web 전달
            connection.query(`select car_status from car where car_num = ${data.car_num}`, (err, rows, data) => {
                car_data.car_status = rows[0].car_status;
    

                var jsonData = JSON.stringify(car_data);
                console.log(jsonData);
                io.emit("car_data", car_data);
            });
        });
           
    });

    // RC카 에러 정보
    socket.on('rc_error', (data) => { // rc카 id, 작업번호, 오류내역
        console.log("들어옴")
        // RC 상태변경, 오류내역
        connection.query(`update car set car_status = "오류", car_error = "${data.err_msg}" where car_num = ${data.car_num}`, (err, rows, fields) => {
            if(err) console.log(err);
        });
        // 해당 작업 상태 변경
        connection.query(`update dlvy set dlvy_status = "오류", dlvy_error = "${data.err_msg}" where dlvy_num = ${data.dlvy_num}`, (err, rows, fields) => {
            if(err) console.log(err);
        });

        var car_data = new Object();
        car_data.car_num = data.car_num;
        car_data.err_msg = data.err_msg;
        car_data.dlvy_num = data.dlvy_num
        // rc 운행상태, 오류 알림 전달

        connection.query(`select car_status, count(*) as cnt from car group by car_status`, (err, rows, fields) => {
            car_data.call = 0;
            car_data.dlvy = 0;
            car_data.err = 0;
            car_data.wait = 0;
            for(var i = 0; i < rows.length; i++){
                if(rows[i].car_status=="호출중"){
                    car_data.call = rows[i].cnt;
                }else if(rows[i].car_status == "배달중"){
                    car_data.dlvy = rows[i].cnt;
                }else if(rows[i].car_status == "배달대기"){
                    car_data.wait = rows[i].cnt;
                }else if(rows[i].car_status == "오류"){
                    car_data.err = rows[i].cnt;
                }
            }
            // web 전달
            io.emit('car_err', car_data);
        });
    });

});

// 함수

function dlvy_call_db_insert(car_num, wait, waiting_num, sender_id, receiver_id, start_point, end_point, sender_name) {
    
    var sql = ''; // 작업 저장 sql문
    var insert_dlvy_num; // insertId 값 ( 새로 저장된 작업 번호)

    if(!wait) { // 바로 사용 o, 대기 없을 때
        // 차id, 배달상태(호출중), 출발지, 목적지, 센더id, 리시버id, 호출 시간, 작업 날짜
        sql = `insert into dlvy(dlvy_car_num, dlvy_status, dlvy_start_point, dlvy_end_point,  dlvy_sender,    dlvy_receiver,   dlvy_call_start, dlvy_date) 
                          values(${car_num},   "호출중",    '${start_point}', '${end_point}', '${sender_id}', '${receiver_id}',   curtime(),    curdate())`;

        // rc카 테이블 상태를 배달대기 -> 호출중 으로 변경
        connection.query(`update car set car_status = "호출중" where car_num=${car_num}`, (err, rows, fields) => {
            if(err) console.log(err);
            else console.log('호출중 변경 Successfully');
        });
    }else { // 바로 사용 x, 대기 있을 때
        // 배달상태(호출중), 출발지, 목적지, 센더id, 리시버id, 대기순번, 대기 시작 시간, 작업 날짜
        sql = `insert into dlvy(dlvy_status, dlvy_start_point,  dlvy_end_point,   dlvy_sender,  dlvy_receiver, dlvy_wait_start, dlvy_date) 
                         values("대기중",    '${start_point}',  '${end_point}', '${sender_id}', '${receiver_id}', curtime(),    curdate())`;
    }
    // db 작업 저장하기
    connection.query(sql, (err, rows, fields) => {
        if(err) console.log("err : " + err); // err 표시
        else {
            console.log(rows.insertId); // insert 성공 후 테이블에 새로 저장 된 데이터 pk 값 가져오기
            insert_dlvy_num = rows.insertId;

            console.log('insert_dlvy_num: ' +insert_dlvy_num);

            // fcm 메시지 보내기, waiting_num이 0이면 대기 x, 아니면 대기순번 포함 메시지 전달
            var fcm_message = {
                notification: { // 알림 
                    title: "배달 수락",
                    body: "배달을 확인해 주세요",
                },
                data: { // 데이터
                    waiting_num: ''+waiting_num, 
                    car_num: ''+car_num,
                    dlvy_num: ''+insert_dlvy_num,
                    sender_name: ''+sender_name,
                },
                token: receiver_token
            };
        
            admin.messaging().send(fcm_message)
                .then((res) => {
                    console.log('Successfully sent message: ', res);
                })
                .catch((err) => {
                    console.log('Error sending message: ', err);
                });
        }


    })

};

// RC카 출발, 도착, 완료 알림 작업
function notification_message(user, title_msg, body_msg) {
    // fcm 메시지 보내기
    var fcm_message = {
        notification: { // 알림 
            title: title_msg,
            body: body_msg,
        },
        data: { // 데이터
            // waiting_num: ''+waiting_num,
            // car_num: ''+car_num,
            // dlvy_num: ''+insert_dlvy_num,
        },
        token: user
    };

    admin.messaging().send(fcm_message)
        .then((res) => {
            console.log('Successfully sent message: ', res);
        })
        .catch((err) => {
            console.log('Error sending message: ', err);
        });
}

http.listen(3000, () => {
    console.log('server listening on port 3000');
});

