import Vue from '../../../../../../node_modules/vue/dist/vue.js';
import PraticheDetail from "./PraticheDetail.vue";
import axios from "../../../../../../node_modules/axios/dist/axios.js";
import VueAxios from '../../../../../../node_modules/vue-axios/dist/vue-axios.min.js';

const client = axios.create({
  headers: {
    "Content-Type": "application/json",
    "X-Requested-With": "XMLHttpRequest"
  }
});
Vue.use(VueAxios, client);
const toCurrency = function(value, precision, decimal, thousands) {
  precision = precision || 2;
  decimal = decimal || ",";
  thousands = thousands || ".";
  let val = (value / 1).toFixed(precision).replace(".", decimal);
  return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
};
Vue.filter("format_number", toCurrency);
Vue.component("PraticheDetail", PraticheDetail);

document.getElementById(
  "block-portalemessina-content"
).innerHTML += `<div id="vueapp"></div>`;
const app = new Vue({
  el: "#vueapp",
  template: "<pratiche-detail />"
});
