<template>
  <div>
  <b-field>
     <h2 class="subtitle"> Recently published schedules or training events</h2>
       </b-field>
     <div class ="ct">
       <ul>
         <li v-for="(item, i) in logs" :key="i">
            <b-taglist attached style="display: inline-flex;"><b-tag type="is-info" size="is-medium">{{item.action}}</b-tag>
           <b-tag type="is-primary" size="is-medium">{{ item.firstname}} {{item.lastname}}</b-tag>
           <b-tag type="is-success" size="is-medium">{{item.date}}</b-tag> </b-taglist>
           {{item.log}}
           </li>
         </ul>

     </div>
 </div>
</template>

<script>
export default {
  name: 'RecentEvents',
  mounted()
  {
       this.$store.dispatch('getLogs')
  },
  computed:
  {
     logs() {
      return this.$store.state.logs;
    },
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.subtitle
{
  display:block;
float:left;
width:100%;
}
.ct
{
  padding:6px;
  border:1px solid black;
  height: 300px; 
  width:100%;
  clear:both;
  float:left;
  display:block;
        overflow-y:auto;
        position:relative;
        margin-left:-5px;
}
.ct li
{
  padding:2px;
}
</style>
