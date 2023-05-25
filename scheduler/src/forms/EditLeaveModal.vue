<template>
  <form action="">
    <div class="modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Edit Leave</p>
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
        <b-field label="Dates Range">
          <b-datepicker
                    v-model="datesrange"
                    placeholder="Click to select..."
                    icon="calendar"
                    trap-focus
                    :unselectable-dates="unselectableDates"
                    :first-day-of-week="1"
                    range append-to-body
                    @change="recalculateDaysBalance"
                  >
                  </b-datepicker>
        </b-field>
        <b-field label="Excluded dates (optional) (not counted to days off)" v-if="requestType.isVacationRequest()">
        <b-datepicker
            placeholder="Click to select..."
            v-model="excluded_dates" append-to-body
            :events="shifts_events"
            indicators="bars"
            :focused-date="focused_date"
            :first-day-of-week="1"
            @change="recalculateDaysBalance"
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
    <b-field label="Changes result" v-if="requestType.isVacationRequest() && daysDifference != 0">
    <h2>After change <span v-show="daysDifference < 0" style="color: red">additional {{ Math.abs(daysDifference) }} days will be taken from vacation pool.</span>
      <span v-show="daysDifference > 0" style="color: blue">{{ Math.abs(daysDifference) }} days will be returned to vacation pool.</span>
    </h2>
    
    </b-field>
      </section>
      <footer class="modal-card-foot" style="margin: unset">
        <b-button label="Close" @click="$emit('close')" />
        <b-button
          icon-left="pen"
          label="Save"
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
  name: "EditLeaveModal",
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
    };
  },
  computed: {
    requestType() {
      return new LeaveRequest(this.request.request_type);
    },
    focused_date()
    {
      return new Date(this.request.date_start)
    },
    daysDifference()
    {
      const OriginalRequestDays = Math.round(
        (new Date(this.request.date_end).getTime() - (new Date(this.request.date_start).getTime() )
        ) / (1000 * 60 * 60 * 24)
      )
      const OriginalExcludedDays = this.request?.excluded_days?.length ? 
      this.request.excluded_days?.split(',').length : 0

      const RequestDaysAfter = Math.round(
        (new Date(this.datesrange[1]).getTime() - (new Date(this.datesrange[0]).getTime() )
        ) / (1000 * 60 * 60 * 24)
      )

      const ExcludedDaysAfter = this.excluded_dates.length

      return (OriginalRequestDays - OriginalExcludedDays) - (RequestDaysAfter - ExcludedDaysAfter)
    }
  },
  mounted() {
    this.range = this.request
    this.days = this.currentdays;
    this.getAgentShifts();
    this.datesrange = [new Date(this.request.date_start), new Date(this.request.date_end)];
    
    let ExclDaysArray = []
    if(this.request.excluded_days!= '')
    {
      this.request.excluded_days.split(',').forEach(i=> ExclDaysArray.push(new Date(i)))
    }

    this.excluded_dates = ExclDaysArray
  },
  methods: {
    recalculateDaysBalance()
    {
       
    },
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

      return day_date < ((new Date().getFullYear()) + '-01-01') || day_date > (new Date().getFullYear()) + '-12-31'
      
    },
    Save() {
      var excl_dates = [];
      if (this.excluded_dates.length > 0) {
        for (const excl_date of this.excluded_dates) {
          const day = new Date(excl_date);
          const month = day.getMonth() + 1
          const day_date =
            day.getFullYear() +
            "-" +
            (month < 10 ? "0" : "") + month +
            "-" +
            (day.getDate() < 10 ? "0" : "") +
            day.getDate();
          excl_dates.push(day_date);
        }
      }

      this.BtnLoading = true;
      this.$http
        .post("./scheduleapi/leave/edit/" + this.request.id, {
          datestart: this.moment(this.datesrange[0]).format("YYYY-MM-DD"),
          dateend: this.moment(this.datesrange[1]).format("YYYY-MM-DD"),
          excl_dates,
          diff: this.daysDifference
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
