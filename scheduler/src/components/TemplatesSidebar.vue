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
      width: 400px;
    "
    class=""
  >
    <div v-if="open === false" class="closedsidebar">
      <a @click="open = true"
        ><b-icon icon="expand-alt" size="is-small" style="float: left"></b-icon
        >Templates</a
      >
    </div>
    <transition name="list">
      <div class="floatright openedsidebar" v-if="open == true">
        <!-- <b-sidebar type="is-light" v-model="open" fullheight right> -->
        <div class="p-3">
          <b-button
            type="is-info"
            label="Close"
            @click="open = false"
            size="is-small"
          />
        </div>
        <b-message type="is-info" has-icon style="margin: 0px 10px">
          Here you can store current week plan as a template.
        </b-message>
        <div class="p-3">
          <b-field label="Name of template">
            <b-input v-model="name" placeholder="Template name"></b-input>
            <b-select placeholder="Select a shift" v-model="templateShifts">
              <option value="All">All</option>
              <option
                v-for="option in shifts"
                :value="option.id"
                :key="option.id"
              >
                {{ option.from }}-{{ option.to }}
              </option>
            </b-select>

            <b-button type="is-primary" @click="addTemplate">Add</b-button>
          </b-field>
        </div>

        <!-- </b-sidebar> -->
        <b-table :data="templates" detailed class="templateslistTbl">
           <template #empty>
            <div class="has-text-centered">No templates found</div>
          </template>
          <b-table-column
            field="id"
            label="ID"
            width="40"
            numeric
            v-slot="props"
            centered
          >
            {{ props.row[0].id }}
          </b-table-column>
          <b-table-column
            field="name"
            label="Name"
            v-slot="props"
            centered
          >
            {{ props.row[0].name }}
          </b-table-column>
          <b-table-column
            field="name"
            label="Actions"
            width="150"
            v-slot="props"
            centered
          >
            <b-tooltip label="Preview" style="text-align:center;margin:0 auto;">
              <b-button type="is-primary" @click="previewRender(props.row[0].id)"  icon-right="search"
              ></b-button
            ></b-tooltip>
            <b-tooltip position="is-left" type="is-info" label="Insert into current week" ><b-button type="is-info" @click="insertTemplate(props.row[0].id)" icon-right="file-upload"
              ></b-button></b-tooltip>
               <b-tooltip position="is-left" type="is-danger" label="Delete template" ><b-button type="is-danger" @click="deleteTemplate(props.row[0].id)" icon-right="trash"
              ></b-button></b-tooltip>
          </b-table-column>

          <template #detail="props">
            <div class="p-3">
              <b-table :data="props.row" class="innertable">
                <b-table-column
                  field="id"
                  label="Agent"
                  width="40"
                  v-slot="propsbis"
                  centered
                >
                  {{ propsbis.row.firstname }} {{ propsbis.row.lastname }}
                </b-table-column>
                <b-table-column
                  field="id"
                  label="Shift"
                  width="40"
                  numeric
                  v-slot="propsbis"
                  centered
                >
                  {{ propsbis.row.from }} {{ propsbis.row.to }}
                </b-table-column>
                <b-table-column
                  field="id"
                  label="Day"
                  width="40"
                  numeric
                  v-slot="propsbis"
                  centered
                >
                 <DayOfWeek :day="propsbis.row.day" />
                 
                </b-table-column>
              </b-table>
            </div>
          </template>
        </b-table>
      </div>
    </transition>
  </div>
</template>
<script>
//import AddGroupForm from "../forms/AddGroupForm.vue";
import renderPreviewModal from "./renderPreviewModal.vue";
import confirmInsertTplModal from "./confirmInsertTplModal.vue";
import confirmDelTplModal from './confirmDelTplModal.vue'
import DayOfWeek from "./DayOfWeek";

