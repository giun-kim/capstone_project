<template>
    <div class="container">
        <div class="left_container">
            <div class="left_container_item1">
                <b-card
                    header="카테고리"
                    header-tag="header"
                    style="max-width: 20rem;text-align:center;"
                    class="mb-2">
                    <b-button class="button" @click="categori_change('complete')">
                        배달 완료 건 수
                    </b-button>
                    <b-button class="button" @click="categori_change('waiting_status')">
                        대기 완료 / 취소 건 수
                    </b-button>
                    <b-button class="button" @click="categori_change('waiting_time_avg')">
                        평균 대기 시간
                    </b-button>
                </b-card>
            </div>
        </div>
        <div class="right_container">
            <div class="right_container_item0">
                <h2>{{categori}}</h2>
            </div>
            <div class="right_container_item1">
                <b-button @click="clicked('acc', term)" v-if="term != 'day' & categori != '평균 대기 시간'">
                        누적
                </b-button>
                <b-button @click="clicked('avg', term)" v-if="term != 'day' & categori != '평균 대기 시간'">
                        평균
                </b-button>
                </div>
            <div class="right_container_item2">
                <vc-date-picker
                    v-if="term=='day'"
                    v-model="date"
                    @dayfocusin="selected"
                    :max-date='yesterday'
                    />
                <vc-date-picker
                    v-if="term=='week'"
                    :attributes='attributes'
                    @dayfocusin="selected"
                    :max-date='for_week_disable'
                    :masks="{ input: `${input_text}`}"
                    :input-props='{ //너무 길다..정리할 필요 있음..
                        placeholder: `${attributes[0].dates.start.getFullYear()}.${attributes[0].dates.start.getMonth()+1}.${attributes[0].dates.start.getDate()} ~ ${attributes[0].dates.end.getFullYear()}.${attributes[0].dates.end.getMonth()+1}.${attributes[0].dates.end.getDate()}`,
                    }'
                    />
                <vue-monthly-picker
                    v-if="term=='month'"
                    @selected="showDate"
                    :max="new Date(today.getFullYear(), today.getMonth()-1,1)"
                    v-model="selected_month"
                    />
            </div>
            <div class="right_container_item3">
                 <b-button @click="clicked(mode, 'day')">
                    일간
                </b-button>
                <b-button @click="clicked(mode, 'week')">
                    주간
                </b-button>
                <b-button @click="clicked(mode, 'month')">
                    월간
                </b-button>
            </div>
            <div class="right_container_item4">
                 <bar-chart
                    :chart-data="datacollection"/>
            </div>
        </div>
    </div>
