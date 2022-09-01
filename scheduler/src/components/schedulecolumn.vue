<template>
  <div
    class="column columnclass animation"
    @drop="onDrop($event)"
    @dragover="onDragging($event)"
    @dragend="dragEnd($event)"
    @dragleave="onDragLeave"
    :class="{ columndragenter: dragging }"
  >
    <span class="columnday" v-if="groupindex===0">{{ day }}</span>
    <ul
      v-if="today && today[shift] && today[shift][date]"
      style="background-color: white"
    >
      <li
        :style="{ backgroundColor: item.bg, color: item.color }"
        v-for="item in today[shift][date]"
        :key="'dt' + date + '-' + item.id"
        class="agentitem"
        :class="[item.draft == 1 || item.deldraftauthor ? 'draftCell' : '']"
        style="display: flex; width: 100%; height: 40px"
      >
        <b-tooltip
          label="Addition candidate. Undo addition"
          position="is-top"
          class="undoicon"
          v-if="item.draft"
        >
          <a @click="remove(item.id)"
            ><b-icon icon="undo" size="is-small"></b-icon></a
        ></b-tooltip>
        <a
          @click="remove(item.id)"
          v-if="showDelBtn && !item.draft && item.deldraftauthor === false"
          ><b-icon icon="trash" size="is-small"></b-icon
        ></a>
        <b-tooltip
          label="Delete candidate. Undo deletion"
          position="is-top"
          class="undoicon"
          v-if="item.deldraftauthor !== false"
        >
          <a @click="undo(item.id)"
            ><b-icon icon="undo" size="is-small"></b-icon></a
        ></b-tooltip>

        <span style="display: block; text-align: center; width: inherit">
          {{ item.agent }}
        </span>
        <span style="margin-right: 3px">
          <b-icon
            v-if="item.draft"
            icon="plus-square"
            class="plusicon"
          ></b-icon>
          <b-icon
            v-if="item.deldraftauthor"
            icon="minus-square"
            class="minusicon"
          ></b-icon>
        </span>
        <!-- <span
          style="display: contents; float: right; text-align: right"
          v-if="editorPermission === 1"
        >
          <b-button
            class="arrowupdownbtn"
            v-if="i != 0 && !item.draft && !item.deldraftauthor"
            size="is-small"
            icon-left="arrow-up"
            @click="moveItemUp(item.id)"
          />
          <b-button
            class="arrowupdownbtn"
            size="is-small"
            v-if="
              i != today[shift][date].length - 1 &&
              !item.draft &&
              !item.deldraftauthor
            "
            icon-left="arrow-down"
            @click="moveItemDown(item.id)"
          />
        </span> -->
      </li>
    </ul>
    <ul v-else style="opacity: 0.5; text-align: center">
      <li style="padding: 0.4rem">Empty</li>
    </ul>
  </div>
