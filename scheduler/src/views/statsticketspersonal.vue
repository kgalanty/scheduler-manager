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
        label="Tickets Stats"
        type="is-primary"
        aria-controls="contentIdForA11y1"
        @click="navigateToStats"
      />
    </div>
    <section class="hero">
      <div class="hero-body">
        <p class="title">Personal stats by tickets</p>
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
      <div class="column">
        <b-field label="Select agent">
          <b-autocomplete
            v-model="operatorFilter"
            placeholder="Operator Name"
            :data="filteredOperators"
            open-on-focus
            @select="(option) => (operatorSelected = option)"
            clearable
            :loading="operatorLoading"
          >
          </b-autocomplete>
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

    <div class="tile is-ancestor">
      <div class="tile is-parent">
        <div class="tile">    <b-loading v-model="loading" :is-full-page="false"></b-loading>
          <article class="tile is-child notification is-primary">
            <p
              class="title"
              v-if="avgFirstReply && avgFirstReply.avg_firstreply"
            >
              {{
                moment
                  .utc(avgFirstReply.avg_firstreply * 1000)
                  .format("HH:mm:ss")
              }}
              h
            </p>
            <p class="title" v-else>No data</p>
            <p class="subtitle">Average First Response Time</p>
          </article>
        </div>
      </div>
      <div class="tile is-parent">
        <article class="tile is-child notification is-info">
          <p class="title" v-if="avgReply && avgReply.avg_seconds">
            {{ moment.utc(avgReply.avg_seconds * 1000).format("HH:mm:ss") }} h
          </p>
          <p class="title" v-else>No data</p>
          <p class="subtitle">Average Response Time</p>
        </article>
      </div>
    </div>

    <div class="tile is-ancestor">
      <div class="tile is-parent">
        <div class="tile">
          <article class="tile is-child notification is-primary">
            <p
              class="title"
              v-if="avgFirstReply && avgFirstReply.replies_count"
            >
              {{ avgFirstReply.replies_count }}
            </p>
            <p class="title" v-else>No data</p>
            <p class="subtitle">Tickets</p>
          </article>
        </div>
      </div>
      <div class="tile is-parent">
        <article class="tile is-child notification is-info">
          <p class="title" v-if="avgReply && avgReply.replies_count">
            {{ avgReply.replies_count }}
          </p>
          <p class="title" v-else>No data</p>
          <p class="subtitle">Total Replies</p>
        </article>
      </div>
    </div>
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
      agentstats: {},
      operators: [],
      datefrom: this.moment().toDate(),
      dateto: this.moment().toDate(),
      operatorFilter: "",
      operatorSelected: "",
      loading: false,
      operatorLoading: true,
    };
  },
  watch: {
    operatorSelected(newval) {
      if (newval != "") {
        this.getStats();
      }
    },
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
    filteredOperators() {
      return this.operators.filter((option) => {
        return (
          option
            .toString()
            .toLowerCase()
            .indexOf(this.operatorFilter.toLowerCase()) >= 0
        );
      });
    },
    avgFirstReply() {
      return this.agentstats?.avgFirstReply ?? "";
    },
    avgReply() {
      return this.agentstats.avgReply ?? "";
    },
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
    this.getOperators();
    //this.getStats();
    //this.$store.dispatch("getTeams");
  },
  methods: {
    getOperators() {
      this.$http.get("./scheduleapi/tickets/operators").then((r) => {
        if (r.data.response === "success") {
          this.operators = r.data.operators;
        } else {
          // this.$router.push({ path: `/`})
        }
        this.operatorLoading = false
      });
    },
    setLastMonth() {
      this.datefrom = this.moment()
        .subtract(1, "month")
        .startOf("Month")
        .toDate();
      this.dateto = this.moment().subtract(1, "month").endOf("Month").toDate();
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
      this.$router.push({ path: `/tickets` });
    },
    getStats() {
      if (
        this.moment(this.dateto).isSameOrAfter(this.datefrom) &&
        this.operatorSelected?.length > 0
      ) {
        this.loading = true;
        this.$http
          .get("./scheduleapi/tickets/personalstats", {
            params: {
              dateFrom: this.moment(this.datefrom).format("YYYY-MM-DD"),
              dateTo: this.moment(this.dateto).format("YYYY-MM-DD"),
              agent: this.operatorSelected
                ? decodeURIComponent(
                    (this.operatorSelected + "").replace(/\+/g, "%20")
                  )
                : "",
            },
          })
          .then((r) => {
            if (r.data.response === "success") {
              this.agentstats = r.data.operators;
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
.tile .title {
  text-align: center;
}
</style>