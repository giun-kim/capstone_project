<template>
    <div class="statistics">
        <div class="categori">
            <b-card>
                    
            </b-card>
        </div>
        <div class = "chart">
            <bar-chart></bar-chart>
        </div>
    </div>
</template>

<script>
import BarChart from './Bar_Chart.js'
    export default {
        components: {
            BarChart
        },
    }
</script>

<style>
.categori{
    width : 30%;
    height : 60%;
}

.chart{
    float : right;
    width : 50%;
    height : 50%;
    margin-top : 5%;
    margin-right : 10%;
}
</style>