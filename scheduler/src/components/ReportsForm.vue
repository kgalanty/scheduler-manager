<template>
  <div>
    <div class="notification">
      <b-message type="is-info" has-icon>
        Due to limited space, it's recommended to select maximum 7 days range.
      </b-message>
      <div class="column">
        <b-field label="Select a date from">
          <b-datepicker
            v-model="datefrom"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
          >
          </b-datepicker>
        </b-field>
      </div>
      <div class="column">
        <b-field label="Select a date to">
          <b-datepicker
            v-model="dateto"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
          >
          </b-datepicker>
        </b-field>
      </div>
      <b-button @click="GenerateReport" type="is-link">
        Generate PDF with My team's Schedule
      </b-button>
    </div>
  </div>
</template>

<script>
export default {
  name: "ReportsForm",
  computed: {},
  methods: {
    GenerateReport() {
      this.$http
        .get(
          "./scheduleapi/report/" +
            this.moment(this.datefrom).format("YYYY-MM-DD") +
            "/" +
            this.moment(this.dateto).format("YYYY-MM-DD")
        )
        .then((r) => {
          if (r.data.result === "success") {
              this.$buefy.toast.open({
              message: "Team schedule has been sent to your e-mail",
              type: "is-success",
            });

          }
          else
          {
             this.$buefy.toast.open({
              message: "Error occurred. "+r.data.msg,
              type: "is-danger",
            });
          }
        });
    },
  },
  data() {
    return {
      datefrom: this.moment().startOf("isoWeek").toDate(),
      dateto: this.moment().endOf("isoWeek").toDate(),
    };
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style >
.b-table {
  float: left;
  width: 100%;
  border: 1px solid black;
}
.b-table td {
  height: auto;
}
.b-table tr {
  height: auto !important;
}

.shiftstable thead:first-child th {
  background: rgb(165, 197, 255);
  background: linear-gradient(
    180deg,
    rgba(165, 197, 255, 1) 0%,
    rgba(40, 68, 207, 1) 100%
  );
  color: white;
}
</style>
