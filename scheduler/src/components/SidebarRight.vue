<template>
  <div
    id="sidebarright"
    style="
      float: right;
      height: 100%;
      max-height: 100%;
      overflow: hidden;
      overflow-y: auto;
      flex-direction: column;
      align-content: stretch;
      background-color: white;
      width: 600px;
      position: sticky;
      top: 0;
      z-index: 300;
    "
    class=""
  >
    <div v-if="open === false" class="closedsidebarright">
      <a @click="open = true"
        ><b-icon icon="expand-alt" size="is-small" style="float: left"></b-icon
        >Agents</a
      >
    </div>
    <transition name="list">
      <div class="floatright openedsidebar" v-if="open == true">
        <!-- <b-sidebar type="is-light" v-model="open" fullheight right> -->
        <div class="p-3" v-if="canShowEditorContent">
          <b-button
            type="is-info"
            label="Close"
            @click="open = false"
            size="is-small"
          />
          <!-- <b-button type="is-success" label="Add new group" icon-left="plus" @click="openModal"  size="is-small" /> -->
          <b-tooltip
            label="Deselect all yellow checkboxes on the timetable"
            position="is-left"
            style="float: right"
          ><b-checkbox
            @input="deselectYellowCheckboxes"
            v-model="yellowCheckbox"
            style="float: right; padding-top: 6px"
            type="is-warning"
          ></b-checkbox></b-tooltip>

          <b-tooltip
            label="Show Delete icons"
            position="is-bottom"
            style="float: right"
          >
            <b-switch
              size="is-small"
              v-model="showDelete"
              type="is-primary"
              style=""
              ><b-icon icon="trash" size="is-medium"
            /></b-switch>
          </b-tooltip>
        </div>
        <b-message type="is-info" has-icon style="margin: 0px 10px" v-if="canShowEditorContent">
          To put an agent into schedule, drag the name and drop to desired day.
          <br />
          To edit members and manage shifts & groups,
          <router-link :to="`/admin`">navigate here</router-link>.
        </b-message>
        <span class="teamlist">
          <div
            class="p-1"
            v-for="(team, team_i) in teams"
            :key="'teams' + team_i"
          >
            <b-table
              :data="team.members"
              narrowed
              bordered
              mobile-cards
              v-if="team.members"
            >
              <template #empty>
                <div class="has-text-centered">No members</div>
              </template>
              <b-table-column
                field="Agent"
                centered
                :label="team.data.group"
                v-slot="props"
                cell-class="buttonscells"
                header-class="groupnameheaderclass"
              >
                <b-button
                  :style="getStyle(team.data.bgcolor, team.data.color)"
                  @dragstart="startDrag($event, props.row, team_i)"
                  draggable
                  @dragend.prevent="dragEnd($event)"
                  >{{
                    props.row.ldap_username
                      ? props.row.ldap_username
                      : props.row.firstname + " " + props.row.lastname
                  }}</b-button
                >
                <!-- draggable
              class="agentName"
             
              @dragstart="startDrag($event, props.row)"
              @dragend.prevent="dragEnd($event)"
              >{{ props.row.name }}</span
            > -->
              </b-table-column>
              <b-table-column
                field="Agent"
                label="Internal Name"
                v-slot="props"
                header-class="headerclass"
                cell-class="internalnamecells"
              >
                <a
                  :href="
                    'https://tmdhosting.slack.com/team/' + props.row.slackid
                  "
                  target="_blank"
                  v-if="props.row.slackid"
                >
                  <img
                    style="height: 15px"
                    src="https://upload.wikimedia.org/wikipedia/commons/d/d5/Slack_icon_2019.svg"
                    :alt="props.row.slackid"
                /></a>
                {{ props.row.firstname }} {{ props.row.lastname }}
              </b-table-column>
              <b-table-column
                field="Phone"
                centered
                label="Phone"
                v-slot="props"
                header-class="headerclass"
                cell-class="internalnamecells"
              >
                {{ props.row.phone }}
              </b-table-column>
              <b-table-column
                field="Agent"
                centered
                label="Off Days"
                header-class="headerclass"
                v-slot="props"
                cell-class="internalnamecells"
                width="50"
              >
                {{ props.row.vacations }}
              </b-table-column>
              <b-table-column
                field="Agent"
                centered
                label="Remaining Vacations"
                v-slot="props"
                header-class="headerclass"
                cell-class="internalnamecells"
                width="50"
              >
                {{ props.row.daysoff }}
              </b-table-column>
              <!-- <b-table-column field="Agent" centered label="Phone" v-slot="props"  header-class="headerclass">
            {{ props.row.name }}
          </b-table-column> -->
            </b-table>
          </div>
          <div class="p-3">
            <b-button
              size="is-medium"
              :style="getStyle('red', 'yellow')"
              @dragstart="
                startDrag($event, { item: 'placeholder', adminid: -1 })
              "
              draggable
              @dragend.prevent="dragEnd($event)"
              >HELP NEEDED</b-button
            >
            Placeholder
          </div>
        </span>
        <!-- </b-sidebar> -->
      </div>
    </transition>
  </div>
