<template>
  <div>
     <b-table paginated :per-page="15" :data="admins" narrowed bordered class="agentsListTbl" :row-class="(row, index) => 'agentsListTbl'"> 
          <b-table-column
            centered
            label="Agent"
            searchable
            field="fullrow">
               <template
                  #searchable="props">
                        <b-input 
                            v-model="props.filters[props.column.field]"
                            placeholder="Search by Agent Name"
                            icon="search"
                            size="is-medium" />
                    </template>
            <template v-slot="props">
             {{ props.row.fullrow }}
              </template>
          </b-table-column>
         <b-table-column
            field="color"
            centered
            label="Groups"
            v-slot="props" 
          >

           <b-select placeholder="Assign a team" v-model="adminteams[props.row.id]" @input="setTeam(props.row.id)">
                <option
                    v-for="team in teams"
                    :value="team.groupid"
                    :key="team.groupid">
                    {{ team.name}}
                </option>
            </b-select>
          </b-table-column>
                     
          <b-table-column
            field="color"
            centered
            label="Text Color"
            v-slot="props" 
          >  <b-tooltip position="is-bottom"  label="Color is not set">
             <b-icon icon="border-none" v-if="props.row.color == null"
                    size="is-small"></b-icon>
                    </b-tooltip>
               <input type="color" name="color[]" :value="props.row.color" @change="setcolor('color', $event.target.value, props.row.id)" >
          </b-table-column>
          <b-table-column
            field="bg"
            centered
            label="Background Color"
            v-slot="props" 
          >
         <b-tooltip position="is-bottom"  label="Color is not set">
         <b-icon icon="border-none" v-if="props.row.bg == null"
                    size="is-small"></b-icon> </b-tooltip>
              <input type="color" name="bgcolor" @change="setcolor('bg', $event.target.value, props.row.id)" :value="getColor(props.row.bg)">
          </b-table-column>
        </b-table>
    

</div>
</template>

<script>

export default {
  name: 'AgentsList',
  computed:
  {
    admins() {
     // this.test;
      return this.$store.state.admins;
    },
    teams()
    {
      return this.$store.state.schedule_teams
    }
  },
   data() {
     return {
       adminteams: {},
       test:''
     }
   },
   mounted() {

   },
 watch:
  {
    admins(n)
    {
   
      if(n.length > 0)
      {
        for(var i=0;i<n.length;i++)
        {
          this.adminteams = {...this.adminteams, [n[i].id]: n[i].groupid}
        }
        
      }
    }
  },
  methods:
  {
    setTeam(admin)
    {
      this.$http
        .post("./scheduleapi/agents/assigntogroup", {team_id: this.adminteams[admin], agent:admin})
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Team has been set",
              type: "is-success",
            });
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
          
        });

    },
    getColor(color)
    {
      if(color == null || !color) return '#000000'; 
      return color
    },
    setcolor(c, val, admin_id)
    {
 
      this.$http
        .post("./scheduleapi/agents/color", {color: c, value:val, admin: admin_id})
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Color changed!",
              type: "is-success",
            });
            this.$store.dispatch("getAdmins");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
          
        });


    },
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
<style >
.pagination-link.is-current {
color:white !important;
}
.b-table 
{
  float:left;
  width:100%;padding-bottom:10px;
}
.b-table td {
  height: auto;
}
.b-table tr
{
  height:auto !important;
}
</style>
