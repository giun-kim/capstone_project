<template>
  <div>
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
          <strong>전체 콜 수</strong><br>
          <span style="font-size:small;">(당일 / 지난 달 평균)</span>
        </div>
        <div id = "completed_call">
          <strong>완료 건 수</strong><br>
        </div>
      </div>
      <div id="cancel_waiting">
        <h6>실시간 대기 취소 현황</h6>
      </div>
      <div id="rank_bldg">
        <h6>지난 주 호출 건물 순위</h6>
      </div>
      <div id="avg_waiting_time">
        <h6>실시간 평균 대기 시간</h6>
      </div>
    </div>

    <div class="right_map">
      <div id="map">
      </div>
    </div>

    <div class="right_dlvy_info">
      <div id="dlvy_info">
        <p>
          운행 정보
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data(){
    return{
        container : '',
        mapOptions : '',
        map : '',
        map_x : 35.8962,
        map_y : 128.6220,
        entire_rc : 1,
        proceeding_rc : 2,
        waiting_rc : 3,
        error_rc : 4,
        operation_rate : 66
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

    this.marker = new kakao.maps.Marker({
      position: new kakao.maps.LatLng(35.8962, 128.6220), // 마커의 좌표35.895243, 128.623593
      map: this.map // 마커를 표시할 지도 객체
    });
    this.marker.setMap(this.map);

    // Axios.get('/api/dlvy')
    // .then((response) => {
    //   this.proceeding_rc = response.data.proceeding_rc;
    //   this.waiting_rc = response.data.waiting_rc;
    //   this.error_rc = response.data.error_rc;
    //   this.entire_rc = this.proceeding_rc + this.waiting_rc;
    //   this.operation_rate = (this.proceeding_rc / this.entire_rc) * 100;
    // })
    // .catch(error => {
    //   console.log(error)
    // })
  }
}
</script>

<style scoped>
.left_container{
  float : left;
  width : 35%;
  height : 600px;
  margin-left : 50px;
  margin-top : 50px;
  background-color : red;
}

.right_map{
  float : right;
  width : 55%;
  height : 400px;
  margin-right : 50px;
  margin-top : 50px;
  background-color : black;
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
  background-color : greenyellow;
}

#rc_status{
  width : 90%;
  height : 110px;
  margin : 0 auto;
  margin-top : 2%;
  background-color : yellow;
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
  background-color : yellow;
}

#cancel_waiting{
  width : 35%;
  height : 260px;
  float : right;
  margin-right : 5%;
  margin-top : 2%;
  background-color : yellow;
}

#rank_bldg{
  width : 50%;
  height : 160px;
  float : left;
  margin-left: 5%;
  margin-top : 2%;
  background-color : yellow;
}

#avg_waiting_time{
  width : 35%;
  height : 160px;
  float : right;
  margin-right : 5%;
  margin-top : 2%;
  background-color : yellow;
}

</style>
