<template>
  <div class="container" style="background:white;padding:20;">
     <div class="modal-card" style="width: auto">
                    <header class="modal-card-head">
                        <p class="modal-card-title">Template Preview</p>
                        <button
                            type="button"
                            class="delete"
                            @click="$emit('close')"/>
                    </header>
 <section class="modal-card-body">

  <div class="columns" style="padding:20;padding: 20px;">
    <div class="column narrowedcolumn" v-for="(item, i) in weekdays" :key="i+'column'">
     <ul style="padding:5px; background: grey;  color:white;font-weight:bold;"><li> {{item}} </li></ul>
      <ul v-if="list[i+1].length > 0">
        <li v-for="(item2, i2) in list[i+1]" :key="i2+'i2'">
          {{item2.firstname}} {{item2.lastname}} <b-taglist attached>
              <b-tag type="is-primary">{{item2.from}}</b-tag>
              <b-tag type="is-info">{{item2.to}}</b-tag>
          </b-taglist>
        </li>
      </ul>
      <ul v-else><li>Empty</li></ul>
    </div>
   
</div>
</section>
</div>
</div>
</template>

<script>

export default {
  name: 'renderPreviewModal',
  props: ['id'],
  computed:
  {
    templates()
    {
       return this.$store.state.templates[this.id]
    },
  
  },
   data() {
     return {
       list: [[],[],[],[],[],[],[],[]],
       weekdays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
     }
   },
   mounted() {
     this.templates.forEach(x =>{
      this.$set(this.list, x.day, [...this.list[x.day], x ])
       //this.list[x.day] = [...this.list[x.day], x ]
     })
   },
  methods:
  {
    
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style >
.agentsListTbl > td:first-child { 
  text-align:left !important;
  font-size:0.9em;
  
  }
</style>
<style scoped>
.narrowedcolumn > ul:first-child > li:first-child
{
  text-align:center;
}
.narrowedcolumn > ul:first-child li
{
  border:0 !important; 
}
.narrowedcolumn > ul:nth-child(n+2) li > .tags
{
justify-content: center;
} 
.narrowedcolumn > ul:nth-child(n+2) li
{
  border: 0 !important;
  padding:2px;
  margin:2px;
  background: rgb(146,179,255);
  background: linear-gradient(180deg, rgba(146,179,255,1) 0%, rgba(0,203,119,1) 100%);
  border-radius:5px;
  font-size:1em;
  text-align:center;
      
}
.narrowedcolumn 
{
  padding: 5px 2px !important;
  margin:0;
  width:200px;
}
.b-table 
{
  float:left;
  width:100%;
}
.b-table td {
  height: auto;
}
.b-table tr
{
  height:auto !important;
}
</style>
