import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    stations: [
      {
        stn_id: 1,
        stn_name: "stn_1",
        latlng: new kakao.maps.LatLng(35.896705, 128.620973),
      },
      {
        stn_id: 2,
        stn_name: "stn_2",
        latlng: new kakao.maps.LatLng(35.896713, 128.622046),
      },
      {
        stn_id: 3,
        stn_name: "stn_3",
        latlng: new kakao.maps.LatLng(35.895331, 128.623376),
      },
    ],
  },
  mutations: {
    addStation(state, newStation) {
      state.stations.push(newStation);
    },
    removeStation(state, stationId) {
      state.stations = state.stations.filter(function(obj) {
        return obj.stn_id !== stationId;
      });
      let count = 1;
      for (let i = 0; i < state.stations.length; i++) {
        state.stations[i].stn_id = count;
        count += 1;
      }
    },
    updateStation(state, param) {
      for (let i = 0; i < state.stations.length; i++) {
        if (state.stations[i].stn_id == param.id) {
          state.stations[i] = param.station;
          break;
        }
      }
      let count = 1;
      for (let i = 0; i < state.stations.length; i++) {
        state.stations[i].stn_id = count;
        count += 1;
      }
    },
  },
  actions: {},
  modules: {},
});
