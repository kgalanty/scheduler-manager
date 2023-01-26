<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Confirm Revoke Calendar Access</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>

      <section class="modal-card-body">
        <b-message type="is-warning" has-icon>
          <p>
          Are you sure you wish to revoke selected calendar access?
        </p>
        <p>In case this user is active, he/she will need to generate new access hash to preserve calendar access.</p>
        </b-message>


      </section>
      <footer class="modal-card-foot" style="margin: unset">
        <b-button label="Close" @click="$emit('close')" />
        <b-button
          icon-left="ban"
          label="Confirm Revoke"
          @click="Revoke"
          type="is-danger"
          :loading="RevokeBtnLoading"
        />
      </footer>
    </div>
  </form>
</template>

<script>
export default {
  name: "ConfirmRevokeCalendarAccess",
  props: ["id"],
  data() {
    return {
      RevokeBtnLoading: false,
    };
  },
  computed: {},
  mounted() {},
  methods: {
    Revoke() {
      this.RevokeBtnLoading = true
      this.$http
        .post("./scheduleapi/calendar/revoke", {id: this.id})
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Success",
              type: "is-success",
            });
            
            this.$emit("reloadapi");

          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
          this.RevokeBtnLoading = false
          this.$emit('close')
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
