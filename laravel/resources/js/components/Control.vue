<template>
  <div class = "control_car">
    <div class="left_container">
      <div id="rc_status">
        <h6>실시간 운행 상태</h6>
        <ul class="status_list">
          <li class = "status_list_item">
            <b-badge style="padding:10px;">{{entire_rc}}</b-badge><br>
            <strong>전체</strong>  
          </li>
          <li class = "status_list_item">
            <b-badge style="padding:10px;">{{proceeding_rc}}</b-badge><br>
            <strong>운행 중</strong> 
          </li>
          <li class = "status_list_item">
            <b-badge style="padding:10px;">{{waiting_rc}}</b-badge><br>
            <strong>운행대기</strong> 
          </li>
          <li class = "status_list_item">
            <b-badge style="padding:10px;">{{error_rc}}</b-badge><br>
            <strong>오류</strong>
          </li>
          <li class = "status_list_item">
            <b-badge style="padding:10px;">{{operation_rate}}%</b-badge><br>
            <strong>가동률</strong>
          </li>
        </ul>
      </div>
      <div id="dlvy_status">
        <h6>실시간 배달 현황</h6>
        <div id = "entire_call">
          <DoughnutChart id="doughnut_chart"
            :percent=persent
            :visibleValue="true"
            :entire_call=entire_call 
            :call_avg_month_ago=call_avg_month_ago
            :width="150"
            :height="150" />
          <div id="dlvy_status_summary">
            <span><strong>전체 콜 수</strong></span><br>
            <p>{{entire_call}}</p>
            <span><strong>완료 건 수</strong></span><br>
            <p>{{complete_call}}</p>
            <span><strong>지난달 <br> 하루평균 콜 수</strong></span><br>
            <p>{{call_avg_month_ago}}</p>
          </div>
        </div>
      </div>
      <div id="cancel_waiting">
        <h6>실시간 대기 취소 현황</h6>
        <div id = "cancel_waiting_summary">
          <strong>총 대기 수</strong><span>{{entire_waiting}} 건</span><br>
          <strong>현재 대기 중</strong><span>{{present_waiting}} 건</span><br>
          <strong>대기 취소 건 수</strong><span>{{canceled_waiting}} 건</span><br>
          <strong>대기 취소율</strong><span>{{cancaled_waiting_rate}}%</span>
        </div>
      </div>
      <div id="rank_bldg">
        <h6>지난 주 호출 건물 순위</h6>
        <div id="rank_bldg_summary">
          <strong>1위</strong><span>{{rank_bldg[0]}}</span><br>
          <strong>2위</strong><span>{{rank_bldg[1]}}</span><br>
          <strong>3위</strong><span>{{rank_bldg[2]}}</span>
        </div>
      </div>
      <div id="avg_waiting_time">
        <h6>실시간 평균 대기 시간</h6>
        <div id="avg_waiting_time_summary">
          <strong>{{avg_waiting_time}}분/ {{avg_waiting_time_month_ago}}분</strong><br>
          <small>(당일 / 지난 달 하루 평균)</small>
        </div>
      </div>
    </div>

    <div class="right_map">
      <div id="map">
      </div>
    </div>

    <div class="right_dlvy_info">
      <div id="dlvy_info">
        <p>운행 정보</p>
          <!-- <b-table stacked small bordered :items="items" style="width:30%;text-align:center"></b-table> -->
        <table border id="dlvy_info_table">
          <tr>
            <th>RC카 이름</th>
            <td>{{rc_name}}</td>
          </tr>
          <tr>
            <th>RC카 상태</th>
            <td>{{rc_status}}</td>
          </tr>
          <tr>
            <th>오류 내역</th>
            <td>{{rc_error_info}}</td>
          </tr>
        </table>
        <div id="test">
          <b-card
            header="header"
            header-tag="header"
            style="max-width: 10rem;"
            class="mb-2"/>
          <b-card
            header="header"
            header-tag="header"
            style="max-width: 10rem;"
            class="mb-2"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import DoughnutChart from './DoughnutChart.vue'
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
        present_waiting : 0,            //현재 대기 중 건 수
        canceled_waiting : 0,           //대기 취소 건 수
        cancaled_waiting_rate : 0,      //대기 취소율
        rank_bldg : [],                 //지난 주 호출 건물 순위 배열
        avg_waiting_time : 0,           //실시간 평균 대기 시간
        avg_waiting_time_month_ago : 0,  //저번달 하루 평균 대기 시간
        rc_name : '',                   //운행 정보에 나올 RC 이름
        rc_status : '',                 //운행 정보에 나올 RC 상태
        rc_error_info : ''              //운행 정보에 나올 오류 내역
    }
  },
  mounted(){
    this.container = document.getElementById("map");
    this.mapOptions = {
      center: new kakao.maps.LatLng(this.map_x, this.map_y),
      level: 3, //지도의 레벨(확대, 축소 정도)
      mapTypeId : kakao.maps.MapTypeId.ROADMAP // 지도종류
    };
    this.map = new kakao.maps.Map(this.container, this.mapOptions);

    this.marker = new kakao.maps.Marker({ // 디비에서 현재 등록되어 있는 RC카, 정류장 가져와 표시
      position: new kakao.maps.LatLng(35.8962, 128.6220), // 마커의 좌표35.895243, 128.623593
      map: this.map // 마커를 표시할 지도 객체
    });
    this.marker.setMap(this.map);

    //임의로 한거임. 나중에 aixos 안에 구현.
    this.rank_bldg = ['본관', '청문관', '도서관']

    // this.items[0].rc_name = 'RC1';       //디비에서 받아오는 값으로 해야됨.
    // this.items[0].rc_status = '배달 중';
    // this.items[0].error_rc = '';
    
    Axios.get('http://1006900a.ngrok.io/api/dlvy/control')
    .then((response) => {
      this.proceeding_rc = response.data.proceeding_rc;
      this.waiting_rc = response.data.waiting_rc;
      this.error_rc = response.data.error_rc;
      this.entire_rc = this.proceeding_rc + this.waiting_rc;
      this.operation_rate = Math.floor((this.proceeding_rc / this.entire_rc) * 100);

      this.entire_call = response.data.entire_call;
      this.complete_call = response.data.complete_call;
      this.persent = Math.floor((this.complete_call / this.entire_call) * 100);

      this.entire_waiting = response.data.entire_waiting + response.data.canceled_waiting;
      this.canceled_waiting = response.data.canceled_waiting;
      this.cancaled_waiting_rate = Math.floor((this.canceled_waiting / this.entire_waiting) * 100);

      this.avg_waiting_time = Math.floor(response.data.avg_waiting_time);
      console.log(response.data)
    })
    .catch(error => {
      console.log(error)
    })
  }
}
</script>

