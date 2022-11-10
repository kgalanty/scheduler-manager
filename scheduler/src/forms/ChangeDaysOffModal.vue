<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Editing Pool</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>

      <section class="modal-card-body">
        <b-message type="is-danger" has-icon v-if="error">
          {{ error }}
        </b-message>
        <b-field label="Days">
          <b-numberinput v-model="days"></b-numberinput>
        </b-field>
        <b-field label="Expiration Date">
          <b-datepicker
            v-model="dateexp"
            :first-day-of-week="1"
            append-to-body
            ref="datepicker"
            expanded
            placeholder="Select a date"
          >
          </b-datepicker>
          <b-button
            @click="$refs.datepicker.toggle()"
            icon-left="calendar"
            type="is-primary"
          />
        </b-field>
      </section>
      <footer class="modal-card-foot" style="margin: unset">
        <b-button label="Close" @click="$emit('close')" />
        <b-button
          icon-left="plus"
          label="Save"
          @click="Save"
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
  props: ["daysoffentry"],
  data() {
    return {
      days: 0,
      saveInProgress: false,
      error: "",
      dateexp: new Date(),
    };
  },
  computed: {},
  mounted() {
    this.days = this.daysoffentry.days;
    this.dateexp = new Date(this.daysoffentry.date_expiration)
  },
  methods: {
    Save() {
      if (this.days < 1) {
        this.error = "You must Specify number of days";
        return;
      }
      let ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(this.dateexp);
      let mo = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(this.dateexp);
      let da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(this.dateexp);

      this.error = "";
      this.saveInProgress = true;
      this.$http
        .post("./scheduleapi/daysoff/entry/" + this.daysoffentry.id, {
          changedays: this.days,
          dateexp: `${ye}-${mo}-${da}`,
        })
        .then((r) => {
          if (r.data.result === "success") {
            this.$emit("reloadapi");
            this.$emit("close");
          } else {
            this.$emit("close");
            // loadingComponent.close();
          }
          this.saveInProgress = false;
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
