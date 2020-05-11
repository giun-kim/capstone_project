<template>
  <b-col align="right">
    {{ data }}
    <table border="2" style="margin: auto">
            <tr>
                <td>car_num</td>
                <td>car_name</td>
                <td>수정</td>
                <td>삭제</td>
            </tr>
            <tr v-for="item in data" :key="item.index">
                <td v-if="item.car_num == ''"><input type="text" v-model="car_num" required/></td>
                <td v-if="item.car_num == '' || update == item.car_num ? true : false"><input type="text" v-model="car_name" required/></td>
                <td v-if="item.car_num != '' && update != item.car_num">{{ item.car_num }}</td>
                <td v-if="item.car_num != '' && update != item.car_num">{{ item.car_name }}</td>
                <td><button v-if="create_id == 1" @click="rc_update(item.car_num)">수정</button></td>
                <td><button @click="create_id == 1 && update_id == 1 ? rc_delete(items.indexOf(item)) : cancel()">{{create_id == 1 && update_id == 1 ? '삭제' : '취소'}}</button></td>
            </tr>
        </table> 
        <br />
        <button v-if="update_id == 1" @click="rc_create()">등록</button>
  </b-col>
</template>

<script>
// 미완성
  export default {
    mounted() {
      Axios.get('/api/dlvy/management/car')
      .then(res => {
        this.data = res.data.car;
      })
    },
    data() {
      return {
        create_id : 1, // 등록하기 순서
        update_id : 1, // 수정하기 순서
        data : "", // rc카 데이터
        car_num : "", // rc카 제품번호
        car_name : "", // rc카 이름
        update : "" // 업데이트
      }
    },
    methods : {
      rc_create(e) {
        if(this.create_id == 1) {
          this.data.push({
            car_num : '',
            car_name : ''
          })
          this.create_id += 1
        } else if(this.create_id == 2) {
          if(data[data.length-1].car_num == '') {
            this.items.splice(this.items.length-1, 1)
          }
          // 저장한 다음 -> 조회
          Axios.post('/api/dlvy/management/car', {
            car_num : this.car_num,
            car_name : this.car_name
          })
          .then(res => {
            this.data = res.data.car
            this.car_num = '',
            this.car_name = ''
            this.create_id = 1
          })
          .catch(err => {
            console.log(err)
          })
        }
      },
      rc_update(car_num) {
        if(this.update_id == 1) {
          this.update_id += 1
          this.update = this.data[car_num].car_num 
        } else if(this.update_id == 2) {
          Axios.patch(`/api/dlvy/management/car/${car_num}`, {
            car_name : this.car_name
          })
          .then(res => {
            this.data = res.data.car
            this.car_name = ''
            this.update = ''
            this.update_id = 1
          })
        }
      },
      rc_delete(num) {
        this.data.splice(num, 1)
      },
      cancel() {
        if(this.data[this.items.length-1].car_name == '') {
          this.data.splice(this.items.length-1, 1)
        }
        this.update = ''
        this.update_id = 1
        this.create_id = 1
      }
    }
  }
</script>