<template>
  <b-collapse class="panel" animation="slide" v-model="daysoffcollapse">
    <template #trigger="props">
      <section class="hero">
        <div class="notification is-info">
          <div class="columns">
            <p class="card-header-title">
              <b-icon icon="bars"></b-icon> <slot name="subtitle"></slot>
            </p>
            <a class="card-header-icon">
              <b-icon :icon="props.open ? 'chevron-down' : 'chevron-right'">
              </b-icon>
            </a>
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
        <b-table-column
          field="cancel"
          label="Cancel"
          centered
          width="100"
          v-slot="props"
          v-if="cancelColumn"
        >
          <b-button
            type="is-warning"
            size="is-small"
            @click="confirmCancel(props.row.id)"
            v-if="props.row.cancelled == 0"
            >Cancel</b-button
          >
          <b-tag type="is-info is-light" v-if="props.row.cancelled == 1"
            >Cancelled</b-tag
          >
        </b-table-column>
      </b-table>
    </section>
  </b-collapse>
</template>
<script>
import SubmitLeaveReviewModalVue from "@/forms/SubmitLeaveReviewModal.vue";
import StatusColumn from "@/components/DaysOff/Requests/StatusColumn.vue";

export default {
  name: "DaysOffTableSkeleton",
  components: { StatusColumn },
  props: {
    mode: {
      type: Number,
      required: true,
    },
    cancelColumn: {
      type: Boolean,
      default: false
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    confirmCancel(id) {
      this.$buefy.dialog.confirm({
        title: "Cancel Leave",
        message:
          "Are you sure you want to <b>cancel</b> this leave? This will not return leave days to agent's pool.",
        confirmText: "Yes, cancel it",
        type: "is-warning",
        hasIcon: true,
        onConfirm: () => {
          this.$http
            .post("./scheduleapi/leave/cancel/" + id, {
              withCredentials: true,
              daysreturn: 0,
            })
            .then(({ data }) => {
              if (data.response === "success") {
                this.$buefy.toast.open("Success");
                this.loadData();
              }

              this.requestLoading = false;
            });
        },
      });
    },
    loadData() {
      this.getDaysOffRequest(this.$route.params.agentid);
    },
    getDaysOffRequest(agentid) {
      this.requestLoading = true;
      this.$http
        .get("./scheduleapi/leave/get/" + agentid + "?mode=" + this.mode, {
          withCredentials: true,
        })
        .then(({ data }) => {
          if (data.response === "success") {
            this.requests = data.data;
            this.daysoffcollapse = true
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
.notification .label,
.card-header-title {
  color: white;
}
.pagination-link.is-current {
  color: #fff;
}
</style>