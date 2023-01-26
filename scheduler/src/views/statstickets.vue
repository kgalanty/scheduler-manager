<template>
  <div style="background-color: white; padding: 20px; display: flow-root">
    <div class="buttons">
      <b-button
        size="is-small"
        label="Back to main stats"
        type="is-primary"
        aria-controls="contentIdForA11y1"
        @click="navigateToStats"
      /> 
      <b-button
        size="is-small"
        label="Agents Personal Stats"
        type="is-primary"
        aria-controls="contentIdForA11y1"
        @click="navigateToStatsPersonal"
      />
    </div>
    <section class="hero">
      <div class="hero-body">
        <p class="title">Stats by tickets</p>
      </div>
    </section>

    <div class="columns">
      <div class="column">
        <b-field label="Select a date from">
          <b-datepicker
            @input="getStats"
            v-model="datefrom"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
          >
          </b-datepicker>
        </b-field>
      </div>
      <div class="column">
        <b-field label="Select a date to">
          <b-datepicker
            @input="getStats"
            v-model="dateto"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
          >
          </b-datepicker>
        </b-field>
      </div>
    </div>
    <section class="section">
      <h3 class="title">Dates filter</h3>
      <h2 class="subtitle">
        <b-button type="is-primary" @click="setLastWeekend()"
          >Last weekend</b-button
        >&nbsp;
        <b-button type="is-primary" @click="setThisWeek()">This week</b-button
        >&nbsp;
        <b-button type="is-primary" @click="setThisMonth()">This month</b-button
        >&nbsp;
        <b-button type="is-primary" @click="setLastMonth()"
          >Last month</b-button
        >
      </h2>
    </section>
    <div class="container">
      <div class="notification">
        <b-taglist attached>
          <b-tag type="is-dark">Tickets created</b-tag>
          <b-tag type="is-info">Replies from Operators</b-tag>
          <b-tag type="is-warning">Replies from Customers</b-tag>
        </b-taglist>
      </div>
    </div>
    <b-table :data="agentstats.shifts" class="" :loading="loading">
      <template #empty>
        <div class="has-text-centered">No stats for given filters</div>
      </template>
      <b-table-column field="id" label="Shift" v-slot="props" centered>
       <b-taglist attached>
              <b-tag type="is-dark">{{ props.row.from }}</b-tag>
              <b-tag type="is-info">-</b-tag>
              <b-tag type="is-dark">{{ props.row.to }}</b-tag>
            </b-taglist>
      </b-table-column>
      <b-table-column field="id" label="Tickets Stats" centered v-slot="props">
        <b-table
          :data="Object.values(agentstats.stats.tickets_created[props.row.id])"
          class=""
        >
          <b-table-column field="day" label="" v-slot="props2" centered>
            {{ props2.row.day }}
          </b-table-column>
          <b-table-column
            field="day"
            label="Ticket Counters"
            v-slot="props2"
            centered
          >
            <b-taglist attached>
              <b-tag type="is-dark">{{ props2.row.counter }}</b-tag>
              <b-tag type="is-info">{{
                getStatsParam(
                  "tickets_replies_admins",
                  props.row.id,
                  props2.row.day
                ).counter
              }}</b-tag>
              <b-tag type="is-warning">{{
                getStatsParam(
                  "tickets_replies_customers",
                  props.row.id,
                  props2.row.day
                ).counter
              }}</b-tag>
            </b-taglist>
          </b-table-column>
        </b-table>
      </b-table-column>
      <!-- <b-table-column
            label="Details"
            v-slot="props2"
            centered
          >
          <b-button  @click="loadShiftStats(props2.row.id)" type="is-info" icon-left="eye" size="is-small">Detailed Stats</b-button>
          </b-table-column> -->
    </b-table>
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
export default {
  name: "stats",
  components: {},
  data() {
    return {
      agentstats: { shifts: [], stats: [] },
      datefrom: this.moment().startOf("month").toDate(),
      dateto: this.moment().toDate(),
      teamFilter: "",
      loading: true,
    };
  },
  watch: {
    datefrom(newval, oldval) {
      if (this.moment(newval).isAfter(this.dateto)) {
        this.$buefy.snackbar.open({
          message: "Incorrect dates",
          type: "is-danger",
        });
        this.datefrom = this.moment(oldval).toDate();
      }
    },
    dateto(newval, oldval) {
      if (this.moment(newval).isBefore(this.datefrom)) {
        this.$buefy.snackbar.open({
          message: "Incorrect dates",
          type: "is-danger",
        });
        this.dateto = this.moment(oldval).toDate();
      }
    },
  },
  computed: {
    daysSpan() {
      return (
        parseInt(
          this.moment
            .duration(this.moment(this.dateto).diff(this.moment(this.datefrom)))
            .asDays()
        ) + 1
      );
    },
    teams() {
      return this.$store.state.schedule_teams;
    },
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
    this.getStats();
    this.$store.dispatch("getTeams");
  },
  methods: {
    loadShiftStats(shift_id)
    {
      this.$router.push({ path: `/stats/${shift_id}` });
      console.log(shift_id)
    },
    setLastMonth()
    {
      this.datefrom = this.moment().subtract(1, "month").startOf('Month').toDate()
      this.dateto = this.moment().subtract(1, 'month').endOf('Month').toDate()
     this.getStats();

    },
    setThisMonth() {
      this.datefrom = this.moment().startOf("Month").toDate();
      this.dateto = this.moment().toDate();
      this.getStats();
    },
    setThisWeek() {
      this.datefrom = this.moment().startOf("isoWeek").day("Monday").toDate();
      this.dateto = this.moment().toDate();
      this.getStats();
    },
    setLastWeekend() {
      this.datefrom = this.moment().isoWeekday(-1).toDate();
      this.dateto = this.moment().startOf("isoWeek").day("Sunday").toDate();
      this.getStats();
      // this.datefrom = this.moment().day(-6).toDate()
    },
    getStatsParam(param, id, day) {
      return this.agentstats.stats[param][id][day];
    },
    navigateToStats() {
      this.$router.push({ path: `/stats` });
    },
    navigateToStatsPersonal()
    {
      this.$router.push({ path: `/stats/ticketspersonal` });
    },
    getStats() {
      if (this.moment(this.dateto).isAfter(this.datefrom)) {
        this.loading = true;
        this.$http
          .get("./scheduleapi/stats", {
            params: {
              datestart: this.moment(this.datefrom).format("YYYY-MM-DD"),
              dateend: this.moment(this.dateto).format("YYYY-MM-DD"),
              type: "weektickets",
            },
          })
          .then((r) => {
            if (r.data.response === "success") {
              this.agentstats = r.data.stats;
            } else {
              // this.$router.push({ path: `/`})
            }
            this.loading = false;
          });
      } else {
        // this.dateto = this.moment(this.datefrom).add(1, 'day').toDate()
        // this.getStats()
        this.loading = false;
      }
    },
    getFirstDay() {
      this.moment();
    },
  },
};
</script>
<style scoped>
.progress-wrapper.is-not-native {
  position: relative;
}
.tags {
  justify-content: center;
}
</style>