<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Submit feedback</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>

      <section class="modal-card-body">
        <p>
          Using this form you can submit us your feedback. If you found
          something broken or working partially, you can submit it here. Please
          describe on which page you found bug and how to recreate it.
        </p>
        <p>
          <br />
          Please don't submit feedback regarding main page (with group tiles).
          It's incomplete and we are aware of it. Thanks!
        </p>
        <br />
        <b-message type="is-danger" has-icon v-if="error">
          {{ error }}
        </b-message>
        <b-field label="Review comment">
          <b-input type="textarea" v-model="review" placeholder="Comment" />
        </b-field>
      </section>
      <footer class="modal-card-foot" style="margin: unset">
        <b-button label="Close" @click="$emit('close')" />
        <b-button
          icon-left="share"
          label="Submit feedback"
          @click="Accept"
          type="is-info"
          :loading="AcceptBtnLoading"
        />
      </footer>
    </div>
  </form>
</template>

<script>
export default {
  name: "SubmitFeedbackModal",
  props: [],
  data() {
    return {
      review: "",
      AcceptBtnLoading: false,
      error: "",
    };
  },
  computed: {},
  mounted() {},
  methods: {
    Accept() {
      if (this.review.length === 0) {
        this.error = "Please write a comment first";
        return;
      }
      if (this.review.length > 500) {
        this.error = "The comment is too long. Max is 500 chars.";
        return;
      }
      this.error = "";
      this.AcceptBtnLoading = true;
      this.$http
        .post("./scheduleapi/feedback", {
          comment: this.review,
        })
        .then((r) => {
          if (r.data.response === "success") {
            this.$buefy.toast.open({
              message: "Success. Feedback sent. Thank you!",
              type: "is-success",
            });
            this.comment = ''
            this.$emit("close");
          } else {
            this.$buefy.toast.open({
              message: r.data.msg,
              type: "is-danger",
            });
            // loadingComponent.close();
          }
          this.AcceptBtnLoading = false;
        });
    },
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
