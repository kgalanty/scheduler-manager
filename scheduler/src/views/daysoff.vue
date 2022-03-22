<template>
  <div style="background-color: white; display: flow-root">
    <section class="hero">
      <div class="hero-body">
        <p class="title">
          Days Off :: {{ agentdata.firstname }} {{ agentdata.lastname }}
        </p>
      </div>
    </section>

    <div class="columns">
      <div class="column">
        <b-field label="Assign a year it belongs to">
          <b-select placeholder="Select a year" expanded v-model="year">
            <option
              v-for="option in yearsEnumerate"
              :value="option"
              :key="option"
            >
              {{ option }}
            </option>
          </b-select>
        </b-field>
      </div>
      <div class="column">
        <b-field label="Select expiration date">
          <b-datepicker
            v-model="dateexp"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
            :first-day-of-week="1"
          >
          </b-datepicker>
        </b-field>
      </div>
      <div class="column">
        <b-field label="Amount of days">
          <b-numberinput v-model="daysoff" step="1"></b-numberinput>
        </b-field>
      </div>

      <div class="column">
        <b-field label="Submit">
          <b-button type="is-primary" expanded @click="SubmitAddition" :loading="btnLoading"
            >Add</b-button
          >
        </b-field>
      </div>
    </div>
    <b-table :data="datatable" class="" paginated :loading="loading">
      <template #empty>
        <div class="has-text-centered">No records</div>
      </template>
      <b-table-column field="year" label="Year" v-slot="props" centered>
        {{ props.row.year }}
      </b-table-column>
      <b-table-column field="id" label="Date Added" v-slot="props" centered>
        {{ props.row.created }}
      </b-table-column>
      <b-table-column
        field="id"
        label="Expiration date"
        v-slot="props"
        centered
      >
        {{ props.row.date_expiration }}
      </b-table-column>
      <b-table-column field="id" label="Days left pool" v-slot="props" centered>
        {{ props.row.days }}
      </b-table-column>
      <b-table-column field="id" label="Actions" v-slot="props" width="100">
        <!-- <b-field ><b-button
          @click="removeDays(props.row.id)"
          icon-right="trash"
          type="is-danger"
          size="is-small"
          >Delete</b-button
        > </b-field> -->
        <b-field>
          <b-button
            @click="ChangeDays(props.row.id, props.row.days)"
            icon-right="pen"
            type="is-primary"
            size="is-small"
            >Change Days Number</b-button
          ></b-field
        >
      </b-table-column>
    </b-table>
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
import ChangeDaysOffModal from "../forms/ChangeDaysOffModal";
export default {
  name: "daysoff",
  components: {},
  mounted() {
    this.getDaysOff(this.$route.params.agentid);
    this.getAgentData(this.$route.params.agentid);
    // this.$store.dispatch("getLogs", {datefrom: this.moment(this.datefrom).format('YYYY-MM-DD HH:mm:ss'), dateto: this.moment(this.dateto).format('YYYY-MM-DD HH:mm:ss') });
  },
  methods: {
    ChangeDays(daysoffentry, currentdays) {
      const that = this;
      this.$buefy.modal.open({
        parent: this,
        component: ChangeDaysOffModal,
        hasModalCard: true,
        props: { daysoffentry: daysoffentry, currentdays: currentdays },
        trapFocus: true,
        events: {
          reloadapi() {
            that.getDaysOff(that.$route.params.agentid);
          },
        },
      });
    },
    getAgentData(agentid) {
      this.$http.get("./scheduleapi/agents/" + agentid).then((r) => {
        if (r.data.data) {
          this.agentdata = r.data.data;
        }
      });
    },
    SubmitAddition() {
      if (this.daysoff < 1) {
        this.$buefy.snackbar.open({
          duration: 5000,
          message:
            "Number of days cannot be lower than 1. If you wish to remove amount of days, delete entries from the table.",
          type: "is-danger",
          position: "is-bottom-left",
        });
        return;
      }
      this.btnLoading = true;
      this.$http
        .post("./scheduleapi/daysoff/" + this.$route.params.agentid, {
          dateexp: this.dateexp,
          daysoff: this.daysoff,
          year: this.year,
        })
        .then((r) => {
          this.btnLoading = false;
          if (r.data.result == "error") {
            this.$buefy.snackbar.open({
              duration: 5000,
              message: r.data.msg,
              type: "is-danger",
              position: "is-bottom-left",
            });
            return;
          }
          if (r.data.result) {
            this.$buefy.snackbar.open(`Days off added to the pool`);
            this.getDaysOff(this.$route.params.agentid);
          }
        });
    },
    getDaysOff(agentid) {
      this.loading = true;
      this.$http
        .get("./scheduleapi/daysoff/" + agentid, { withCredentials: true })
        .then((r) => {
          if (r.data.result === "success") {
            this.datatable = r.data.data;
          }

          this.loading = false;
        });
    },
    getStyle(bg, color) {
      if (!bg && !color) {
        return {
          "background-color": "white",
          color: "black",
        };
      }
      return {
        "background-color": bg,
        color: color,
      };
    },
  },
  computed: {
    yearsEnumerate() {
      const endYear = new Date().getFullYear() + 3;
      let startYear = 2020;
      let years = [];
      while (startYear <= endYear) {
        years.push(startYear);
        startYear++;
      }
      return years;
    },
  },
  data() {
    return {
      loading: true,
      btnLoading: false,
      tblLoading: {},
      dateexp: new Date(),
      daysoff: 0,
      datatable: [],
      agentdata: {},
      year: new Date().getFullYear(),
      //datefrom: this.moment().startOf("month").toDate(),
      //dateto: this.moment().endOf("month").toDate(),
    };
  },
};
</script>
<style scoped>
</style>