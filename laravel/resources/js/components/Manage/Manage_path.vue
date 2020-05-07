<template>
    <div>
        <b-container>
            <b-row>
                <b-col cols="3">
                    <p v-if="id == 1">
                        등록
                    </p>
                    <p v-if="id == 0">
                        수정/삭제
                    </p>
                </b-col>
            
                <b-col cols="7">
                    <div id="map"></div> 
                </b-col>
            </b-row>
        </b-container>
    </div>
</template>

<script>
    export default {
        props : ['id'],
        mounted() {
            window.kakao && window.kakao.maps ? this.initMap() : this.addScript();
        }, 
        data() {
            return {
                map_x : 35.896309,
                map_y : 128.621917,
                id : '', // 1 : 등록, 2 : 수정/삭제
            }
        },
        methods : {
            initMap() {
                var container = document.getElementById('map');
                var options = {
                    center: new kakao.maps.LatLng(this.map_x, this.map_y), 
                    level: 2
                };
                let map = new kakao.maps.Map(container, options);
                this.map = map;
                let marker = new kakao.maps.Marker({
                    position: ''
                });

                marker.setMap(map);
                kakao.maps.event.addListener(map, 'click', function(mouseEvent) {   
                    // 클릭한 위도, 경도 정보를 가져옵니다 
                    let latlng = mouseEvent.latLng; 
        
                    // 마커 위치를 클릭한 위치로 옮깁니다
                    marker.setPosition(latlng);

                    lat = latlng.getLat();
                    lng = latlng.getLng();     
                });
            },
        }
    }
</script>

<style scoped>
#map{
    width:65rem; 
    height:50rem;
}
</style>