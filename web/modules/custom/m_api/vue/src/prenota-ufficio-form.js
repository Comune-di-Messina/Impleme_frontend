import Vue from "vue";
import PrenotaUfficioForm from "./PrenotaUfficioForm.vue";
import axios from "axios";
import VueAxios from "vue-axios";

const client = axios.create({
  headers: {
    "Content-Type": "application/json",
    "X-Requested-With": "XMLHttpRequest"
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
Vue.component("PrenotaUfficioForm", PrenotaUfficioForm);

document.getElementById(
  "block-portalemessina-content"
).innerHTML += `<div id="vueapp"></div>`;
const app = new Vue({
  el: "#vueapp",
  template: "<prenota-ufficio-form />"
});
