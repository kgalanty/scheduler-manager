<template>
  <div>
    <section class="hero">
      <div class="notification is-info is-light">
        <div class="columns">
          <div class="column allrequests"><b-field>
            <b-checkbox v-model="showAll" @input="getRequests">Show All Requests</b-checkbox>
        </b-field></div>
        </div>
      </div>
    </section>
    <b-table :data="requests" narrowed bordered class="agentsListTbl" :loading="loading"  :total="total"
            paginated
            backend-pagination
            :per-page="perPage"
            @page-change="onPageChange">
     <template #empty>
        <div class="has-text-centered">No pending requests</div>
      </template>
      <b-table-column field="date_start" label="Agent" v-slot="props" centered>
        {{ props.row.a_firstname }} {{ props.row.a_lastname }}
      </b-table-column>
      <b-table-column
        field="date_start"
        label="Vacation Range"
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
      <b-table-column field="desc" label="Description" v-slot="props" centered>
        {{ props.row.desc }}
      </b-table-column>
      <b-table-column field="" label="Status" v-slot="props" centered>
        <StatusColumn :request="props.row" :submitFunction="SubmitReview" />
      </b-table-column>
    </b-table>

    
  </div>
</template>

<script>
import SubmitLeaveReviewModal from "../forms/SubmitLeaveReviewModal.vue";
import StatusColumn from './DaysOff/Requests/StatusColumn.vue'

export default {
  name: "DaysOffRequestsForm",
  components: {StatusColumn},
  computed: {},
  methods: {
    onPageChange(page) {
      this.page = page;
      this.getRequests();
    },
    getRequests() {
      this.loading = true;
      const status = this.showAll ===true ? '' : 0

      const params = [
        `all=1`,
        `status=${status}`,
        `order=id`,
        `page=${this.page}`,
        `perpage=${this.perPage}`,
        `orderdir=desc`,
      ].join("&");

      this.$http
        .get(`./scheduleapi/leave/get?${params}`)
        .then((r) => {
          if (r.data.response === "success") {
            this.requests = r.data.data;
            this.total = r.data.total;
            this.loading = false;
          }
        });
    },
    SubmitReview(request) {
      const that = this;
      this.$buefy.modal.open({
        parent: this,
        component: SubmitLeaveReviewModal,
        hasModalCard: true,
        props: { request },
        trapFocus: true,
        events: {
          reloadapi() {
            that.getRequests();
          },
        },
      });
    },
  },
  data() {
    return {
      requests: [],
      loading: false,
      showAll: false,
      page: 1,
      perPage: 20,
      total: 0,
    };
  },
  mounted() {
    this.getRequests();
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style >
.allrequests
{
  font-size:0.8em;
}
.agentsListTbl > td:first-child {
  text-align: left !important;
  font-size: 0.9em;
}
.agentsListTbl {
  background: white !important;
}
</style>
<style >
.pagination-link.is-current {
  color: white !important;
}
.b-table {
  float: left;
  width: 100%;
  padding-bottom: 10px;
}
.b-table td {
  height: auto;
  font-size: 16px;
}
.b-table tr {
  height: auto !important;
}
</style>
