<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Submit review for the leave</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>

      <section class="modal-card-body">
        <p>
          Leave dates: {{ request.date_start }} - {{ request.date_end }} for
          {{ request.a_firstname }} {{ request.a_lastname }}
        </p>
        <br />
        <b-message type="is-danger" has-icon v-if="error">
          {{ error }}
        </b-message>
        <b-field label="Review comment">
          <b-input type="text" v-model="review" placeholder="Comment" />
        </b-field>
      </section>
      <footer class="modal-card-foot" style="margin: unset">
        <b-button label="Close" @click="$emit('close')" />
        <b-button
          icon-left="plus"
          label="Accept"
          @click="Accept"
          type="is-success"
          :loading="AcceptBtnLoading"
        />
        <b-button
          icon-left="plus"
          label="Reject"
          @click="Reject"
          type="is-danger"
          :loading="RejectBtnLoading"
        />
      </footer>
    </div>
  </form>
</template>

<script>
export default {
  name: "SubmitLeaveReviewModal",
  props: ["request"],
  data() {
    return {
      review: "",
      decision: false,
      saveInProgress: false,
      AcceptBtnLoading: false,
      RejectBtnLoading: false,
      error: '',
    };
  },
  computed: {},
  mounted() {
    this.days = this.currentdays;
  },
  methods: {
    Accept() {
      this.AcceptBtnLoading = true;
      this.$http
        .post("./scheduleapi/leave/review/" + this.request.id, {
          comment: this.review,
          decision: true,
        })
        .then((r) => {
          if (r.data.response === "success") {
            this.$emit("reloadapi");
            this.$emit("close");
            this.$emit("close");
            this.$buefy.toast.open({
              message: "Success",
              type: "is-success",
            });
          } else {
            this.$emit("close");
            this.$buefy.toast.open({
              message: r.data.msg,
              type: "is-danger",
            });
            // loadingComponent.close();
          }
          this.AcceptBtnLoading = false;
        });
    },
    Reject() {
      this.RejectBtnLoading = true;
      this.$http
        .post("./scheduleapi/leave/review/" + this.request.id, {
          comment: this.review,
          decision: false,
        })
        .then((r) => {
          if (r.data.response === "success") {
            this.$emit("reloadapi");
            this.$emit("close");
            this.$buefy.toast.open({
              message: "Success",
              type: "is-success",
            });
          } else {
            this.$emit("close");
            this.$buefy.toast.open({
              message: r.data.msg,
              type: "is-danger",
            });
            // loadingComponent.close();
          }
          this.RejectBtnLoading = false;
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
