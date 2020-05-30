<template>
  <div class="page-container">
    <div id="map"></div>
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
            <b-button type="button" variant="info" @click="stn_update(old_station_name)">수정하기</b-button>
            <b-button variant="danger" type="button" @click="stn_delete(old_station_name)">삭제하기</b-button>
            <b-button type="button" @click="initialize()">취소하기</b-button>
          </b-button-group>
        </b-form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    // 정류장 데이터 불러오기
    Axios.get('/api/dlvy/management/station')
    .then(res => {
      this.data = res.data.station_all
      this.initMap()
    }) 
  },
  data() {
    return {
      map_stage: 1, // 카카오맵 생성 제한 map_stage = 1 : 맵 생성, map_stage = 2 : 맵 생성 안함
      stage: 1, // 단계별 보여지는 화면 stage = 1 : 정류장 클릭, stage = 2 : 수정 데이터 입력
      old_station_name: "", // 수정 전 정류장 이름
      station_name: "", // 수정 후 정류장 이름
      lat: "", // 위도
      lon: "", // 경도
      data: "", // 정류장 데이터
      markers: [], // 마커 배열
      map: "", // 맵
    }
  },
  methods: {
    // 인포윈도우 여는 함수
    makeOverListener(map, marker, infowindow) {
      return function() {
        infowindow.open(map, marker)
      }
    },

    // 인포윈도우 닫는 함수
    makeOutListener(infowindow) {
      return function() {
        infowindow.close()
      }
    },

    // 맵 불러오기 및 카카오 이벤트
    initMap() {
      // 카카오맵 불러오기
      if(this.map_stage == 1) {
        var container = document.getElementById("map");
        var options = {
          center: new kakao.maps.LatLng(35.896309, 128.621917), // 지도 중심 좌표
          level: 2, // 지도 확대
          draggable: false, // 지도 이동 막기
        };
        this.map = new kakao.maps.Map(container, options) // 맵 설정
        this.map_stage = 2
      }

      // 모든 마커 생성
      if (this.stage == 1) {
        for (let i = 0, len = this.data.length; i < len; i++) {
          // 마커를 생성합니다
          const marker = new kakao.maps.Marker({
            position: new kakao.maps.LatLng(this.data[i].station_lat, this.data[i].station_lon), // 마커를 표시할 위치
          })

          // 인포 윈도우 생성
          var infowindow = new kakao.maps.InfoWindow({
            content:
              "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
              (this.data[i].station_name) +
              "</div>",
            })

          // 인포윈도우 여는 이벤트
          kakao.maps.event.addListener(
            marker,
            "mouseover",
            this.makeOverListener(this.map, marker, infowindow)
          )

          // 인포윈도우 닫는 이벤트
          kakao.maps.event.addListener(
            marker,
            "mouseout",
            this.makeOutListener(infowindow)
          )

          // 마커 표시 및 마커 배열 푸시
          marker.setMap(this.map)
          this.markers.push(marker)

          // 마커 클릭 이벤트
          kakao.maps.event.addListener(this.markers[i], "click", () => {
            this.stage = 2 // 마커 클릭 후 수정 페이지 이동
            this.markers[i].setDraggable(true)
            this.old_station_name = this.data[i].station_name
            this.station_name = this.data[i].station_name
            this.lat = this.data[i].station_lat
            this.lon = this.data[i].station_lon
          })

          // 마커 드래그 끝 이벤트
          kakao.maps.event.addListener(this.markers[i], "dragend", () => {
            this.lat = this.markers[i].getPosition().Ha
            this.lon = this.markers[i].getPosition().Ga
          })
        }
      }
    },

    // 정류장 데이터 삭제 함수
    stn_delete(station_name) {
      // 정류장 데이터 삭제 데이터 서버로 보내기
      Axios.delete(`/api/dlvy/management/station/${station_name}`)
      .then(res => {
        this.initialize(1) // 마커 제거
        this.data = res.data.station_all
        this.initialize(2) // 데이터 초기화
      })
      .catch(err => {
        console.log(err)
      })
    },

    // 정류장 데이터 수정 함수
    stn_update(station_name) {
      // 정류장 데이터 수정 데이터 서버로 보내기
      Axios.patch(`/api/dlvy/management/station/${station_name}`, {
        station_name : this.station_name,
        station_lat : this.lat,
        station_lon : this.lon
      })
      .then(res => {
        this.initialize(1) // 마커 제거
        this.data = res.data.station_all
        this.initialize(2) // 데이터 초기화
      })
      .catch(err => {
        console.log(err)
      })
    },

    // 데이터 초기화
    initialize(num) {
      // 마커 삭제 및 초기화 num = 1 : 마커 제거 , num = 2 : 데이터 초기화
      if(num == 1) {
        for (let i = 0, len = this.data.length; i < len; i++) {
          this.markers[i].setMap(null)
        }
      } else {
        this.stage = 1
        this.station_name = ""
        this.old_station_name = ""
        this.lat = ""
        this.lon = ""
        this.markers = []

        this.initMap()
      }
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
