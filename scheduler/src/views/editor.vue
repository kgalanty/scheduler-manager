<template>
  <div style="background-color: white">
    <b-tabs v-model="activeTab" type="is-toggle">
      <b-tab-item label="Shifts Management" v-if="hasAnyGroupShiftsManagement">
        <div class="sectionscontainer">
          <section class="section adminsection">
            <h1 class="title">Add New Shift</h1>
            <h2 class="subtitle" style="align-items: flex-start">
              <b-field
                label="Team"
                style="margin-right: 20px; margin-bottom: 0"
              >
                <b-select placeholder="Select a team" v-model="team_id">
                  <option
                    v-for="(option, i) in topteams"
                    :value="option.group_id"
                    :key="i"
                  >
                    <span v-if="option.parent > 0">- </span> {{ option.name }}
                  </option>
                </b-select>
              </b-field>
              <b-field
                label="Time from"
                style="margin-right: 20px; margin-bottom: 0"
              >
                <b-timepicker
                  inline
                  v-model="timefrom"
                  rounded
                  placeholder="Click to select..."
                  icon="clock"
                  :date-formatter="formatter"
                >
                </b-timepicker>
              </b-field>
              <b-field label="Time to" style="margin-bottom: 0">
                <b-timepicker
                  inline
                  v-model="timeto"
                  rounded
                  placeholder="Click to select..."
                  icon="clock"
                >
                </b-timepicker>
              </b-field>
            </h2>
            <h2 class="subtitle">
              <b-button type="is-primary" @click="addShift"
                >Add New Shift</b-button
              >
            </h2>
          </section>
        </div>
        <ShiftsListEditor />
      </b-tab-item>
    <b-tab-item label="Days Off Requests"  v-if="hasAnyGroupDaysOffManagement">
        <section class="section adminsection">
          <h1 class="title agentstitle">Days Off Requests</h1>
          <div class="subtitle">
             <DaysOffRequestsForm />
          </div>
        </section>
      </b-tab-item>
      <b-tab-item label="Agents' List"  v-if="hasAnyGroupDaysOffManagement">
        <section class="section adminsection">
          <h1 class="title agentstitle">Agent's List</h1>
          <div class="subtitle">
             <AgentsListEditor />
          </div>
        </section>
      </b-tab-item>
    </b-tabs>
  </div>
</template>
<script>
import ShiftsListEditor from "../components/ShiftsListEditor.vue";
import DaysOffRequestsForm from '../components/DaysOffRequestsForm.vue'
import AgentsListEditor from '../components/AgentsListEditor.vue'
import AgentsMixin from '../mixins/AgentsMixin';

export default {
  name: "EditorCP",
  mixins: [AgentsMixin],
  components: {
    ShiftsListEditor,
    DaysOffRequestsForm,
    AgentsListEditor
  },
  data() {
    return {
      activeTab: undefined,
      timefrom: null,
      timeto: null,
      team_id: null,
      new_team: "",
      datefrom: "",
      dateto: "",
      parentteam: 0,
    };
  },
  computed: {
    hasAnyGroupShiftsManagement()
    {
        return this.$store.state.editorPermissionsGroups && this.$store.state.editorPermissionsGroups[3]?.length > 0
    },
    hasAnyGroupDaysOffManagement()
    {
        return this.$store.state.editorPermissionsGroups && this.$store.state.editorPermissionsGroups[4]?.length > 0
    },
    teams() {
      return this.$store.getters['teams/teams']
    },
    topteams() {
      return this.$store.getters.topteams('Editor')
    },
    admins() {
      return this.$store.state.admins;
    },
    shifts() {
      return this.$store.state.shifts;
    },
    days() {
      return [
        this.moment().day(1).format("ddd DD.MM"),
        this.moment().day(2).format("ddd DD.MM"),
        this.moment().day(3).format("ddd DD.MM"),
        this.moment().day(4).format("ddd DD.MM"),
        this.moment().day(5).format("ddd DD.MM"),
        this.moment().day(6).format("ddd DD.MM"),
        this.moment().day(7).format("ddd DD.MM"),
      ];
    },
  },
  mounted() {
    this.$http
      .get("./scheduleapi/verify", { withCredentials: true })
      .then(() => {
      
        if (this.$store.getters.canShowEditorCP) {
          this.$store.dispatch("getAdmins");
          // this.$store.dispatch("getShiftsList");
          // this.$store.dispatch("getTeams");
          this.$store.dispatch("teams/getTeams");

        } else {
          this.$router.push({ path: `/` });
        }
      });


  },
  methods: {

    formatter(d) {
      return d.toLocaleDateString();
    },
    addTeam() {
      if (!this.new_team) {
        this.$buefy.toast.open({
          message: "All fields must be filled",
          type: "is-danger",
        });
        return;
      }
      this.$http
        .post("./scheduleapi/agents/addgroup", {
          name: this.new_team,
          parent: this.parentteam,
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "New Team Added",
              type: "is-success",
            });
            this.new_team = "";
            this.$store.dispatch("getShiftsList");
            this.$store.dispatch("teams/getTeams");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
    addShift() {
      if (!this.timefrom || !this.timeto || !this.team_id) {
        this.$buefy.toast.open({
          message: "All fields must be filled",
          type: "is-danger",
        });
        return;
      }
      this.$http
        .post("./scheduleapi/shift/new", {
          from: this.moment(this.timefrom).format("HH:mm"),
          to: this.moment(this.timeto).format("HH:mm"),
          team_id: this.team_id,
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Added!",
              type: "is-success",
            });
            this.$store.dispatch("teams/getTeams");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
    getFirstDay() {
      this.moment();
    },
  },
};
</script>
<style scoped>
.agentstitle {
  text-align: center;
}
.adminsection {
  background: rgb(198, 204, 255);
  background: linear-gradient(
    167deg,
    rgba(198, 204, 255, 1) 1%,
    rgba(79, 158, 251, 1) 100%
  );
  flex-grow: 1;
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
  display: flex;
  align-items: center;
  justify-content: center;
}
.table tr {
  height: 40px;
}
.sectionscontainer {
  display: -webkit-flex; /* Safari */
  display: flex; /* Standard syntax */
  flex-grow: 1;
}
</style>