<template>
  <div class="page-container">
    <div id="map"></div>
    <div id="manager">
      <!-- stage = 1 : 두 정류장 클릭 -->
      <div v-if="stage == 1">경로를 등록할 두 정류장을 클릭해 주세요.</div>
      <!-- stage = 2 : 경로 체크포인트 클릭 -->
      <div v-if="stage == 2">체크포인트 클릭 후 확인 버튼 클릭해 주세요. <br> 
      <!-- check() : 두 정류장 간 체크포인트를 이은 경로 생성 -->
      <b-button type="button" variant="primary" @click="check()">확인</b-button> </div>
      <!-- stage = 3 : 경로 등록 정보 -->
      <div v-if="stage == 3">
        <b-form>
          <!-- 두 정류장 이름 -->
          {{station_all[station_start].station_name}} ↔ {{station_all[station_end].station_name}}
          <div>체크포인트 수 : {{ checkpoint_num }}</div>
          <div>총 거리 : {{ distance }} m</div>
          <!-- path_create() : 경로 등록 함수, initialize() : 선택 경로 취소 -->
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
    // 정류장과 체크포인트 데이터 불러오기
    Axios.get('/api/dlvy/management/path')
    .then(res => {
      this.station_all = res.data.station_all
      this.checkpoint_all = res.data.checkpoint_all
      this.initMap()
    })
  },
  data() {
    return {
      map_stage: 1, // 카카오맵 생성 제한 map_stage = 1 : 맵 생성, map_stage = 2 : 맵 생성 안함
      stage: 1, // 단계별 보여지는 화면 stage = 1 : 두 정류장 클릭, stage = 2 : 체크포인트 클릭, stage = 3 : 경로 정보
      station_all: "", // 정류장 데이터
      checkpoint_all: "", // 체크포인트 데이터
      station_start: "", // 건물1 정류장
      station_end: "", // 건물2 정류장
      station_markers: [], // 정류장 마커 배열
      checkpoint_markers: [], // 체크포인트 마커 배열
      checkpoint_markers_click: [], // 체크포인트 클릭 마커 배열
      checkpoint_markers_clicked: [], // 체크포인트 클릭 금지
      checkpoint_markers_clicknumber: [], // 생성된 체크포인트 클릭 순서
      checkpoint_markers_id: [], // 체크포인트 클릭 아이디 배열
      station_stage: 1, // 정류장 클릭 station_stage = 1 : 첫번째 정류장, station_stage = 2 : 두번째 정류장, station_stage = 3 : 체크포인트 표시
      checkpoint_num: 0, // 체크포인트 클릭 수
      overlay_data: [], // 모든 오버레이 데이터
      distance: 0, // 거리
      polylines: [], // 폴리라인 길 표시 
      linepath: [], // 선을 구성하는 좌표 배열
      map: "" // 맵
    }
  },
  methods: {
    // 두 좌표간의 거리 계산 함수
    getDistance(lat1, lon1, lat2, lon2) {
      var R = 6371
      var dLat = this.deg2rad(lat2 - lat1)
      var dLon = this.deg2rad(lon2 - lon1)
      var a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(this.deg2rad(lat1)) *
          Math.cos(this.deg2rad(lat2)) *
          Math.sin(dLon / 2) *
          Math.sin(dLon / 2)
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
      var d = R * c
      return d // 두 좌표간 거리
    },

    // 두 좌표간의 거리 계산식
    deg2rad(deg) {
      return deg * (Math.PI / 180)
    },

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

      // 체크포인트 클릭 막는 배열
      for(let i = 0, len = this.checkpoint_all.length; i < len; i++) {
        this.checkpoint_markers_clicked.push(1)
      }

      // 체크포인트 마커 이미지
      var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png"

      // 모든 정류장 마커 생성
      for (let i = 0, len = this.station_all.length; i < len; i++) {
        // 마커를 생성합니다
        const marker = new kakao.maps.Marker({
          position: new kakao.maps.LatLng(this.station_all[i].station_lat, this.station_all[i].station_lon), // 마커를 표시할 위치
        })

        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            (this.station_all[i].station_name) +
            "</div>",
        })

        // 정류장 마커 표시 및 마커 배열 푸시
        marker.setMap(this.map)
        this.station_markers.push(marker)

        // 정류장 마커 클릭 이벤트
        kakao.maps.event.addListener(this.station_markers[i], "click", () => {
          if(this.station_stage != 4)
            this.station_stage += 1

          // 두 정류장 마커 클릭 데이터 
          if (this.station_stage == 2) {
            this.station_start = i // 첫번째 정류장 클릭 
            this.station_custom_overlay() // 클릭한 정류장 커스텀 오버레이
          } else if (this.station_stage == 3) {
            this.station_end = i // 두번째 정류장 클릭
            this.station_delete() // 클릭한 정류장 제외 마커 삭제
            this.station_custom_overlay() // 클릭한 정류장 커스텀 오버레이
            this.checkpoint_start() // 체크포인트 표시
            this.stage = 2 // 체크포인트 클릭 화면
          }
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
      }
    },

    // 정류장 오버레이 함수
    station_custom_overlay() {
      // 첫번째 정류장
      if (this.station_stage == 2) {
        // 커스텀 오버레이(위 건물1표시)
        const content =
          "<div style='margin-bottom:36px;'>" +
          "  <span style='font-size:20px; font-weight:bold; color:red'>건물1</span>" +
          "</div>";

        // 정류장 커스텀 오버레이 설정
        let customOverlay = new kakao.maps.CustomOverlay({
          position: new kakao.maps.LatLng(this.station_all[this.station_start].station_lat, this.station_all[this.station_start].station_lon),
          content: content,
          yAnchor: 1,
        });

        // 정류장 커스텀 오버레이 표시 및 배열 푸시
        customOverlay.setMap(this.map)
        this.overlay_data.push(customOverlay)
      }

      // 두번째 정류장
      if (this.station_stage == 3) {
        // 커스텀 오버레이(위 건물2표시)
        const content =
          "<div style='margin-bottom:36px;'>" +
          "  <span style='font-size:20px; font-weight:bold; color:red'>건물2</span>" +
          "</div>";

        // 정류장 커스텀 오버레이 설정
        let customOverlay = new kakao.maps.CustomOverlay({
          position: new kakao.maps.LatLng(this.station_all[this.station_end].station_lat, this.station_all[this.station_end].station_lon),
          content: content,
          yAnchor: 1,
        });

        // 정류장 커스텀 오버레이 표시 및 배열 푸시
        customOverlay.setMap(this.map)
        this.overlay_data.push(customOverlay)
      }
    },

    // 체크포인트 표시
    checkpoint_start() {
      // 체크포인트 마커 이미지
      var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";

      // 모든 체크포인트 마커 생성
      for (let i = 0; i < this.checkpoint_all.length; i++) {
        // 체크포인트 이미지 사이즈 및 경로
        var imageSize = new kakao.maps.Size(24, 35);
        var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize);

        // 체크포인트 마커 생성
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

        // 체크포인트 마커 표시 및 마커 배열 푸시
        marker.setMap(this.map);
        this.checkpoint_markers.push(marker);

        // 체크포인트 클릭 이벤트
        kakao.maps.event.addListener(this.checkpoint_markers[i], "click", () => {
          // 체크포인트 작업
          if(this.checkpoint_markers_clicked[i] == 1) { // 체크포인트 두번 클릭 방지
            this.checkpoint_markers_clicknumber.push(i) // 생성된 체크포인트 클릭 순서
            this.checkpoint_markers_id.push(this.checkpoint_all[i].checkpoint_id) // 클릭한 체크포인트 아이디 푸시
            this.checkpoint_num += 1 // 체크포인트 클릭 수
            this.checkpoint_custom_overlay(this.checkpoint_num, i) // 클릭한 체크포인트 오버레이 표시
            this.checkpoint_markers_click.push(marker) // 체크포인트 클릭 마커 배열 푸시
            this.checkpoint_markers_clicked[i] += 1 
          }
        });

        // 인포윈도우 여는 이벤트
        kakao.maps.event.addListener(
          marker,
          "mouseover",
          this.makeOverListener(this.map, marker, infowindow)
        );

        // 인포윈도우 닫는 이벤트
        kakao.maps.event.addListener(
          marker,
          "mouseout",
          this.makeOutListener(infowindow)
        );
      }
    },

    // 체크포인트 오버레이 함수
    checkpoint_custom_overlay(num, i) {
        // 클릭한 체크포인트 마커 위 커스텀 오버레이 표시
        const content =
        "<div style='margin-bottom:36px;'>" +
        "  <span style='font-size:20px; font-weight:bold; color:red'>" +
        (num) +
        "</span>" +
        "</div>";

        // 클릭한 체크포인트 위치
        const position = new kakao.maps.LatLng(
          this.checkpoint_all[i].checkpoint_lat,
          this.checkpoint_all[i].checkpoint_lon
        );

        // 체크포인트 커스텀 오버레이 설정
        let customOverlay = new kakao.maps.CustomOverlay({
          position: position,
          content: content,
          yAnchor: 1,
        });

        // 체크포인트 커스텀 오버레이 표시 및 배열 푸시
        customOverlay.setMap(this.map)
        this.overlay_data.push(customOverlay)
    },

    // 두 정류장 클릭시 실행되는 함수
    station_delete() {
      // 클릭한 정류장 제외한 모든 정류장 삭제
      for (let i = 0, len = this.station_markers.length; i < len; i++) {
        if(this.station_start == i || this.station_end == i)
          continue
        this.station_markers[i].setMap(null)
      }
    },

    // 거리 계산 및 경로 표시 함수
    check() {
      if(this.checkpoint_markers.length > 0) {
        // stage = 3 : 경로 정보
        this.stage = 3

        // 모든 체크포인트마커 삭제
        for(let i = 0, len = this.checkpoint_markers.length; i < len; i++) {
          this.checkpoint_markers[i].setMap(null)
        }

        // 클릭한 체크포인트 표시
        for(let i = 0, len = this.checkpoint_markers_click.length; i < len; i++) {
          this.checkpoint_markers_click[i].setMap(this.map)
        }

        // 두 정류장 및 클릭한 체크포인트 좌표배열  
        this.linepath = []

        // 총 거리 계산
        for(let i = 0, len = this.checkpoint_markers_clicknumber.length; i < len; i++) {
          var checkpoint_lat = this.checkpoint_all[this.checkpoint_markers_clicknumber[i]].checkpoint_lat
          var checkpoint_lon = this.checkpoint_all[this.checkpoint_markers_clicknumber[i]].checkpoint_lon
          if(len-1 != i) {
            var checkpoint_lat_plus = this.checkpoint_all[this.checkpoint_markers_clicknumber[i+1]].checkpoint_lat
            var checkpoint_lon_plus = this.checkpoint_all[this.checkpoint_markers_clicknumber[i+1]].checkpoint_lon
          }
          if(i == 0) {
            var station_one_lat = this.station_all[this.station_start].station_lat
            var station_one_lon = this.station_all[this.station_start].station_lon
            var station_two_lat = this.station_all[this.station_end].station_lat
            var station_two_lon = this.station_all[this.station_end].station_lon

            this.distance = this.distance + this.getDistance(station_one_lat, station_one_lon, checkpoint_lat, checkpoint_lon)
            this.linepath.push(new kakao.maps.LatLng(station_one_lat, station_one_lon))
            this.linepath.push(new kakao.maps.LatLng(checkpoint_lat, checkpoint_lon))
            if(this.checkpoint_markers_clicknumber.length == 1) {
              this.distance = this.distance + this.getDistance(checkpoint_lat, checkpoint_lon, station_two_lat, station_two_lon)
              this.linepath.push(new kakao.maps.LatLng(station_two_lat, station_two_lon))
            } else {
              this.distance = this.distance + this.getDistance(checkpoint_lat, checkpoint_lon, checkpoint_lat_plus, checkpoint_lon_plus)
              this.linepath.push(new kakao.maps.LatLng(checkpoint_lat_plus, checkpoint_lon_plus))
            }
            continue
          } else if(i == this.checkpoint_markers_clicknumber.length - 1) {
            this.distance = this.distance + this.getDistance(checkpoint_lat, checkpoint_lon, station_two_lat, station_two_lon)
            this.linepath.push(new kakao.maps.LatLng(station_two_lat, station_two_lon))
            continue
          }
          this.distance = this.distance + this.getDistance(checkpoint_lat, checkpoint_lon, checkpoint_lat_plus, checkpoint_lon_plus)
          this.linepath.push(new kakao.maps.LatLng(checkpoint_lat, checkpoint_lon))
          this.linepath.push(new kakao.maps.LatLng(checkpoint_lat_plus, checkpoint_lon_plus))
        }
 
        // 폴리라인 설정
        const polyline = new kakao.maps.Polyline({
            path: this.linepath, // 선을 구성하는 좌표배열
            strokeWeight: 5, // 선의 두께
            strokeColor: '#FFAE00', // 선의 색깔
            strokeOpacity: 0.7, // 선의 불투명도 입니다 1에서 0 사이의 값이며 0에 가까울수록 투명
            strokeStyle: 'solid' // 선의 스타일
        });

        // 폴리라인 표시 및 배열 푸시
        polyline.setMap(this.map)
        this.polylines.push(polyline)

        // 거리 단위 m
        this.distance = this.distance.toFixed(3) * 1000
      }
    },

    // 경로 등록 함수
    path_create() {
      // 경로 등록 데이터 서버로 보내기
      Axios.post('/api/dlvy/management/path', {
        checkpoint_id: this.checkpoint_markers_id, // 클릭한 체크포인트 배열
        path_start_point: this.station_all[this.station_start].station_name, // 첫번째 정류장 이름
        path_end_point: this.station_all[this.station_end].station_name, // 두번째 정류장 이름
      })
      .then(res => {
        this.initialize()
      })
      .catch(err => {
        console.log(err)
      })
    },

    // 데이터 초기화
    initialize() {
      // 폴리라인 배열 삭제
      this.polylines[0].setMap(null)

      // 모든 오버레이 삭제
      for(let i = 0, len = this.overlay_data.length; i < len; i++) {
        this.overlay_data[i].setMap(null)
      }

      // 모든 체크포인트 마커 삭제
      for(let i = 0, len = this.checkpoint_markers_click.length; i < len; i++) {
        this.checkpoint_markers_click[i].setMap(null)
      }

      // 모든 정류장 마커 삭제
      for(let i = 0, len = this.station_markers.length; i < len; i++) {
        this.station_markers[i].setMap(null)
      }
     
      this.stage = 1 
      this.station_start = ""
      this.station_end = ""
      this.station_markers = []
      this.checkpoint_markers = []
      this.checkpoint_markers_click = []
      this.checkpoint_markers_clicked = []
      this.checkpoint_markers_clicknumber = []
      this.checkpoint_markers_id = []
      this.station_stage = 1
      this.checkpoint_num = 0
      this.overlay_data = []
      this.distance = 0
      this.linepath = []
      this.polylines = []

      this.initMap()
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