<template>
  <div>
     <b-table :data="shifts" striped narrowed bordered mobile-cards> 

          <b-table-column
            field="Shift"
            centered
            label="Teams"
            v-slot="props" 
          >
           <strong>#{{ props.row.group_id }} {{ props.row.team }}</strong> </b-table-column>
<b-table-column
            field="Shift"
            centered
            label="Shifts"
            v-slot="props" 
          >
                  <b-table :data=" props.row.shifts" striped narrowed mobile-cards>
                    <template #empty>
                                  <div class="has-text-centered">No records</div>
                              </template>
                    <b-table-column
                              field="Shift"
                              centered
                              label="Shift"
                              v-slot="props" 
                            >
                              {{ props.row.from }} - {{ props.row.to }}
                              </b-table-column>
                  <b-table-column
                              field="Shift"
                              centered
                              label="Action"
                              v-slot="props" 
                            >
                              <b-button size="is-small" type="is-info" icon-left="trash" @click="removeShift(props.row.id)">Remove</b-button>

                              </b-table-column>
                  </b-table>
          </b-table-column>
         

        </b-table>
    

</div>
</template>

<script>
export default {
  name: 'AgentsList',
  computed:
  {
    shifts() {
      return this.$store.state.shifts
    }
  },
  methods:
  {
    removeShift(id)
    {
      this.$http
        .post("/scheduleapi/shifts/delete", {id})
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Removed!",
              type: "is-success",
            });
            this.$store.dispatch("getShiftsList");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
          
        });
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style >
.b-table 
{
  float:left;
  width:100%;
  border:1px solid black;
}
.b-table td {
  height: auto;
}
.b-table tr
{
  height:auto !important;
}
</style>
