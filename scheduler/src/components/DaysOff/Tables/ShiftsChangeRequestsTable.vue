<template>
  <b-collapse class="panel" animation="slide" v-model="daysoffcollapse">
    <template #trigger="props">
      <section class="hero">
        <div class="notification is-info">
          <div class="columns">
            <div class="column">
              <b-icon icon="bars"> </b-icon>Shifts Change Requests
            </div>
            <div class="column is-1" style="flex: none; width: auto">
              <b-icon :icon="props.open ? 'chevron-down' : 'chevron-right'">
              </b-icon>
            </div>
          </div>
        </div>
      </section>
    </template>
    <section class="hero">
      <b-table :data="requests" class="" paginated :loading="requestLoading">
        <template #empty>
          <div class="has-text-centered">No records</div>
        </template>
        <b-table-column
          field="date_submit"
          label="Added"
          v-slot="props"
          centered
          width="300"
        >
          {{ props.row.date_submit }}
        </b-table-column>
        <b-table-column
          field="date_start"
          label="Leave Dates"
          v-slot="props"
          centered
          width="300"
        >
          {{ props.row.date_start }} - {{ props.row.date_end }}
        </b-table-column>
        <b-table-column
          field="desc"
          label="Agent Comment"
          v-slot="props"
          centered
        >
          {{ props.row.desc }}
        </b-table-column>
        <b-table-column
          field="approve_status"
          label="Approve Status"
          v-slot="props"
          centered
          width="400"
        >
          <StatusColumn :request="props.row" :submitFunction="SubmitReview" />
        </b-table-column>
        <!-- <b-table-column field="cancel" label="Cancel" centered width="100">
          <b-button type="is-warning" size="is-small">Cancel</b-button>
        </b-table-column> -->
      </b-table>
    </section>
  </b-collapse>
</template>
<script>
import SubmitLeaveReviewModalVue from "@/forms/SubmitLeaveReviewModal.vue";
import StatusColumn from "@/components/DaysOff/Requests/StatusColumn.vue";

export default {
  name: "DaysOffRequestsTable",
  components: { StatusColumn },
  mounted() {
    this.loadData();
  },
  methods: {
    loadData() {
      this.getDaysOffRequest(this.$route.params.agentid);
    },
    getDaysOffRequest(agentid) {
      this.requestLoading = true;
      this.$http
        .get("./scheduleapi/leave/get/" + agentid + "?mode=2", {
          withCredentials: true,
        })
        .then(({ data }) => {
          if (data.response === "success") {
            this.requests = data.data;
          }

          this.requestLoading = false;
        });
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
  computed: {},
  data() {
    return {
      btnLoading: false,
      requestLoading: false,
      dateexp: new Date(),
      daysoff: 0,
      requests: [],
      daysoffcollapse: false,
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