</template>
<script>
import AddShiftMixin from "../mixins/AddShiftMixin.js";
export default {
  mixins: [AddShiftMixin],
  props: ["ind", "day", "shift", "group", "refdate", 'groupindex'],
  computed: {
    // todaydate()
    // {

    // },
    editorPermission() {
      return this.$store.state.editorPermission;
    },
    showDelBtn() {
      return this.$store.state.showDel;
    },
    ref() {
      return this.refdate.format("YYYY-MM-DD");
    },
    date() {
      //console.log(this.day)
      return this.moment(this.ref)
        .day(this.ind + 1)
        .format("YYYY-MM-DD");
    },
    shifts() {
      return this.$store.state.timetable[this.date].shifts;
    },
    today() {
      if (
        !this.$store.state.timetable[this.ref] ||
        typeof this.$store.state.timetable[this.ref].t == "undefined"
      ) {
        return [];
      }
      return this.$store.state.timetable[this.ref].t;
    },
  },
  data() {
    return {
      //dt: [{ "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }],
      dragging: false,
    };
  },
  methods: {
    moveItemUp(id) {
      const loadingComponent = this.$buefy.loading.open({
        container: null,
      });
      this.$http
        .post("./scheduleapi/shifts/reorder/up", {
          id: id,
        })
        .then((r) => {
          if (r.data.response === "success") {
            //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
            this.$store
              .dispatch("loadFromAPI", {
                //  team: this.group,
                refdate: this.ref,
                teamroute: this.$route.params.team,
                refdateroute: this.$route.params.date,
              })
              .then(() => {
                loadingComponent.close();
              });
          } else {
            this.$buefy.toast.open({
              duration: 5000,
              message: r.data.response,
              position: "is-top",
              type: "is-danger",
            });
            loadingComponent.close();
          }
        });
    },
    moveItemDown(id) {
      const loadingComponent = this.$buefy.loading.open({
        container: null,
      });
      this.$http
        .post("./scheduleapi/shifts/reorder/down", {
          id: id,
        })
        .then((r) => {
          if (r.data.response === "success") {
            //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
            this.$store
              .dispatch("loadFromAPI", {
                //  team: this.group,
                refdate: this.ref,
                teamroute: this.$route.params.team,
                refdateroute: this.$route.params.date,
              })
              .then(() => {
                loadingComponent.close();
              });
          } else {
            this.$buefy.toast.open({
              duration: 5000,
              message: r.data.response,
              position: "is-top",
              type: "is-danger",
            });

            loadingComponent.close();
          }
        });
    },
    undo(id) {
      const loadingComponent = this.$buefy.loading.open({
        container: null,
      });
      this.$http
        .post("./scheduleapi/shifts/delete_draft", {
          id: id,
        })
        .then((r) => {
          if (r.data.response === "success") {
            //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
            this.$store
              .dispatch("loadFromAPI", {
                //  team: this.group,
                refdate: this.ref,
                teamroute: this.$route.params.team,
                refdateroute: this.$route.params.date,
              })
              .then(() => {
                loadingComponent.close();
              });
          } else {
            loadingComponent.close();
          }
        });
    },
    remove(id) {
      const loadingComponent = this.$buefy.loading.open({
        container: null,
      });
      this.$http
        .post("./scheduleapi/shifts/delete_duty", {
          id: id,
        })
        .then((r) => {
          if (r.data.response === "success") {
            //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
            this.$store
              .dispatch("loadFromAPI", {
                //  team: this.group,
                refdate: this.ref,
                teamroute: this.$route.params.team,
                refdateroute: this.$route.params.date,
              })
              .then(() => {
                loadingComponent.close();
              });
          } else {
            loadingComponent.close();
          }
        });
    },
    PostShift(agent_id, force) {
      return this.$http.post("./scheduleapi/shifts/timetable", {
        date: this.date,
        agent_id: agent_id,
        shift_id: this.shift,
        group_id: this.group,
        force: force,
      });
    },
    onDrop(evt, force = false) {
      this.dragging = false;
      //console.log(evt.dataTransfer.getData("agentItem"))
      const AgentItem = JSON.parse(evt.dataTransfer.getData("agentItem"));
      console.log(AgentItem);
      // console.log(this.date);
      // console.log(itemID)
      if (typeof this.today == "undefined") {
        this.today = [];
      }
      if (typeof this.today[this.date] == "undefined") {
        this.today[this.date] = [];
      }
      const loadingComponent = this.$buefy.loading.open({
        container: null,
      });
      this.PostShift(AgentItem.adminid, force).then((r) => {
        if (r.data.response === "success") {
          loadingComponent.close();
          //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
          this.$store.dispatch("loadFromAPI", {
            teamroute: this.$route.params.team,
            refdate: this.ref,
            refdateroute: this.$route.params.date,
          });
        } else {
          // this.$buefy.snackbar.open({
          //         duration: 10000,
          //         message:  r.data.response,
          //         type: 'is-danger',
          //         position: 'is-top',
          //         queue: false,
          //         actionText: r.data.action ?? null,
          //         onAction: () => {
          //           const loadingComponent = this.$buefy.loading.open({
          //             container: null,
          //           });

          //            this.PostShift(AgentItem.agent_id, true)
          //            .then((r) => {
          //                             if (r.data.response === "success") {
          //                               loadingComponent.close();
          //                               //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
          //                               this.$store.dispatch("loadFromAPI", {
          //                                 teamroute: this.$route.params.team,
          //                                 refdate: this.ref,
          //                                 refdateroute: this.$route.params.date,
          //                               });
          //                             }

          //         })
          //     }
          // })
          this.ForceAddDutyConfirm(r, AgentItem.agent_id, this.ref);
          // this.$buefy.toast.open({
          //   message: r.data.response,
          //   type: "is-danger",
          // });
          loadingComponent.close();
        }
      });
      //const item = this.items.find(item => item.id == itemID)
      //item.list = list
      evt.target.style.opacity = 1;
    },
    dragEnd: function (e) {
      e.target.style.opacity = 1;
      this.dragging = false;
    },
    onDragLeave(ev) {
      ev.preventDefault();
      // ev.target.style.marginTop = ''
      //  ev.target.style.marginBottom = ''
      this.dragging = false;
    },
    onDragging(ev) {
      this.dragging = true;
      ev.preventDefault();
      //
      // console.log(ev)
    },
  },
};
</script>
<style >
</style>
<style scoped >
.draftCell {
  box-shadow: rgba(255, 255, 255, 0.2) 0px 0px 0px 1px inset,
    rgba(0, 0, 0, 0.9) 0px 0px 0px 1px;
  background-color: rgb(255, 255, 255) !important;
  border: 3px dashed rgb(255, 2, 242) !important;
  color: black !important;
}
</style>
<style >
.columnclass {
  padding: 0.5rem 0;
}
.undoicon {
  background: rgb(105, 105, 255);
  background: linear-gradient(
    90deg,
    rgba(105, 105, 255, 1) 0%,
    rgba(79, 158, 251, 1) 100%
  );
  padding: 5px;
  border: 1px solid black;
  border-radius: 5px;
}
.undoicon:hover {
  opacity: 0.9;
}
.plusicon {
  color: green;
}
.minusicon {
  color: red;
}
.agentitem {
  padding: 2px 0;
  font-size: 14px;
  text-align: center;
  font-weight: bold;
}
.agentitemplaceholder {
  padding: 2px 0;
  font-size: 14px;
  text-align: center;
  font-weight: bold;
  height: 30px !important;
  border: 3px dashed grey !important;
}
.columnday {
  font-weight: bold;
  text-align: center;
  width: 100%;
  display: block;
  background-color:rgb(72, 55, 201);
  font-size: 1.1rem;
}
.arrowupdownbtn:hover {
  opacity: 0.6;
  transition: opacity 0.5s cubic-bezier(0.19, 0.64, 0.55, 0.93);
}
</style>