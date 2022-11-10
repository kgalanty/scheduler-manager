<template>
  <div style="background-color: white; display: flow-root">
    <section class="hero">
      <div class="hero-body">
        <p class="title">
         Permissions :: {{ agentdata.firstname }} {{ agentdata.lastname }}
        </p>
      </div>
    </section>

    <EditorsPermissionTable />
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
import ChangeDaysOffModal from "../forms/ChangeDaysOffModal";
import SubmitLeaveReviewModalVue from "../forms/SubmitLeaveReviewModal.vue";
import EditorsPermissionTable from "../components/Permissions/Tables/EditorsPermissionTable.vue";

export default {
  name: "permissions",
  components: { EditorsPermissionTable },
  mounted() {
    this.loadData();
    // this.$store.dispatch("getLogs", {datefrom: this.moment(this.datefrom).format('YYYY-MM-DD HH:mm:ss'), dateto: this.moment(this.dateto).format('YYYY-MM-DD HH:mm:ss') });
  },
  methods: {
    loadData() {
     // this.getDaysOff(this.$route.params.agentid);
      this.getAgentData(this.$route.params.agentid);
    },
    ChangeDays(daysoffentry) {
      const that = this;
      this.$buefy.modal.open({
        parent: this,
        component: ChangeDaysOffModal,
        hasModalCard: true,
        props: { daysoffentry: daysoffentry },
        trapFocus: true,
        events: {
          reloadapi() {
            that.loadData();
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
            this.$store.commit("daysoff/UPDATE_DAYSOFF", r.data.data);
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
    SubmitReview(request) {
      const that = this;
      this.$buefy.modal.open({
        parent: this,
        component: SubmitLeaveReviewModalVue,
        hasModalCard: true,
        props: { request },
        trapFocus: true,
        events: {
          reloadapi() {
            that.loadData();
          },
        },
      });
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
      DaysOffTableloadingReq: false,
      ShiftsTableloadingReq: false,
      dateexp: new Date(new Date().setFullYear(new Date().getFullYear() + 1)),
      daysoff: 0,
      datatable: [],
      agentdata: {},
      year: new Date().getFullYear(),
      requests: [],
      shiftsrequests: [],
      daysoffcollapse: false,
      shiftscollapse: false,
      //datefrom: this.moment().startOf("month").toDate(),
      //dateto: this.moment().endOf("month").toDate(),
    };
  },
};
</script>
<style>
.notification .label {
  color: white;
}
.pagination-link.is-current {
  color: #fff;
}
</style>