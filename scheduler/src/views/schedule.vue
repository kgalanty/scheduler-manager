<template>
  <div>
      <GroupSchedule />


  </div>
</template>
<script>
//import SidebarRight from '../components/SidebarRight.vue'

import GroupSchedule from '../components/GroupSchedule.vue'
export default {
  components: {
   // SidebarRight,
    GroupSchedule
  },
  data() {
    // const dt = [
    //  [
    //  ],
    //  [
    //    { "agent_id": 3, "name": "Stoyan", "bg": "", "color": "" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }
    //  ],
    //  [
    //    { "agent_id": 3, "name": "Stoyan", "bg": "", "color": "" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }
    //  ],
    //  [
    //    { "agent_id": 3, "name": "Stoyan", "bg": "", "color": "" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }
    //  ],
    //  [
    //    { "agent_id": 3, "name": "Stoyan", "bg": "", "color": "" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }
    //  ],
    //  [
    //    { "agent_id": 3, "name": "Stoyan", "bg": "", "color": "" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }
    //  ],
    //  [
    //    { "agent_id": 3, "name": "Stoyan", "bg": "", "color": "" }, { "agent_id": 3, "name": "Stoyan", "bg": "purple", "color": "white" }
    //  ]
    // ]

    // const data = [
    //             { 'mon': {'agent': 'agent 1', 'style' : {'background-color' : 'darkblue', 'color':'white'}}, 
    //             'tue': 'Jesse', 
    //             'wed': 'Simmons', 
    //             'thu': '2016/10/15 13:43:27', 
    //             'fri': 'Male', 
    //             'sat': 'Male', 
    //             'sun': 'Male' },
    //             {'wed': 'Simmons', 'fri': 'Male'},
    //             {'sat': 'asdasd', 'fri': 'Male'}, {'mon': {'agent': 'agent 1', 'style' : {'background-color' : 'darkblue', 'color':'white'}}}
    //         ]
    return {
      activeTab:null,
     // dt,
    }
  },
  computed:
  {
    teams()
    {
      return this.$store.state.schedule_teams
    },
   
  },
  mounted(){
    //this.activeTab = Object.keys(this.teams)[0]
   // console.log(this.$route.params)
   this.checkpointDate()
  },
  methods:
  {
      getFirstDay()
      {
        this.moment()
      }
  }
}
</script>
<style>
.columndragenter
{
  border-style: dashed;
  opacity: .5;
}
.column li 
{
  border:1px solid black;
  border-bottom: 0;
}
.column li:last-child
{
  border:1px solid black;
}

.table tr
{
  height:40px;
}
</style>