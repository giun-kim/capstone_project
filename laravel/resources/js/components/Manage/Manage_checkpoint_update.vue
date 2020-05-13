<template>
  <div class="page-container">
    <div id="map"></div>
    <div id="manager">
      <div v-if="stage == 1">지도에서 수정/삭제할 체크포인트를 클릭해 주세요.</div>
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
            <b-button type="button" variant="primary" @click="chk_update()">수정하기</b-button>
            <b-button variant="danger" type="button" @click="chk_delete()">삭제하기</b-button>
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
      this.data = res.data.checkpoint_all
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
      data: "", // 데이터
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
        this.map = new kakao.maps.Map(container, options);
      }

      var imageSrc =
        "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";
      // 여러 개 마커 생성하기
      if (this.stage == 1) {
        for (let i = 0; i < this.data.length; i++) {
          var imageSize = new kakao.maps.Size(24, 35);
          var markerImage = new kakao.maps.MarkerImage(imageSrc, imageSize);
          // 마커를 생성합니다
          const marker = new kakao.maps.Marker({
            position: new kakao.maps.LatLng(this.data[i].checkpoint_lat, this.data[i].checkpoint_lon), // 마커를 표시할 위치
            image: markerImage,
          });
          // 인포 윈도우 생성
          var infowindow = new kakao.maps.InfoWindow({
            content:
              "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
              "checkpoint id : " + (this.data[i].checkpoint_id) +
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

          kakao.maps.event.addListener(this.markers[i], "click", () => {
            this.stage = 2; // 정류장 클릭 후 수정 페이지 이동
            this.markers[i].setDraggable(true);
            this.checkpoint_id = this.data[i].checkpoint_id;
            this.lat = this.data[i].checkpoint_lat; // 위도
            this.lon = this.data[i].checkpoint_lon; // 경도
            this.click += 1;
          });

          kakao.maps.event.addListener(this.markers[i], "dragend", () => {
            this.lat = this.markers[i].getPosition().Ha;
            this.lon = this.markers[i].getPosition().Ga;
          });
        }
      }
    },
    chk_delete() {
      Axios.delete(`/api/dlvy/management/checkpoint/${this.checkpoint_id}`)
      .then(res => {
        this.initialize();
        this.data = res.data.checkpoint_all
        this.initMap();
      })
      .catch(err => {
        console.log(err)
      })
    },
    chk_update() {
      Axios.patch(`/api/dlvy/management/checkpoint/${this.checkpoint_id}`, {
        checkpoint_lat : this.lat,
        checkpoint_lon : this.lon
      })
      .then(res => {
        this.stage == 1
        this.initialize()
        this.data = res.data.checkpoint_all
        this.initMap();
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
        (this.checkpoint_id = ""), // 체크포인트 이름
        (this.lat = ""), // 위도
        (this.lon = ""), // 경도
        (this.markers = []); // 마커 표시
      this.click = 0;
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