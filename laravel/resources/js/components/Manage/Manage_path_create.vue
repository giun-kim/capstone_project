<template>
  <div class="page-container">
    <div id="map"></div>
    <div id="manager">
      <div v-if="stage == 1">경로를 등록할 두 정류장을 클릭해 주세요.</div>
      <div v-if="stage == 2">체크포인트 클릭 후 확인 버튼 클릭해 주세요. <br> <b-button type="button" variant="primary" @click="check()">확인</b-button></div>
      <div v-if="stage == 3">
        <b-form>
          {{station_all[station_start].station_name}} - {{station_all[station_end].station_name}}
          <div>체크포인트 수 : {{ checkpoint_num }}</div>
          <div>총 거리 : 1.8 km</div>
          <b-button-group>
            <b-button type="button" variant="primary" @click="path_create()">등록하기</b-button>
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
    Axios.get('/api/dlvy/management/path')
    .then(res => {
      this.station_all = res.data.station_all
      this.checkpoint_all = res.data.checkpoint_all
      this.initMap();
    })
  },
  data() {
    return {
      stage: 1, // 단계별 보여지는 화면
      map_stage: 1, // 맵 스테이지
      station_all: "", // 모든 정류장 데이터
      checkpoint_all: "", // 모든 체크포인트 데이터
      station_start: "", // 출발 정류장
      station_end: "", // 도착 정류장
      station_markers: [], // 마커 저장
      checkpoint_markers_all: [], // 전체 마커 저장
      checkpoint_markers_click: [], // 클릭한 마커 저장(마커 데이터)
      checkpoint_markers_clicked: [], // 클릭한 마커 클릭 금지(숫자)
      station_stage: 1, // 정류장 클릭시
      checkpoint_num: 0,
      overlay_data: [],
    };
  },
  methods: {
    //두 좌표간의 거리
    getDistance(lat1, lon1, lat2, lon2) {
      var R = 6371;
      var dLat = this.deg2rad(lat2 - lat1);
      var dLon = this.deg2rad(lon2 - lon1);
      var a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(this.deg2rad(lat1)) *
          Math.cos(this.deg2rad(lat2)) *
          Math.sin(dLon / 2) *
          Math.sin(dLon / 2);
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      var d = R * c; // Distance in km
      return d;
    },

    // 두 좌표간의 거리 계산 도움
    deg2rad(deg) {
      return deg * (Math.PI / 180);
    },

    // 인포메이션
    makeOverListener(map, marker, infowindow) {
      return function() {
        infowindow.open(map, marker);
      };
    },

    // 인포윈도우 닫기
    makeOutListener(infowindow) {
      return function() {
        infowindow.close();
      };
    },
    initMap() {
      if(this.map_stage == 1) {
        var container = document.getElementById("map");
        var options = {
          center: new kakao.maps.LatLng(35.896309, 128.621917), // 지도 중심 좌표
          level: 2, // 지도 확대
          draggable: false, // 지도 이동 막기
        };
        this.map = new kakao.maps.Map(container, options)
        this.map_stage = 2
      }
      for(let i = 0; i < this.checkpoint_all.length; i++) {
        this.checkpoint_markers_clicked.push(1)
      }
      //마커 이미지
      var imageSrc =
        "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";

      //station_all
      for (let i = 0; i < this.station_all.length; i++) {
        // 마커를 생성합니다
        const marker = new kakao.maps.Marker({
          position: new kakao.maps.LatLng(this.station_all[i].station_lat, this.station_all[i].station_lon), // 마커를 표시할 위치
        });
        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            (this.station_all[i].station_name) +
            "</div>",
        });
        marker.setMap(this.map);
        this.station_markers.push(marker);

        kakao.maps.event.addListener(this.station_markers[i], "click", () => {
          if(this.station_stage != 4)
            this.station_stage += 1
          if (this.station_stage == 2) {
            this.station_start = i
            this.station_custom_overlay()
          } else if (this.station_stage == 3) {
            this.station_end = i
            this.station_delete()
            this.station_custom_overlay()
            this.checkpoint_start()
            this.stage = 2
          }
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
      }
    },
    station_custom_overlay() {
      if (this.station_stage == 2) { // 출발 정류장
        // 커스텀 오버레이(위 출발표시)
        const content =
          "<div style='margin-bottom:36px;'>" +
          "  <span style='font-size:20px; font-weight:bold; color:red'>출발</span>" +
          "</div>";

        let customOverlay = new kakao.maps.CustomOverlay({
          position: new kakao.maps.LatLng(this.station_all[this.station_start].station_lat, this.station_all[this.station_start].station_lon),
          content: content,
          yAnchor: 1, // y좌표 위치
        });

        customOverlay.setMap(this.map)
        this.overlay_data.push(customOverlay)
      }
      if (this.station_stage == 3) { // 도착 정류장
        // 커스텀 오버레이(위 도착표시)
        const content =
          "<div style='margin-bottom:36px;'>" +
          "  <span style='font-size:20px; font-weight:bold; color:red'>도착</span>" +
          "</div>";

        let customOverlay = new kakao.maps.CustomOverlay({
          position: new kakao.maps.LatLng(this.station_all[this.station_end].station_lat, this.station_all[this.station_end].station_lon),
          content: content,
          yAnchor: 1, // y좌표 위치
        });
        customOverlay.setMap(this.map)
        this.overlay_data.push(customOverlay)
      }
    },
    checkpoint_start() {
      var imageSrc =
        "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";
      for (let i = 0; i < this.checkpoint_all.length; i++) {
        var imageSize = new kakao.maps.Size(24, 35);
        var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize);
        // 마커를 생성합니다
        const marker = new kakao.maps.Marker({
          position: new kakao.maps.LatLng(this.checkpoint_all[i].checkpoint_lat, this.checkpoint_all[i].checkpoint_lon), // 마커를 표시할 위치
          image: markerImage,
        });
        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            (this.checkpoint_all[i].checkpoint_id) +
            "</div>",
        });
        marker.setMap(this.map);
        this.checkpoint_markers_all.push(marker);

        // 체크포인트 클릭 이벤트
        kakao.maps.event.addListener(this.checkpoint_markers_all[i], "click", () => {
          console.log(this.checkpoint_markers_click)
          if(this.checkpoint_markers_clicked[i] == 1) {
            this.checkpoint_markers_clicked[i] += 1
            this.checkpoint_num += 1
            this.checkpoint_custom_overlay(this.checkpoint_num, i)
            this.checkpoint_markers_click.push(marker)
          }
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
      }
    },
    checkpoint_custom_overlay(num, i) {
        const content =
        "<div style='margin-bottom:36px;'>" +
        "  <span style='font-size:20px; font-weight:bold; color:red'>" +
        (num) +
        "</span>" +
        "</div>";
        const position = new kakao.maps.LatLng(
          this.checkpoint_all[i].checkpoint_lat,
          this.checkpoint_all[i].checkpoint_lon
        );

        let customOverlay = new kakao.maps.CustomOverlay({
          position: position,
          content: content,
          yAnchor: 1,
        });
        customOverlay.setMap(this.map)
        this.overlay_data.push(customOverlay)
    },
    station_delete() { // 두 정류장을 클릭 했을 경우
      console.log(this.station_markers)
      for (let i = 0; i < this.station_markers.length; i++) {
        if(this.station_start == i || this.station_end == i)
          continue
        this.station_markers[i].setMap(null)
      }
    },
    check() {
      if(this.stage != 3)
        this.stage +=1
      for(let i = 0; i < this.checkpoint_markers_all.length; i++) {
        this.checkpoint_markers_all[i].setMap(null)
      }
      for(let i = 0; i < this.checkpoint_markers_click.length; i++) {
        this.checkpoint_markers_click[i].setMap(this.map)
      }
    },
    initialize() {
      for(let z = 0; z < this.overlay_data.length; z++) {
        this.overlay_data[z].setMap(null)
      }
      for(let x = 0; x < this.checkpoint_markers_click.length; x++) {
        this.checkpoint_markers_click[x].setMap(null)
      }
      for(let y = 0; y < this.station_markers.length; y++) {
        this.station_markers[y].setMap(null)
      }
     
      (this.stage = 1), // 단계별 보여지는 화면
      (this.station_markers = []),
      (this.checkpoint_markers_all = []),
      (this.checkpoint_markers_click = []),
      (this.checkpoint_markers_clicked = []),
      (this.station_start = ""),
      (this.station_end = ""),
      (this.station_stage = 1),
      (this.checkpoint_num = 0),
      (this.overlay_data = []),
      (this.initMap());
    }
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