</template>
<script>
//import AddGroupForm from "../forms/AddGroupForm.vue";
export default {
  props: ['canShowEditorContent'],
  data() {
    return {
      open: false,
      showDelete: false,
      newadmin: {},
      yellowCheckbox: true,
    };
  },
  watch: {
    open(val) {
      if (val) {
        if (document.documentElement.clientWidth < 2400) {
          document.getElementById("mainwindow").style.marginLeft = 0;
          document.getElementById("mainwindow").style.width = "70%";
        }
      } else {
        // if (document.documentElement.clientWidth < 2400) {
        document.getElementById("mainwindow").style.marginLeft = "";
        document.getElementById("mainwindow").style.width = "";
        // }
      }
      //console.log(val);
    },
    showDelete() {
      this.$store.dispatch("switchShowDelete", {
        val: this.showDelete,
      });
    },
  },
  computed: {
    teams() {
      // console.log(this.$filterObject(this.$store.state.schedule_teams, "name", this.$route.params.team))
      // if (this.$route.name == "Vacationing") {
      //   return this.$store.state.schedule_teams;
      // }
      return this.$store.state.agentsGroups.groups_subgroups;
      // const filterByName =  this.$filterObject(
      //   this.$store.state.schedule_teams,
      //   "name",
      //   this.$route.params.team
      // );
      // //filter out only top level teams using 'parent' key
      // return this.$filterObject(
      //   filterByName,
      //   "parent",
      //   0
      // );
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
  destroyed() {
    document.getElementById("mainwindow").style.marginLeft = "";
    document.getElementById("mainwindow").style.width = "";
  },
  mounted() {
    this.$store.dispatch("getTeams");
    this.$store.dispatch("getAdmins");
  },
  methods: {
    deselectYellowCheckboxes() {
      this.$store.commit("SetGroupShiftsDrop", []);
      setTimeout(() => {
        this.yellowCheckbox = true;
      }, 100);
    },
    addMember(team_id) {
      this.$http
        .post("./scheduleapi/agents/assigntogroup", {
          team_id: team_id,
          agent: this.newadmin[team_id],
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Added",
              type: "is-success",
            });
            this.$store.dispatch("getTeams");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
    getStyle(bg, color) {
      if ((!bg && !color) || (bg == "null" && color == "null")) {
        return {
          backgroundColor: "rgb(202, 202, 202)",
          color: "black",
        };
      }
      return {
        "background-color": bg,
        color: color,
      };
    },
    // agentRow(row, index) {
    //   //console.log(row);
    //   //console.log(index);
    // },
    startDrag: (evt, item, team_i) => {
      evt.target.style.opacity = 0.5;
      evt.dataTransfer.dropEffect = "move";
      evt.dataTransfer.effectAllowed = "move";
      item.team_i = parseInt(team_i);

      //document.getElementById('sidebarright').style.zIndex = -1
      // console.log(item)
      evt.dataTransfer.setData("agentItem", JSON.stringify(item));
    },
    dragEnd(event) {
      event.target.style.opacity = "";
    },
    // openModal() {
    //   this.$buefy.modal.open({
    //     parent: this,
    //     component: AddGroupForm,
    //     hasModalCard: true,
    //     trapFocus: true,
    //     width: 960,
    //   });
    // },
  },
};
</script>
<style scoped>
</style>
<style >
.buttonscells {
  margin: 0;
  padding: 0;
  display: contents;
}
.internalnamecells {
  margin: 0;
  padding: 0;
  font-size: 0.8em;
}
.buttonscells button {
  width: 100%;
}
.closedsidebarright {
  position: fixed;
  top: 100px;
  right: 0;
  writing-mode: vertical-rl;
  text-orientation: upright;
  border: 1px solid black;
  border-right: 0;
  background-color: white;
  border-radius: 5px;
  z-index: 1;
  padding: 5px;
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
.openedsidebar {
  border: 1px solid black;
}
</style>
<style scoped>
.teamlist {
  height: 100vh;
  overflow-y: scroll;
  display: block;
}
.agentName {
  padding: 5px 10px;
}
.agentstable {
  padding: 0 !important;
}
.list-enter,
.list-leave-to {
  visibility: hidden;
  height: 0;
  margin: 0;
  padding: 0;
  opacity: 0;
  transform: translateX(90px);
}

.list-enter-active,
.list-leave-active {
  transition: all 0.3s;
}
</style>
<style >
.headerclass {
  font-weight: bold;
  background: rgb(185, 221, 255);
}
.groupnameheaderclass {
  color: white;
  vertical-align: middle !important;
}
.table tr:first-child {
  height: auto;
}
.b-sidebar .sidebar-content {
  width: auto;
}

.floatright {
  z-index: 10;
  background-color: white;
}

.team_name {
  text-decoration: underline;
  font-weight: bold;
}
</style>