<template>
  <div
    style="
      background-color: white;
      text-align: center;
      display: block;
      height: content-fit;
    "
    class="agentListContainer container"
  >
    <h1 class="title">Assign Editors</h1>

    <div class="columns">
      <div class="column" style="text-align: right">
        <b-select placeholder="Select a name" v-model="neweditor">
          <option v-for="a in admins" :value="a.id" :key="a.id">
            {{ a.firstname }} {{ a.lastname }}
          </option>
        </b-select>
      </div>
      <div class="column" style="text-align: left">
        <b-button type="is-primary" @click="addEditor">Add Editor</b-button>
      </div>
    </div>
    <h1 class="title">Assigned Editors</h1>

    <b-table :data="list" narrowed bordered class="agentsListTbl">
      <b-table-column centered label="Agent" v-slot="props">
        #{{ props.row.id }} {{ props.row.firstname }} {{ props.row.lastname }} (
        {{ props.row.username }} )
      </b-table-column>
      <b-table-column field="color" centered label="Delete" v-slot="props">
        <b-button type="is-danger" @click="removeEditor(props.row.id)"
          >Delete</b-button
        >
      </b-table-column>
    </b-table>
  </div>
</template>
<style >
.agentListContainer table td {
  background-color: rgb(214, 224, 255);
}
</style>
<script>
export default {
  name: "assigneditors",
  components: {},
  data() {
    return {
      neweditor: null,
      list: [],
    };
  },
  computed: {
    teams() {
      return this.$store.state.schedule_teams;
    },
    admins() {
      return this.$store.state.admins;
    },
  },
  mounted() {
    this.$http
      .get("./scheduleapi/verify", { withCredentials: true })
      .then((r) => {
        if (r.data.response === "success") {
          this.$store.dispatch("getAdmins");
          this.getAssignedEditors();
        } else {
          this.$router.push({ path: `/` });
        }
      });
  },
  methods: {
    removeEditor(editor_id) {
      this.$http
        .post("./scheduleapi/editors/delete", { agent_id: editor_id })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Editor has been removed",
              type: "is-success",
            });
            this.getAssignedEditors();
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
    getAssignedEditors() {
      this.$http.get("./scheduleapi/editors/list").then((response) => {
        if (response.data.response == "success") {
          this.list = response.data.list;
        }
      });
    },
    addEditor() {
      if (!this.neweditor) {
        this.$buefy.toast.open({
          message: "All fields must be filled",
          type: "is-danger",
        });
        return;
      }
      this.$http
        .post("./scheduleapi/editors/add", { agent_id: this.neweditor })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "New Editor added",
              type: "is-success",
            });
            this.new_team = null;
            this.getAssignedEditors();
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
<style scoped>
.title {
  text-decoration: underline;
}
.agentListContainer {
  display: flow-root;
}
.agentsListTbl {
  border: 1px solid black;
}
</style>
<style >
.notification a:not(.button):not(.dropdown-item) {
  text-decoration: none;
}
.section:first-child {
  margin-right: 5px;
}
.section {
  -webkit-flex: 1; /* Safari */
  -ms-flex: 1; /* IE 10 */
  flex: 1; /* Standard syntax */
  border: 1px solid rgb(211, 211, 211);
  border-radius: 5px;
}
.subtitle {
  text-align: center;
}
.table tr {
  height: 40px;
}
.sectionscontainer {
  display: -webkit-flex; /* Safari */
  display: flex; /* Standard syntax */
}
</style>