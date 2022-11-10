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
        @click="navigateToTicketsStats"
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
      <div class="column" v-if="isAdmin">
        <b-field label="Select agent">
          <b-autocomplete
            v-model="operatorFilter"
            placeholder="Operator Name"
            :data="filteredOperators"
            open-on-focus
            @select="(option) => (operatorSelected = option)"
            clearable
            :loading="operatorLoading"
            :custom-formatter="
              (option) => option.firstname + ' ' + option.lastname
            "
          >
          </b-autocomplete>
        </b-field>
      </div>
      <div class="column" v-if="!isEditor">
        <b-field label="Agent">
          <b-input :placeholder="adminname" disabled></b-input>
        </b-field>
      </div>
      <div class="column" v-if="!isAdmin">
        <b-field label="Agent">
          <b-button type="is-info" expanded  @click="showMyStats()"
          >Show My Stats</b-button
        >
        </b-field>
      </div>
      <div class="column" v-if="isEditor">
        <b-field label="or a team">
          <b-select placeholder="Select a name" v-model="teamSelected" expanded>
            <option value="">- None -</option>
            <option
              v-for="team in teams"
              :value="team.groupid"
              :key="team.groupid"
              :disabled="team.children > 0"
            >
              <span v-if="team.parent > 0 && isAdmin">- </span> {{ team.name }}
            </option>
          </b-select>
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

    <!-- <div class="tile is-ancestor">
      <div class="tile is-parent">
        <div class="tile">
          <b-loading v-model="loading" :is-full-page="false"></b-loading>
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
    </div> -->
    <article id="agentstable">
      <b-table
        :data="groupStats"
        narrowed
        bordered
        hoverable
        :loading="loadingTbl"
      >
        <template #empty>
          <div class="has-text-centered">No records</div>
        </template>
        <b-table-column centered label="Agent" field="agent" sortable>
          <template v-slot="props">
            {{ props.row.agent }}
          </template>
        </b-table-column>

        <b-table-column
          centered
          label="Average First Response [s]"
          field="avgfirstreply"
          sortable
        >
          <template v-slot="props">
            {{ props.row.avgfirstreply }}
          </template>
        </b-table-column>
        <b-table-column
          centered
          label="Tickets Of First Response"
          field="tickets"
          sortable
        >
          <template v-slot="props">
            {{ props.row.tickets }}
          </template>
        </b-table-column>
        <b-table-column
          centered
          label="Average Response [s]"
          field="avgreply"
          sortable
        >
          <template v-slot="props">
            {{ props.row.avgreply }}
          </template>
        </b-table-column>
        <b-table-column
          centered
          label="Tickets With Last Response"
          field="lastreplies"
          sortable
        >
          <template v-slot="props">
            {{ props.row.lastreplies }}
          </template>
        </b-table-column>
        <b-table-column
          centered
          label="Total Responses"
          field="totalreplies"
          sortable
        >
          <template v-slot="props">
            {{ props.row.totalreplies }}
          </template>
        </b-table-column>
      </b-table>
    </article>
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
      teamSelected: "",
      groupStats: [],
      loadingTbl: false,
    };
  },
  watch: {
    operatorSelected(newval) {
      if (newval != "") {
        this.teamSelected = "";
        this.getStats();
      }
    },
    teamSelected(newval) {
      if (newval != "") {
        this.operatorSelected = "";
        this.operatorFilter = "";
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
    isEditor() {
      return this.$store.state.editorPermission === 1 ? true : false;
    },
    isAdmin() {
      return this.$store.state.adminPermission;
    },
    groupsAllowed() {
      return this.$store.state.editorPermissionsGroups[2];
    },
    adminname() {
      return this.$store.state.myadmindata?.info ?? "";
    },
    filteredOperators() {
      return this.operators.filter((option) => {
        return (
          option.firstname
            .toString()
            .toLowerCase()
            .indexOf(this.operatorFilter.toLowerCase()) >= 0 ||
          option.lastname
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
      return this.isAdmin ? this.$store.state.schedule_teams : this.$store.state.schedule_teams.filter((item) => {
        return this.groupsAllowed.includes(item.groupid);
      });
    },
    agents() {
      return this.$store.state.agentsGroups.agents;
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
    if (!this.teams.length) {
      this.$store.dispatch("getTeams");
    }
    let that = this;
    if (!this.isAdmin) {
      setTimeout(function () {
        if (that.adminname) that.operatorFilter = that.adminname;
      }, 1000);
    }
  },
  methods: {
    showMyStats()
    {
      this.teamSelected = ''
      this.getStats()
    },
    getOperators() {
      this.$http.get("./scheduleapi/agents").then((r) => {
        if (r.data) {
          this.operators = r.data;
        } else {
          // this.$router.push({ path: `/`})
        }
        this.operatorLoading = false;
      });
    },
    loadAgentsTeams() {
      return this.$store.dispatch("loadAgentsForGroup", {
        //  team: this.group,
        topteam: this.teamSelected,
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
      this.$router.push({ path: `/stats` });
    },
    navigateToTicketsStats() {
      this.$router.push({ path: `/stats/tickets` });
    },
    getStats() {
      if (
        this.moment(this.dateto).isSameOrAfter(this.datefrom) &&
        (this.operatorSelected || this.teamSelected > 0 || this.operatorFilter)
      ) {
        if (this.teamSelected > 0) {
          this.loadingTbl = true;

          this.loadAgentsTeams().then(() => {
            this.groupStats = [];
            let agentsCounter = this.agents.length;
            if (this.agents.length === 0) {
              this.loadingTbl = false;
              this.$buefy.snackbar.open({
                message: "This team is empty. Consider adding people first.",
                type: "is-warning",
                position: "is-top",
                actionText: "Okay",
              });

              return;
            }

            this.agents.forEach(async (item) => {
              await this.$http
                .get("./scheduleapi/tickets/personalstats", {
                  params: {
                    dateFrom: this.moment(this.datefrom).format("YYYY-MM-DD"),
                    dateTo: this.moment(this.dateto).format("YYYY-MM-DD"),
                    agent: encodeURIComponent(
                      item.firstname + " " + item.lastname
                    ),
                  },
                })
                .then((r) => {
                  if (r.data.response === "success") {
                    this.groupStats.push(r.data.stats);
                    agentsCounter--;
                    // this.agentstats = r.data.operators;
                  } else {
                    // this.$router.push({ path: `/`})
                  }
                  if (agentsCounter === 0) {
                    this.loadingTbl = false;
                  }
                });
            });
          });
          return;
        }
        this.$http
          .get("./scheduleapi/tickets/personalstats", {
            params: {
              dateFrom: this.moment(this.datefrom).format("YYYY-MM-DD"),
              dateTo: this.moment(this.dateto).format("YYYY-MM-DD"),
              agent: this.operatorSelected
                ? encodeURIComponent(
                    this.operatorSelected.firstname +
                      " " +
                      this.operatorSelected.lastname
                  )
                : (this.operatorFilter ? encodeURIComponent(this.operatorFilter) : ''),
            },
          })
          .then((r) => {
            if (r.data.response === "success") {
              this.groupStats = [];
              this.groupStats.push(r.data.stats);
            } else {
              // this.$router.push({ path: `/`})
            }
            this.loadingTbl = false;
          });
      } else {
        // this.dateto = this.moment(this.datefrom).add(1, 'day').toDate()
        // this.getStats()
        this.loadingTbl = false;
      }
    },
    getFirstDay() {
      this.moment();
    },
  },
};
</script>
<style>
#agentstable .table {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  border-collapse: collapse !important;
}
#agentstable .table td {
  border: 0;
  border-bottom: 1px solid black;
  margin: 3px;
  font-size: 0.9rem;
}
#agentstable th span {
  margin: 0 auto;
  text-align: center;
}
#agentstable th {
  border: 0;
  border-bottom: 1px solid #8b8c91;
  color: #8b8c91;
  text-transform: uppercase;
}
</style>
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