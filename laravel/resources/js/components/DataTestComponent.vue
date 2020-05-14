<template>
    <div class="container">
        <button v-on:click="test1()">등록</button>
        <button v-on:click="test2()">수정</button>
        
        <button v-on:click="test3()">삭제</button>
        <button v-on:click="test4()">로드</button>
        <button v-on:click="test5()">정류장 클릭</button>
        <button v-on:click="test6()">리스트 클릭</button>

    </div>
</template>
<script>
export default {
    mounted() {
        console.log('데이터 테스트 페이지.')
    },methods: {
        test1(){
            console.log('등록')
            Axios.post('/api/dlvy/management/path',{path_start_point:'연서관', path_end_point:'본관', checkpoint_id:[3, 4, 7, 6]} )
            .then(response=>{
                console.log(response.data)
            })
        },
         test2(){
            console.log('수정')
            Axios.patch('/api/dlvy/management/path/id',{station_name:'test3',  station_lat:35.1111, station_lon:128.2222})
            .then(response=>{
                console.log(response.data)
            })
        },
         test3(){
            console.log('삭제')
            Axios.delete('/api/dlvy/management/path/14')
            .then(response=>{
                console.log(response.data)
            })
        }, test4(){
            console.log('페이지 로드')
            Axios.get('/api/dlvy/management/path')
            .then(response=>{
                console.log(response.data)
            })
        },
        test5(){
            console.log('정류장 클릭')
            Axios.get('/api/dlvy/management/path/연서관')
            .then(response=>{
                console.log(response.data)
            })
        },
        test6(){
            console.log('리스트 클릭')
            Axios.get('/api/dlvy/management/path/16')
            .then(response=>{
                console.log(response.data)
            })
        },
    },data(){
        return{

        };
    }
}
</script>