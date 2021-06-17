<template>
  <div
    class="column columnclass animation"
    @drop="onDrop($event)"
    @dragover.prevent="dragging = true"
    @dragend="dragEnd($event)"
    @dragleave.prevent="dragging = false"
    :class="{ columndragenter: dragging }"
  >
    <span class="columnday">{{ day }}</span>
    <ul
      v-if="today && today[shift] && today[shift][date]"
      style="background-color: white"
    >
      <li
        :style="{ backgroundColor: item.bg, color: item.color }"
        v-for="(item, i) in today[shift][date]"
        :key="'dt' + date + '-' + i"
        class="agentitem"
        :class="[item.draft == 1 || item.deldraftauthor ? 'draftCell' : '']"
      >
       
        <b-tooltip label="Addition candidate. Undo addition" position="is-top" class="undoicon" v-if="item.draft">
          <a @click="remove(item.id)" 
          ><b-icon
            icon="undo"
            size="is-small"
          ></b-icon
        ></a></b-tooltip>
        <a @click="remove(item.id)" v-if="showDelBtn && !item.draft && item.deldraftauthor === false"
          ><b-icon
            icon="trash"
            size="is-small"
          ></b-icon
        ></a>
         <b-tooltip label="Delete candidate. Undo deletion" position="is-top" class="undoicon" v-if="item.deldraftauthor !== false">
          <a @click="undo(item.id)" 
          ><b-icon
          
            icon="undo"
            size="is-small"
          ></b-icon
        ></a></b-tooltip>
         <b-icon v-if="item.draft" icon="plus-square" class="plusicon"></b-icon> 
         <b-icon v-if="item.deldraftauthor" icon="minus-square" class="minusicon"></b-icon>

        {{ item.agent }}

      </li>
    </ul>
    <ul v-else style="opacity: 0.5;text-align: center;">
      <li >Empty</li>
    </ul>
  </div>
</template>
<script>
export default {
  props: ["ind", "day", "shift", "group", "refdate"],
  computed: {
    // todaydate()
    // {

    // },
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
    undo(id)
    {
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
    pushToApi() {},
    onDrop(evt) {
      this.dragging = false;
      //console.log(evt.dataTransfer.getData("agentItem"))
      const AgentItem = JSON.parse(evt.dataTransfer.getData("agentItem"));
      //console.log(day)
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
      this.$http
        .post("./scheduleapi/shifts/timetable", {
          date: this.date,
          agent_id: AgentItem.agent_id,
          shift_id: this.shift,
          group_id: this.group,
        })
        .then((r) => {
          if (r.data.response === "success") {
            loadingComponent.close();
            //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
            this.$store.dispatch("loadFromAPI", {
              teamroute: this.$route.params.team,
              refdate: this.ref,
              refdateroute: this.$route.params.date,
            });
          } else {
            this.$buefy.toast.open({
              message: r.data.response,
              type: "is-danger",
            });
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
.columnclass
{
  padding: 0.5rem;
}
.undoicon
{
  background: rgb(105,105,255);
background: linear-gradient(90deg, rgba(105,105,255,1) 0%, rgba(79,158,251,1) 100%);
padding:5px;
border:1px solid black;
border-radius:5px;
}
.undoicon:hover
{
  opacity:.9;
}
.plusicon
{
  color:green;
}
.minusicon
{
  color: red;
}
.agentitem {
  padding: 2px 0;
  font-size:14px;
  text-align: center;
  font-weight:bold;
}
.columnday {
  font-weight: bold;
  text-align: center;
  width: 100%;
  display: block;
  margin: 2px 2px;
  font-size: 18px;
}
</style>