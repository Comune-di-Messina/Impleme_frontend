import Vue from '../../../../../../node_modules/vue/dist/vue.js';
import axios from "../../../../../../node_modules/axios/dist/axios.js";
import VueAxios from '../../../../../../node_modules/vue-axios/dist/vue-axios.min.js';
import PraticheNew from './PraticheNew.vue';

const client = axios.create({
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});
Vue.use(VueAxios, client);
const toCurrency = function (value, precision, decimal, thousands) {
  precision = precision || 2;
  decimal = decimal || ",";
  thousands = thousands || ".";
  let val = (value / 1).toFixed(precision).replace(".", decimal);
  return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
};
Vue.filter("format_number", toCurrency);
Vue.component("PraticheNew", PraticheNew);

document.getElementById(
  "block-portalemessina-content"
).innerHTML += `<div id="vueapp"></div>`;
const app = new Vue({
  el: "#vueapp",
  template: "<pratiche-new />"
});
