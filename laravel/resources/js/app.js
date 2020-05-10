require('./bootstrap');

window.Vue = require('vue');
window.VueRouter=require('vue-router').default;
window.VueAxios=require('vue-axios').default;
window.Axios=require('axios').default;

Vue.use(VueRouter, Axios, VueAxios);

import BootstrapVue from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import Parent from './components/Parent';
import Control from './components/Control';
import Statistics from './components/Statistics';
import Manage from "./components/Manage/Manage";
import Manage_station_create from "./components/Manage/Manage_station_create";
import Manage_station_update from "./components/Manage/Manage_station_update";
import Manage_path_create from "./components/Manage/Manage_path_create";
import Manage_path_update from "./components/Manage/Manage_path_update";
import Manage_check_create from "./components/Manage/Manage_check_create";
import Manage_check_update from "./components/Manage/Manage_check_update";
import Manage_rc from "./components/Manage/Manage_rc";

Vue.use(BootstrapVue);

const router = new VueRouter({
    mode : 'history',
    routes : [
        {
            path : '/',
            name : 'Control',
            component : Control
        },
        {
            path : '/statistics',
            name : 'statistics',
            component : Statistics
        },
        {
            path: "/manage",
            name: "Manage",
            component: Manage,
            children: [
              {
                path: "manage_station_create",
                name: "Manage_station_create",
                component: Manage_station_create,
              },
              {
                path: "manage_station_update",
                name: "Manage_station_update",
                component: Manage_station_update,
              },
              {
                path: "manage_path_create",
                name: "Manage_path_create",
                component: Manage_path_create,
              },
              {
                path: "manage_path_update",
                name: "Manage_path_update",
                component: Manage_path_update,
              },
              {
                path: "manage_check_create",
                name: "Manage_check_create",
                component: Manage_check_create,
              },
              {
                path: "manage_check_update",
                name: "Manage_check_update",
                component: Manage_check_update,
              },
              { path: "manage_rc", name: "Manage_rc", component: Manage_rc },
            ],
          },
    ]
});

const app = new Vue({
    el : '#app',
    router,
    render : (h) => h(Parent)
});

Vue.prototype.$EventBus = new Vue();    //컴포넌트간 통신을 하기 위해서