export default {
  components:
  {
    DayOfWeek
  },
  data() {
    return {
      open: false,
      name: "",
      templateShifts: "All",
    };
  },
  watch: {
    open(opened) {
      if (opened) {
        this.$store.dispatch("getTemplates", {
          val: this.$store.state.groupid,
        });
      }
    },
  },
  computed: {
    templates() {
      if(this.$store.state.templates)
      {
        return Object.values(this.$store.state.templates);
      }
      return []
    },
    shifts() {
      // console.log(this.$store.getters.currentShifts)
      return this.$store.getters.currentShifts;
    },
    admins() {
      return this.$store.state.admins;
    },
    teams() {
      console.log(
        this.$filterObject(
          this.$store.state.schedule_teams,
          "name",
          this.$route.params.team
        )
      );
      return this.$filterObject(
        this.$store.state.schedule_teams,
        "name",
        this.$route.params.team
      );
    },
    group() {
      return this.$store.state.groupid;
    },
    refDate() {
      return this.$store.state.refDate;
    },
  },
  mounted() {
    //this.$store.dispatch("getAdmins");
  },
  methods: {
    deleteTemplate(id)
    {
       this.$buefy.modal.open({
                    parent: this,
                    component: confirmDelTplModal ,
                    hasModalCard: true,
                    trapFocus: true,
                    props: {"id":id},
                    width:'1500px'
                })
    },
    insertTemplate(id)
    {
       this.$buefy.modal.open({
                    parent: this,
                    component: confirmInsertTplModal,
                    hasModalCard: true,
                    trapFocus: true,
                    props: {"id":id},
                    width:'1500px'
                })
    },
    previewRender(id)
    {
       this.$buefy.modal.open({
                    parent: this,
                    component: renderPreviewModal,
                    hasModalCard: true,
                    trapFocus: true,
                    props: {"id":id},
                    width:'1500px'
                })
    },
    addTemplate() {
      if (this.name.length == 0) {
        this.$buefy.toast.open({
          duration: 5000,
          message: `Name cannot be empty`,
          position: "is-top-right",
          type: "is-danger",
        });
        return;
      }
      this.$http
        .post("./scheduleapi/templates/add", {
          name: this.name,
          group_id: this.group,
          shift_id: this.templateShifts,
          date: this.refDate,
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Added",
              type: "is-success",
            });
              this.$store.dispatch("getTemplates", {
                val: this.$store.state.groupid,
              });
              this.name = ''
            //this.$store.dispatch('getTeams')
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
    getStyle(bg, color) {
      if (!bg && !color) {
        return {
          "background-color": "white",
          color: "black",
        };
      }
      return {
        "background-color": bg,
        color: color,
      };
    },
    agentRow(row, index) {
      console.log(row);
      console.log(index);
    },
    startDrag: (evt, item) => {
      evt.target.style.opacity = 0.5;
      evt.dataTransfer.dropEffect = "move";
      evt.dataTransfer.effectAllowed = "move";
      console.log(item);
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
<style>
.modal-card-foot
{
  margin:0 auto;
}
.templateslistTbl div.detail-container {
  padding: 0 !important;
  margin: 0 !important;
}
.templateslistTbl div table th {
  margin: 0;
  padding: 0;
  text-align: center;
}
.innertable div table {
  border: 1px solid black !important;
}
.innertable div table th {
  background: rgb(146, 179, 255);
  background: linear-gradient(
    180deg,
    rgba(146, 179, 255, 1) 0%,
    rgba(96, 56, 255, 1) 100%
  );
  text-align: center;
  color: white;
}
.innertable td {
  font-size: 12px;
  padding: 0;
  margin: 0;
}
.headerclass {
  font-weight: bold;
  text-decoration: underline;
}
.table tr:first-child {
  height: auto;
}
.b-sidebar .sidebar-content {
  width: auto;
}
</style>
<style scoped>
.floatright {
  z-index: 10;
  background-color: white;
}
</style>
<style scoped>
.team_name {
  text-decoration: underline;
  font-weight: bold;
}
.closedsidebar {
  position: fixed;
  top: 270px;
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
  font-family: "sans-serif";
}
.openedsidebar {
  border: 1px solid black;
  background-color: rgb(182, 220, 255);
}
</style>