<template>
  <div style="width: -webkit-fill-available">
    <section class="hero">
      <div class="notification is-info is-light">
        <div class="columns">
          <div class="column allrequests">
            <b-field> Show Answered Requests</b-field>
            <b-field>
              <b-checkbox v-model="showAll" @input="getRequests"></b-checkbox>
            </b-field>
          </div>
          <div class="column">
            <b-field>
              Filter by request type:
              <b-taginput
                v-model="requestTypeFilter"
                :data="requestTypes"
                :maxtags="requestTypes.length"
                autocomplete
                :allow-new="false"
                :open-on-focus="true"
                field="label"
                icon="chevron-right"
                placeholder="Add a type"
                type="is-info"
                @typing="getFilteredTags"
                @input="getRequests"
                :has-counter="false"
              >
              </b-taginput>
            </b-field>
          </div>
          <div class="column">
            <b-field>
              Filter by teams:
              <b-select
                placeholder="Assign a team"
                v-model="teamsfilter"
                @input="getRequests"
              >
                <option value="">-- None --</option>
                <option
                  v-for="team in teams"
                  :value="team.group_id"
                  :key="team.group_id"
                  :disabled="team.hasSubteams > 0"
                >
                  <span v-if="team.parent > 0">- </span> {{ team.name }}
                </option>
              </b-select></b-field
            >
          </div>
        </div>
      </div>
    </section>
    <b-table
      :data="requests"
      narrowed
      bordered
      class="agentsListTbl"
      :loading="loading"
      :total="total"
      paginated
      backend-pagination
      :per-page="perPage"
      @page-change="onPageChange"
    >
      <template #empty>
        <div class="has-text-centered">No pending requests</div>
      </template>
      <b-table-column
        field="date_start"
        label="Agent"
        v-slot="props"
        centered
        width="140"
      >
        {{ props.row.a_firstname }} {{ props.row.a_lastname }}
      </b-table-column>
      <b-table-column
        field="group"
        label="Group"
        v-slot="props"
        centered
        width="140"
      >
        {{ props.row.group }}
      </b-table-column>
      <b-table-column
        field="date_start"
        label="Vacation Range"
        v-slot="props"
        centered
        width="230"
      >
        {{ props.row.date_start }} - {{ props.row.date_end }}
      </b-table-column>
      <b-table-column
        field="request_type"
        label="Request Type"
        v-slot="props"
        centered
        width="100"
      >
        <TypeColumn :request="props.row" />
      </b-table-column>
      <b-table-column
        field="date_submit"
        label="Date Added"
        v-slot="props"
        centered
        width="180"
      >
        {{ props.row.date_submit }}
      </b-table-column>
      <b-table-column field="desc" label="Description" v-slot="props" centered>
        {{ props.row.desc }}
      </b-table-column>
      <b-table-column field="" label="Status" v-slot="props" centered>
        <StatusColumn
          :request="props.row"
          :submitFunction="SubmitReview"
          :hasPermission="hasPermission(props.row.agent_group_id)"
        />
      </b-table-column>
      <b-table-column field="" label="Edition" v-slot="props" centered>
        <b-button
          type="is-info"
          @click="OpenEditModal(props.row)"
          :disabled="!hasPermission(props.row.agent_group_id)"
          icon-right="pen"
        />
      </b-table-column>
    </b-table>
  </div>
</template>

<script>
import SubmitLeaveReviewModal from "../forms/SubmitLeaveReviewModal.vue";
import StatusColumn from "./DaysOff/Requests/StatusColumn.vue";
import TypeColumn from "./DaysOff/Requests/TypeColumn";
import EditLeaveModal from "../forms/EditLeaveModal.vue";
import Permissions from "..//libs/permissions";

export default {
  name: "DaysOffRequestsForm",
  components: { StatusColumn, TypeColumn },
  computed: {
    teams() {
      return this.$store.getters["teams/teams"];
    },
    LeaveManagementPermissions() {
      return Permissions.MANAGE_LEAVES;
    },
  },
  methods: {
    hasPermission(group_id) {
      return this.$store.getters.hasPermission(
        this.LeaveManagementPermissions,
        group_id
      );
    },
    OpenEditModal(request) {
      const that = this;
      this.$buefy.modal.open({
        parent: this,
        component: EditLeaveModal,
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
    getFilteredTags(input) {
      this.filteredTags = this.requestTypes.filter((i) =>
        i.label.toLowerCase().indexOf(input.toLowerCase() >= 0)
      );
    },
    onPageChange(page) {
      this.page = page;
      this.getRequests();
    },
    getRequests() {
      this.loading = true;
      const status = this.showAll === true ? "" : 0;

      const params = [
        `all=1`,
        `status=${status}`,
        `order=id`,
        `page=${this.page}`,
        `perpage=${this.perPage}`,
        `team=${this.teamsfilter}`,
        `mode=${this.requestTypeFilter.map((u) => u.id).join(",")}`,
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
      teamsfilter: "",
      requestTypeFilter: [],
      requestTypes: [
        { id: 1, label: "Vacation" },
        { id: 2, label: "Shift Change" },
        { id: 3, label: "Sick Leave" },
      ],
      filteredTags: [],
    };
  },
  mounted() {
    this.getRequests();
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style >
.allrequests {
  font-size: 0.9em;
}
.agentsListTbl {
  font-size: 0.8em;
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
