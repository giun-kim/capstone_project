<template>

  <div class="container">
    <div class="left_container">
        <div class=rc_status>
          <div id="rc_status_header">실시간 운행상태</div>
          <div class="status_list">
            <div class = "status_list_item1">
              <span>전체</span>
              <strong><div>{{entire_rc}}</div></strong>
            </div>
            <div class = "status_list_item2">
              <span>운행 중</span>
              <strong><div>{{proceeding_rc}}</div></strong>
            </div>
            <div class = "status_list_item3">
              <span>운행대기</span>
              <strong><div>{{waiting_rc}}</div></strong>
            </div>
            <div class = "status_list_item4">
              <span>오류</span>
              <strong><div>{{error_rc}}</div></strong>
            </div>
            <div class = "status_list_item5">
              <span>가동률</span>
              <strong><div style="color:#1ABBA0;">{{operation_rate}}%</div></strong>
            </div>
          </div>
        </div>
        <div class="dlvy_status">
          <div id="dlvy_status_header">실시간 배달 현황</div>
          <DoughnutChart id="doughnut_chart"
              :percent=persent
              :visibleValue="true"
              :entire_call=entire_call 
              :call_avg_month_ago=call_avg_month_ago
              :width="150"
              :height="130" />
            <div id="dlvy_status_summary">
              <table class= "dlvy_status_list">
              <tbody>
                <tr>
                  <td>전체 콜 수</td>
                  <td>{{entire_call}}</td>
                </tr>
                <tr>
                  <td>완료 건 수</td>
                  <td>{{complete_call}}</td>
                </tr>
                <tr>
                  <td>지난 달 하루 평균 콜 수</td>
                  <td>{{call_avg_month_ago}}</td>
                </tr>
              </tbody>
              </table>
            </div>
        </div>
        <div class="cancel_waiting">
            <div id = "cancel_waiting_header">실시간 대기 취소 현황</div>
            <div id = "cancel_waiting_summary">
              <table class="info_item1_list">
              <tbody>
                <tr>
                  <strong><td>총 대기 수</td></strong>
                  <td>{{entire_waiting}} 건</td>
                </tr>
                <tr>
                  <strong><td>현재 대기 중</td></strong>
                  <td>{{now_waiting}} 건</td>
                </tr>
                <tr>
                  <strong><td>대기 취소 건 수</td></strong>
                  <td>{{canceled_waiting}} 건</td>
                </tr>
                <tr>
                 <strong><td>대기 취소율</td></strong>
                  <td>{{cancaled_waiting_rate}}%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="rank_bldg">
          <div id="rank_bldg_header">지난 주 호출 건물 순위</div>
          <div id="rank_bldg_summary">
                <strong>1위 </strong><span>{{rank_bldg[0]}}</span><br>
                <strong>2위 </strong><span>{{rank_bldg[1]}}</span><br>
                <strong>3위 </strong><span>{{rank_bldg[2]}}</span>
          </div>
        </div>
        <div class="avg_waiting_time">
            <div id="avg_waiting_time_header">실시간 평균 대기 시간</div>
            <div id="avg_waiting_time_summary">
            <strong>{{avg_waiting_time}}분/ {{avg_waiting_time_month_ago}}분</strong><br>
            <small>(당일 / 지난 달 하루 평균)</small>
          </div>
        </div>
      </div>
      <div class="right_container">
        <div class="right_map">
          <div id="map" style="width:100%;height:100%;">
          </div>
        </div>
        <div class="right_dlvy_info">
          <div id="right_dlvy_info_header">운행 정보</div>
          <div id="info_item1">
            <table class="info_item1_list">
              <tbody>
                <tr>
                  <td>RC이름</td>
                  <td>{{rc_name}}</td>
                </tr>
                <tr>
                  <td>RC상태</td>
                  <td>{{rc_status}}</td>
                </tr>
                <tr>
                  <td>오류내역</td>
                  <td>{{rc_error_info}}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div id="info_item2">
            <table class="info_item2_list">
              <tbody>
                <tr>
                  <td>출발 정류장 :</td>
                  <td>{{start_point}}</td>
                </tr>
                <tr>
                  <td>이름 :</td>
                  <td>{{sender_name}}</td>
                </tr>
                <tr>
                  <td>전화번호 :</td>
                  <td>{{sender_phone}}</td>
                </tr>
                 <tr>
                  <td>출발 시간 :</td>
                  <td>{{start_time}}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div id="info_item3">
            <table class="info_item3_list">
              <tbody>
                <tr>
                  <td>도착 정류장 :</td>
                  <td>{{end_point}}</td>
                </tr>
                <tr>
                  <td>이름 :</td>
                  <td>{{receiver_name}}</td>
                </tr>
                <tr>
                  <td>전화번호 :</td>
                  <td>{{receiver_phone}}</td>
                </tr>
                 <tr>
                  <td>예상 도착 시간 :</td>
                  <td>{{end_time}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
</div>
</template>


<script>
import DoughnutChart from './DoughnutChart.vue'
import io from 'socket.io-client';

export default {
  components: {
    DoughnutChart
  },
  data(){
    return{
        container : '',                 //map 담기 위한 div
        mapOptions : '',                //map 옵션 객체
        map : '',                       //map 객체
        map_x : 35.8962,                //지도의 중심점 위도
        map_y : 128.6220,               //지도의 중심점 경도
        entire_rc : 0,                  //전체 RC카
        proceeding_rc : 0,              //운행 중 RC카
        waiting_rc : 0,                 //운행 대기 RC카
        error_rc : 0,                   //오류 RC카
        operation_rate : 0,             //RC카 가동률
        entire_call : 0,                //당일 총 콜 수
        call_avg_month_ago : 0,         //저번 달 하루 평균 콜수
        complete_call : 0,              //당일 완료 건 수
        persent : 0,                    //원그래프에 나올 퍼센트(완료율 / 전체 콜 수)
        entire_waiting : 0,             //전체 대기 수
        complete_waiting : 0,            //대기 완료 건 수
        now_waiting : 0,                //현재 대기 중 건수
        canceled_waiting : 0,           //대기 취소 건 수
        cancaled_waiting_rate : 0,      //대기 취소율
        rank_bldg : [],                 //지난 주 호출 건물 순위 배열
        avg_waiting_time : 0,           //실시간 평균 대기 시간
        avg_waiting_time_month_ago : 0,  //저번달 하루 평균 대기 시간
        rc_list : [],                   //지도에 나올 RC카 리스트
        station_list : [],              //지도에 나올 정류장 리스트
        rc_name : '',                   //운행 정보에 나올 RC 이름
        rc_status : '',                 //운행 정보에 나올 RC 상태
        rc_error_info : '',              //운행 정보에 나올 오류 내역
        start_point : '',               //출발지 이름
        start_point_lat : '',           //출발지 위도
        start_point_lon : '',           //출발지 경도
        sender_name : '',               //sender 이름
        sender_phone : '',              //sender 전화번호
        start_time : '',                //출발 시간
        end_point : '',                 //목적지 이름
        end_point_lat : '',             //목적지 위도
        end_point_lon : '',             //목적지 경도
        receiver_name : '',             //receiver 이름
        receiver_phone : '',            //receiver 전화번호
        end_time : '',                   //예상완료시간
        socket : io.connect('https://d5bc27ae.ngrok.io', {
          port : 3000
        }),
        marker : []
    }
  },
  mounted(){
    this.socket.on("call_count", (data) => {
      this.entire_call = data[0].count;
      this.persent = Math.floor((this.complete_call / this.entire_call) * 100);
    });
    this.socket.on("complete_dlvy_count", (data) => {
      // console.log(data);
      this.complete_call = data[0].count;
      this.persent = Math.floor((this.complete_call / this.entire_call) * 100);
    });
    this.socket.on("rc_status", (data) => {           //배달 시작할 떄 바뀌는건 배달 수락이 있은 후여야 바뀌기 때문에 그 기능 완성 후 변하는거 확인 가능.
      // console.log(data);
      this.proceeding_rc = data.call + data.dlvy;
      this.waiting_rc = data.wait;
      this.error_rc = data.err;
      this.entire_rc = this.proceeding_rc + this.waiting_rc + this.error_rc;
      this.operation_rate = Math.floor((this.proceeding_rc / this.entire_rc) * 100);
    });

    this.socket.on("wait_data", (data) => {           //배달 시작할 떄 바뀌는건 배달 수락이 있은 후여야 바뀌기 때문에 그 기능 완성 후 변하는거 확인 가능.
      console.log(data);
      this.now_waiting = data.wait_now;
      this.complete_waiting = data.wait_complete;
      this.canceled_waiting = data.wait_cancel;
      this.entire_waiting = this.now_waiting + this.complete_waiting + this.canceled_waiting;
      this.cancaled_waiting_rate = Math.floor((this.canceled_waiting / this.entire_waiting) * 100);
    });
    

    this.socket.on("car_data", (data) => {
      // console.log("들어옴")
      var test = 0;
      for(var i = 0 ; i < this.marker.length; i++){
        if(this.marker[i].getTitle() == data.car_num){
          this.marker[i].setPosition(new kakao.maps.LatLng(data.car_lat, data.car_lon));
        } 
      }
    });

    this.container = document.getElementById("map");
    this.mapOptions = {
      center: new kakao.maps.LatLng(this.map_x, this.map_y),
      level: 3, //지도의 레벨(확대, 축소 정도)
      mapTypeId : kakao.maps.MapTypeId.ROADMAP // 지도종류
    };

    this.map = new kakao.maps.Map(this.container, this.mapOptions);
    var stationMarker = new kakao.maps.MarkerImage('/image/station.png', new kakao.maps.Size(20,25))
    
    Axios.get('/api/dlvy/control')    //첫 페이지 로딩
    .then((response) => {
      this.proceeding_rc = response.data.proceeding_rc;
      this.waiting_rc = response.data.waiting_rc;
      this.error_rc = response.data.error_rc;
      this.entire_rc = this.proceeding_rc + this.waiting_rc + this.error_rc;
      this.operation_rate = Math.floor((this.proceeding_rc / this.entire_rc) * 100);
      this.entire_call = response.data.entire_call;
      this.complete_call = response.data.complete_call;
      this.persent = Math.floor((this.complete_call / this.entire_call) * 100);
      this.call_avg_month_ago = Math.floor(response.data.call_avg_month_ago);

      this.rank_bldg = response.data.build_rank;

      this.complete_waiting = response.data.complete_waiting;
      this.now_waiting = response.data.now_waiting;
      this.canceled_waiting = response.data.canceled_waiting;
      
      this.entire_waiting = this.complete_waiting + this.now_waiting + this.canceled_waiting;
      this.cancaled_waiting_rate = Math.floor((this.canceled_waiting / this.entire_waiting) * 100);

      this.avg_waiting_time = Math.floor(response.data.avg_waiting_time);
      this.avg_waiting_time_month_ago = Math.floor(response.data.avg_waiting_time_month_ago)

      this.rc_list = response.data.map_car_status;
      this.station_list = response.data.station_info;
      console.log(response.data);

      for(var i = 0; i < this.rc_list.length; i++){ //마커 등록 and 마커에 이벤트 달기
        const marker = new kakao.maps.Marker({
          map: this.map, // 마커를 표시할 지도
          position: new kakao.maps.LatLng(this.rc_list[i].car_lat, this.rc_list[i].car_lon), // 마커의 위치
          title : this.rc_list[i].car_num
        });
        this.marker[i] = marker;
        kakao.maps.event.addListener(marker, 'click', () => {
          this.rc_name = '';
          this.rc_status = '';
          this.rc_error_info = '';
          this.start_point = '';
          this.end_point = '';
          this.sender_name = '';
          this.sender_phone = '';
          this.receiver_name = '';
          this.receiver_phone = '';
          this.start_time = '';
          this.end_time = '';
          Axios.get('/api/dlvy/control/show/' + marker.getTitle())
          .then((response) => {
            console.log(response)
            if(response.data.car_status == "배달대기"){
              this.rc_name = response.data.car_name;
              this.rc_status = response.data.car_status;
            }else if(response.data.car_status == "오류"){
              this.rc_name = response.data.car_name;
              this.rc_status = response.data.car_status;
              this.rc_error_info = response.data.car_error;
            }else{
              this.rc_name = response.data.car.car_name;
              this.rc_status = response.data.car.car_status;
              this.sender_name = response.data.sender_info.user_name;
              this.sender_phone = response.data.sender_info.user_phone;
              this.receiver_name = response.data.receiver_info.user_name;
              this.receiver_phone = response.data.receiver_info.user_phone;
              this.start_point = response.data.dlvy_start_point.station_name;
              this.start_point_lat = response.data.dlvy_start_point.station_lat;
              this.start_point_lon = response.data.dlvy_start_point.station_lon;
              this.end_point = response.data.dlvy_end_point.station_name;
              this.end_point_lat = response.data.dlvy_end_point.station_lat;
              this.end_point_lon = response.data.dlvy_end_point.station_lon;
              if(this.rc_status == "호출중"){
                this.start_time = '호출중'
                this.end_time = '호출중'
              }else if(this.rc_status == "배달중"){
                var split_start_time = response.data.dlvy_status.dlvy_start.split(":");
                var split_start_time = [split_start_time[0], split_start_time[1]];
                this.start_time = split_start_time.join(':');
                var rc_lat = response.data.car.car_lat;
                var rc_lon = response.data.car.car_lon;
                var distance = this.predict_time(rc_lat, rc_lon, this.end_point_lat, this.end_point_lon);
                var time = Math.ceil(distance / (1/12)) //1분
                if(Number(split_start_time[1]) + time >= 60){
                  split_start_time[0] = Number(split_start_time[0]) + 1
                  this.end_time = split_start_time.join(':');
                }else{
                  split_start_time[1] = Number(split_start_time[1]) + time;
                  this.end_time = split_start_time.join(':');
                }
              }
            }
          })
          .catch(err => {
            console.log(err);
          })
        });
      }
      for(var i = 0; i < this.station_list.length; i++){    //정류장 등록
        const marker = new kakao.maps.Marker({
          map: this.map, // 마커를 표시할 지도
          position: new kakao.maps.LatLng(this.station_list[i].station_lat, this.station_list[i].station_lon), // 마커의 위치
          title : this.station_list[i].station_name,
          image : stationMarker
        });
      }
    })
    .catch(error => {
      console.log(error)
    })
  },
  methods : {
    predict_time(rc_lat, rc_lon, start_lat, start_lon){
      var startLat = this.degreesToRadians(rc_lat);
      var startLon = this.degreesToRadians(rc_lon);
      var endLat = this.degreesToRadians(start_lat);
      var endLon = this.degreesToRadians(start_lon);
      var Radius = 6371; //지구의 반경(km

      var distance = Math.acos(Math.sin(startLat) * Math.sin(endLat) + 
                    Math.cos(startLat) * Math.cos(endLat) *
                    Math.cos(startLon - endLon)) * Radius;

      return distance;
    },
    degreesToRadians(degrees){
      var radians = (degrees * Math.PI)/180

      return radians;
    }
  }
}
</script>

<style scoped>
.container{
  display: flex;
  width: 100%;
  height:640px;
  max-width:1500px;
  margin-top: 20px;
  background-color: #F7F7F7;
  color:#73879C;
}
.left_container{
  display: grid;
  grid-template-columns: 50% 50%;
  grid-template-rows: 20% 55% 25%;
  width: 40%;
  height: 100%;
  text-align: center;
}

.right_container{
  display: grid;
  grid-template-columns:100%;
  grid-template-rows: 65% 35%;
  width: 60%;
  height: 100%;
  margin-left: 20px;
}

.rc_status{
  display: grid;
  grid-column-start: 1;
  grid-column-end: 3;
  grid-template-rows: 25% 75%;
  border-style: solid;
  border-width: 1px;
  border-color: #E6E9ED;
  background-color: #F7F7F7;
  height: 100%;

}
#rc_status_header{
  border-bottom:3px solid #E6E9ED;
  padding: 5px;
  margin-right: 2%;
  margin-left: 2%;
  text-align: start;
  font-size: 1em;

}
.status_list{
  display: flex;
  margin-top: 10px;
  margin-bottom: 20px;
}

