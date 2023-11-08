<template>
  <div class="row my-4 d-flex align-items-stretch">
    <div class="col-12 col-lg-6">
      <date-picker
        ref="datePicker"
        color="green"
        class="calendario shadow"
        :value="date"
        :model-config="modelConfig"
        :available-dates="availableDates"
        @dayclick="onDayClick"
        @update:from-page="onPageChange"
      />
    </div>
    <div class="col-12 col-lg-6">
      <div class="timepicker shadow">
        <span class="timepicker__title">ORARI DISPONIBILI</span>
        <div v-if="!calendarError" class="timepicker__grid row">
          <div v-for="time in currentTimeSlots" class="col-4">
            <div
              class="timepicker__grid__item"
              v-on:click="() => setTimeSlot(time.text, time.id)"
              :class="{
                selected: selectedTimeSlot === time.text,
                disabled: !time.available
              }"
            >
              {{ time.text }}
            </div>
          </div>
        </div>
        <div v-else class="timepicker__grid row">
          {{ calendarError }}
        </div>
      </div>
    </div>
    <div class="col-12 text-center position-absolute" v-if="loading">
      <spinner />
    </div>
  </div>
</template>

<script>
import Spinner from "../ui/Spinner.vue";
export default {
  name: "PrenotaUfficioCalendar",
  components: {
    Spinner,
    DatePicker: () =>
      import(
        /* webpackChunkName: "date-picker" */
        "v-calendar/lib/components/date-picker.umd"
      )
  },
  props: {
    publicServiceId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      modelConfig: {
        type: "string",
        mask: "YYYY-MM-DD"
      },
      date: null,
      days: [],
      timeSlots: {},
      selectedTimeSlot: false,
      loading: false,
      calendarError: false
    };
  },
  watch: {
    publicServiceId: function() {
      this.getCalendar();
    }
  },
  computed: {
    availableDates() {
      return this.days.reduce((res, day) => {
        if (day.timeslots.filter(time => time.reservable).length > 0) {
          const timestamp = new Date(day.date).getTime();
          this.generateTimeSlot(day.date, day.timeslots);
          res.push(timestamp);
        }
        return res;
      }, []);
    },
    currentTimeSlots() {
      const slotDay = this.timeSlots[this.date] || [];
      return slotDay.map(slot => {
        const { startTime, endTime, available, id } = slot;
        return { text: `${startTime} - ${endTime}`, available, id };
      }, []);
    }
  },
  methods: {
    getCalendar(props) {
      this.loading = true;
      let startDate = props ? props.startDate : this.calculateDate(new Date());
      let endDate = props ? props.endDate : this.calculateEndDate(new Date());
      const { municipalityId, officeId } = drupalSettings.m_api.data;
      const params = `?municipalityId=${municipalityId}&officeId=${officeId}&publicServiceId=${this.publicServiceId}&startDate=${startDate}&endDate=${endDate}`;
      const endpoint = `${drupalSettings.m_api.endpoints.calendar}${params}`;
      this.$http.get(endpoint).then(res => {
        this.selectedTimeSlot = false;
        this.loading = false;
        if (res.data.days) {
          this.calendarError = false;
          this.days = res.data.days;
        } else {
          this.calendarError = `${res.data.error}: ${res.data.message}`;
        }
      });
    },
    generateTimeSlot(timestamp, timeslots) {
      this.timeSlots[timestamp] = timeslots.reduce(
        (res, { startTime, endTime, id }) => {
          const available =
            this.date !==
              `${new Date().getFullYear()}-${this.fixMontZero(
                new Date().getMonth()
              )}-${this.fixZero(new Date().getDate())}` ||
            new Date().getHours() < parseInt(startTime.split(":")[0], 10);
          res.push({
            startTime: startTime,
            endTime: endTime,
            available,
            id
          });
          return res;
        },
        []
      );
    },
    fixTimeString(value) {
      return value < 10 ? `0${value}:00` : `${value}:00`;
    },
    fixZero(value) {
      return value < 10 ? `0${value}` : value;
    },
    fixMontZero(value) {
      return value < 9 ? `0${value + 1}` : value + 1;
    },
    setTimeSlot(value, id) {
      this.selectedTimeSlot = value;
      this.$emit("onTimeSelect", { date: this.date, id });
    },
    calculateDate(value) {
      const date = new Date(value);
      const year = date.getFullYear();
      const month = this.fixMontZero(date.getMonth());
      const day = this.fixZero(date.getDate());
      return `${year}-${month}-${day}`;
    },
    calculateEndDate(value) {
      const date = new Date(value);
      const year = date.getFullYear();
      const month = this.fixMontZero(date.getMonth());
      const day = this.fixZero(new Date(year, month, 0).getDate());
      return `${year}-${month}-${day}`;
    },
    onDayClick({ id }) {
      this.date = id;
    },
    onPageChange({ month, year }) {
      const todayYear = new Date().getFullYear();
      const todayMonth = new Date().getMonth();
      const todayDay = new Date().getDate();
      const startDay =
        todayYear === year && todayMonth === parseInt(month) - 1
          ? todayDay
          : "1";
      const d = new Date(year, month, 0);
      this.getCalendar({
        startDate: `${year}-${this.fixMontZero(month - 1)}-${this.fixZero(
          startDay
        )}`,
        endDate: `${year}-${this.fixMontZero(month - 1)}-${this.fixZero(
          d.getDate()
        )}`
      });
    }
  },
  mounted() {
    this.getCalendar();
  }
};
</script>
