<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Add Agent to the shift</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>

      <section class="modal-card-body">
         <b-message type="is-danger" has-icon v-if="error">
           {{ error }}
        </b-message>
         <b-field label="Agent">
            <b-autocomplete
                v-model="agentsToAdd"
                placeholder="Search for name"
                :append-to-body="true"
                :data="filteredMembers"
                field="name"
                @select="option => (selectedAgentId = option.agent_id)"
            >
            </b-autocomplete>
        </b-field>
         <b-field label="Day">
            <b-select placeholder="Select a day" expanded v-model="selectedDay">
                <option
                    v-for="day in days"
                    :value="day.date"
                    :key="day.date">
                    {{ day.show }}
                </option>
                </b-select>
        </b-field>
      </section>
      <footer class="modal-card-foot" style="margin: unset" >
        <b-button label="Close" @click="$emit('close')" />
        <b-button icon-left="plus" label="Add" @click="addAgentToShift" type="is-primary" />
      </footer>
    </div>
  </form>
</template>

<script>
export default {
  name: "AddAgentToShiftForm",
  props: ['shift_id', 'group_id'],
  data() {
    return {
      agentsToAdd: '',
      selectedAgentId: null,
      selectedDay: null,
      error: ''
    };
  },
  computed: {
    days()
    {
      return this.expandDaysWeekMixin(this.moment(this.$store.state.refDate), 'ddd DD.MM (YYYY-MM-DD)', true)
    },
    filteredMembers() {
            return this.teams[this.shift_id].members.filter(option => {
                return (
                    option.name
                        .toString()
                        .toLowerCase()
                        .indexOf(this.agentsToAdd.toLowerCase()) >= 0
                )
            })
        },
    agents() {
      return this.$store.state.admins;
    },
    teams() {
     // console.log(this.$filterObject(this.$store.state.schedule_teams, "name", this.$route.params.team))
    // console.log(this.$route.name);
    
      return this.$filterObject(this.$store.state.schedule_teams, "name", this.$route.params.team);
    },
  },
  mounted()
  {
    
  },
  methods:
  {
    addAgentToShift()
    {
     
      if(this.selectedDay == null)
      {
         this.error = 'You must select day'
        return;
      }
      if(this.selectedAgentId == null)
      {
        this.error = 'You must select Agent'
        return;
      }
      this.error = ''
     
       this.$http
        .post("./scheduleapi/shifts/timetable", {
          date: this.selectedDay,
          agent_id: this.selectedAgentId,
          shift_id: this.shift_id,
          group_id: this.group_id
        })
        .then((r) => {
          if (r.data.response === "success") {
              this.$parent.$emit('reloadapi')
              this.$emit('close')
          } else {
            this.$buefy.toast.open({
              message: r.data.response,
              type: "is-danger",
            });
           // loadingComponent.close();
          }
        });




    },
    // addGroup()
    // {
    //    this.$http
    //     .post("./scheduleapi/agents/addgroup", {name:this.groupname, agents:this.agentsToAdd})
    //     .then((response) => {
    //       if (response.data.response == "success") {
    //         this.$buefy.toast.open({
    //           message: "Removed!",
    //           type: "is-success",
    //         });
            
    //       } else {
    //         this.$buefy.toast.open({
    //           message: response.data.response,
    //           type: "is-danger",
    //         });
    //       }
    //       this.$emit('close')
    //     });
    // }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

.dropdown-content {
  width: 100%;
  min-width: 0;
}
.checkboxgroup {
  float: left;
}
</style>
