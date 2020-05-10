<template>
    <div>
        <b-container>
            id : {{ id }}
            {{ data }}
            {{ data[stn_id] ? data[stn_id] : '' }}
            {{ markers }}
            {{ stage }}
            <b-row>
                <b-col>
                    <b-row v-if="id == 1"> <!-- 등록 -->
                        <p v-if="stage == 1">지도에서 원하는 위치를 클릭해 주세요.</p> 
                        <div v-if="stage == 2">
                            <b-form @submit="onSubmit">
                                <p>정류장 이름</p>
                                <b-form-input v-model="stn_name" placeholder="정류장을 입력해 주세요."></b-form-input>
                                <p>위도</p>
                                <p>{{ lat }}</p>
                                <p>경도</p>
                                <p>{{ lng }}</p>
                                <b-button type="submit" variant="primary">등록하기</b-button>
                                <b-button type="button" >취소</b-button>
                            </b-form>
                        </div>
                    </b-row>
                    <b-row v-if="id == 0"> <!-- 수정 -->
                        <p v-if="stage == 1">지도에서 수정/삭제할 RC카를 클릭해 주세요.</p>
                        <div v-if="stage == 2 || stage == 3">
                            <b-form>
                                <p>정류장 이름</p>
                                <b-form-input v-model="stn_name" :placeholder="stn_name" ></b-form-input>
                                <p>위도</p>
                                <p id="lat">{{ lat }}</p>
                                <p>경도</p>
                                <p id="lng">{{ lng }}</p>
                                <b-button type="submit" variant="primary" @click="stn_update()">수정하기</b-button>
                                <b-button type="button" @click="stn_delete()">삭제하기</b-button>
                            </b-form>
                        </div>
                    </b-row>
                </b-col>
                <b-col cols="7">
                    <div id="map" @click="map_click()"></div> 
                </b-col>
            </b-row>
        </b-container>
    </div>
</template>

<script>
import data from './data/station'
    export default {
        props : ['id'],
        mounted() {
            if(data[this.length-1].stn_name != '' && this.id == 1) {
                this.data.push({
                    stn_id : '',
                    stn_name : '',
                    latlng : ''
                })
            } else if(data[this.length-1].stn_name == '' && this.id == 0) {
                data.splice(this.length-1, 1)
            }
            
            window.kakao && window.kakao.maps ? this.initMap() : this.addScript();
        }, 
        data() {
            return {
                id : -1, // 1 : 등록, 2 : 수정/삭제
                stage : 1,
                stn_name : '',
                lat : '',
                lng : '',
                data : data,
                length : data.length,
                stn_id : ''
            }
        },
        methods : {
            initMap() {
                var container = document.getElementById('map');
                var options = {
                    center: new kakao.maps.LatLng(35.896309, 128.621917), 
                    level: 2
                };
                let map = new kakao.maps.Map(container, options);
                this.map = map;

                var contact = this

                var markers = []

                // 여러 개 마커 생성하기
                for (let i = 0; i < this.data.length; i ++) {
                    // 마커를 생성합니다
                    if(this.id == 1) {
                        var marker = new kakao.maps.Marker({
                            map: map, // 마커를 표시할 지도
                            position: data[i].latlng ? data[i].latlng : '', // 마커를 표시할 위치
                            title : data[i].stn_name ? data[i].stn_name : '',  // 마커의 타이틀, 마커에 마우스를 올리면 타이틀이 표시됩니다
                        })
                    } else if(this.id == 0) {
                        var marker = new kakao.maps.Marker({
                            map: map, // 마커를 표시할 지도
                            position: data[i].latlng, // 마커를 표시할 위치
                            title : data[i].stn_name,  // 마커의 타이틀, 마커에 마우스를 올리면 타이틀이 표시됩니다
                        })
                        markers.push(marker)

                        console.log(markers[0])

                        kakao.maps.event.addListener(markers[i], 'click', function() { 
                            // 클릭한 위도, 경도 정보를 가져옵니다 
                            contact.stage = 2
                            contact.stn_id = i                    
                        })

                        kakao.maps.event.addListener(map, 'click', function(mouseEvent) { // 문제점 맵 선택
                            var a = 1
                            if(contact.stage == 2) {
                                let latlng = mouseEvent.latLng
                                if(a == 1) {
                                    contact.lat = latlng.getLat()
                                    contact.lng = latlng.getLng()
                                    a +=1
                                }
                            } else if (contact.stage == 3) {
                                // 클릭한 위도, 경도 정보를 가져옵니다 
                                let latlng = mouseEvent.latLng
                    
                                // 마커 위치를 클릭한 위치로 옮깁니다
                                markers[i].setPosition(latlng)

                                contact.lat = latlng.getLat()
                                contact.lng = latlng.getLng()
                            }
                        })
                    }
                }
            
                // 마커 표시
                marker.setMap(map)
                if(this.id == 1) {
                    kakao.maps.event.addListener(map, 'click', function(mouseEvent) { 
                        // 클릭한 위도, 경도 정보를 가져옵니다 
                        let latlng = mouseEvent.latLng
            
                        // 마커 위치를 클릭한 위치로 옮깁니다
                        marker.setPosition(latlng)

                        contact.lat = latlng.getLat()
                        contact.lng = latlng.getLng()
                    })
                }
            },
            map_click() {
                if(this.stage == 1 && this.id == 1)
                    this.stage +=1
            },
            onSubmit() { // 등록
                data[this.data.length-1].stn_id = this.data.length
                data[this.data.length-1].stn_name = this.stn_name
                data[this.data.length-1].latlng = new kakao.maps.LatLng(this.lat, this.lng)
                this.$router.push({name:'Manage'})
            },
            stn_delete() { // 삭제
                data.splice(data[this.stn_id-1], 1)
            },
            stn_update() { // 수정
                if(this.stage == 2) {
                    this.stage += 1
                } else if(this.stage == 3) {
                    this.stage += 1
                }
                else if(this.stage == 4) {
                    var a = 1
                    data[this.stn_id].stn_name = this.stn_name
                    data[this.stn_id].latlng = new kakao.maps.LatLng(this.lat, this.lng)
                    this.$router.push({name:'Manage'})
                }
            }
        },
        watch : {
            id : function(id) {
                if(id == 0 || id == 1) {
                    window.kakao && window.kakao.maps ? this.initMap() : this.addScript()
                    this.stage = 1
                    this.stn_name = ''
                }
            },
            stn_id : function(stn_id) {
                this.lng = this.data[stn_id].latlng.Ga
                this.lat = this.data[stn_id].latlng.Ha
                this.stn_name = this.data[stn_id].stn_name
            }
        }
    }
</script>

<style scoped>
#map{
    width:65rem; 
    height:50rem;
}
</style>