.status_list_item1{
  width : 100%;
  text-align : center;
  border-right:2px solid #ADB2B5;
}

.status_list_item1 div{
font-size: 2em;
}

.status_list_item2{
  width : 100%;
  text-align : center;
  border-right:2px solid #ADB2B5;
}

.status_list_item2 div{
font-size: 2em;
}
.status_list_item3{
  width : 100%;
  text-align : center;
  border-right:2px solid #ADB2B5;
}
.status_list_item3 div{
font-size: 2em;
}

.status_list_item4{
  width : 100%;
  text-align : center;
  border-right:2px solid #ADB2B5;
}
.status_list_item4 div{
font-size: 2em;
}

.status_list_item5{
  width : 100%;
  text-align : center;

}
.status_list_item5 div{
font-size: 2em;
}
.dlvy_status{
  border-style: solid;
  border-width: 2px;
  border-color: #E6E9ED;
  margin-top: 10px;
  margin-right: 20px;
  margin-bottom: 20px;
  background-color:white;
}

.cancel_waiting{
  border-style: solid;
  border-width: 2px;
  border-color: #E6E9ED;
  margin-top: 10px;
  margin-bottom: 20px;
  background-color: white;
}
.rank_bldg{
  border-style: solid;
  border-width: 2px;
  border-color: #E6E9ED;
  background-color: white;
  margin-right: 20px;
}

