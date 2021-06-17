<template>
  <div
    style="
      float: right;
      height: 100%;
      max-height: 100%;
      overflow: hidden;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      align-content: stretch;
      background-color: white;
      width:400px;
    "
    class=""
  >

    <div v-if="open === false" class="closedsidebar">
      <a @click="open = true"><b-icon icon="expand-alt" size="is-small" style="float:left"></b-icon>Agents</a>
    </div>
      <transition name="list">
    <div class=" floatright openedsidebar" v-if="open == true">
      <!-- <b-sidebar type="is-light" v-model="open" fullheight right> -->
      <div class="p-3">
        <b-button type="is-info" label="Close" @click="open = false"  size="is-small" />
        <!-- <b-button type="is-success" label="Add new group" icon-left="plus" @click="openModal"  size="is-small" /> -->
         <b-tooltip label="Show Delete icons" position="is-bottom" style="float: right;">
         <b-switch size="is-small"
         v-model="showDelete"
            type="is-primary" style=""
            ><b-icon 
                    icon="trash"
                    size="is-medium" /></b-switch>
                     </b-tooltip>
      </div>
      <b-message type="is-info" has-icon style="margin:0px 10px">
            To edit members and manage shifts & groups, <router-link :to="`/admin`">navigate here</router-link>.
        </b-message>
      <div class="p-3" v-for="(team, team_i) in teams" :key="'teams' + team_i">
        <span class="team_name">#{{team_i}} {{team.name}}</span>
        <b-table :data="team.members" narrowed bordered mobile-cards> 
          <template #empty>
                <div class="has-text-centered">No members</div>
            </template>
          <b-table-column field="Agent" centered label="Agent" v-slot="props" header-class="headerclass">
            <b-button size="is-small" :style="getStyle(props.row.bg, props.row.color)"
            @dragstart="startDrag($event, props.row)"
            draggable
              @dragend.prevent="dragEnd($event)"
            >{{ props.row.name }}</b-button>
              <!-- draggable
              class="agentName"
             
              @dragstart="startDrag($event, props.row)"
              @dragend.prevent="dragEnd($event)"
              >{{ props.row.name }}</span
            > -->
          </b-table-column>
          <b-table-column
            field="Agent"
            centered
            label="Internal Name"
            v-slot="props"  header-class="headerclass"
          >
            {{ props.row.name }}
          </b-table-column>
          <!-- <b-table-column field="Agent" centered label="Phone" v-slot="props"  header-class="headerclass">
            {{ props.row.name }}
          </b-table-column> -->
        </b-table>
      </div>
    
      <!-- </b-sidebar> -->
    </div>

  </transition>
  </div>
</template>
<script>
import AddGroupForm from '../forms/AddGroupForm.vue'
export default {
  data() {
    return {
      open: false,
      showDelete:false,
      newadmin: {}
    };
  },
  watch:
  {
    showDelete()
    {
      this.$store.dispatch('switchShowDelete', {
        val: this.showDelete
      })
    }
  },
  computed: {
    admins()
    {
      return this.$store.state.admins;
    },
    teams() {
     // console.log(this.$filterObject(this.$store.state.schedule_teams, "name", this.$route.params.team))
      return this.$filterObject(this.$store.state.schedule_teams, "name", this.$route.params.team);
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
    this.$store.dispatch('getTeams')
    this.$store.dispatch("getAdmins");
  },
  methods: {
    addMember(team_id)
    {
      this.$http
        .post("./scheduleapi/agents/assigntogroup", {team_id: team_id, agent: this.newadmin[team_id]})
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Added",
              type: "is-success",
            });
            this.$store.dispatch('getTeams')
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
          
        });
    },
    getStyle(bg, color)
    {
      if(!bg && !color)
      {
         return {
        'background-color' : 'white',
        'color': 'black'
      }
      }
      return {
        'background-color' : bg,
        'color': color
      }
    },
    agentRow(row, index)
    {
      console.log(row)
      console.log(index)
    },
    startDrag: (evt, item) => {
      evt.target.style.opacity = 0.5;
      evt.dataTransfer.dropEffect = "move";
      evt.dataTransfer.effectAllowed = "move";
     // console.log(item)
      evt.dataTransfer.setData("agentItem", JSON.stringify(item));
    },
    dragEnd(event) {
      event.target.style.opacity = "";
    },
    openModal()
    {
      this.$buefy.modal.open({
                    parent: this,
                    component: AddGroupForm,
                    hasModalCard: true,
                    trapFocus: true,
                    width: 960

      })
    }
  },
};
</script>
<style scoped>
.agentName
{
  padding: 5px 10px;
}
.agentstable {
  padding:0 !important;
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

.headerclass
{
  font-weight:bold;
  text-decoration:underline;
}
.table tr:first-child {
  height:auto;
}
.b-sidebar .sidebar-content {
  width: auto;
}
</style>
<style scoped>
.floatright {
  z-index: 10;
  background-color:white;
}
</style>
<style scoped>
.team_name
{
  text-decoration: underline;
  font-weight: bold;
}
.closedsidebar {
  position: fixed;
  top: 100px;
  right: 0;
  writing-mode: vertical-rl;
  text-orientation: upright;
  border: 1px solid black;
  border-right:0;
  background-color: white;
  border-radius:5px;
      z-index: 1;
      padding:5px;
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
}
.openedsidebar {
  border: 1px solid black;
}
</style>