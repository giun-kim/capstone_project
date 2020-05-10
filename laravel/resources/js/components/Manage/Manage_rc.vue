<template>
  <b-col align="right">
    <table border="2" style="margin: auto">
            <tr>
                <td>car_num</td>
                <td>car_name</td>
                <td>수정</td>
                <td>삭제</td>
            </tr>
            <tr v-for="item in items" :key="item.index">
                <td v-if="item.car_num == '' || update == item.car_num ? true : false"><input type="text" v-model="car_num" required/></td>
                <td v-if="item.car_num == '' || update == item.car_num ? true : false"><input type="text" v-model="car_name" required/></td>
                <td v-if="item.car_num != '' && update != item.car_num">{{ item.car_num }}</td>
                <td v-if="item.car_num != '' && update != item.car_num">{{ item.car_name }}</td>
                <td><button v-if="create_id == 1" @click="rc_update(items.indexOf(item))">수정</button></td>
                <td><button @click="create_id == 1 && update_id == 1 ? rc_delete(items.indexOf(item)) : cancel()">{{create_id == 1 && update_id == 1 ? '삭제' : '취소'}}</button></td>
            </tr>
        </table> 
        <br />
        <button v-if="update_id == 1" @click="rc_create()">등록</button>
  </b-col>
</template>

<script>
  export default {
    mounted() {
      Axios.get('/api/car')
      .then(res => {
        console.log(res.data.car)
      })
    },
    data() {
      return {
        create_id : 1, // 등록하기 순서
        update_id : 1, // 수정하기 순서
        items : data, // rc카 데이터
        car_num : '', // rc카 제품번호
        car_name : '', // rc카 이름
        update : ''
      }
    },
    methods : {
      rc_create(e) {
        if(this.create_id == 1) {
          this.items.push({
            car_num : '',
            car_name : ''
          })
          this.create_id += 1
        } else if(this.create_id == 2) {
          if(data[data.length-1].car_num == '') {
            this.items.splice(this.items.length-1, 1)
          }
            this.items.push({
              car_num : this.car_num,
              car_name : this.car_name
            })
            this.car_num = '',
            this.car_name = ''
          this.create_id = 1
        }
      },
      rc_update(num) {
        if(this.update_id == 1) {
          this.update_id += 1
          this.update = this.items[num].car_num
        } else if(this.update_id == 2) {
            data[num].car_num = this.car_num,
            data[num].car_name = this.car_name
            this.car_num = ''
            this.car_name = ''
            this.update = ''
            this.update_id = 1
        }
      },
      rc_delete(num) {
        this.items.splice(num, 1)
      },
      cancel() {
        if(this.items[this.items.length-1].car_name == '') {
          this.items.splice(this.items.length-1, 1)
        }
        this.items = data
        this.update = ''
        this.update_id = 1
        this.create_id = 1
      }
    }
  }
</script>