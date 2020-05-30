<template>
  <div class="page-container">
    <div id="map"></div>
    <div id="manager">
      <div v-if="stage == 1">지도에서 등록할 체크포인트를 클릭해주세요.</div>
      <div v-if="stage == 2">
        <b-form>
          <div style="margin: 5px;">
            <div>
              <span style="font-size: 13px">위도 : {{ lat }}</span>
            </div>
            <div>
              <span style="font-size: 13px">경도 : {{ lon }}</span>
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
export default {
  mounted() {
    // 체크포인트 데이터 불러오기
    Axios.get('/api/dlvy/management/checkpoint')
    .then(res => {
      this.data = res.data.checkpoint_all
      this.data.push({
        checkpoint_id: "",
        checkpoint_lat: "",
        checkpoint_lon: ""
      })
      this.initMap()
    }) 
  },
  data() {
    return {
      map_stage: 1, // 카카오맵 생성 제한 map_stage = 1 : 맵 생성, map_stage = 2 : 맵 생성 안함
      stage: 1, // 단계별 보여지는 화면 stage = 1 : 체크포인트 클릭, stage = 2 : 등록 데이터 입력
      checkpoint_id: "", // 체크포인트 아이디
      lat: "", // 위도
      lon: "", // 경도
      data: "", // 체크포인트 데이터
      map: "", // 맵
      markers: [] // 마커 배열
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

      // 마커 이미지
      var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png"

      // 모든 마커 생성
      for (let i = 0, len = this.data.length; i < len; i++) {
        // 마커 이미지 사이즈 및 경로
        var imageSize = new kakao.maps.Size(24, 35);
        var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize)

        // 마커 생성
        const marker = new kakao.maps.Marker({
          position: new kakao.maps.LatLng(this.data[i].checkpoint_lat, this.data[i].checkpoint_lon) ? new kakao.maps.LatLng(this.data[i].checkpoint_lat, this.data[i].checkpoint_lon) : "", // 마커를 표시할 위치
          image: markerImage,
        })

        // 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            "checkpoint id : " + (this.data[i].checkpoint_id ? this.data[i].checkpoint_id : "") +
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
      }

      // 맵 클릭 이벤트
      kakao.maps.event.addListener(this.map, "click", (mouseEvent) => {
        let latlng = mouseEvent.latLng

        this.markers[this.markers.length-1].setPosition(latlng)

        this.lat = latlng.getLat()
        this.lon = latlng.getLng()

        // 맵 클릭시 다음 화면
        if(this.stage == 1)
          this.stage += 1
      })
    },

    // 체크포인트 등록 함수
    chk_create() {
      // 체크포인트 등록 데이터 서버로 보내기
      Axios.post('/api/dlvy/management/checkpoint', {
        checkpoint_id : this.checkpoint_id,
        checkpoint_lat : this.lat,
        checkpoint_lon : this.lon
      })
      .then(res => {
        this.data = res.data.checkpoint_all // 체크포인트 데이터
        this.initialize()
      })
      .catch(err => {
        console.log(err)
      })
    },

    // 데이터 초기화
    initialize() {
      // 마커 삭제하기
      for (let i = 0, len = this.data.length; i < len; i++) {
        this.markers[i].setMap(null)
      }

      this.stage = 1
      this.lat = ""
      this.lon = ""
      this.checkpoint_id = ""
      this.markers = []

      // 체크포인트 배열 데이터 초기화
      if (this.data[this.data.length - 1].checkpoint_id == "") {
        this.data.splice(this.data.length - 1, 1)
        this.data.push({
          checkpoint_id: "",
          checkpoint_lat: "",
          checkpoint_lon: ""
        })
      } 
      else if(this.data[this.data.length - 1].checkpoint_id != "" ) {
        this.data.push({
          checkpoint_id: "",
          checkpoint_lat: "",
          checkpoint_lon: ""
        })
      }
      this.initMap()
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