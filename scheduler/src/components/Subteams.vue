<template>
  <div style="width: 80%; margin: 0 auto; padding-top: 10px">
    <b-message type="is-info" v-if="subteams.length == 0">
      No subteams here.
    </b-message>
    <b-table
      :data="subteams"
      striped
      narrowed
      bordered
      mobile-cards
      class="shiftstable"
      v-if="subteams.length > 0"
    >
      <template #empty>
        <div class="has-text-centered">No records</div>
      </template>
      <b-table-column field="team" centered label="Subgroup" v-slot="props">
        <b-tag type="is-info" size="is-medium"> {{ props.row.team }}</b-tag>
      </b-table-column>
      <b-table-column field="action" centered label="Text Color" v-slot="props">
        <input
          type="color"
          name="color[]"
          :value="props.row.color"
          @change="setcolor('color', $event.target.value, props.row.group_id)"
        />
      </b-table-column>
      <b-table-column
        field="action"
        centered
        label="Background Color"
        v-slot="props"
      >
        <input
          type="color"
          name="color[]"
          :value="props.row.bgcolor"
          @change="setcolor('bgcolor', $event.target.value, props.row.group_id)"
        />
      </b-table-column>
      <b-table-column field="action" centered label="Preview" v-slot="props">
        <span
          :style="{
            padding: '3px',
            backgroundColor:
              props.row.bgcolor != null ? props.row.bgcolor : 'white',
            color: props.row.color != null ? props.row.color : 'black',
            display: 'block',
          }"
          >Test name</span
        >
      </b-table-column>
      <b-table-column
        field="action"
        centered
        label="Order"
        v-slot="props"
        width="13%"
      >
        <b-button
          class="arrowupdownbtn"
          type="is-info"
          size="is-small"
          outlined
          icon-left="arrow-up"
          v-if="props.index > 0"
          @click="moveItemUp(props.row.group_id)"
        />
        <b-button
          class="arrowupdownbtn"
          type="is-info"
          size="is-small"
          outlined
          icon-left="arrow-down"
          v-if="props.index + 1 < subteams.length"
          @click="moveItemDown(props.row.group_id)"
        />
      </b-table-column>
      <b-table-column
        field="action"
        centered
        label="Actions"
        v-slot="props"
        width="10%"
      >
        <b-button
          type="is-danger"
          @click="delSubteam(props.row.group_id)"
          icon-left="trash"
        ></b-button>
      </b-table-column>
    </b-table>
  </div>
</template>

<script>
export default {
  name: "Subteams",
  props: ["parent_team"],
  computed: {
    subteams() {
      return this.$store.state.shifts.filter(
        (i) => i.parent === this.parent_team.group_id
      );
    },
  },
  methods: {
    moveItemUp(id) {
      this.$http
        .post("./scheduleapi/teams/reorder/up", {
          id: id,
        })
        .then((r) => {
          if (r.data.response === "success") {
           // this.$store.dispatch("getTeams");
            this.$store.dispatch("getShiftsList");
          }
        });
    },
    moveItemDown(id) {
      console.log(id);
      this.$http
        .post("./scheduleapi/teams/reorder/down", {
          id: id,
        })
        .then((r) => {
          if (r.data.response === "success") {
           // this.$store.dispatch("getTeams");
            this.$store.dispatch("getShiftsList");
          }
        });
    },
    delSubteam(groupid) {
      this.$buefy.dialog.confirm({
        title: "Deleting group",
        message:
          "Are you sure you want to <b>delete</b> this group? It will delete all related information. This cannot be undone.",
        confirmText: "Delete group",
        type: "is-danger",
        hasIcon: true,
        onConfirm: () => {
          this.$http
            .post("./scheduleapi/groups/delete", { group: groupid })
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
    setcolor(c, val, groupid) {
      this.$http
        .post("./scheduleapi/subteams/color", {
          color: c,
          value: val,
          groupid: groupid,
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Color changed!",
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
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.arrowupdownbtn:hover {
  opacity: 0.6;
  transition: opacity 0.5s cubic-bezier(0.19, 0.64, 0.55, 0.93);
}
</style>
