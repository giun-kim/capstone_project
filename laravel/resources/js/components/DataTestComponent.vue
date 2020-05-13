<template>
    <div class="container">
        <button v-on:click="test1()">테스트</button>

    </div>
</template>
<script>
export default {
    mounted() {
        console.log('데이터 테스트 페이지.')
    },methods: {
        test1(){
            console.log('페이지 로드')
            Axios.get('/api/dlvy/completedlvy/test/all/0/0')
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