.avg_waiting_time{
  border-style: solid;
  border-width: 2px;
  border-color: #E6E9ED;
  background-color: white;
}

.right_map{
  border-style: solid;
  border-width: 2px;
  border-color: #E6E9ED;
  margin-bottom:15px;
}

.right_dlvy_info{
  display: grid;
  grid-template-columns: 30% 35% 35%;
  grid-template-rows: 20% 80%;
  border-style: solid;
  border-width: 2px;
  border-color: #E6E9ED;
  background-color: white;
}

#dlvy_info_table{
  margin-top: 70px;
  margin-left: 30px;
}
#info_item1{
  margin-top: 15px;
  margin-left: 15%;
}

#info_item2{
  margin-top: 15px;

}

#info_item3{
  margin-top: 15px;

}
#dlvy_status_header{
  border-bottom:3px solid #E6E9ED;
  padding: 10px;
  margin-right: 2%;
  margin-left: 2%;
  text-align: start;
  font-size: 1em;
}
#dlvy_status_summary{
  margin-top: 10px;
  width: 100%;
  text-align: start;
  margin-left: 10%;
}

#cancel_waiting_header{
  border-bottom:3px solid #E6E9ED;
  padding: 10px;
  margin-right: 2%;
  margin-left: 2%;
  text-align: start;
  font-size: 1em;
}
#cancel_waiting_summary{
   margin-top: 50px;
   margin-left: 25px;
   font-size: 1.2em;
   text-align: left;
}
#rank_bldg_summary{
  margin-top: 10px;
  margin-left: 20px;
  font-size: 1.2em;
  text-align: left;
  
 
}
#rank_bldg_header{
  border-bottom:3px solid #E6E9ED;
  padding: 10px;
  margin-right: 2%;
  margin-left: 2%;
  text-align: start;
  font-size: 1em;
}
#avg_waiting_time_summary{
  margin-top: 20px;
  font-size: 1.3em;
}
#avg_waiting_time_header{
  border-bottom:3px solid #E6E9ED;
  padding: 10px;
  margin-right: 2%;
  margin-left: 2%;
  text-align: start;
  font-size: 1em;
}

#right_dlvy_info_header{
  grid-column-start: 1;
  grid-column-end: 4;
  border-bottom:3px solid #E6E9ED;
  padding: 10px;
  margin-right: 2%;
  margin-left: 2%;
  text-align: start;
  font-size: 1em;
}

.info_item1_list td {
  padding: 10px;
  line-height: 20px;
  border-top: 1px solid #eeeeee;
  font-size: 0.9em;
}
.info_item2_list td {
  padding: 10px;
  line-height: 20px;
  border-top: 1px solid #eeeeee;
  font-size: 0.9em;
}
.info_item3_list td {
  padding: 10px;
  line-height: 20px;
  border-top: 1px solid #eeeeee;
  font-size: 0.9em;
}

#doughnut_chart{
margin-top: 30px;
}
.dlvy_status_list td {
  padding: 10px;
  line-height: 10px;
  border-top: 1px solid #eeeeee;
  font-size: 1em;
}
</style>