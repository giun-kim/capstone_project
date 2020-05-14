<template>
  <div style="margin-top: 20px">
    <b-container fluid class="ld-over">
      <b-row>
      <b-col>
      <b-card
        header="카테고리"
        style="width:215px; height:600px; position: absolute; top:0; left: 20; z-index:10; "
        class="mb-2"
        border-variant="info"
        align="center"
      >
        <b-form-group>
          <b-container>
            <b-col sm="12" align="center">
              <b-row>
                <h5>정류장</h5>
              </b-row>
              <b-row>
                <b-card-text style="cursor:pointer" variant="link" @click="station_create()">&nbsp;&nbsp; 등록하기</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="station_update()"
                >&nbsp;&nbsp; 수정/삭제</b-card-text>
                <br />
              </b-row>

              <b-row>
                <h5>경로</h5>
              </b-row>
              <b-row>
                <b-card-text>1.체크포인트 설정</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="checkpoint_create()"
                >&nbsp;&nbsp; 등록하기</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="checkpoint_update()"
                >&nbsp;&nbsp; 수정/삭제</b-card-text>
                <br />
              </b-row>
              <b-row>
                <b-card-text>2.경로 설정</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="path_create()"
                >&nbsp;&nbsp; 등록하기</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="path_update()"
                >&nbsp;&nbsp; 수정/삭제</b-card-text>
                <br />
              </b-row>

              
              <b-row>
                <b-card-text style="cursor:pointer" variant="link" @click="rc()">
                  <h5>RC카</h5>
                </b-card-text>
              </b-row>
            </b-col>
          </b-container>
        </b-form-group>
      </b-card>
      <router-view v-if="id == 1"></router-view>
      </b-col>
      <b-col v-if="id == 2" sm="10">
      <router-view></router-view>
      </b-col>
      </b-row>
    </b-container>
  </div>
</template>

<script>
export default {
  name: "Manage",
  mounted() {},
  data() {
    return {
      id : ''
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