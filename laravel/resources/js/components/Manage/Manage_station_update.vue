<template>
  <div class="page-container">
    <div id="map" @click="map_click()"></div>
    <div id="manager">
      <div v-if="stage == 1">지도에서 수정/삭제할 정류장을 클릭해 주세요.</div>
      <div v-if="stage == 2">
        <b-form>
          <p>정류장 이름 : {{ station_name }}</p>
          <b-form-input
            v-model="station_name"
            :placeholder="station_name"
          ></b-form-input>
          <div style="margin: 5px;">
            <div>
              <span style="font-size: 10px">위도 : {{ lat }}</span>
            </div>
            <div>
              <span style="font-size: 10px">경도 : {{ lon }}</span>
            </div>
          </div>
          <b-button-group>
            <b-button type="submit" variant="primary" @click="stn_update()"
              >수정하기</b-button
            >
            <b-button variant="danger" type="submit" @click="stn_delete()"
              >삭제하기</b-button
            >
            <b-button type="submit" @click="cancel()">취소하기</b-button>
          </b-button-group>
        </b-form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    Axios.get('/api/dlvy/management/station')
    .then((res) => {
      this.data = res.data.station
      this.initMap();
    }) 
  },
  data() {
    return {
      map_stage: 1, // 맵 한번만 생성
      stage: 1, // 단계별 보여지는 화면
      old_station_name: "",
      station_name: "", // 정류장 이름
      station_num: "", // 선택한 정류장
      lat: "", // 위도
      lon: "", // 경도
      data: "", // 정류장 데이터
      markers: [], // 마커 표시
      map: "", // 맵 저장
      click: 0,
    };
  },
  methods: {
    makeOverListener(map, marker, infowindow) {
      return function() {
        infowindow.open(map, marker);
      };
    },

    // 인포윈도우를 닫는 클로저를 만드는 함수입니다
    makeOutListener(infowindow) {
      return function() {
        infowindow.close();
      };
    },
    initMap() {
      // 단 한번만 실행으로 모든 걸 처리
      if (this.map_stage == 1) {
        var container = document.getElementById("map");
        var options = {
          center: new kakao.maps.LatLng(35.896309, 128.621917), // 지도 중심 좌표
          level: 2, // 지도 확대
          draggable: false, // 지도 이동 막기
        };
        var map = new kakao.maps.Map(container, options);
        this.map = map;
        this.map_stage = 2;
      }

      // 여러 개 마커 생성하기
      if (this.stage == 1) {
        for (let i = 0; i < this.data.length; i++) {
          // 마커를 생성합니다
          const marker = new kakao.maps.Marker({
            position: new kakao.maps.LatLng(this.data[i].station_lat, this.data[i].station_lon), // 마커를 표시할 위치
          });
          // 인포 윈도우 생성
          var infowindow = new kakao.maps.InfoWindow({
            content:
              "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
              (this.data[i].station_name) +
              "</div>",
            });

          // 마우스 오버 이벤트
          kakao.maps.event.addListener(
            marker,
            "mouseover",
            this.makeOverListener(map, marker, infowindow)
          );
          kakao.maps.event.addListener(
            marker,
            "mouseout",
            this.makeOutListener(infowindow)
          );
          marker.setMap(this.map);
          this.markers.push(marker);

          kakao.maps.event.addListener(marker, "click", () => {
            this.stage = 2; // 정류장 클릭 후 수정 페이지 이동
            if (this.click == 0) {
              this.click += 1;
              this.old_station_name = this.data[i].station_name;
              this.station_name = this.data[i].station_name;
              this.station_num = i
              this.lat = this.data[i].station_lat; // 위도
              this.lon = this.data[i].station_lon; // 경도
              marker.setDraggable(true);
            }
          });

          kakao.maps.event.addListener(marker, "dragend", () => {
            this.lat = marker.getPosition().Ha;
            this.lon = marker.getPosition().Ga;
            console.log(this.lat, this.lng);
          });
        }
      }
    },
    stn_delete() {
      Axios.delete(`/api/dlvy/management/station/${this.old_station_name}`)
      .then(res => {
        this.markers[this.station_num].setMap(null);
        this.initialize();
        this.initMap();
      })
      .catch(err => {
        console.log(err)
      })
    },
    stn_update() {
      Axios.post("/api/dlvy/management/station", {
        id : 2,
        old_station_name : this.old_station_name,
        station_name : this.station_name,
        station_lat : this.lat,
        station_lon : this.lon
      })
      .then(res => {})
      .catch(err => {
        console.log(err)
      })
    },
    cancel() {
      Axios.post("/api/dlvy/management/station", {
        id : 3
      })
    },
    initialize() {
      (this.map_stage = 2), // 맵 한번만 생성
        (this.stage = 1), // 단계별 보여지는 화면
        (this.station_name = ""), // 정류장 이름
        (this.lat = ""), // 위도
        (this.lng = ""), // 경도
        (this.data = ""), // 정류장 데이터
        (this.markers = []), // 마커 표시
        (this.old_station_name = "");
      this.click = 0;
      this.map = "";
    },
    map_click() {
      if (this.stage == 2) this.initMap();
    },
  },
};
</script>

<style scoped>
#map {
  height: 600px;
}

#manager {
  position: absolute;
  top: 0;
  right: 0;
  z-index: 10;
  background-color: white;
  border: 1px solid #18a2b8;
  padding: 10px;
  text-align: center;
}

.page-container {
  position: relative;
}
</style>
