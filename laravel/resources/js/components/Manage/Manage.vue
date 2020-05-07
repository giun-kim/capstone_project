<template>
    <div>
        <b-container fluid class="ld-over">
            {{ name }}
            <div></div>
            <b-row>
                <b-col cols="2">
                    <b-card
                    header="카테고리"
                    style="width:215px; height:600px;"
                    class="mb-2"
                    border-variant="info"
                    align="center">
                        <b-form-group>
                            <b-container>
                                <b-row class="my-1">
                                    <b-col sm="12">
                                        <b-col>
                                            <h5>정류장</h5>
                                        </b-col>
                                        <b-col>
                                            <b-button variant="link" @click="stn(1)">등록하기</b-button>
                                        </b-col>
                                        <b-col>
                                            <b-button variant="link" @click="stn(0)">수정/삭제</b-button>
                                        </b-col>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-col>
                                            <h5>경로</h5>
                                        </b-col>
                                        <b-col>
                                            <b-button variant="link" @click="path(1)">등록하기</b-button>
                                        </b-col>
                                        <b-col>
                                            <b-button variant="link" @click="path(0)">수정/삭제</b-button>
                                        </b-col>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-col>
                                            <h5>체크포인트</h5>
                                        </b-col>
                                        <b-col>
                                            <b-button variant="link" @click="check(1)">등록하기</b-button>
                                        </b-col>
                                        <b-col>
                                            <b-button variant="link" @click="check(0)">수정/삭제</b-button>
                                        </b-col>
                                    </b-col>
                                    <b-col sm="12">
                                        <b-col>
                                            <b-button variant="link" @click="rc(1)"><h5>RC카</h5></b-button>
                                        </b-col>
                                    </b-col> 
                                </b-row>
                            </b-container>
                        </b-form-group>
                    </b-card>
                </b-col>

                <router-view :id="id"></router-view>
            </b-row>
        </b-container>
    </div>
</template>

<script>
    export default {
        name : 'Manage',
        mounted() {
            
        }, 
        data() {
            return {
                name : '1',
                id : ''
            }
        },
        methods : {
            stn(e) {
                this.id = e
                this.$router.push({name:'Manage_station'}).catch(err =>{})
            },
            path(e) {
                this.id = e
                this.$router.push({name:'Manage_path'}).catch(err =>{})
            },
            rc(e) {
                this.id = e
                this.$router.push({name:'Manage_rc'}).catch(err =>{})
            },
            check(e) {
                this.id = e
                this.$router.push({name:'Manage_checkpoint'}).catch(err =>{})
            },
        },

    }
</script>

<style scoped>
#map{
    width:65rem; 
    height:50rem;
}

</style>