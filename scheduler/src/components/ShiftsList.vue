<template>
  <div>
    <b-table :data="shifts" striped narrowed bordered mobile-cards class="shiftstable">
      <b-table-column field="Shift" centered label="Teams" v-slot="props">
        <strong>#{{ props.row.group_id }} {{ props.row.team }}</strong>
        <p></p>
        <b-button
          type="is-danger"
          icon-left="trash"
          @click="confirmCustomDelete(props.row.group_id)"
          size="is-small"
        >
          Delete
        </b-button>
      </b-table-column>
      <b-table-column field="Shift" centered label="Shifts" v-slot="props">
        <b-table :data="props.row.shifts" striped narrowed mobile-cards>
          <template #empty>
            <div class="has-text-centered">No records</div>
          </template>
          <b-table-column field="Shift" centered label="Shift" v-slot="props">
            {{ props.row.from }} - {{ props.row.to }}
          </b-table-column>
          <b-table-column field="Shift" centered label="Action" v-slot="props">
            <b-button
              size="is-small"
              type="is-info"
              icon-left="trash"
              @click="removeShift(props.row.id)">Remove</b-button>
          </b-table-column>
        </b-table>
      </b-table-column>
    </b-table>
  </div>
</template>

<script>
export default {
  name: "AgentsList",
  computed: {
    shifts() {
      return this.$store.state.shifts;
    },
  },
  methods: {
    confirmCustomDelete(group) {
      this.$buefy.dialog.confirm({
        title: "Deleting group",
        message:
          "Are you sure you want to <b>delete</b> this group? It will delete all related information. This action cannot be undone.",
        confirmText: "Delete group",
        type: "is-danger",
        hasIcon: true,
        onConfirm: () => {
          this.$http
            .post("./scheduleapi/groups/delete", { group: group })
            .then((response) => {
              if (response.data.response == "success") {
                this.$buefy.toast.open({
                  message: "Removed!",
                  type: "is-success",
                });
                this.$store.dispatch("getShiftsList");
                this.$store.dispatch("getTeams");
              } else {
                this.$buefy.toast.open({
                  message: response.data.response,
                  type: "is-danger",
                });
              }
            });
        },
      });
    },

    removeShift(id) {
      this.$http
        .post("./scheduleapi/shifts/delete", { id })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Removed!",
              type: "is-success",
            });
            this.$store.dispatch("getShiftsList");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style >
.b-table {
  float: left;
  width: 100%;
  border: 1px solid black;
}
.b-table td {
  height: auto;
}
.b-table tr {
  height: auto !important;
}

.shiftstable thead:first-child th {
background: rgb(146,179,255);
background: linear-gradient(180deg, rgba(146,179,255,1) 0%, rgba(96,56,255,1) 100%);
color:white;
}
</style>
