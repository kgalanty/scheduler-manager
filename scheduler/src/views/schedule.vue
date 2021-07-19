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
    GroupSchedule
  },
  data() {
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