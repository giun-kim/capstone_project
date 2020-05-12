<template>
  <div class="page-container">
    <div id="map" @click="map_click()"></div>
    <div id="manager">
      <div v-if="stage == 1">출발 정류장을 클릭해 주세요.</div>
      <div v-if="stage == 2">도착 정류장을 클릭해 주세요.</div>
      <div v-if="stage == 3">체크포인트를 클릭해 주세요.</div>
      <div v-if="stage == 4">
        <b-form>
          <div>체크포인트 수 : {{ checkpoints.length }}</div>
          <div>총 거리 : {{ distance }} km</div>
          <b-button-group>
            <b-button type="submit" variant="primary" @click="path_create()">등록하기</b-button>
            <b-button type="button" @click="cancel()">취소하기</b-button>
          </b-button-group>
        </b-form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    Axios.get('/api/dlvy/management/path', {

    })
    this.initMap();
  },
  data() {
    return {
      stage: 1, // 단계별 보여지는 화면
      stn_name: "", // 정류장 이름
      lat: "", // 위도
      lng: "", // 경도
      stn_data: "", // 정류장 데이터
      cp_data: "", // 체크포인트 데이터
      stn_length: "", // 정류장 데이터 길이(반복문)
      cp_length: "", // 체크포인트 데이터 길이(반복문)
      stn_markers: [], // 마커 저장
      cp_markers: [], // 마커 저장
      map: "", // 맵 저장
      click: 0,
      // 2개의 정류장이 먼저 선택되야 하므로, 선택됬는지 여부를 알리는 깃발
      stn_selected: false,
      // 시작과 끝 스테이션
      start_stn: "",
      end_stn: "",
      checkpoints: [], // 중간 체크포인트
      distance: 0,
    };
  },
  methods: {

    // 두 좌표간의 거리
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
      var container = document.getElementById("map");
      var options = {
        center: new kakao.maps.LatLng(35.896309, 128.621917), // 지도 중심 좌표
        level: 2, // 지도 확대
        draggable: false, // 지도 이동 막기
      };
      var map = new kakao.maps.Map(container, options);
      this.map = map;
      var imageSrc =
        "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";

      if (this.stage >= 2) { // 도착 정류장
        // 커스텀 오버레이(위 출발표시)
        var content =
          "<div style='margin-bottom:36px;'>" +
          "  <span style='font-size:20px; font-weight:bold; color:red'>출발</span>" +
          "</div>";
        var position = new kakao.maps.LatLng(
          this.start_stn.lat,
          this.start_stn.lng
        );

        var customOverlay1 = new kakao.maps.CustomOverlay({
          map: map,
          position: position,
          content: content,
          yAnchor: 1, // y좌표 위치
        });
      }
      if (this.stage >= 3) { // 출발 정류장
        // 커스텀 오버레이(위 도착표시)
        var content =
          "<div style='margin-bottom:36px;'>" +
          "  <span style='font-size:20px; font-weight:bold; color:red'>도착</span>" +
          "</div>";
        var position = new kakao.maps.LatLng(
          this.end_stn.lat,
          this.end_stn.lng
        );

        var customOverlay2 = new kakao.maps.CustomOverlay({
          map: map,
          position: position,
          content: content,
          yAnchor: 1, // y좌표 위치
        });

        // cp overlay
        for (let i = 0; i < this.checkpoints.length; i++) {
          // 커스텀 오버레이
          var content2 =
            "<div style='margin-bottom:36px;'>" +
            "  <span style='font-size:20px; font-weight:bold; color:red'>" +
            (i + 1) +
            "</span>" +
            "</div>";
          var position2 = new kakao.maps.LatLng(
            this.checkpoints[i].lat,
            this.checkpoints[i].lng
          );

          var customOverlay3 = new kakao.maps.CustomOverlay({
            map: map,
            position: position2,
            content: content2,
            yAnchor: 1,
          });
        }
      }

      // get distance
      if (this.checkpoints.length == 0) {
        this.distance = this.getDistance(
          this.start_stn.lat,
          this.start_stn.lng,
          this.end_stn.lat,
          this.end_stn.lng
        );
      } else {
        let tmpDistance = this.getDistance(
          this.start_stn.lat,
          this.start_stn.lng,
          this.checkpoints[0].lat,
          this.checkpoints[0].lng
        );
        for (let j = 1; j < this.checkpoints.length; j++) {
          tmpDistance += this.getDistance(
            this.checkpoints[j - 1].lat,
            this.checkpoints[j - 1].lng,
            this.checkpoints[j].lat,
            this.checkpoints[j].lng
          );
        }
        tmpDistance += this.getDistance(
          this.checkpoints[this.checkpoints.length - 1].lat,
          this.checkpoints[this.checkpoints.length - 1].lng,
          this.end_stn.lat,
          this.end_stn.lng
        );
        this.distance = tmpDistance;
      }

      // stn
      for (let i = 0; i < this.stn_data.length; i++) {
        // 마커를 생성합니다
        const marker = new kakao.maps.Marker({
          position: this.stn_data[i].latlng, // 마커를 표시할 위치
          title: this.stn_data[i].name, // 마커의 타이틀, 마커에 마우스를 올리면 타이틀이 표시됩니다.
        });
        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            (this.stn_data[i].stn_name ? this.stn_data[i].stn_name : "") +
            "</div>",
        });
        marker.setMap(this.map);
        this.stn_markers.push(marker);

        kakao.maps.event.addListener(marker, "click", () => {
          if (this.stage == 1) {
            // 첫 스테이션
            this.start_stn = {
              stn_name: this.stn_data[i].stn_name,
              stn_id: this.stn_data[i].stn_id,
              lat: this.stn_data[i].latlng.Ha,
              lng: this.stn_data[i].latlng.Ga,
            };
            this.stage = 2;
          } else if (this.stage == 2) {
            // 마지막 스테이션
            this.end_stn = {
              stn_name: this.stn_data[i].stn_name,
              stn_id: this.stn_data[i].stn_id,
              lat: this.stn_data[i].latlng.Ha,
              lng: this.stn_data[i].latlng.Ga,
            };
            this.stage = 3;
          }
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
      // cp
      for (let i = 0; i < this.cp_data.length; i++) {
        var imageSize = new kakao.maps.Size(24, 35);
        var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize);
        // 마커를 생성합니다
        const marker = new kakao.maps.Marker({
          position: this.cp_data[i].latlng, // 마커를 표시할 위치
          title: this.cp_data[i].name, // 마커의 타이틀, 마커에 마우스를 올리면 타이틀이 표시됩니다.
          image: markerImage,
        });
        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            (this.cp_data[i].name ? this.cp_data[i].name : "") +
            "</div>",
        });
        marker.setMap(this.map);
        this.cp_markers.push(marker);
        // console.log(marker)

        kakao.maps.event.addListener(marker, "click", () => {
          //  체크포인트 설정
          if (this.stage != 3) {
            return;
          }

          // 현재 마커가 체크포인트 리스트 내에 없으면 등록
          for (let j = 0; j < this.checkpoints.length; j++) {
            if (this.checkpoints[j].id == this.cp_data[i].id) {
              return;
            }
          }
          this.checkpoints.push({
            name: this.cp_data[i].name,
            id: this.cp_data[i].id,
            lat: this.cp_data[i].latlng.Ha,
            lng: this.cp_data[i].latlng.Ga,
          });
        });
        // }
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
    },
    path_create() {
      const newId =
        this.$store.state.paths.length != 0
          ? this.$store.state.paths[this.$store.state.paths.length - 1].id + 1
          : 1;
      this.$store.commit("addPath", {
        id: newId,
        start: this.start_stn.stn_id,
        end: this.end_stn.stn_id,
        checkpoints: this.checkpoints,
      });
      this.initialize();
      this.initMap();
    },

    cancel() {
      this.initialize();
      this.initMap();
    },
    initialize() {
      (this.stage = 1), // 단계별 보여지는 화면
        (this.stn_name = ""), // 정류장 이름
        (this.lat = ""), // 위도
        (this.lng = ""), // 경도
        (this.stn_data = this.$store.state.stations),
        (this.stn_ength = this.$store.state.stations.length),
        (this.cp_data = this.$store.state.checkpoints),
        (this.cp_length = this.$store.state.checkpoints.length),
        (this.stn_markers = []),
        (this.cp_markers = []);
      this.click = 0;
      this.map = "";
      this.stn_selected = false;
      this.start_stn = "";
      this.end_stn = "";
      this.checkpoints = [];
      this.distance = 0;
    },
    map_click() {
      if (this.stage == 2 || this.stage == 3 || this.stage == 4) this.initMap();
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