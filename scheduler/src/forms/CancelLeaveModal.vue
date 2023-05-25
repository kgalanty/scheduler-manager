<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Cancel Leave</p>
        <button type="button" class="delete" @click="$emit('close')" />
      </header>

      <section class="modal-card-body">
        <p>
          Leave dates: {{ request.date_start }} - {{ request.date_end }} for
          {{ request.a_firstname }} {{ request.a_lastname }}
        </p>
        <p>
          Excluded dates: <strong>{{ request.excluded_days }} </strong> (red
          dots in calendar)
        </p>
        <br />
        <b-message type="is-danger" has-icon v-if="error">
          {{ error }}
        </b-message>
        <b-field label="Dates Range">
          <b-datepicker
            v-model="datesrange"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
            :unselectable-days-of-week="[0, 1, 2, 3, 4, 5, 6]"
            :first-day-of-week="1"
            range
            append-to-body
            :events="excluded_dates_calendar"
          >
          </b-datepicker>
        </b-field>
        <b-field label="Return days to pool">
          <b-input
            placeholder="Number"
            type="number"
            min="0"
            max="20"
            v-model="daysreturn"
          >
          </b-input>
        </b-field>
      </section>
      <footer class="modal-card-foot" style="margin: unset">
        <b-button label="Close" @click="$emit('close')" />
        <b-button
          icon-left="check"
          label="Accept Cancellation"
          @click="Save"
          type="is-success"
          :loading="BtnLoading"
        />
      </footer>
    </div>
  </form>
</template>

<script>
import LeaveRequest from "../libs/LeaveRequest";

export default {
  name: "CancelLeaveModal",
  props: ["request"],
  data() {
    return {
      review: "",
      decision: false,
      saveInProgress: false,
      BtnLoading: false,
      error: "",
      excluded_dates: [],
      shifts_events: [],
      datesrange: [],
      daysreturn: 0,
    };
  },
  computed: {
    excluded_dates_calendar() {
      let dates = new Array();
      this.excluded_dates.map((i) =>
        dates.push({
          date: new Date(i),
          type: "is-danger",
        })
      );
      return dates;
    },
    requestType() {
      return new LeaveRequest(this.request.request_type);
    },
    focused_date() {
      return new Date(this.request.date_start);
    },
    daysDifference() {
      const OriginalRequestDays = Math.round(
        (new Date(this.request.date_end).getTime() -
          new Date(this.request.date_start).getTime()) /
          (1000 * 60 * 60 * 24)
      );
      const OriginalExcludedDays = this.request?.excluded_days?.length
        ? this.request.excluded_days?.split(",").length
        : 0;

      const RequestDaysAfter = Math.round(
        (new Date(this.datesrange[1]).getTime() -
          new Date(this.datesrange[0]).getTime()) /
          (1000 * 60 * 60 * 24)
      );

      const ExcludedDaysAfter = this.excluded_dates.length;

      return (
        OriginalRequestDays -
        OriginalExcludedDays -
        (RequestDaysAfter - ExcludedDaysAfter)
      );
    },
  },
  mounted() {
    this.range = this.request;
    this.days = this.currentdays;
    this.datesrange = [
      new Date(this.request.date_start),
      new Date(this.request.date_end),
    ];

    let ExclDaysArray = [];
    this.request.excluded_days
      ?.split(",")
      .forEach((i) => ExclDaysArray.push(new Date(i)));

    this.excluded_dates = ExclDaysArray;

    const OriginalExcludedDays = this.request?.excluded_days?.length
      ? this.request.excluded_days?.split(",").length
      : 0;

    this.daysreturn =
      new Date(this.request.date_start) > new Date()
        ? Math.round(
            (new Date(this.request.date_end).getTime() -
              new Date(this.request.date_start).getTime()) /
              (1000 * 60 * 60 * 24)
          ) +
          1 -
          OriginalExcludedDays
        : 0;
  },
  methods: {
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
        day_date < new Date().getFullYear() + "-01-01" ||
        day_date > new Date().getFullYear() + "-12-31"
      );
    },
    Save() {
      this.BtnLoading = true;
      this.$http
        .post("./scheduleapi/leave/cancel/" + this.request.id, {
          daysreturn: this.daysreturn
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
          this.BtnLoading = false;
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
