<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Change Days in the Pool</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>

      <section class="modal-card-body">
        <b-message type="is-danger" has-icon v-if="error">
          {{ error }}
        </b-message>
       <b-field label="Days">
      <b-numberinput v-model="days"></b-numberinput>
    </b-field>
      </section>
      <footer class="modal-card-foot" style="margin: unset">
        <b-button label="Close" @click="$emit('close')" />
        <b-button
          icon-left="plus"
          label="Save"
          @click="AddDays"
          type="is-primary"
          :loading="saveInProgress"
        />
      </footer>
    </div>
  </form>
</template>

<script>
export default {
  name: "ChangeDaysOffModal",
  props: ["daysoffentry", 'currentdays'],
  data() {
    return {
      days: 0,
      saveInProgress: false,
      error: ''
    };
  },
  computed: {
   
  },
  mounted() {
    this.days = this.currentdays
  },
  methods: {
    AddDays() {
      
      if (this.days < 1) {
        this.error = "You must Specify number of days";
        return;
      }

      this.error = "";
      this.saveInProgress = true
      this.$http
        .post("./scheduleapi/daysoff/entry/" + this.daysoffentry, {
          changedays: this.days
        })
        .then((r) => {
          if (r.data.result === "success") {
            this.$emit("reloadapi");
            this.$emit("close");
          } else {
           
            this.$emit("close");
            // loadingComponent.close();
          }
           this.saveInProgress = false
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
  },
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
