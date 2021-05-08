<template>
  <div
    class="column"
    @drop="onDrop($event)"
    @dragover.prevent="dragging = true"
    @dragend="dragEnd($event)"
    @dragleave.prevent="dragging = false"
    :class="{ columndragenter: dragging }"
  >
    <span class="columnday">{{ day }}</span>
    <ul v-if="today && today[shift] && today[shift][date]" style="background-color: white;">
      <li :style="{backgroundColor: item.bg, color: item.color}" v-for="(item, i) in today[shift][date]" :key="'dt' + date+'-'+i" class="agentitem">
        <a @click="remove(item.id)"><b-icon v-if="showDelBtn" 
                    icon="trash"
                    size="is-small"></b-icon></a>
       
        {{ item.agent }}
        

      </li>
    </ul>
   <ul v-else style="opacity:.5;">
      <li style="height: 50px;">Empty</li>
    </ul>
    
  </div>
</template>
<script>
export default {
  props: ["ind", "day", 'shift', 'group', 'refdate'],
  computed:
  {
    // todaydate()
    // {

    // },
    showDelBtn()
    {
      return this.$store.state.showDel
    },
    ref()
    {
      return this.refdate.format("YYYY-MM-DD")
    },
    date()
    {
      //console.log(this.day)
      return this.moment(this.ref).day(this.ind+1).format('YYYY-MM-DD')
    },
        shifts()
    {
      return this.$store.state.timetable[this.date].shifts
    },
    today()
    {
      if(!this.$store.state.timetable[this.ref] || typeof this.$store.state.timetable[this.ref].t == 'undefined')
      {
        return []
      }
       return this.$store.state.timetable[this.ref].t
    }
  },
  data() {
    return {
      //dt: [{ "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }],
      dragging: false,
    };
  },
  methods: {
    remove(id)
    {
      const loadingComponent = this.$buefy.loading.open({
        container: null
      });
      this.$http
        .post(
          "/scheduleapi/shifts/delete_duty",
          {
            id: id
          }
        )
        .then((r) => {
         if(r.data.response === 'success')
         {
           //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
           this.$store.dispatch('loadFromAPI', {
              team: this.group,
              refdate: this.ref
            }).then(() => {
              loadingComponent.close();
            })
         }
          else 
          {
            loadingComponent.close();
          }
          
        });
    },
    pushToApi()
    {

    },
    onDrop(evt) {
      this.dragging = false;
      //console.log(evt.dataTransfer.getData("agentItem"))
      const AgentItem = JSON.parse(evt.dataTransfer.getData("agentItem"));
      //console.log(day)
     // console.log(this.date);
      // console.log(itemID)
      if(typeof this.today == 'undefined')
      {
        this.today = []
      }
      if(typeof this.today[this.date] == 'undefined')
      {
        this.today[this.date] = []
      }
       const loadingComponent = this.$buefy.loading.open({
        container: null
      });
      this.$http
        .post(
          "/scheduleapi/shifts/timetable",
          {
            date: this.date,
            agent_id: AgentItem.agent_id,
            shift_id: this.shift,
            group_id: this.group
          }
        )
        .then((r) => {
         if(r.data.response === 'success')
         {
           loadingComponent.close();
           //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
           this.$store.dispatch('loadFromAPI', {
              team: this.group,
              refdate: this.ref
            })
         }
          else 
          {
            loadingComponent.close();
          }
          
        });
      //const item = this.items.find(item => item.id == itemID)
      //item.list = list
      evt.target.style.opacity = 1;
    },
    dragEnd: function (e) {
      e.target.style.opacity = 1;
      this.dragging = false
    },
  },
};
</script>
<style >
.agentitem
{
  padding:8px 0;
  text-align:center;
}
.columnday {
font-weight:bold; text-align:center;width:100%;display:block;margin:5px 2px;
font-size: 18px ;
}
</style>