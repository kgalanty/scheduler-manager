<template>
  <div style="background-color: white; display: flow-root">
    <section class="hero">
      <div class="hero-body">
        <p class="title">
          Days Off :: {{ agentdata.firstname }} {{ agentdata.lastname }}
        </p>
      </div>
    </section>

    <DaysOffTableSkeleton :mode="1" cancel-column>
      <template #subtitle>Days Off Requests</template>
    </DaysOffTableSkeleton>

    <DaysOffTableSkeleton :mode="2">
      <template #subtitle>Shifts Change Requests</template>
    </DaysOffTableSkeleton>

    <DaysOffTableSkeleton :mode="3">
      <template #subtitle>Sick Leave Requests</template>
    </DaysOffTableSkeleton>
    <!-- <DaysOffRequestsTable /> -->

    <!-- <ShiftsChangeRequestsTable />

    <SickLeaveRequestsTable /> -->

    <section class="hero">
      <div class="notification is-info">
        <div class="columns">
          <div class="column">
            <b-field label="Assign a year">
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
              <b-numberinput
                v-model="daysoff"
                step="1"
                type="is-primary is-light"
              ></b-numberinput>
            </b-field>
          </div>

          <div class="column">
            <b-field label="Submit">
              <b-button
                type="is-primary is-light"
                expanded
                @click="SubmitAddition"
                :loading="btnLoading"
                >Add</b-button
              >
            </b-field>
          </div>
        </div>
      </div>
    </section>
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
            @click="ChangeDays(props.row)"
            icon-right="pen"
            type="is-primary"
            size="is-small"
            >Edit</b-button
          >
        </b-field>
      </b-table-column>
    </b-table>
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
import ChangeDaysOffModal from "../forms/ChangeDaysOffModal";
import SubmitLeaveReviewModalVue from "../forms/SubmitLeaveReviewModal.vue";
// import DaysOffRequestsTable from "../components/DaysOff/Tables/DaysOffRequestsTable.vue";
// import ShiftsChangeRequestsTable from "../components/DaysOff/Tables/ShiftsChangeRequestsTable.vue";
// import SickLeaveRequestsTable from '../components/DaysOff/Tables/SickLeaveRequestsTable.vue'
import DaysOffTableSkeleton from "../components/shared/DaysOffTableSkeleton.vue";

export default {
  name: "daysoff",
  components: { DaysOffTableSkeleton },
  mounted() {
    
    this.loadData();
      
    // this.$store.dispatch("getLogs", {datefrom: this.moment(this.datefrom).format('YYYY-MM-DD HH:mm:ss'), dateto: this.moment(this.dateto).format('YYYY-MM-DD HH:mm:ss') });
  },
  methods: {
    loadData() {
      this.getAgentData(this.$route.params.agentid);
      this.getDaysOff(this.$route.params.agentid);
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
    canLoadThisPage() {
      return (
        (this.$store.state.editorPermissionsGroups &&
          this.$store.state.editorPermissionsGroups[4]?.length > 0 && this.$store.state.editorPermissionsGroups[4].includes(this.agentdata.group_id)) ||
        this.$store.state.adminPermission > 0
      );
    },
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