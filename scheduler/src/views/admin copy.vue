<template>
  <div style="background-color: white; display: flex; justify-content: center">
    <section class="section">
      <h1 class="title">Add New Shift</h1>
      <h2 class="subtitle">
        <b-field
          label="Time from"
          style="display: inline-block; margin-right: 20px"
        >
          <b-timepicker
            inline
            v-model="timefrom"
            rounded
            placeholder="Click to select..."
            icon="clock"
            :date-formatter="formatter"
          >
          </b-timepicker>
        </b-field>
        <b-field label="Time to" style="display: inline-block">
          <b-timepicker
            inline
            v-model="timeto"
            rounded
            placeholder="Click to select..."
            icon="clock"
          >
          </b-timepicker>
        </b-field>
      </h2>
      <h2 class="subtitle">
        <b-button type="is-primary" outlined @click="addShift"
          >Add New Shift</b-button
        >
      </h2>
    </section>
    <section class="section">
      <h1 class="title">Assign a person to a shift</h1>
      <h2 class="subtitle">
        <b-select placeholder="Select a name" style="display: inline-block">
          <option v-for="option in admins" :value="option.id" :key="option.id">
            {{ option.firstname }} {{ option.lastname }}
          </option>
        </b-select>
        <b-select placeholder="Select a shift" style="display: inline-block">
          <option v-for="option in shifts" :value="option.id" :key="option.id">
            {{ option.from }}-{{ option.to }}
          </option>
        </b-select>
      </h2>
      <h2 class="subtitle">
        <b-button type="is-primary" outlined>Assign</b-button>
      </h2>
    </section>
  </div>
</template>
<script>
export default {
  data() {
    const data = [
      {
        mon: {
          agent: "agent 1",
          style: { "background-color": "darkblue", color: "white" },
        },
        tue: "Jesse",
        wed: "Simmons",
        thu: "2016/10/15 13:43:27",
        fri: "Male",
        sat: "Male",
        sun: "Male",
      },
      { wed: "Simmons", fri: "Male" },
    ];
    const agents = [
      {
        id: 5,
        first_name: "agent name",
      },
    ];

    return {
      data,
      agents,
      timefrom: null,
      timeto: null,
    };
  },
  computed: {
    admins() {
      return this.$store.state.admins;
    },
    shifts() {
      return this.$store.state.shifts;
    },
    days() {
      return [
        this.moment().day(1).format("ddd DD.MM"),
        this.moment().day(2).format("ddd DD.MM"),
        this.moment().day(3).format("ddd DD.MM"),
        this.moment().day(4).format("ddd DD.MM"),
        this.moment().day(5).format("ddd DD.MM"),
        this.moment().day(6).format("ddd DD.MM"),
        this.moment().day(7).format("ddd DD.MM"),
      ];
    },
  },
  mounted() {
    this.$store.dispatch("getAdmins");
    this.$store.dispatch("getShiftsList");
  },
  methods: {
    formatter(d) {
      return d.toLocaleDateString();
    },
    addShift() {
      if (!this.timefrom || !this.timeto) {
        this.$buefy.toast.open({
          message: "All fields must be filled",
          type: "is-danger",
        });
        return;
      }
      this.$http
        .post("/scheduleapi/insertshift", {
          from: this.moment(this.timefrom).format("HH:mm"),
          to: this.moment(this.timeto).format("HH:mm"),
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Added!",
              type: "is-success",
            });
            this.$store.dispatch("getShiftsList");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
          
        });
    },
    getFirstDay() {
      this.moment();
    },
  },
};
</script>
<style >
.section:first-child {
  margin-right: 5px;
}
.section {
  width: 50%;
  float: left;
  border: 1px solid rgb(211, 211, 211);
  border-radius: 5px;
}
.subtitle {
  text-align: center;
}
.table tr {
  height: 40px;
}
</style>