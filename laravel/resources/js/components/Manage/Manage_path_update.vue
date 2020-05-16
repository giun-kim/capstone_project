<template>
  <div class="page-container">
    <div id="map"></div>
    <div id="manager">
      <div v-if="stage == 1">경로를 수정/삭제할 정류장을 클릭해 주세요.</div>
      <div v-if="stage == 2">수정/삭제를 원하는 경로를 클릭해 주세요.</div>
      <div
        v-show="stage == 2"
        style="border-top: 1px solid #18a2b8; margin-top: 10px"
      >
        <div style="margin-top:10px; margin-bottom: 10px">현재 등록된 경로</div>
        <b-list-group v-for="path_one in path_one_all" :key="path_one.id">
          <b-list-group-item>
            <h5 style="cursor:pointer" @click="path_click(path_one)">
                {{ path_one.path_start_point }} ↔ {{ path_one.path_end_point }}
            </h5>
            <div v-if="path_one.path_id == path_check">
            <h6>체크포인트 수 : {{ checkpoint_num }}</h6>
            <h6>총 거리 : {{ distance }} m</h6>
              <div style="margin-top:10px; margin-bottom: 0">
                <b-button
                  variant="info"
                  type="button"
                  @click="path_update(path_one)"
                >
                  수정하기</b-button>
                <b-button type="button" variant="danger" @click="path_delete(path_one.path_id)">삭제하기</b-button>
                <b-button type="button" @click="initialize()">취소하기</b-button>
              </div>
            </div>
          </b-list-group-item>
        </b-list-group>
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
      map: "", // 맵 저장
      stage: 1, // 단계별 보여지는 화면
      map_stage: 1, // 맵 스테이지
      station_all: "", // 모든 정류장 데이터
      checkpoint_all: "", // 모든 체크포인트 데이터
      path_one_all: "", // 모든 경로 데이터
      path_start_point: [], 
      path_check: "",
      station_stop: 1, // 정류장 클릭 막기
      checkpoint_num: 0, // 체크포인트 갯수
      checkpoint_update_num: 0, // 체크포인트 업데이트할 순서
      checkpoint_sequence: [], // 체크포인트 순서 및 아이디
      coordinates: [], // 
      checkpoint: "",
      checkpoint_markers_clicked: [], // 클릭 금지
      station_markers: [], // 마커 저장
      checkpoint_markers_all: [], // 전체 마커 저장
      overlay_data: [], // 모든 오버레이 데이터
      distance: 0,
      polylines: [],
      linepath: [],
      check_one: 0
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
    // 지도 생성 및 각종 이벤트
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
      // for(let i = 0; i < this.checkpoint_all.length; i++) {
      //   this.checkpoint_markers_clicked.push(1)
      // }
      //마커 이미지
      var imageSrc =
        "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png";

      //station_all
      for (let i = 0; i < this.station_all.length; i++) {
        // 정류장 마커 생성
        const marker = new kakao.maps.Marker({
          position: new kakao.maps.LatLng(this.station_all[i].station_lat, this.station_all[i].station_lon), // 마커를 표시할 위치
        });
        // 정류장 인포 윈도우 생성
        var infowindow = new kakao.maps.InfoWindow({
          content:
            "<div style='text-align:center; margin-left:5px; color:#18a2b8'>" +
            (this.station_all[i].station_name) +
            "</div>",
        });
        marker.setMap(this.map);
        this.station_markers.push(marker);

        kakao.maps.event.addListener(this.station_markers[i], "click", () => {
          if(this.station_stop == 1) {
            if(this.stage != 2)
                this.stage += 1

            Axios.get(`/api/dlvy/management/path/${this.station_all[i].station_name}`)
            .then(res => {
                this.path_one_all = res.data // 해당 정류장의 모든 경로
            })
            .catch(err => {
                console.log(err)
            })
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
    // 정류장 오버레이
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
    // 체크포인트 표시
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

        for(let i = 0; i < this.checkpoint_all.length; i++) {
          this.checkpoint_markers_clicked.push(1)
        }

        marker.setMap(this.map);
        this.checkpoint_markers_all.push(marker);
        if(this.checkpoint_sequence.length > i) {
          this.checkpoint_custom_overlay() // 해당 리스트 체크포인트 순서 출력
          this.check(i, 1) // 거리 및 폴리라인 표시 
        }

        kakao.maps.event.addListener(this.checkpoint_markers_all[i], "click", () => {
            let click_check = this.checkpoint_sequence.filter((item) => {
              return item.check_id === this.checkpoint_all[i].checkpoint_id
            })

            if(click_check.length == 0) {
              click_check.push({
                check_id: "",
                sequence: ""
              })
            }

            if(click_check[0].check_id == this.checkpoint_all[i].checkpoint_id) { // 삭제 check() 실행
              let indexnumber = this.checkpoint_sequence.findIndex((item) => item.check_id == this.checkpoint_all[i].checkpoint_id)
              const sequence_length = this.checkpoint_sequence.length
              let overlay_data_copy = Array.prototype.slice.call(this.overlay_data)
              let checkpoint_sequence_copy = Array.prototype.slice.call(this.checkpoint_sequence)
              for(let y = indexnumber ; y < sequence_length; y++) {
                this.checkpoint_update_num -= 1
                this.checkpoint_num = this.checkpoint_update_num
                this.overlay_data[y].setMap(null)
              }
              overlay_data_copy.splice(indexnumber,sequence_length-1)
              this.checkpoint_sequence.splice(indexnumber, sequence_length)
              this.overlay_data = overlay_data_copy
              for(let i = 0; i < this.polylines.length; i++) {
                this.polylines[i].setMap(null)
              } 
              this.linepath = []
              this.distance = 0
              for(let i = 0; i < this.checkpoint_sequence.length; i++) {
                this.check(i, 3)
              }
            } else { // 생성 check() 실행 
              click_check.splice(click_check.length, 1)
              this.checkpoint_sequence.push({ check_id : this.checkpoint_all[i].checkpoint_id, sequence : this.checkpoint_update_num + 1}) 
              this.checkpoint_custom_overlay(this.checkpoint_update_num) 
              this.checkpoint_num = this.checkpoint_update_num
              this.check(this.checkpoint_num - 1,2)
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
    // 체크포인트 오버레이 숫자 표시
    checkpoint_custom_overlay() {
      this.checkpoint_update_num = this.checkpoint_update_num + 1
      const content =
      "<div style='margin-bottom:36px;'>" +
      "  <span style='font-size:20px; font-weight:bold; color:red'>" +
      (this.checkpoint_update_num) +
      "</span>" +
      "</div>";

      const coordinate = this.checkpoint_all.filter((item) => {
        return item.checkpoint_id === this.checkpoint_sequence[this.checkpoint_update_num - 1].check_id
      })

      const position = new kakao.maps.LatLng(
        coordinate[0].checkpoint_lat,
        coordinate[0].checkpoint_lon
      );

      let customOverlay = new kakao.maps.CustomOverlay({
        position: position,
        content: content,
        yAnchor: 1,
      });
      customOverlay.setMap(this.map)
      this.overlay_data.push(customOverlay)
    },
    station_delete(start_point, end_point) {  // 두 정류장 이름
      for (let i = 0; i < this.station_markers.length; i++) {
        if(start_point == this.station_all[i].station_name || end_point == this.station_all[i].station_name)
          continue
        this.station_markers[i].setMap(null)
      }
    },
    // 거리 계산 및 폴리라인 표시
    check(point, id) { 
      if(point != 0) {
        var checkpoint_markers_previous = this.checkpoint_all.filter((item) => {
          return item.checkpoint_id === this.checkpoint_sequence[point-1].check_id
        }) 
      }

      var checkpoint_markers_clicked = this.checkpoint_all.filter((item) => {
        return item.checkpoint_id === this.checkpoint_sequence[point].check_id
      }) // 클릭한 체크포인트 아이디, 위도, 좌표

      var station_clicked = this.path_one_all.filter((item) => {
        return item.path_id === this.path_check}) // 정류장 두 곳 확인

      var station_start = this.station_all.filter((item) => {
        return item.station_name === station_clicked[0].path_start_point
      }) // 출발 정류장 위도 좌표

      var station_end = this.station_all.filter((item) => {
        return item.station_name === station_clicked[0].path_end_point
      }) // 도착 정류장 위도 좌표

      if(id == 1 || id == 3) { 
        // 처음 거리 계산 및 폴리라인 
        if(point == 0) {
          this.distance = this.distance + this.getDistance(station_start[0].station_lat,station_start[0].station_lon,checkpoint_markers_clicked[0].checkpoint_lat,checkpoint_markers_clicked[0].checkpoint_lon).toFixed(3) * 1000
          this.linepath.push(new kakao.maps.LatLng(station_start[0].station_lat, station_start[0].station_lon))
          this.linepath.push(new kakao.maps.LatLng(checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon))
          if(this.checkpoint_sequence.length == 1) {
            this.distance = this.distance + this.getDistance(checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon, station_end[0].station_lat, station_end[0].station_lon).toFixed(3) * 1000
            this.linepath.push(new kakao.maps.LatLng(station_end[0].station_lat, station_end[0].station_lon))
          }
        } else if(point == this.checkpoint_sequence.length - 1) {
          this.distance = this.distance + this.getDistance(checkpoint_markers_previous[0].checkpoint_lat, checkpoint_markers_previous[0].checkpoint_lon, checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon).toFixed(3) * 1000
          this.distance = this.distance + this.getDistance(checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon, station_end[0].station_lat, station_end[0].station_lon).toFixed(3) * 1000
          this.linepath.push(new kakao.maps.LatLng(checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon))
          this.linepath.push(new kakao.maps.LatLng(station_end[0].station_lat, station_end[0].station_lon))
        } else {
          this.distance = this.distance + this.getDistance(checkpoint_markers_previous[0].checkpoint_lat, checkpoint_markers_previous[0].checkpoint_lon, checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon).toFixed(3) * 1000
          this.linepath.push(new kakao.maps.LatLng(checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon))
        }
      } else if (id == 2) { // 번호 등록
        this.distance = this.distance - this.getDistance(checkpoint_markers_previous[0].checkpoint_lat, checkpoint_markers_previous[0].checkpoint_lon, station_end[0].station_lat, station_end[0].station_lon).toFixed(3) * 1000
        this.distance = this.distance + this.getDistance(checkpoint_markers_previous[0].checkpoint_lat, checkpoint_markers_previous[0].checkpoint_lon, checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon).toFixed(3) * 1000
        this.distance = this.distance + this.getDistance(checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon, station_end[0].station_lat, station_end[0].station_lon).toFixed(3) * 1000
        this.linepath.splice(this.linepath.length-1,1)
        this.linepath.push(new kakao.maps.LatLng(checkpoint_markers_clicked[0].checkpoint_lat, checkpoint_markers_clicked[0].checkpoint_lon))
        this.linepath.push(new kakao.maps.LatLng(station_end[0].station_lat, station_end[0].station_lon))
        for(let i = 0; i < this.polylines.length; i++) {
          this.polylines[i].setMap(null)
        } 
        const polyline = new kakao.maps.Polyline({
            path: this.linepath, // 선을 구성하는 좌표배열 입니다
            strokeWeight: 5, // 선의 두께 입니다
            strokeColor: '#FFAE00', // 선의 색깔입니다
            strokeOpacity: 0.7, // 선의 불투명도 입니다 1에서 0 사이의 값이며 0에 가까울수록 투명합니다
            strokeStyle: 'solid' // 선의 스타일입니다
        });
        polyline.setMap(this.map)
        this.polylines.push(polyline)
      } 

      if(point == this.checkpoint_sequence.length - 1) {
        const polyline = new kakao.maps.Polyline({
            path: this.linepath, // 선을 구성하는 좌표배열 입니다
            strokeWeight: 5, // 선의 두께 입니다
            strokeColor: '#FFAE00', // 선의 색깔입니다
            strokeOpacity: 0.7, // 선의 불투명도 입니다 1에서 0 사이의 값이며 0에 가까울수록 투명합니다
            strokeStyle: 'solid' // 선의 스타일입니다
        });
        polyline.setMap(this.map)
        this.polylines.push(polyline)
      }
    },
    // 삭제 완료
    path_delete(id) {
      Axios.delete(`/api/dlvy/management/path/${id}`)
      .then(res => {
        this.path_one_all = res.data
        this.initialize()
      })
      .catch(err => {
        console.log(err)
      })
    },
    // 수정 checkpoint_sequence 전달만 하면 끝
    path_update(id) {
      let checkpoint_id = []
      for(let i = 0; i < this.checkpoint_sequence.length; i++) {
         checkpoint_id.push(this.checkpoint_sequence[i].check_id)
      }
      console.log(checkpoint_id)
      Axios.patch(`/api/dlvy/management/path/${id.path_id}`, {
        checkpoint_id: checkpoint_id
      })
      .then(res => {
        this.path_one_all = res.data
        this.initialize()
      })
    },
    // 리스트 클릭
    path_click(path_one) {
      this.path_check = path_one.path_id // 클릭한 리스트 구별
      this.station_stop = 2 // 정류장 클릭 금지
      
      // path_id 넘겨서 path_check 데이터 받음
      axios.get(`/api/dlvy/management/pathcheck/${this.path_check}`)
      .then(res => {
        this.checkpoint_num = res.data.length
        this.checkpoint_sequence = res.data
        this.checkpoint_start(res.data)
        this.station_delete(path_one.path_start_point, path_one.path_end_point)
      })
      .catch(err => {
        console.log(err)
      })
    },
    // 취소, 삭제, 수정 누를 시
    initialize() {
      for(let i = 0; i < this.polylines.length; i++) {
        this.polylines[i].setMap(null)
      }
      for(let i = 0; i < this.checkpoint_markers_all.length; i++) {
        this.checkpoint_markers_all[i].setMap(null)
      }
      for(let i = 0; i < this.station_markers.length; i++) {
        this.station_markers[i].setMap(null)
      }
      for(let i = 0; i < this.overlay_data.length; i++) {
        this.overlay_data[i].setMap(null)
      }

      this.checkpoint_num = 0
      this.checkpoint_update_num = 0
      this.distance = 0
      this.station_stop = 1
      this.path_check = ""
      this.station_markers = []
      this.checkpoint_markers_clicked = []
      this.checkpoint_markers_all = []
      this.overlay_data = []
      this.coordinates = []
      this.polylines = []
      this.linepath = []
      this.initMap()
    }
  }
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