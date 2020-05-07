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
import Manage from './components/Manage';

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
        }
    ]
});

const app = new Vue({
    el : '#app',
    router,
    render : (h) => h(Parent)
});

Vue.prototype.$EventBus = new Vue();    //컴포넌트간 통신을 하기 위해서