<style scoped>
#test{
  float : right;
}

.left_container{
  float : left;
  width : 35%;
  height : 600px;
  margin-left : 50px;
  margin-top : 50px;
  /* background-color : #E6E6E6; */
}

.right_map{
  float : right;
  width : 55%;
  height : 400px;
  margin-right : 50px;
  margin-top : 50px;
  background-color : #BDBDBD;
}

#map{
  width : 100%;
  height : 400px;
}

.right_dlvy_info{
  float : right;
  width : 55%;
  height : 170px;
  margin-right : 50px;
  margin-top : 30px;
  background-color : #BDBDBD;
  float : left;
}

#dlvy_info_table{
  text-align : center;
  width : 20%;
  margin-left : 5%;
  display : inline-block;
}

#dlvy_info_table th {
  width : 7%;
}

#dlvy_info_table td{
  width : 7%;
}

#dlvy_info{
  width : 100%;
  height : 170px;
}

#rc_status{
  width : 90%;
  height : 110px;
  margin : 0 auto;
  margin-top : 2%;
  background-color : #BDBDBD;
}

.status_list{
  padding : 0;
  width : 100%;
}

.status_list_item{
  list-style : none;
  float : left;
  padding : 0;
  width : 20%;
  text-align : center;
}

#dlvy_status{
  width : 50%;
  height : 260px;
  float : left;
  margin-left: 5%;
  margin-top : 2%;
  background-color : #BDBDBD;
}

#doughnut_chart{
  width : 150px;
  height : 150px;
  float : left;
  margin-top : 10%;
}

#dlvy_status_summary{
  width : 40%;
  float: right;
  text-align: center;
}

#cancel_waiting{
  width : 35%;
  height : 260px;
  float : right;
  margin-right : 5%;
  margin-top : 2%;
  background-color : #BDBDBD;
}

#cancel_waiting_summary > span{
  float : right;
}

#rank_bldg{
  width : 50%;
  height : 160px;
  float : left;
  margin-left: 5%;
  margin-top : 2%;
  background-color : #BDBDBD;
}

#rank_bldg_summary{
  width : 100%;
  text-align: center;
}

#avg_waiting_time{
  width : 35%;
  height : 160px;
  float : right;
  margin-right : 5%;
  margin-top : 2%;
  background-color : #BDBDBD;
}

#avg_waiting_time_summary{
  margin-top : 20%;
  text-align : center;
}

#avg_waiting_time_summary strong{
  font-size: 3vh;
}
</style>
