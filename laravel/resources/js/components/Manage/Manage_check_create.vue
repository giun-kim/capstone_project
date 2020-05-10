<template>
  <div class="page-container">
    <div id="map" @click="map_click()"></div>
    <div id="manager">
      <div v-if="stage == 1">지도에서 원하는 위치를 클릭해 주세요.</div>
      <div v-if="stage == 2">
        <b-form>
          <div style="margin: 5px;">
            <div>
              <span style="font-size: 10px">위도 : {{ lat }}</span>
            </div>
            <div>
              <span style="font-size: 10px">경도 : {{ lon }}</span>
            </div>
          </div>
          <b-button-group>
            <b-button type="submit" variant="primary" @click="stn_create()"
              >등록하기</b-button
            >
            <b-button type="submit" @click="cancel">취소하기</b-button>
          </b-button-group>
        </b-form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    Axios.get('/api/manage')
    .then((res) => {
      this.data = res.data.station
      if (this.data[this.data.length - 1].station_name != "") {
      this.data.push({
        station_name: "",
        station_lat: "",
        station_lon: ""
      });
      } else if (this.data[this.data.length - 1].station_name == "") {
        this.data.splice(this.data.length - 1, 1);
      }
      this.initMap();
    }) 
  },
  data() {
    return {
      map_stage: 1, // 맵 한번만 생성
      stage: 1, // 단계별 보여지는 화면
      station_name: "", // 정류장 이름
      lat: "", // 위도
      lon: "", // 경도
      data: "", // 정류장 데이터
      map: "",
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
      if (this.map_stage == 1) {
        var container = document.getElementById("map");
        var options = {
          center: new kakao.maps.LatLng(35.896309, 128.621917),
          level: 2,
          draggable: false,
        };
        var map = new kakao.maps.Map(container, options);
        this.map = map;
      }

      // 여러 개 마커 생성하기
      for (let i = 0; i < this.data.length; i++) {
        // 마커를 생성합니다
        var marker = new kakao.maps.Marker({
          map: this.map,
          position: new kakao.maps.LatLng(this.data[i].station_lat, this.data[i].station_lon) ? new kakao.maps.LatLng(this.data[i].station_lat, this.data[i].station_lon) : "", // 마커를 표시할 위치
        });
        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            (this.data[i].station_name ? this.data[i].station_name : "") +
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
      }

      // 마커 표시
      kakao.maps.event.addListener(map, "click", (mouseEvent) => {
        // 클릭한 위도, 경도 정보를 가져옵니다
        let latlng = mouseEvent.latLng;

        // 마커 위치를 클릭한 위치로 옮깁니다
        marker.setPosition(latlng);

        this.lat = latlng.getLat();
        this.lon = latlng.getLng();
      });
    },
    stn_create() {
      console.log(this.station_name)
      Axios.post('/api/manage', {
        id : 1,
        station_name : this.station_name,
        station_lat : this.lat,
        station_lon : this.lon
      })
      .then(res => {
        this.data = res.data.station
        this.initialize();
        this.initMap();
      })
      .catch(err => {
        console.log(err)
      })
    },
    map_click() {
      if (this.stage == 1) this.stage += 1;
    },
    initialize() {
      (this.map_stage = 1), // 맵 한번만 생성
        (this.stage = 1), // 단계별 보여지는 화면
        (this.lat = ""), // 위도
        (this.lng = ""), // 경도
        (this.data = ""), // 정류장 데이터
        (this.station_name = ""); // 정류장 이름
      if (this.data[this.data.length - 1].station_name != "") {
        this.data.push({
          station_name: "",
          station_lat: "",
          station_lon: ""
        });
      } else if (this.data[this.data.length - 1].station_name == "") {
        this.data.splice(this.length - 1, 1);
      }
    },
  },
  cancel() {
      Axios.post("/api/manage", {
        id : 3
      })
      .then(res => {
        this.initialize();
        this.initMap();
      })
      .catch(err => {
        console.log(err)
      })
    },
};
</script>

<style scoped>
#map {
  /* width: 50rem; */
  /* height: 40rem; */
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