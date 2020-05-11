<template>
    <div class="container">
        <button v-on:click="test1()">페이지 로드</button>
        <button v-on:click="test2()">등록하기</button>
        <button v-on:click="test3()">수정하기</button>
        <button v-on:click="test4()">마커클릭</button>
        <button v-on:click="test5()">삭제하기</button>
        <button v-on:click="test6()">호출하기</button>
        <button v-on:click="test7()">동명이인 체크</button>

        <button v-on:click="test8()">보낸 배달</button>
    </div>
</template>
<script>
export default {
    mounted() {
        console.log('데이터 테스트 페이지.')
    },methods: {
        test1(){
            console.log('페이지 로드')
            Axios.get('/api/dlvy/control/show/'+5)
            .then(response=>{
                console.log(response.data)
            })
        },
        test2(){
            console.log('등록하기')
            Axios.post('/api/dlvy/management/car', {car_num:15856, car_name:'test_car'})
            .then(response=>{
                console.log(response.data)
            })
        },
        test3(){
            console.log('수정하기')
            Axios.patch('/api/dlvy/management/car/'+'15856', {car_name:'test_car_update'})
            .then(response=>{
                console.log(response.data)
            })
        },
        test4(){
            console.log('마커 클릭')
            Axios.get('/api/dlvy/management/car/'+'15856')
            .then(response=>{
                console.log(response.data)
            })
        },
        test5(){
            console.log('삭제하기')
            Axios.delete('/api/dlvy/management/car'+'/'+'15856')
            .then(response=>{
                console.log(response.data)
            })
        },
        
        
        test6(){
            console.log('호출하기')
            Axios.get('/api/dlvy/call')
            .then(response=>{
                console.log(response.data)
            })
        },
        test7(){
            console.log('유저체크')
            Axios.post('/api/dlvy/check_user/'+'김기운')
            .then(response=>{
                console.log(response.data)
            })
        },
        
        
        test8(){
            console.log('보낸 배달')
            Axios.get('/api/dlvy/send/'+'xidd')
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