</template>
<script>
import BarChart from './Bar_Chart.js';
import VueMonthlyPicker from 'vue-monthly-picker'

    export default {
        data(){
            return {
                categori : '배달 완료',
                date : new Date(),  //일간에서 계속 바뀔 date
                today : new Date(), //오늘 date
                for_week_disable : '', //주간에서 안보이게할 범위
                selected_month : new Date().getFullYear()+'/'+new Date().getMonth(),    //월별에서 처음에 넣을 데이터
                yesterday : '', //일간에서 어제부터 시작하기 위해
                mode : 'acc',
                term : 'day',
                input_text : '',    //주별에서 기간 표시하기 위해
                attributes: [       //주별에서 일주일 간격으로 색칠하기 위해
                    {
                    highlight: {
                        color: 'purple'
                    },
                    dates : {
                        start: '',     //여기 숫자들을 변수로 지정해서 바뀌게 하면됨.
                        end: '',
                    },
                    }
                ],
                complete_date : [],         //배달 완료 날짜
                complete_number : [],       //배달 완료 건 수
                waiting_status_date : [],   //대기 완료/취소 날짜
                waiting_complete : [],      //대기 완료 수
                waiting_cancel : [],        //대기 취소 수
                wait_time_avg_date : [],    //평균 대기 시간 날짜
                wait_time_avg : [],          //평균 대기 시간
                datacollection: null,
            }
        },
        components: {
            VueMonthlyPicker,
            BarChart
        },
        mounted(){
            this.yesterday = this.date.getTime() - (1 * 24 * 60 * 60 * 1000);
            this.date.setTime(this.yesterday)        //일간일 때 당일과, 이후는 클릭 못하게

            this.loaded();
            var week_start =  this.today.getDate() - (this.today.getDay()+7);   //주간 처음에 전 주가 초기 값이 되기 위해
            var week_end = this.today.getDate() - (this.today.getDay()+1);
            this.attributes[0].dates = {
                start : new Date(this.today.getFullYear(), this.today.getMonth(), week_start),
                end : new Date(this.today.getFullYear(), this.today.getMonth(),week_end)
            }
            this.for_week_disable = new Date(this.today.getFullYear(), this.today.getMonth(), week_end);
        },
        methods : {
            categori_change(val){                       //카테고리 변경
                if(val == 'complete'){
                    this.categori = "배달 완료";
                }else if(val == 'waiting_status'){
                    this.categori = "대기 완료/취소";
                }else if(val == 'waiting_time_avg'){
                    this.categori = "평균 대기 시간";
                }
                this.mode = "acc"
                this.term = "day"
                this.loaded();
            },
            clicked(mode, term){    //기간, 모드 변경
                this.mode = mode;
                this.term = term;
                this.loaded();      //기간, 모드 변경 시 그래프도 같이 바껴야됨.
            },
            selected(day){
                if(this.categori == "배달 완료"){   //날짜를 사용자가 바꿧을 때 해당 값으로 요청하기 위해
                    if(this.term == 'day'){     //this.date로 써서 한 번에 적을라고 했는데 캘린더에서 날짜 바꾸면 이벤트 파라미터 day와 this.date가 변하는 속도가 다름;;
                    Axios.get('/api/dlvy/statistics/complete/'+this.mode+'/'+this.term+'/'+this.getFormDate(day.date)) //배달 완료 건 수 첫 로딩
                    .then((response) => {
                        this.complete_date = response.data.date_info.reverse();
                        this.complete_number = response.data.statis_info.reverse();
                        this.datacollection = {
                                labels : this.complete_date,
                                datasets: [
                                    {
                                        label : '배달 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.complete_number
                                    },
                                ]
                        }
                    })
                    .catch(error => {
                        console.log(error)
                    })
                    }else if(this.term == 'week'){
                        if(day.date <= this.for_week_disable){
                            var week_start = day.day - (day.weekday-1); //선택한 날의 주 계산
                            var week_end = day.day + (7-day.weekday);
                            this.attributes[0].dates = {
                                start : new Date(day.year, day.month-1, week_start),
                                end : new Date(day.year, day.month-1,week_end)
                            }
                        }
                        this.loaded();
                    }
                }else if(this.categori == "대기 완료/취소"){
                    if(this.term == 'day'){
                        Axios.get('/api/dlvy/statistics/waitcancel/'+this.mode+'/'+this.term+'/'+this.getFormDate(day.date))
                        .then((response) => {
                            this.waiting_status_date = response.data.date_info.reverse();
                            this.waiting_complete = response.data.wait_count.reverse();
                            this.waiting_cancel = response.data.wait_cancel.reverse();
                            this.datacollection = {
                                labels : this.waiting_status_date,
                                datasets: [
                                    {
                                        label : '대기 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.waiting_complete
                                    },
                                    {
                                        label : '대기 취소 건 수',
                                        backgroundColor : '#2E2EFE',
                                        data : this.waiting_cancel
                                    }
                                ]
                            }
                        })
                    }else if(this.term == 'week'){
                        if(day.date <= this.for_week_disable){
                            var week_start = day.day - (day.weekday-1); //선택한 날의 주 계산
                            var week_end = day.day + (7-day.weekday);
                            this.attributes[0].dates = {
                                start : new Date(day.year, day.month-1, week_start),
                                end : new Date(day.year, day.month-1,week_end)
                            }
                        }
                        this.loaded();
                    }
                }else if(this.categori == "평균 대기 시간"){
                    if(this.term == 'day'){
                        Axios.get('/api/dlvy/statistics/waittimeavg/'+this.term+'/'+this.getFormDate(day.date))
                        .then((response) => {
                            this.wait_time_avg_date = response.data.date_info.reverse();
                            this.wait_time_avg = response.data.statis_info.reverse();
                            this.datacollection = {
                                labels : this.wait_time_avg_date,
                                datasets: [
                                    {
                                        label : '평균 대기 시간',
                                        backgroundColor: '#f87979',
                                        data : this.wait_time_avg
                                    },
                                ]
                            }
                        })
                    }else if(this.term == 'week'){
                        if(day.date <= this.for_week_disable){
                            var week_start = day.day - (day.weekday-1); //선택한 날의 주 계산
                            var week_end = day.day + (7-day.weekday);
                            this.attributes[0].dates = {
                                start : new Date(day.year, day.month-1, week_start),
                                end : new Date(day.year, day.month-1,week_end)
                            }
                        }
                        this.loaded();
                    }
                }
            },
            showDate(date){                     //월 간 날짜 바꿧을 때 요청하기 위해
                this.selected_month = date._i
                this.loaded();
            },
            getFormDate(date){
                var year = date.getFullYear();              //yyyy
                var month = (1 + date.getMonth());          //M
                month = month >= 10 ? month : '0' + month;  //month 두자리로 저장
                var day = date.getDate();                   //d
                day = day >= 10 ? day : '0' + day;          //day 두자리로 저장
                return  year + '-' + month + '-' + day;
            },
            loaded(){       //맨 첨에 로딩 될 때, 각종 카테고리, 모드, 기간 클릭했을 때 나오도록.
                if(this.categori == "배달 완료"){   //배달 완료 : 일간, 주간, 월간 버튼 눌렀을 시 로딩 되는 것.
                    if(this.term=='day'){
                        Axios.get('/api/dlvy/statistics/complete/'+this.mode+'/'+this.term+'/'+this.getFormDate(this.date)) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.complete_date = response.data.date_info.reverse();
                            this.complete_number = response.data.statis_info.reverse();
                            this.datacollection = {
                                labels : this.complete_date,
                                datasets: [
                                    {
                                        label : '배달 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.complete_number
                                    },
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });                        
                    }else if(this.term == 'week'){
                        Axios.get('/api/dlvy/statistics/complete/'+this.mode+'/'+this.term+'/'+this.getFormDate(this.attributes[0].dates.start)) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.complete_date = response.data.date_info.reverse();
                            this.complete_number = response.data.statis_info.reverse();
                            this.datacollection = {
                                labels : this.complete_date,
                                datasets: [
                                    {
                                        label : '배달 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.complete_number
                                    },
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    }else if(this.term == 'month'){
                        var parse_month = this.selected_month.split('/');
                        Axios.get('/api/dlvy/statistics/complete/'+this.mode+'/'+this.term+'/'+this.getFormDate(new Date(parse_month[0], parse_month[1]-1, 1))) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.complete_date = response.data.date_info.reverse();
                            this.complete_number = response.data.statis_info.reverse();
                            this.datacollection = {
                                labels : this.complete_date,
                                datasets: [
                                    {
                                        label : '배달 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.complete_number
                                    },
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    }
                }else if(this.categori == "대기 완료/취소"){
                    if(this.term=='day'){
                        Axios.get('/api/dlvy/statistics/waitcancel/'+this.mode+'/'+this.term+'/'+this.getFormDate(this.date)) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.waiting_status_date = response.data.date_info.reverse();
                            this.waiting_complete = response.data.wait_count.reverse();
                            this.waiting_cancel = response.data.wait_cancel.reverse();
                            this.datacollection = {
                                labels : this.waiting_status_date,
                                datasets: [
                                    {
                                        label : '대기 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.waiting_complete
                                    },
                                    {
                                        label : '대기 취소 건 수',
                                        backgroundColor : '#2E2EFE',
                                        data : this.waiting_cancel
                                    }
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });                        
                    }else if(this.term == 'week'){
                        Axios.get('/api/dlvy/statistics/waitcancel/'+this.mode+'/'+this.term+'/'+this.getFormDate(this.attributes[0].dates.start)) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.waiting_status_date = response.data.date_info.reverse();
                            this.waiting_complete = response.data.wait_count.reverse();
                            this.waiting_cancel = response.data.wait_cancel.reverse();
                            this.datacollection = {
                                labels : this.waiting_status_date,
                                datasets: [
                                    {
                                        label : '대기 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.waiting_complete
                                    },
                                    {
                                        label : '대기 취소 건 수',
                                        backgroundColor : '#2E2EFE',
                                        data : this.waiting_cancel
                                    }
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    }else if(this.term == 'month'){
                        var parse_month = this.selected_month.split('/');
                        console.log(new Date(parse_month[0], parse_month[1]-1, 1))
                        Axios.get('/api/dlvy/statistics/waitcancel/'+this.mode+'/'+this.term+'/'+this.getFormDate(new Date(parse_month[0], parse_month[1]-1, 1))) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.waiting_status_date = response.data.date_info.reverse();
                            this.waiting_complete = response.data.wait_count.reverse();
                            this.waiting_cancel = response.data.wait_cancel.reverse();
                            this.datacollection = {
                                labels : this.waiting_status_date,
                                datasets: [
                                    {
                                        label : '대기 완료 건 수',
                                        backgroundColor: '#f87979',
                                        data : this.waiting_complete
                                    },
                                    {
                                        label : '대기 취소 건 수',
                                        backgroundColor : '#2E2EFE',
                                        data : this.waiting_cancel
                                    }
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    }
                }else if(this.categori == "평균 대기 시간"){
                    if(this.term=='day'){
                        Axios.get('/api/dlvy/statistics/waittimeavg/'+this.term+'/'+this.getFormDate(this.date)) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            console.log(response.data)
                            this.wait_time_avg_date = response.data.date_info.reverse();
                            this.wait_time_avg = response.data.statis_info.reverse();
                            this.datacollection = {
                                labels : this.wait_time_avg_date,
                                datasets: [
                                    {
                                        label : '평균 대기 시간',
                                        backgroundColor: '#f87979',
                                        data : this.wait_time_avg
                                    },
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });                        
                    }else if(this.term == 'week'){
                        Axios.get('/api/dlvy/statistics/waittimeavg/'+this.term+'/'+this.getFormDate(this.attributes[0].dates.start)) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.wait_time_avg_date = response.data.date_info.reverse();
                            this.wait_time_avg = response.data.statis_info.reverse();
                            this.datacollection = {
                                labels : this.wait_time_avg_date,
                                datasets: [
                                    {
                                        label : '평균 대기 시간',
                                        backgroundColor: '#f87979',
                                        data : this.wait_time_avg
                                    },
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    }else if(this.term == 'month'){
                        var parse_month = this.selected_month.split('/');
                        console.log(new Date(parse_month[0], parse_month[1]-1, 1))
                        Axios.get('/api/dlvy/statistics/waittimeavg/'+this.term+'/'+this.getFormDate(new Date(parse_month[0], parse_month[1]-1, 1))) //배달 완료 건 수 첫 로딩
                        .then((response) => {
                            this.wait_time_avg_date = response.data.date_info.reverse();
                            this.wait_time_avg = response.data.statis_info.reverse();
                            this.datacollection = {
                                labels : this.wait_time_avg_date,
                                datasets: [
                                    {
                                        label : '평균 대기 시간',
                                        backgroundColor: '#f87979',
                                        data : this.wait_time_avg
                                    },
                                ]
                            }
                        })
                        .catch(error => {
                            console.log(error);
                        });
                    }
                }
            }
        },
        watch : {
            attributes : {
                deep: true,
                handler(){  //주별에서 선택했을 때 칸에 써줄거...너무 길다..따로 정리할 필요가 있겠다..
                    this.input_text = this.attributes[0].dates.start.getFullYear()+'.'+(this.attributes[0].dates.start.getMonth()+1)+'.'+this.attributes[0].dates.start.getDate()+" ~ "+ this.attributes[0].dates.end.getFullYear()+'.'+(this.attributes[0].dates.end.getMonth()+1)+'.'+this.attributes[0].dates.end.getDate();
                }
                
            }
        }
    }
</script>

<style scoped>

.container{
    display:grid;
    grid-template-columns: 22% 78%;
    width: 100%;
    max-width:1500px;
}

.left_container_item1{
    width: 70%;
    margin-top: 55%;
}

.right_container{
    display:grid;
    grid-template-columns: 30% 33% 37%;
    grid-template-rows: 3% 14% 81%;
}
.right_container_item0{
    display:grid;
    grid-column-start: 1;
    grid-column-end: 4;
    justify-content:center;
    margin-top: 30px;
    margin-right: 130px;
}
.right_container_item1{
    margin-top: 100px;
    margin-left: 50%;
}
.right_container_item2{
    margin-top: 80px;
    width: 40%;
    margin-left: 24%;
}
.right_container_item3{
    margin-top: 100px;

}

.right_container_item4{
    display: grid;
    grid-column-start: 1;
    grid-column-end: 4;
    height: 50%;
    margin-top: 5%;
    width: 90%;
  
}

.button{
    color : black;
    background-color : white;
    border : none;
    margin-top : 5%;
}

.categori{
    width : 20%;
    height : 60%;
    margin-left : 5%;
    margin-top : 5%;
    float : left;
}

.content{
    float : right;
    width : 70%;
    height : 100%;
}

.chart{
    float : right;
    width : 80%;
    height : 50%;
    margin-top : 5%;
    margin-right : 10%;
}

.button{
    color : black;
    background-color : white;
    border : none;
    margin-top : 5%
}

.title{
    float : right;
    margin-right : 45%;
    margin-top : 2%
}

.mode_button{
    margin-top : 10%;
    margin-left : 10%;
    display : inline-block;
}

.term_button{
    float : right;
    margin-left : 10%;
    margin-right : 10%;
}
.datepicker{
    float : right;
    margin-right : 3%;
}

</style>