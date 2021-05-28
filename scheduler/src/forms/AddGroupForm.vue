<template>
  <form action="">
    <div class="modal-card" >
      <header class="modal-card-head">
        <p class="modal-card-title">Add New Group</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>
      <section class="modal-card-body">
        <b-field label="Name">
          <b-input type="text" v-model="groupname" placeholder="Group name" required>
          </b-input>
        </b-field>
      </section>
      <section class="modal-card-body">
        <b-field label="Members">
          <ul style="max-height: 200px"> 
          <li
            :key="'agents' + agent_id"
            v-for="(agent, agent_id) in agents"
          >
            <b-checkbox v-model="agentsToAdd" :native-value="agent.id">
              {{ agent.firstname }} {{ agent.lastname }}
            </b-checkbox>
          </li>
          </ul>
        </b-field>
      </section>
      <footer class="modal-card-foot">
        <b-button label="Close" @click="$emit('close')" />
        <b-button icon-left="plus" label="Add" @click="addGroup" type="is-primary" />
      </footer>
    </div>
  </form>
</template>

<script>
export default {
  name: "AddGroupForm",
  data() {
    return {
      groupname: "",
      agentsToAdd: [],
    };
  },
  computed: {
    agents() {
      return this.$store.state.admins;
    },
  },
  mounted()
  {
    
  },
  methods:
  {
    addGroup()
    {
       this.$http
        .post("./scheduleapi/agents/addgroup", {name:this.groupname, agents:this.agentsToAdd})
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Removed!",
              type: "is-success",
            });
            
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
          this.$emit('close')
        });
    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.checkboxgroup {
  float: left;
}
</style>
