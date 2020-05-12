<template>
  <div class="page-container">
    <div id="map"></div>
    <div id="manager">
      <div v-if="stage == 1">지도에서 등록할 체크포인트를 클릭해주세요.</div>
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
            <b-button type="button" variant="primary" @click="chk_create()">등록하기</b-button>
            <b-button type="button" @click="initialize()">취소하기</b-button>
          </b-button-group>
        </b-form>
      </div>
    </div>
  </div>
</template>

<script>
// 미완성
export default {
  mounted() {
    Axios.get('/api/dlvy/management/checkpoint')
    .then((res) => {
      console.log(res.data.checkpoint_all)
      this.data = res.data.checkpoint_all
      this.data.push({
        checkpoint_id: "",
        checkpoint_lat: "",
        checkpoint_lon: ""
      });
      this.initMap();
    }) 
  },
  data() {
    return {
      map_stage: 1, // 맵 한번만 생성
      stage: 1, // 단계별 보여지는 화면
      checkpoint_id: "", // 체크포인트 아이디
      lat: "", // 위도
      lon: "", // 경도
      data: "", // 체크포인트 데이터
      map: "",
      markers: []
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
        this.map = new kakao.maps.Map(container, options);
      }

      var imageSrc =
        "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";
      // 여러 개 마커 생성하기
      for (let i = 0; i < this.data.length; i++) {
        var imageSize = new kakao.maps.Size(24, 35);
        var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize);
        // 마커를 생성합니다
        const marker = new kakao.maps.Marker({
          position: new kakao.maps.LatLng(this.data[i].checkpoint_lat, this.data[i].checkpoint_lon) ? new kakao.maps.LatLng(this.data[i].checkpoint_lat, this.data[i].checkpoint_lon) : "", // 마커를 표시할 위치
          image: markerImage,
        });
        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            "checkpoint id : " + (this.data[i].checkpoint_id ? this.data[i].checkpoint_id : "") +
            "</div>",
        });
        // 마우스 오버 이벤트
        kakao.maps.event.addListener(
          marker,
          "mouseover",
          this.makeOverListener(this.map, marker, infowindow)
        );
        kakao.maps.event.addListener(
          marker,
          "mouseout",
          this.makeOutListener(infowindow)
        );

        marker.setMap(this.map);
        this.markers.push(marker);
      }

      // 마커 표시
      kakao.maps.event.addListener(this.map, "click", (mouseEvent) => {
        // 클릭한 위도, 경도 정보를 가져옵니다
        let latlng = mouseEvent.latLng;

        // 마커 위치를 클릭한 위치로 옮깁니다
        this.markers[this.markers.length-1].setPosition(latlng);

        this.lat = latlng.getLat();
        this.lon = latlng.getLng();
        if(this.stage == 1)
          this.stage += 1;
      });
    },
    chk_create() {
      console.log(this.checkpoint_id)
      console.log(this.lat)
      Axios.post('/api/dlvy/management/checkpoint', {
        checkpoint_id : this.checkpoint_id,
        checkpoint_lat : this.lat,
        checkpoint_lon : this.lon
      })
      .then(res => {
        this.data = res.data.checkpoint_all
        this.initialize()
      })
      .catch(err => {
        console.log(err)
      })
    },
    initialize() {
      for (let i = 0; i < this.data.length; i++) {
        this.markers[i].setMap(null)
      }
      (this.map_stage = 2), // 맵 한번만 생성
        (this.stage = 1), // 단계별 보여지는 화면
        (this.lat = ""), // 위도
        (this.lng = ""), // 경도
        (this.checkpoint_id = ""), // 체크포인트 이름
        (this.markers = []);
      if (this.data[this.data.length - 1].checkpoint_id == "") {
        this.data.splice(this.data.length - 1, 1);
        this.data.push({
          checkpoint_id: "",
          checkpoint_lat: "",
          checkpoint_lon: ""
        });
      } else if(this.data[this.data.length - 1].checkpoint_id != "" ) {
        this.data.push({
          checkpoint_id: "",
          checkpoint_lat: "",
          checkpoint_lon: ""
        });
      }
      this.initMap();
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