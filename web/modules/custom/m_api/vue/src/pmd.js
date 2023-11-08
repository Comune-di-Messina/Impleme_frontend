import Vue from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import PrenotameAllegati from './PrenotameAllegati.vue';

const client = axios.create({
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});
Vue.use(VueAxios, client);
const toCurrency = function (value, precision, decimal, thousands) {
  precision = precision || 2;
  decimal = decimal || ',';
  thousands = thousands || '.';
  let val = (value / 1).toFixed(precision).replace('.', decimal);
  return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousands);
};
Vue.filter('format_number', toCurrency);
Vue.component('PrenotameAllegati', PrenotameAllegati);

const app = new Vue({
  el: '#allegati',
  template: '<Prenotame-allegati />',
});
