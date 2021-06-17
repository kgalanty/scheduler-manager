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
          <b-button
          type="is-success"
          icon-left="plus"
          @click="addOnCallShift(props.row.group_id)"
          size="is-small"
        >
          Add On Call Shift
        </b-button>
      </b-table-column>
      <b-table-column field="Shift" centered label="Shifts" v-slot="props">
        <b-table :data="props.row.shifts" striped narrowed mobile-cards>
          <template #empty>
            <div class="has-text-centered">No records</div>
          </template>
          <b-table-column field="Shift" centered label="Shift" v-slot="props">
            <span v-if="props.row.from==='on'&&props.row.to==='call'">
                  <b-button
              size="is-small"
              type="is-info"
              icon-left="phone-volume"
             >On Call</b-button>

            </span>
            <span v-else>
            {{ props.row.from }} - {{ props.row.to }}
            </span>
          </b-table-column>
          <b-table-column field="Shift" centered label="Action" v-slot="props">
            <b-button
              size="is-small"
              type="is-info"
              icon-left="trash"
              @click="removeShift(props.row.shiftid)">Remove</b-button>
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
    addOnCallShift(group)
    { 
       this.$http
            .post("./scheduleapi/shift/new", { team_id: group, oncall: 1 })
            .then((response) => {
              if (response.data.response == "success") {
                this.$buefy.toast.open({
                  message: "Added!",
                  type: "is-success",
                });
                this.$store.dispatch("getShiftsList");
               // this.$store.dispatch("getTeams");
              } else {
                this.$buefy.toast.open({
                  message: response.data.response,
                  type: "is-danger",
                });
              }
            });
    },
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
background: rgb(165,197,255);
background: linear-gradient(180deg, rgba(165,197,255,1) 0%, rgba(40,68,207,1) 100%);
color:white;
}
</style>
