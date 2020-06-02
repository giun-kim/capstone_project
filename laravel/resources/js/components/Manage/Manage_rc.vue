<template>
  <b-col align="right">
    <!-- RC카 데이터 테이블 -->
    <b-table
      show-empty
      :fields="fields"
      :items="items"
      :per-page="perPage"
      :current-page="currentPage"
    >
      <!-- RC카 제품번호 -->
      <template v-slot:cell(car_num)="data">
        <!-- 등록버튼 클릭유무 -->
        {{ data.item.car_num == '' ? '' :  data.value }} 
        <!-- 등록할 시 인풋창 생성 -->
        <b-form-input 
          v-if="data.item.car_num == ''" 
          v-model="car_num" 
          :placeholder="update_car_num ? update_car_num : 'RC카 제품번호'">
        </b-form-input>
      </template>

      <!-- RC카 이름 -->
      <template v-slot:cell(car_name)="data">
        <!-- 등록버튼 클릭유무 및 수정버튼 클릭유무 => 활성화 및 비활성화 -->
        {{ data.item.car_name == update_car_name || data.item.car_name == '' ? '' : data.value }} 
        <!-- 등록 및 수정시 인풋창 생성 -->
        <b-form-input 
          v-if="data.item.car_name == update_car_name || data.item.car_name == ''" 
          v-model="car_name" 
          :placeholder="update_car_name ? update_car_name : 'RC카 이름'"></b-form-input>
      </template>

      <!-- RC카 수정버튼 -->
      <template v-slot:cell(update)="data">
        <!-- 수정버튼 누를 시 수정 입력창 생성 -->
        <!-- create_id => 1 : 등록전 2 : 등록중 update_id => 1 : 업뎃전 2 : 업뎃중 -->
        <b-button  
          type="button" 
          @click="updateclick(data.item)" 
          variant="info" 
          v-if="create_id != 2 && update_id != 2">수정</b-button>
        <!-- 수정 완료 버튼 누를 시 수정 -->
        <!-- update_car_num 업데이트되는 제품번호 => 수정 -->
        <b-button 
          type="button" 
          @click="updateclick(data.item)" 
          variant="info" 
          v-if="create_id != 2 && data.item.car_num == update_car_num">수정 완료</b-button>
      </template>

      <!-- RC카 삭제 및 취소버튼 -->
      <template v-slot:cell(delete)="data">
          <!-- 삭제버튼 -->
          <b-button 
            type="button" 
            @click="deleteclick(data.item.car_num)" 
            variant="danger" v-if="create_id != 2 && update_id != 2">삭제</b-button>
          <!-- 등록중이거나 수정중일 때 취소버튼 -->
          <b-button 
            type="button" 
            @click="cancelclick()" 
            v-if="(create_id == 2 || update_id == 2) && (data.item.car_num == '' || data.item.car_num == update_car_num)">취소</b-button>
      </template>
    </b-table>
    <!-- 페이지네이션 -->
    <b-pagination
      v-model="currentPage"
      :total-rows="rows"
      :per-page="perPage"
      align="center"
    ></b-pagination>
    <!-- 등록버튼 -->
    <!-- create_id => 1 : 등록전, 2 : 등록중 -->
    <b-button 
      type="button" 
      @click="createclick()" 
      variant="success" 
      v-if="update_id != 2"> {{ create_id == 1 ? "등록" : "등록 완료"}}</b-button>
  </b-col>
</template>

<script>
  export default {
    mounted() {
      // RC카 데이터 불러오기
      Axios.get('/api/dlvy/management/car')
      .then(res => {
        this.items = res.data.car_all
      })
    },
    data() {
      return {
        perPage: 5, // 페이지당 행 수
        currentPage: 1,
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
          label : ''
        }],
        items: [], // RC카 데이터
        create_id : 1, // 1: 등록전 2: 등록후 
        update_id : 1, // 1: 업뎃전 2: 업뎃후
        car_num : "", // RC카 제품번호
        car_name : "", // RC카 이름
        update_car_num : "", // 업데이트되는 RC카 제품번호
        update_car_name : "" // 업데이트되는 RC카 이름
      }
    },
    methods: {
      // RC카 수정 함수
      updateclick(car) {
        // RC카 처음 수정버튼 클릭시
        if(this.update_id == 1) {
          this.update_car_num = car.car_num
          this.update_car_name = car.car_name
          this.car_num = car.car_num
          this.car_name = car.car_name
          this.update_id += 1
        } 
        // RC카 데이터 수정후 수정버튼 클릭시
        else if(this.update_id == 2) {
          // RC카 수정 데이터 서버로 보내기
          Axios.patch(`/api/dlvy/management/car/${car.car_num}`, {
            car_name : this.car_name
          })
          .then(res => {
            this.update_id = 1
            this.update_car_num = ''
            this.update_car_name = ''
            this.car_num = ''
            this.car_name = ''
            this.items = res.data.car_all
          })
        }
      },

      // RC카 삭제 함수
      deleteclick(car_num) {
        // RC카 삭제 번호 서버로 보내기
        Axios.delete(`/api/dlvy/management/car/${car_num}`)
        .then(res => {
          this.items = res.data.car_all // rc카 데이터
        })
      },

      // 취소 클릭 함수 
      cancelclick(){
        // 데이터 초기화
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

      // RC카 생성 함수
      createclick() {
        // RC카 처음 등록버튼 클릭시
        if(this.create_id == 1) {
          this.currentPage = Math.floor((this.items.length + 5) / 5) 
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
        // RC카 데이터 입력후 생성버튼 클릭시
        else if (this.create_id == 2){
          // RC카 등록 데이터 보내기
          Axios.post('/api/dlvy/management/car', {
            car_num : this.car_num,
            car_name : this.car_name
          })
          .then(res => {
            this.create_id = 1
            this.items.splice(this.items.length-1, 1)
            this.items = res.data.car_all
            this.currentPage = Math.floor((this.items.length + 5) / 5)
            this.cancelclick()
          })
        }
      }
    },

    // RC카 데이터 변경시 데이터 길이 반환
    computed: {
      rows() {
        return this.items.length
      }
    }
  }
</script>