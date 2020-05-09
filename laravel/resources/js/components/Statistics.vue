<template>
    <div class="statistics">
        <div class="categori">
            <b-card
                header="카테고리"
                header-tag="header"
                style="max-width: 20rem;text-align:center;"
                class="mb-2">
                <b-button class="button">
                    배달 완료 건 수
                </b-button>
                <b-button class="button">
                    대기 완료 / 취소 건 수
                </b-button>
                <b-button class="button">
                    평균 대기 시간
                </b-button>
            </b-card>
        </div>
        <div class="content">
            <div class="title">
                <h1>{{graph_title}}</h1>
            </div>
            <div class="mode_button">
                <b-button>
                    누적
                </b-button>
                <b-button>
                    평균
                </b-button>
            </div>
            <div class="term_button">
                <b-button>
                    일간
                </b-button>
                <b-button>
                    주간
                </b-button>
                <b-button>
                    월간
                </b-button>
            </div>
            <div class="datepicker">
                <datepicker
                    :value="date">
                </datepicker>
            </div>
            <div class = "chart">
                <bar-chart></bar-chart>
            </div>
        </div>
    </div>
</template>

<script>
import BarChart from './Bar_Chart.js';
import Datepicker from 'vuejs-datepicker';

    export default {
        data(){
            return {
                graph_title : '대기 완료',
                date : new Date()
            }
        },
        components: {
            BarChart,
            Datepicker
        },
        mounted(){

        }
    }
</script>

<style>
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
    margin-right : 5%;
}
.datepicker{
    float : right;
    margin-right : 20%;
}

</style>