<template>
  <b-col align="right">
    <b-table
      show-empty
      :fields="fields"
      :items="items"
    >
      <template v-slot:cell(car_num)="data">
          {{ data.item.car_num == '' ? '' :  data.value }} <b-form-input v-if="data.item.car_num == ''" v-model="car_num" :placeholder="update_car_num ? update_car_num : 'RC카 제품번호'"></b-form-input>
      </template>

      <template v-slot:cell(car_name)="data">
          {{ data.item.car_name == update_car_name || data.item.car_name == '' ? '' : data.value }} <b-form-input v-if="data.item.car_name == update_car_name || data.item.car_name == ''" v-model="car_name" :placeholder="update_car_name ? update_car_name : 'RC카 이름'"></b-form-input>
      </template>

      <template v-slot:cell(update)="data">
          <b-button type="button" @click="updateclick(data.item)" variant="primary" v-if="create_id != 2 && update_id != 2">수정</b-button>
           <b-button type="button" @click="updateclick(data.item)" variant="primary" v-if="create_id != 2 && data.item.car_num == update_car_num">수정 완료</b-button>
      </template>

      <template v-slot:cell(delete)="data">
          <b-button type="button" @click="deleteclick(data.item.car_num)" variant="danger" v-if="create_id != 2 && update_id != 2">삭제</b-button>
          <b-button type="button" @click="cancelclick()" v-if="(create_id == 2 || update_id == 2) && (data.item.car_num == '' || data.item.car_num == update_car_num)">취소</b-button>
      </template>
    </b-table>
    <b-button type="button" @click="createclick()" variant="success" v-if="update_id != 2"> {{ create_id == 1 ? "등록" : "등록 완료"}}</b-button>
  </b-col>
</template>

<script>
// 미완성
  export default {
    mounted() {
      Axios.get('/api/dlvy/management/car')
      .then(res => {
        this.items = res.data.car_all
      })
    },
    data() {
      return {
        fields: [{
          key : 'car_num',
          label : 'car_num'
        },{
          key : 'car_name',
          label : 'car_name'
        },{
          key : 'update',
          label : 'update'
        },{
          key : 'delete',
          label : 'delete'
        }],
        items: [],
        create_id : 1, // 1: 등록전 2: 등록후 
        update_id : 1, // 1: 업뎃전 2: 업뎃후
        car_num : "", // RC카 제품번호
        car_name : "", // RC카 이름
        update_car_num : "", // 업데이트되는 RC카 제품번호
        update_car_name : "" // 업데이트되는 RC카 이름
      }
    },
    methods : {
      updateclick(car) {
        if(this.update_id == 1) {
          this.update_car_num = car.car_num
          this.update_car_name = car.car_name
          this.car_num = car.car_num
          this.car_name = car.car_name
          this.update_id += 1
        } else if(this.update_id == 2) {
          Axios.patch(`/api/dlvy/management/car/${car.car_num}`, {
            car_name : this.car_name
          })
          .then(res => {
            this.update_id = 1
            this.update_car_num = ''
            this.update_car_name = ''
            this.car_num = ''
            this.car_name = ''
            // this.items = res.data.car
          })
          .catch(err => {
            console.log(err)
          })
        }
      },
      deleteclick(car_num) {
        Axios.delete(`/api/dlvy/management/car/${car_num}`)
        .then(res => {
          // this.items = res.data.car
        })
        .catch(err => {
          console.log(err)
        })
      },
      cancelclick(){
        if(this.items[this.items.length-1].car_num == "") {
          this.items.splice(this.items.length-1, 1)
        }
        this.update_id = 1
        this.create_id = 1
        this.update_car_num = ''
        this.update_car_name = ''
        this.car_num = ''
        this.car_name = ''
      },
      createclick() {
        // 등록
        if(this.create_id == 1) {
          this.items.push({
            car_num : "",
            car_name : "",
            car_status : "",
            car_lat : "",
            car_lon : "",
            car_error : ""
          })
          this.create_id += 1
        }
        // 등록 완료
        else if (this.create_id == 2){
          Axios.post('/api/dlvy/management/car', {
            car_num : this.car_num,
            car_name : this.car_name
          })
          .then(res => {
            this.create_id = 1
            this.items.splice(this.items.length-1, 1)
            this.items = res.data.car
          })
          .catch(err => {
            console.log(err)
          })
        }
      }
    },
  }
</script>