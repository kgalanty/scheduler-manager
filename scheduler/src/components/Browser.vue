<template>
  <div>
   <h2 class="subtitle">TMD Teams Schedules Browser</h2>

   <div class="tile is-ancestor">
        <GroupTile label="Technical Support" icon="users" />
        <GroupTile label="DevOps & Cloud" icon="users" />
        <GroupTile label="System Administrators" icon="users" />
        
       
</div>
     <div class="tile is-ancestor">
        <GroupTile label="Technical Support" icon="users" />
        <GroupTile label="DevOps & Cloud" icon="users" />
        <GroupTile label="System Administrators" icon="users" />
     </div>
     <div class="tile is-ancestor">
        <GroupTile label="Feedback" icon="meh-o" />
        <GroupTile label="Trainings" icon="graduation-cap" />
        <GroupTile label="Vacationing" icon="sun-o" />
     </div>
</div>
</template>

<script>
import GroupTile from './GroupTile.vue'
export default {
  name: 'Browser',
  components: {
    GroupTile
   }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
