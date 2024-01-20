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
        <b-field label="Excluded dates (optional) (not counted to days off)" v-if="requestType.isVacationRequest()">
        <b-datepicker
            placeholder="Click to select..."
            v-model="excluded_dates" append-to-body
            :unselectable-dates="unselectableDates"
            :events="shifts_events"
            indicators="bars"
            :focused-date="focused_date"
            multiple>
        </b-datepicker></b-field>

        <b-field label="Nearest Shifts" v-if="requestType.isVacationRequest()">
          <b-collapse :open="false" aria-id="contentIdForA11y1" v-if="requestType.isVacationRequest()">
            <template #trigger="props">
                <b-button
                    label="Expand the list"
                    type="is-primary"
                    aria-controls="contentIdForA11y1"
                    :aria-expanded="props.open" />
            </template>
            <div style="max-height: 300px;">
                <div class="content">
                    <p>
                        <ul>
                          <li v-for="(event, index) in shifts_events" :key="index">{{event.day }} {{ event.from}}-{{event.to}}</li>

                      </ul>
                    </p>
                </div>
            </div></b-collapse>
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
import LeaveRequest from "../libs/LeaveRequest";

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
      error: "",
      excluded_dates: [],
      shifts_events: [],
    };
  },
  computed: {
    requestType() {
      return new LeaveRequest(this.request.request_type);
    },
    focused_date()
    {
      return new Date(this.request.date_start)
    }
  },
  mounted() {
    this.days = this.currentdays;
    this.getAgentShifts();
  },
  methods: {
    getAgentShifts() {
      this.$http
        .get(
          "./scheduleapi/agents/" +
            this.request.agent_id +
            "/timetable?date=" +
            this.request.date_start
        )
        .then((r) => {
          this.shifts_events = r.data.timetable;

          this.shifts_events.forEach((item, i) => {
            (this.shifts_events[i].date = new Date(item.day)),
              (this.shifts_events[i].type =
                this.shifts_events[i].from < "12:00"
                  ? "is-info"
                  : "is-warning");
          });
        });
    },
    unselectableDates(day) {
      const month = day.getMonth() + 1;
      const day_date =
        day.getFullYear() +
        "-" +
        (month < 10 ? "0" + month : month) +
        "-" +
        (day.getDate() < 10 ? "0" : "") +
        day.getDate();
      return (
        day_date < this.request.date_start || day_date > this.request.date_end
      );
    },
    Accept() {
      var excl_dates = [];
      if (this.excluded_dates.length > 0) {
        for (const excl_date of this.excluded_dates) {
          const day = new Date(excl_date);
          const month = day.getMonth() + 1
          const day_date =
            day.getFullYear() +
            "-" +
            (month < 10 ? "0" : '') +
            month + "-" +
            (day.getDate() < 10 ? "0" : "") +
            day.getDate();
          excl_dates.push(day_date);
        }
      }

      this.AcceptBtnLoading = true;
      this.$http
        .post("./scheduleapi/leave/review/" + this.request.id, {
          comment: this.review,
          decision: true,
          excl_dates,
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
            this.$buefy.toast.open({
              message: r.data.msg,
              type: "is-danger",
              duration: 5000
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
            this.$buefy.toast.open({
              message: r.data.msg,
              type: "is-danger",
              duration: 5000
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
