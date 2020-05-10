<template>
  <div style="margin-top: 20px">
    <b-container fluid class="ld-over">
      <router-view></router-view>
      <b-card
        header="카테고리"
        style="width:215px; height:600px; position: absolute; top:0; left: 20; z-index:10; margin-top: 100px"
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
                <b-card-text style="cursor:pointer" variant="link" @click="stn_create()">&nbsp; 등록하기</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="stn_update()"
                >&nbsp; 수정/삭제</b-card-text>
                <br />
              </b-row>
              <b-row>
                <h5>경로</h5>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="path_create()"
                >&nbsp; 등록하기</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="path_update()"
                >&nbsp; 수정/삭제</b-card-text>
                <br />
              </b-row>

              <b-row>
                <h5>체크포인트</h5>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="check_create()"
                >&nbsp; 등록하기</b-card-text>
              </b-row>
              <b-row>
                <b-card-text
                  style="cursor:pointer"
                  variant="link"
                  @click="check_update()"
                >&nbsp; 수정/삭제</b-card-text>
                <br />
              </b-row>

              <b-row>
                <b-card-text style="cursor:pointer" variant="link" @click="rc()">
                  &nbsp;
                  <h5>RC카</h5>
                </b-card-text>
              </b-row>
            </b-col>
          </b-container>
        </b-form-group>
      </b-card>
    </b-container>
  </div>
</template>

<script>
export default {
  name: "Manage",
  mounted() {},
  data() {
    return {};
  },
  methods: {
    stn_create() {
      this.$router.push({ name: "Manage_station_create" }).catch(err => {});
    },
    stn_update() {
      this.$router.push({ name: "Manage_station_update" }).catch(err => {});
    },
    path_create() {
      this.$router.push({ name: "Manage_path_create" }).catch(err => {});
    },
    path_update() {
      this.$router.push({ name: "Manage_path_update" }).catch(err => {});
    },
    rc() {
      this.$router.push({ name: "Manage_rc" }).catch(err => {});
    },
    check_create() {
      this.$router.push({ name: "Manage_check_create" }).catch(err => {});
    },
    check_update() {
      this.$router.push({ name: "Manage_check_update" }).catch(err => {});
    }
  }
};
</script>

<style scoped>
/* #map {
  width: 65rem;
  height: 50rem;
} */

/* .view-col {
  max-height: 600px;
} */
</style>