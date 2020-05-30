<template>
   <div class="container">
        <div class="left_container">
            <div class="left_container_item1">
                <b-card
                    header="카테고리"
                    header-tag="header"
                    style="max-width: 20rem;text-align:center;"
                    class="mb-2">
                    <div class="category_item_1">
                      <div style="text-align:center; color:gray; font-size:1.1em; margin-bottom:15px;">
                        정류장
                      </div>
                      <b-button class="button" @click="station_create()">
                        정류장 등록
                      </b-button>
                      <b-button class="button" @click="station_update()">
                        정류장 수정/삭제
                      </b-button>
                    </div>

                    <div class="category_item_2">
                      <div style="text-align:center; color:gray; font-size:1.1em; margin-bottom:15px; margin-top:15px;">
                        경로
                      </div>
                      <b-button class="button" @click="checkpoint_create()">
                        체크포인트 등록
                      </b-button>
                      <b-button class="button1" @click="checkpoint_update()">
                        체크포인트 수정/삭제
                      </b-button>
                      <b-button class="button" @click="path_create()">
                        경로 등록
                      </b-button>
                      <b-button class="button" @click="path_update()">
                        경로 수정/삭제
                      </b-button>
                    </div>

                    <div class="category_item_3">
                      <b-button class="button" @click="rc()">
                        RC카
                      </b-button>
                    </div>
                </b-card>
            </div>
        </div>
        <div class="right_container">
          <div class="right_container_item1">
            <!-- 지도 관리 페이지 -->
            <router-view v-if="id == 1"></router-view>

            <!-- 카테고리 선택 안 할 시 정중앙 배치 -->
            <b-col v-if="id == 0" style="line-height:400px; vertical-align:middle; left: 40%; position: absolute;">
             <font size="6em">원하는 카테고리를 선택해 주세요</font>
            </b-col>

            <!-- RC카 페이지 -->
            <b-col v-if="id == 2" sm="10">
              <router-view></router-view>
            </b-col>
          </div>
        </div>
    </div>
</template>

<script>
export default {
  name: "Manage",
  mounted() {},
  data() {
    return {
      id : 0
    };
  },
  methods: {
    station_create() {
      this.id = 1
      this.$router.push({ name: "Manage_station_create" }).catch(err => {});
    },
    station_update() {
      this.id = 1
      this.$router.push({ name: "Manage_station_update" }).catch(err => {});
    },
    path_create() {
      this.id = 1
      this.$router.push({ name: "Manage_path_create" }).catch(err => {});
    },
    path_update() {
      this.id = 1
      this.$router.push({ name: "Manage_path_update" }).catch(err => {});
    },
    rc() {
      this.id = 2
      this.$router.push({ name: "Manage_rc" }).catch(err => {});
    },
    checkpoint_create() {
      this.id = 1
      this.$router.push({ name: "Manage_checkpoint_create" }).catch(err => {});
    },
    checkpoint_update() {
      this.id = 1
      this.$router.push({ name: "Manage_checkpoint_update" }).catch(err => {});
    }
  }
};
</script>

<style scoped>
.container{
    display:grid;
    grid-template-columns: 16% 83%;
    width: 100%;
    max-width:1500px;
}
.left_container_item1{
  margin-top: 20px;
}
.category_item_1{
  border-bottom:2px solid #E6E9ED;
  padding: 10px;
  margin-right: 2%;
  margin-left: 2%;
  
}

.category_item_2{
  border-bottom:2px solid #E6E9ED;
  padding: 10px;
  margin-right: 2%;
  margin-left: 2%;
  
}
.button{
    color : black;
    background-color : white;
    border : none;
    margin-top : 5%;
}
.button1{
   color : black;
    background-color : white;
    border : none;
    margin-top : 5%;
    font-size: 0.9em;
}

.right_container_item1{
  margin-top:20px;
  border:1px solid #E6E9ED;
  margin-left: 40px;
 
}
</style>
