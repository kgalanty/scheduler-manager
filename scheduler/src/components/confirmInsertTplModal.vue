<template>
  <div class="container" style="background:white;padding:20;">
  <form action="">
                <div class="modal-card" style="width: auto">
                    <header class="modal-card-head">
                        <p class="modal-card-title">Are you sure?</p>
                        <button
                            type="button"
                            class="delete"
                            @click="$emit('close')"/>
                    </header>
                    <section class="modal-card-body">
                       You are about to insert selected template into current week ( {{ $route.params.date}} ). Are you sure ?

                    </section>
                    <section class="modal-card-body">

                        <b-checkbox v-model="overwrite">Overwrite shifts (Current week will be erased first - Only in corresponding shifts)</b-checkbox>
                    </section>
                    <footer class="modal-card-foot">
                        <b-button
                            label="Close"
                            @click="$emit('close')" />
                        <b-button
                            label="Yes"
                            @click="InsertConfirm"
                            type="is-primary" />
                    </footer>
                </div>
            </form>

</div>
</template>

<script>

export default {
  name: 'confirmInsertTplModal',
  props: ['id'],
  computed:
  {
 
  
  },
   data() {
     return {
       overwrite: false
     }
   },
   mounted() {

   },
  methods:
  {
    InsertConfirm()
    {
      const loadingComponent = this.$buefy.loading.open({
        container: null,
      });
      this.$http
            .post("./scheduleapi/templates/confirm", {
              id: this.id,
              date: this.$route.params.date,
              overwrite: this.overwrite
            })
            .then((r) => {
              if (r.data.response === "success") {
                this.$parent.close()
                 this.$buefy.toast.open({
                    duration: 5000,
                    message: `Schedule updated successfuly`,
                    position: 'is-top',
                    type: 'is-success'
                })
                //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
                this.$store
                  .dispatch("loadFromAPI", {
                    //  team: this.group,
                    refdate: this.moment(this.$store.state.refDate).format("YYYY-MM-DD"),
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
    }
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
