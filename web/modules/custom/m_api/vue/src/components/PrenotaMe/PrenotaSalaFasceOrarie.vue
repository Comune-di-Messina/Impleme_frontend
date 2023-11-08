<template>
  <div class="orari" @input="">
    <div class="orari-header text-center">
      <h5 class="text-uppercase">Fasce orarie</h5>
    </div>
    <div class="orari-all d-flex justify-content-between align-items-center">
      <strong>{{ label }}</strong>
      <div class="orari-toggle">
        <div class="toggles">
          <label for="interaGiornata">
            <input
              id="interaGiornata"
              v-model="
                hasRange || disabledToggle
                  ? bookingType === 'week'
                  : interagiornata
              "
              type="checkbox"
              :disabled="hasRange || disabledToggle"
              :class="{ disabled: !hasRange || disabledToggle }"
              @change="changeInteraGiornata($event.target.checked)"
            />
            <span class="lever" />
          </label>
        </div>
      </div>
    </div>
    <div class="orari-content">
      <button
        v-for="(fascia, index) in fasce"
        :key="index"
        type="button"
        class="orari-fascia"
        :class="{
          'h-selected': selected(fascia) || checkIfInRange(fascia),
        }"
        :disabled="
          fascia.disponibile !== 'undefined' && fascia.disponibile === false
        "
        @click="toggle(fascia)"
      >
        {{ labelOrario(fascia) }}
      </button>
      <div v-if="currentDay && isEmpty">Nessuna disponibilità</div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    tariffario: {
      type: [Object, null],
      default: null,
    },
    specifiche: {
      type: [Object, null],
      default: null,
    },
    fasceOrarie: {
      type: [Array, null],
      default: null,
    },
    currentDay: {
      type: [String, null],
      default: null,
    },
    daysMap: {
      type: Object,
      required: true,
    },
    hasRange: {
      type: Boolean,
      default: false,
    },
    bookingType: {
      type: [String, Boolean],
      default: false,
    },
  },
  data() {
    return {
      selection: [],
      notallowed: [],
      interagiornata: false,
      label: "Tutto il giorno",
      disabledToggle: false,
      isEmpty: false,
    };
  },
  computed: {
    flagInteraGiornata() {
      if (this.tariffario == null) {
        return true;
      }
      return this.tariffario.flagInteraGiornata;
    },
    fasce() {
      if (this.fasceOrarie.length === 0) {
        this.isEmpty = false;
        return [];
      }
      const result = this.fasceOrarie.reduce((res, fascia, index) => {
        if (index === 0) {
          fascia.ore.map((ora) => {
            if (
              ora.stato === "DISPONIBILE" ||
              ora.stato === "DISPONIBILE CON RISERVA"
            ) {
              const start = ora.oraDa.split(":");
              const end = ora.oraA.split(":");
              res.push({
                start: "" + start[0] + ":" + start[1],
                end: "" + end[0] + ":" + end[1],
                disponibile: true,
              });
            } else if (ora.stato === "NON DISPONIBILE") {
              const start = ora.oraDa.split(":");
              const end = ora.oraA.split(":");
              res.push({
                start: "" + start[0] + ":" + start[1],
                end: "" + end[0] + ":" + end[1],
                disponibile: false,
              });
            }
          });
        }
        return res;
      }, []);
      this.isEmpty = result.length === 0;
      return result;
    },
  },
  watch: {
    bookingType: function (val) {
      if (val === "week") {
        this.label = "Tutta la settimana";
        this.disabledToggle = true;
      } else {
        this.label = "Tutto il giorno";
        this.disabledToggle = !val;
      }
    },
    fasceOrarie: function (val) {
      this.isEmpty = typeof val[0] !== "undefined";
    },
  },
  mounted() {
    this.$root.$on("resetFasce", () => {
      this.selection = [];
    });
  },
  methods: {
    labelOrario(value) {
      return value.start + " - " + value.end;
    },
    orario() {
      const firstSelected = this.selection[0];

      let oraDa = null;
      let oraA = null;

      if (this.selection.length === 0) {
        return { start: oraDa, end: oraA };
      }

      if (this.selection.length > 1) {
        oraDa = this.getOrarioDa();
        oraA = this.getOrarioA();
      } else {
        oraDa = firstSelected.start;
        oraA = firstSelected.end;
      }

      return { start: oraDa + ":00", end: oraA + ":00" };
    },
    getOrarioDa() {
      return this.selection[0].start > this.selection[1].start
        ? this.selection[1].start
        : this.selection[0].start;
    },
    getOrarioA() {
      return this.selection[0].end > this.selection[1].end
        ? this.selection[0].end
        : this.selection[1].end;
    },
    toggle(value) {
      const orarioExist = this.checkOrarioExist(value);
      if (this.tariffario.costoFasce.length !== 0) {
        this.selection = [];
        this.selection.push(value);
      } else if (!orarioExist && this.selection.length < 2) {
        if (
          this.selection.length > 0 &&
          this.rangeIsFullyAvailable(value, this.selection[0])
        ) {
          this.selection.push(value);
        } else {
          this.selection = [];
          this.selection.push(value);
        }
      } else {
        this.selection = [];
        this.selection.push(value);
      }

      const result = this.orario();
      this.$emit("fasce", result);
    },
    selected(value) {
      return (
        this.selection.indexOf(value) > -1 ||
        (this.selection.length > 1 &&
          value > this.selection[0] &&
          value < this.selection[this.selection.length - 1])
      );
    },
    checkIfInRange(fascia) {
      return (
        this.selection.length > 1 &&
        fascia.start > this.getOrarioDa() &&
        fascia.end < this.getOrarioA()
      );
    },
    disabled(value) {
      return (
        (this.bookingType === "week" ||
          this.interagiornata == true ||
          this.fasce.length == 0 ||
          this.notallowed.indexOf(value) > -1) &&
        !value.disponibile
      );
    },
    changeInteraGiornata(value) {
      if (value === true) {
        this.selection.splice(0, this.selection.length);
      }
      this.$emit("interaGiornata", value);
    },
    checkOrarioExist(orario) {
      for (let i = 0; i < this.selection.length; i++) {
        const current = this.selection[i];
        if (orario.start === current.start && orario.end === current.end) {
          return true;
        }
      }
    },
    rangeIsFullyAvailable(a, b) {
      let start, end;
      if (a === "undefined" || b === "undefined") return true;
      if (a.start > b.start) {
        start = b;
        end = a;
      } else {
        start = a;
        end = b;
      }
      const intermediate = this.fasce.filter(
        (value, index) => value.start > start.start && value.end < end.end
      );
      return intermediate.every((value, index) => value.disponibile);
    },
  },
};
</script>
