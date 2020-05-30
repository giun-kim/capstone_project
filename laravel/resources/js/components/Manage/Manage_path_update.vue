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
            <div v-if="path_one.path_id == path_check"> <!-- 클릭한 리스트 -->
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
                <b-button type="button" @click="initialize(1)">취소하기</b-button>
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
    // 정류장과 체크포인트 데이터 불러오기
    Axios.get('/api/dlvy/management/path')
    .then(res => {
      this.station_all = res.data.station_all
      this.checkpoint_all = res.data.checkpoint_all
      this.initMap();
    })
  },
  data() {
    return {
      stage: 1, // 단계별 보여지는 화면 stage = 1 : 정류장 클릭, stage = 2 : 경로 클릭 및 정보
      map_stage: 1, // 카카오맵 생성 제한 map_stage = 1 : 맵 생성, map_stage = 2 : 맵 생성 안함
      station_all: "", // 정류장 데이터
      checkpoint_all: "", // 체크포인트 데이터
      path_one_all: "", // 모든 경로 데이터
      path_start_point: [], // 클릭한 정류장 관련 경로 데이터
      checkpoint_sequence: [], // 체크포인트 순서 및 아이디
      checkpoint_markers_clicked: [], // 클릭 금지
      station_markers: [], // 마커 저장
      checkpoint_markers_all: [], // 전체 마커 저장
      overlay_data: [], // 모든 오버레이 데이터
      polylines: [], // 폴리라인 길 표시 
      linepath: [], // 선을 구성하는 좌표 배열
      path_check: "", // 경로 구별
      station_stop: 1, // 정류장 클릭 막기
      station_end_point: "", // 다른 경로 클릭했을 시 삭제할 정류장
      checkpoint_num: 0, // 체크포인트 수
      checkpoint_update_num: 0, // 체크포인트 업데이트 순서
      distance: 0, // 거리
      checkpoint_stop: 0, // 체크포인트 생성 방지
      map: "", // 맵
    };
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
        }
        this.map = new kakao.maps.Map(container, options) // 맵 설정
        this.map_stage = 2
      }

      // 체크포인트 마커 이미지
      var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png"

      // 모든 정류장 마커 생성
      for (let i = 0, len = this.station_all.length; i < len; i++) {
        // 정류장 마커 생성
        const marker = new kakao.maps.Marker({
          position: new kakao.maps.LatLng(this.station_all[i].station_lat, this.station_all[i].station_lon), // 마커를 표시할 위치
        })

        // 정류장 인포 윈도우 생성
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
          if(this.station_stop == 1) {
            if(this.stage != 2)
                this.stage += 1

            // 클릭한 정류장과 관련된 모든 경로 서버에서 불러오기
            Axios.get(`/api/dlvy/management/path/${this.station_all[i].station_name}`)
            .then(res => {
                this.path_one_all = res.data // 해당 정류장의 모든 경로
            })
            .catch(err => {
                console.log(err)
            })
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

    // 체크포인트 표시 함수
    checkpoint_start(list) {
      // 체크포인트 마커 이미지
      var imageSrc = "https://t1.daumcdn.net/localimg/localimages/07/mapapidoc/markerStar.png"

      // 모든 체크포인트 마커 생성
      for (let i = 0, len = this.checkpoint_all.length; i < len; i++) {
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

        // 체크포인트 중복 클릭 방지
        for(let i = 0, len = this.checkpoint_all.length; i < len; i++) {
          this.checkpoint_markers_clicked.push(1)
        }

        // 체크포인트 마커 표시 및 마커 배열 푸시
        marker.setMap(this.map)
        this.checkpoint_markers_all.push(marker)

        // 불러온 체크포인트 표시
        if(this.checkpoint_sequence.length > i) {
          this.checkpoint_custom_overlay() // 해당 리스트 체크포인트 순서 출력
          this.check(i) // 거리 및 폴리라인 표시 
        }

        // 체크포인트 클릭 마커 이벤트
        kakao.maps.event.addListener(this.checkpoint_markers_all[i], "click", () => {
            // 클릭한 체크포인트 불러오기
            let click_check = this.checkpoint_sequence.filter((item) => {
              return item.check_id === this.checkpoint_all[i].checkpoint_id
            })

            // 클릭할 체크포인트 초기화
            if(click_check.length == 0) {
              click_check.push({
                check_id: "",
                sequence: ""
              })
            }

            // 체크포인트 삭제
            if(click_check[0].check_id == this.checkpoint_all[i].checkpoint_id) { 
              // 체크포인트 번호, 커스텀 오버레이, 순서 배열 데이터
              let indexnumber = this.checkpoint_sequence.findIndex((item) => item.check_id == this.checkpoint_all[i].checkpoint_id)
              let overlay_data_copy = Array.prototype.slice.call(this.overlay_data)
              let checkpoint_sequence_copy = Array.prototype.slice.call(this.checkpoint_sequence)

              // 체크포인트 오버레이 삭제
              for(let i = indexnumber, len = this.checkpoint_sequence.length; i < len; i++) {
                this.checkpoint_update_num -= 1
                this.checkpoint_num = this.checkpoint_update_num
                this.overlay_data[i].setMap(null)
              }

              // 삭제된 후 오버레이 데이터, 체크포인트 순서 배열 설정
              overlay_data_copy.splice(indexnumber,this.checkpoint_sequence.length-1)
              this.checkpoint_sequence.splice(indexnumber, this.checkpoint_sequence.length)
              this.overlay_data = overlay_data_copy
            } 
            // 체크포인트 추가 
            else { 
              // 체크포인트 순서 및 커스텀 오버레이 추가
              click_check.splice(click_check.length, 1)
              this.checkpoint_sequence.push({ check_id : this.checkpoint_all[i].checkpoint_id, sequence : this.checkpoint_update_num + 1 }) 
              this.checkpoint_custom_overlay(this.checkpoint_update_num) 
              this.checkpoint_num = this.checkpoint_update_num
            }

            // 체크포인트 폴리라인 삭제
            for(let i = 0, len = this.polylines.length; i < len; i++) {
              this.polylines[i].setMap(null)
            } 

            // 좌표배열, 거리 초기화
            this.linepath = []
            this.distance = 0

            // 삭제된 후 체크포인트 경로 설정
            for(let i = 0, len = this.checkpoint_sequence.length; i < len; i++) {
              this.check(i)
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

    // 체크포인트 오버레이 함수
    checkpoint_custom_overlay() {
      // 커스텀 오버레이 번호
      this.checkpoint_update_num = this.checkpoint_update_num + 1

      // 커스텀 오버레이 내용
      const content =
      "<div style='margin-bottom:36px;'>" +
      "  <span style='font-size:20px; font-weight:bold; color:red'>" +
      (this.checkpoint_update_num) +
      "</span>" +
      "</div>"

      // 클릭한 체크포인트 아이디
      const coordinate = this.checkpoint_all.filter((item) => {
        return item.checkpoint_id === this.checkpoint_sequence[this.checkpoint_update_num - 1].check_id
      })

      // 커스텀오버레이 위치 설정
      const position = new kakao.maps.LatLng(
        coordinate[0].checkpoint_lat,
        coordinate[0].checkpoint_lon
      )

      // 커스텀오버레이 설정
      let customOverlay = new kakao.maps.CustomOverlay({
        position: position,
        content: content,
        yAnchor: 1,
      })

      // 커스텀오버레이 표시 및  배열 푸시
      customOverlay.setMap(this.map)
      this.overlay_data.push(customOverlay)
    },

    // 클릭한 정류장을 제외한 나머지 정류장 삭제 함수
    station_delete(start_point, end_point) {
      for (let i = 0; i < this.station_markers.length; i++) {
        if(start_point == this.station_all[i].station_name || end_point == this.station_all[i].station_name)
          continue
        this.station_markers[i].setMap(null)
      }
    },

    // 거리 계산 및 폴리라인 표시
    check(point) { 
      if(point != 0) {
        // 체크포인트 거리 비교 필요
        var checkpoint_markers_previous = this.checkpoint_all.filter((item) => {
          return item.checkpoint_id === this.checkpoint_sequence[point-1].check_id
        }) 
        // 체크포인트 좌표
        var chk_prev_lat = checkpoint_markers_previous[0].checkpoint_lat
        var chk_prev_lon = checkpoint_markers_previous[0].checkpoint_lon
      }

      // 클릭한 체크포인트 아이디, 위도, 좌표
      var checkpoint_markers_clicked = this.checkpoint_all.filter((item) => {
        return item.checkpoint_id === this.checkpoint_sequence[point].check_id
      }) 
      var checkpoint_lat = checkpoint_markers_clicked[0].checkpoint_lat
      var checkpoint_lon = checkpoint_markers_clicked[0].checkpoint_lon

      // 정류장 두 곳 확인
      var station_clicked = this.path_one_all.filter((item) => {
        return item.path_id === this.path_check}) 

      // 첫번째 정류장 위도 좌표
      var station_start = this.station_all.filter((item) => {
        return item.station_name === station_clicked[0].path_start_point
      }) 

      // 두번째 정류장 위도 좌표
      var station_end = this.station_all.filter((item) => {
        return item.station_name === station_clicked[0].path_end_point
      }) 

      // 클릭한 두 정류장 좌표
      let stn_start_lat = station_start[0].station_lat
      let stn_start_lon = station_start[0].station_lon
      let stn_end_lat = station_end[0].station_lat
      let stn_end_lon = station_end[0].station_lon

      // 첫번째 정류장과 체크포인트 거리
      if(point == 0) {
        this.distance = this.distance + this.getDistance(stn_start_lat,stn_start_lon,checkpoint_lat,checkpoint_lon).toFixed(3) * 1000
        this.linepath.push(new kakao.maps.LatLng(stn_start_lat, stn_start_lon))
        this.linepath.push(new kakao.maps.LatLng(checkpoint_lat, checkpoint_lon))
        if(this.checkpoint_sequence.length == 1) {
          this.distance = this.distance + this.getDistance(checkpoint_lat, checkpoint_lon, stn_end_lat, stn_end_lon).toFixed(3) * 1000
          this.linepath.push(new kakao.maps.LatLng(stn_end_lat, stn_end_lon))
        }
      } 
      // 체크포인트 간 거리
      else if(point == this.checkpoint_sequence.length - 1) {
        this.distance = this.distance + this.getDistance(chk_prev_lat, chk_prev_lon, checkpoint_lat, checkpoint_lon).toFixed(3) * 1000
        this.distance = this.distance + this.getDistance(checkpoint_lat, checkpoint_lon, stn_end_lat, stn_end_lon).toFixed(3) * 1000
        this.linepath.push(new kakao.maps.LatLng(checkpoint_lat, checkpoint_lon))
        this.linepath.push(new kakao.maps.LatLng(stn_end_lat, stn_end_lon))
      } 
      // 체크포인트와 두번째 정류장 거리
      else {
        this.distance = this.distance + this.getDistance(chk_prev_lat, chk_prev_lon, checkpoint_lat, checkpoint_lon).toFixed(3) * 1000
        this.linepath.push(new kakao.maps.LatLng(checkpoint_lat, checkpoint_lon))
      }

      // 거리 계산 끝낸 후 폴리라인 설정 및 표시
      if(point == this.checkpoint_sequence.length - 1) {
        const polyline = new kakao.maps.Polyline({
            path: this.linepath, // 선을 구성하는 좌표배열
            strokeWeight: 5, // 선의 두께
            strokeColor: '#FFAE00', // 선의 색깔
            strokeOpacity: 0.7, // 선의 불투명도 입니다 1에서 0 사이의 값이며 0에 가까울수록 투명
            strokeStyle: 'solid' // 선의 스타일
        })

        // 폴리라인 표시 및 배열 푸시
        polyline.setMap(this.map)
        this.polylines.push(polyline)
      }
    },

    // 리스트 클릭 함수
    path_click(path_one) {
      // 체크포인트 생성 방지
      this.checkpoint_stop += 1
      if(this.checkpoint_stop >= 2)
        this.initialize(2)

      // 클릭한 리스트 구별
      this.path_check = path_one.path_id
      this.station_stop = 2 // 정류장 클릭 금지
      
      // 경로 클릭 아이디에 관련된 데이터 불러오기
      axios.get(`/api/dlvy/management/pathcheck/${this.path_check}`)
      .then(res => {
        this.checkpoint_num = res.data.length
        this.checkpoint_sequence = res.data 
        this.checkpoint_start(this.checkpoint_stop)
        this.station_delete(path_one.path_start_point, path_one.path_end_point)
      })
      .catch(err => {
        console.log(err)
      })
    },

    // 경로 삭제 함수
    path_delete(id) {
      // 경로 삭제 아이디 서버로 보내기
      Axios.delete(`/api/dlvy/management/path/${id}`)
      .then(res => {
        this.path_one_all = res.data
        this.initialize(1)
      })
      .catch(err => {
        console.log(err)
      })
    },

    // 경로 수정 함수
    path_update(id) {
      // 체크포인트 순서 및 아이디
      let checkpoint_id = []
      for(let i = 0, len = this.checkpoint_sequence.length; i < len; i++) {
         checkpoint_id.push(this.checkpoint_sequence[i].check_id)
      }

      // 체크포인트 순서 및 아이디 서버로 보내기
      Axios.patch(`/api/dlvy/management/path/${id.path_id}`, {
        checkpoint_id: checkpoint_id
      })
      .then(res => {
        this.path_one_all = res.data
        this.initialize(1)
      })
      .catch(err => {
        console.log(err)
      })
    },

    // 데이터 초기화
    initialize(id) {
      // 모든 폴리라인 삭제
      for(let i = 0, len = this.polylines.length; i < len; i++) {
        this.polylines[i].setMap(null)
      }

      // 모든 체크포인트 마커 삭제
      for(let i = 0, len = this.checkpoint_markers_all.length; i < len; i++) {
        this.checkpoint_markers_all[i].setMap(null)
      }

      // 모든 정류장 마커 삭제
      for(let i = 0, len = this.station_markers.length; i < len; i++) {
        this.station_markers[i].setMap(null)
      }

      // 모든 오버레이 삭제
      for(let i = 0, len = this.overlay_data.length; i < len; i++) {
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
      this.polylines = []
      this.linepath = []
      if(id == 1)
        this.checkpoint_stop = 0
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