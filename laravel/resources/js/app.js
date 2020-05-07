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
import Control_car from './components/Control_car';
import Statistics from './components/Statistics';
import Manage from './components/Manage/Manage';
import Manage_station from './components/Manage/Manage_station';
import Manage_path from './components/Manage/Manage_path';
import Manage_checkpoint from './components/Manage/Manage_checkpoint';
import Manage_rc from './components/Manage/Manage_rc';

Vue.use(BootstrapVue);

const router = new VueRouter({
    mode : 'history',
    routes : [
        {
            path : '/',
            name : 'Control_car',
            component : Control_car
        },
        {
            path : '/statistics',
            name : 'statistics',
            component : Statistics
        },
        {
            path : '/manage',
            name : 'Manage',
            component : Manage
        },
        {
            path : '/manage',
            name : 'Manage',
            component : Manage,
            children : [
                { path : 'manage_station', name : 'Manage_station', component : Manage_station },
                { path : 'manage_path', name : 'Manage_path', component : Manage_path },
                { path : 'manage_checkpoint', name : 'Manage_checkpoint', component : Manage_checkpoint },
                { path : 'manage_rc', name : 'Manage_rc', component : Manage_rc },

            ]
        }
    ]
});

const app = new Vue({
    el : '#app',
    router,
    render : (h) => h(Parent)
});

Vue.prototype.$EventBus = new Vue();    //컴포넌트간 통신을 하기 위해서
