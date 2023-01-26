<template>
  <div style="background-color: white; display: flow-root">
    <section class="hero">
      <div class="hero-body">
        <p class="title">Request days off</p>
      </div>
    </section>
    <b-tabs type="is-boxed" position="is-centered"  class="block" v-model="requestFormTab" :animated="false">
      <b-tab-item label="Vacation" icon="umbrella-beach">
       
        <section class="hero">
          <div class="notification is-info is-light">
            <div class="columns">
              <div class="column is-one-fifth has-text-info">
                <b-field label="Select vacation range ">
                  <b-datepicker
                    v-model="vacationdates"
                    placeholder="Click to select..."
                    icon="calendar"
                    trap-focus
                    :first-day-of-week="1"
                    range
                  >
                  </b-datepicker>
                </b-field>
              </div>
              <div class="column">
                <b-field label="Description">
                  <b-input v-model="desc"></b-input>
                </b-field>
              </div>
              <div class="column is-one-fifth">
                <b-field label="Submit">
                  <b-button
                    type="is-primary"
                    expanded
                    @click="SubmitAddition"
                    :loading="btnLoading"
                    >Submit vacation request</b-button
                  >
                </b-field>
              </div>
            </div>
          </div>
        </section>
      </b-tab-item>
      <b-tab-item label="Shift Change" icon="people-arrows">
        <section class="hero">
          <div class="notification is-link is-light">
            <h2>
              Below you can reserve days leave or request for fixed shifts. Fill
              any details into Description field below.
            </h2>
            <div class="columns">
              <div class="column is-one-fifth">
                <b-field label="Select shift date/range">
                  <b-datepicker
                    v-model="shiftchangedates"
                    placeholder="Click to select..."
                    icon="calendar"
                    trap-focus
                    :first-day-of-week="1"
                    range
                  >
                  </b-datepicker>
                </b-field>
              </div>
              <div class="column">
                <b-field label="Description for shift change">
                  <b-input v-model="shiftdesc"></b-input>
                </b-field>
              </div>
              <div class="column is-one-fifth">
                <b-field label="Submit">
                  <b-button
                    type="is-primary"
                    expanded
                    @click="SubmitShiftRequest"
                    :loading="btnLoading2"
                    >Submit Shift change request</b-button
                  >
                </b-field>
              </div>
            </div>
          </div>
        </section></b-tab-item
      >
      <b-tab-item label="Sick Leave" icon="notes-medical">
        <section class="hero">
          <div class="notification is-danger is-light">
            <div class="columns">
              <div class="column is-one-fifth">
                <b-field label="Select sick leave range">
                  <b-datepicker
                    v-model="sickleavedates"
                    placeholder="Click to select..."
                    icon="calendar"
                    trap-focus
                    :first-day-of-week="1"
                    range
                  >
                  </b-datepicker>
                </b-field>
              </div>
              <div class="column">
                <b-field label="Description">
                  <b-input v-model="sickdesc"></b-input>
                </b-field>
              </div>
              <div class="column is-one-fifth">
                <b-field label="Submit">
                  <b-button
                    type="is-primary"
                    expanded
                    @click="SubmitSickLeave"
                    :loading="SubmitSickLeaveBtnLoading"
                    >Submit sick leave request</b-button
                  >
                </b-field>
              </div>
            </div>
          </div>
        </section></b-tab-item
      >
    </b-tabs>

    <b-table :data="requests" class="" paginated :loading="loading">
      <template #empty>
        <div class="has-text-centered">No records</div>
      </template>
      <b-table-column
        field="date_start"
        label="Date Range"
        v-slot="props"
        centered
        width="250"
      >
        {{ props.row.date_start }} - {{ props.row.date_end }}
      </b-table-column>
      <b-table-column
        field="date_submit"
        label="Date Added"
        v-slot="props"
        centered
        width="250"
      >
        {{ props.row.date_submit }}
      </b-table-column>
      <b-table-column field="desc" label="Request Type" v-slot="props" centered>
        <TypeColumn :request="props.row" />
      </b-table-column>
      <b-table-column field="desc" label="Description" v-slot="props" centered>
        {{ props.row.desc }}
      </b-table-column>
      <b-table-column field="" label="Status" v-slot="props" centered>
        <StatusColumn :request="props.row" />
      </b-table-column>
    </b-table>
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
import StatusColumn from "../components/DaysOff/Requests/StatusColumn";
import TypeColumn from "../components/DaysOff/Requests/TypeColumn";
export default {
  name: "leave",
  components: { StatusColumn, TypeColumn },
  mounted() {
    this.getDaysOff();
    // this.$store.dispatch("getLogs", {datefrom: this.moment(this.datefrom).format('YYYY-MM-DD HH:mm:ss'), dateto: this.moment(this.dateto).format('YYYY-MM-DD HH:mm:ss') });
  },
  methods: {
    SubmitShiftRequest() {
      if (!this.shiftchangedates.length) {
        this.$buefy.snackbar.open({
          duration: 5000,
          message: "You need to set dates range.",
          type: "is-danger",
          position: "is-bottom-left",
        });
        return;
      }
      if (!this.shiftdesc.length) {
        this.$buefy.snackbar.open({
          duration: 5000,
          message: "You need to give some description.",
          type: "is-danger",
          position: "is-bottom-left",
        });
        return;
      }
      this.btnLoading2 = true;
      this.$http
        .post("./scheduleapi/leave/submit", {
          datestart: this.moment(this.shiftchangedates[0]).format("YYYY-MM-DD"),
          dateend: this.moment(this.shiftchangedates[1]).format("YYYY-MM-DD"),
          desc: this.shiftdesc,
          mode: 2,
        })
        .then((r) => {
          this.btnLoading2 = false;
          if (r.data.result == "error") {
            this.$buefy.snackbar.open({
              duration: 5000,
              message: r.data.msg,
              type: "is-danger",
              position: "is-bottom-left",
            });
            return;
          }
          if (r.data.response === "success") {
            this.$buefy.snackbar.open(`Request submitted successfuly`);
            this.getDaysOff(this.$route.params.agentid);
          }
        });
    },
    SubmitAddition() {
      if (!this.vacationdates.length) {
        this.$buefy.snackbar.open({
          duration: 5000,
          message: "You need to set dates range.",
          type: "is-danger",
          position: "is-top",
        });
        return;
      }
      this.btnLoading = true;
      this.$http
        .post("./scheduleapi/leave/submit", {
          datestart: this.moment(this.vacationdates[0]).format("YYYY-MM-DD"),
          dateend: this.moment(this.vacationdates[1]).format("YYYY-MM-DD"),
          desc: this.desc,
          mode: 1,
        })
        .then((r) => {
          this.btnLoading = false;
          if (r.data.response == "error") {
            this.$buefy.snackbar.open({
              duration: 5000,
              message: r.data.msg,
              type: "is-danger",
              position: "is-bottom-left",
            });
            return;
          }
          if (r.data.response === "success") {
            this.$buefy.snackbar.open(`Request submitted successfuly`);
            this.getDaysOff(this.$route.params.agentid);
          }
        });
    },
    SubmitSickLeave() {
      if (!this.sickleavedates.length) {
        this.$buefy.snackbar.open({
          duration: 5000,
          message: "You need to set dates range.",
          type: "is-danger",
          position: "is-bottom-left",
        });
        return;
      }
      this.SubmitSickLeaveBtnLoading = true;
      this.$http
        .post("./scheduleapi/leave/submit", {
          datestart: this.moment(this.sickleavedates[0]).format("YYYY-MM-DD"),
          dateend: this.moment(this.sickleavedates[1]).format("YYYY-MM-DD"),
          desc: this.sickdesc,
          mode: 3,
        })
        .then((r) => {
          this.SubmitSickLeaveBtnLoading = false;
          if (r.data.response == "error") {
            this.$buefy.snackbar.open({
              duration: 5000,
              message: r.data.msg,
              type: "is-danger",
              position: "is-bottom-left",
            });
            return;
          }
          if (r.data.response === "success") {
            this.$buefy.snackbar.open(`Request submitted successfuly`);
            this.getDaysOff(this.$route.params.agentid);
          }
        });
    },
    getDaysOff() {
      this.loading = true;
      const params = [
        `order=id`,
        `page=${this.page}`,
        `perpage=${this.perPage}`,
        `orderdir=desc`,
      ].join("&");

      this.$http.get(`./scheduleapi/leave/get?${params}`).then((r) => {
        if (r.data.response === "success") {
          this.requests = r.data.data;
          this.total = r.data.total;
          this.loading = false;
        }
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
  computed: {},
  data() {
    return {
      loading: true,
      btnLoading: false,
      btnLoading2: false,
      SubmitSickLeaveBtnLoading: false,
      vacationdates: [],
      sickleavedates: [],
      shiftchangedates: [],
      shiftdesc: "",
      sickdesc: "",
      requests: [],
      desc: "",
      page: 1,
      perPage: 20,
      total: 0,
      requestFormTab: 0,
    };
  },
};
</script>
<style scoped>
.label {
  color: black;
}
</style>