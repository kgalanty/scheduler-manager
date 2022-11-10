<template>
  <b-collapse class="panel" animation="slide" v-model="collapse">
    <template #trigger="props">
      <section class="hero">
        <div class="notification is-info">
          <div class="columns">
            <div class="column">
              <b-icon icon="bars"> </b-icon>
              Editor Permissions
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
      <b-table :data="teams" class="" :loading="requestLoading">
        <template #empty>
          <div class="has-text-centered">No records</div>
        </template>
        <b-table-column field="name" label="Name" v-slot="props" width="200">
          <span v-if="props.row.parent > 0">- </span> #{{ props.row.groupid }}
          {{ props.row.name }}
        </b-table-column>
        <b-table-column
          field="desc"
          label="Adding/Removing People in Timetable"
          centered
          v-slot="props"
        >
          <b-checkbox
            v-model="permissions"
            :native-value="getNativeCheckboxValue(props.row.groupid, 1)"
            @input="setPermission(1, props.row.groupid)"
          ></b-checkbox>
        </b-table-column>
        <b-table-column
          field="desc"
          label="Stats View"
          centered
          v-slot="props"
        >
          <b-checkbox v-if="shouldShowStatsCheckbox(props.row)"
            v-model="permissions"
            :native-value="getNativeCheckboxValue(props.row.groupid, 2)"
            @input="setPermission(2, props.row.groupid)"
          ></b-checkbox>
        </b-table-column>
        <b-table-column
          field="desc"
          label="Shifts Management"
          centered
          v-slot="props"
        >
          <b-checkbox  v-if="shouldShowShiftsManagementCheckbox(props.row)"
            v-model="permissions"
            :native-value="getNativeCheckboxValue(props.row.groupid, 3)"
            @input="setPermission(3, props.row.groupid)"
          ></b-checkbox>
        </b-table-column>
        <b-table-column
          field="desc"
          label="Days Off"
          centered
          v-slot="props"
        >
          <b-checkbox v-if="shouldShowDaysOffCheckbox(props.row)"
            v-model="permissions"
            :native-value="getNativeCheckboxValue(props.row.groupid, 4)"
            @input="setPermission(4, props.row.groupid)"
          ></b-checkbox>
        </b-table-column>
      </b-table>
    </section>
  </b-collapse>
</template>
<script>
import SubmitLeaveReviewModalVue from "@/forms/SubmitLeaveReviewModal.vue";

export default {
  name: "EditorsPermissionTable",
  components: {},
  mounted() {
    if (!this.$store.state.schedule_teams?.length) {
      this.$store.dispatch("getTeams");
    }
    this.loadData();
  },
  methods: {
    shouldShowDaysOffCheckbox(row)
    {
      return (row.parent > 0 ) || (row.parent === 0 && row.children === 0)
    },
    shouldShowShiftsManagementCheckbox(row)
    {
      return row.parent === 0
    },
    shouldShowStatsCheckbox(row)
    {
      return (row.parent > 0 ) || (row.parent === 0 && row.children === 0)
    },
    getNativeCheckboxValue(group_id, permission) {
      const found = { group_id, permission };
      return found;
    },
    loadData() {
      this.getPermissions(this.$route.params.agentid);
    },
    getPermissions(agentid) {
      this.requestLoading = true;
      const that = this;
      this.$http
        .get("./scheduleapi/permissions/agent/" + agentid, {
          withCredentials: true,
        })
        .then((data) => {
          if (data.data.response === "success") {
            that.permissions = data.data.permissions;
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
    setPermission(perm, group_id) {
      this.$http
        .post("./scheduleapi/permissions/agent/" + this.$route.params.agentid, {
          perm,
          group_id,
        })
        .then((r) => {
          if (r.data.result === "success") {
            this.$buefy.toast.open({
              message: "Permissions changed",
              type: "is-success",
            });
          } else {
            this.$buefy.toast.open({
              message: "Error with request",
              type: "is-danger",
              duration: 5000,
            });
            this.loadData();
          }
        });
    },
  },
  computed: {
    teams() {
      return this.$store.state.schedule_teams;
    },
  },
  data() {
    return {
      btnLoading: false,
      requestLoading: false,
      dateexp: new Date(),
      daysoff: 0,
      requests: [],
      collapse: true,
      permissions: [],
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