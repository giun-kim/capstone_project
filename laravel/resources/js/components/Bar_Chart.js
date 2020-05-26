import {Bar, mixins} from 'vue-chartjs'
const { reactiveProp } = mixins
export default{
    extends: Bar,
    data () {
      return {
        // datacollection: {
        //   //Data to be represented on x-axis
        //   labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        //   datasets: [
        //     {
        //       label: 'Data One',
        //       backgroundColor: '#f87979',
        //       pointBackgroundColor: 'white',
        //       borderWidth: 1,
        //       pointBorderColor: '#249EBF',
        //       //Data to be represented on y-axis
        //       data: [40, 20, 30, 50, 90, 10, 20, 40, 50, 70, 90, 100]
        //     }
        //   ]
        // },
        //Chart.js options that controls the appearance of the chart
    //     options: {
    //       scales: {
    //         yAxes: [{
    //           ticks: {
    //             beginAtZero: true
    //           },
    //           gridLines: {
    //             display: true
    //           }
    //         }],
    //         xAxes: [ {
    //           gridLines: {
    //             display: true
    //           }
    //         }]
    //       },
    //       legend: {
    //         display: true
    //       },
    //       responsive: true,
    //       maintainAspectRatio: false
    //     }
    //   }
    // },
    // props:['chartdata'],
        options: { //Chart.js options
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true
              },
              gridLines: {
                display: true
              }
            }],
            xAxes: [ {
              gridLines: {
                display: false
              }
            }]
          },
          legend: {
            display: true
          },
          responsive: true,
          maintainAspectRatio: false
        }
      }
    },
    mixins : [reactiveProp],
    mounted () {
      //renderChart function renders the chart with the datacollection and options object.
      this.renderChart(this.chartData, this.options)
    }
  }