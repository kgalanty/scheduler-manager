<template>
  <div>
    <div v-if="shifts.length > 0">
      <b-button @click="moveWeek(-1)" type="is-primary" icon-left="angle-double-left">1 week back</b-button
      ><b-button style="float: right" type="is-primary" @click="moveWeek(1)"  icon-right="angle-double-right"
        >1 week forward</b-button
      >
      <div class="container" v-for="(shift, index) in shifts" :key="index">
        <strong style="display:block;width:100%;font-weght:bold;font-size:20px;">
          <h1><b-icon icon="user-clock" size="is-medium"></b-icon> {{ shift.from }} - {{ shift.to }}</h1>
          </strong>
        <p></p>
        <div class="columns is-desktop">
          <scheduleColumn class="graphcolumn"
            v-for="(day, index) in days"
            :key="day"
            :ind="index"
            :day="day"
            :shift="shift.id"
            :group="team_id"
            :refdate="referenceDate"
          ></scheduleColumn>
        </div>
      </div>
    </div>
    <div
      v-if="shifts.length == 0 && this.loading == null"
      style="text-align: center"
    >
      <b-message title="Info" type="is-info" has-icon :closable="false">
        No shifts defined for this team yet
      </b-message>
    </div>
        <b-skeleton style="float: left" animated v-if="this.loading != null"></b-skeleton>
  </div>
</template>

<script>
import scheduleColumn from "../components/schedulecolumn.vue";
export default {
  components: {
    scheduleColumn,
  },
  name: "GroupSchedule",
  props: ["team_id"],
  mounted() {
    this.readAPI();
   
  },
    computed: {
    days() {
      let days = [];
      var reference = this.moment(this.referenceDate);
      days.push(reference.format("ddd DD.MM"));
      for (var i = 1; i < 7; i++) {
        reference = reference.add(1, "day");
        days.push(reference.format("ddd DD.MM"));
      }
      return days;
    },
    timetable() {
      return this.$store.state.timetable[
        this.moment(this.referenceDate).format("YYYY-MM-DD")
      ].t;
    },
    shifts() {
      if (
        this.$store.state.timetable[
          this.moment(this.referenceDate).format("YYYY-MM-DD")
        ] &&
        this.$store.state.timetable[
          this.moment(this.referenceDate).format("YYYY-MM-DD")
        ].shifts
      )
        return this.$store.state.timetable[
          this.moment(this.referenceDate).format("YYYY-MM-DD")
        ].shifts;
      return [];
    },
  },
  data() {
    return {
      teams: [],
      schedule: [],
      referenceDate: this.moment().day(1),
      loading: null,
    };
  },
  methods: {
    readAPI() {
      this.loading = this.$buefy.loading.open({
        container: null,
      });
      this.$store
        .dispatch("loadFromAPI", {
          team: this.team_id,
          teamroute: this.$route.params.team,
          refdateroute: this.$route.params.date,
          refdate: this.referenceDate.format("YYYY-MM-DD"),
 
        })
        .then(() => {
          this.loading.close();
          this.loading = null;
        });
      // this.$http
      //   .get(
      //     "/scheduleapi/shifts/shiftsgroups/" +
      //       this.team_id +
      //       "?startdate=" +
      //       this.referenceDate.format("YYYY-MM-DD")
      //   )
      //   .then((response) => {
      //     this.schedule = response.data;
      //     loadingComponent.close();
      //   });
    },
    moveWeek(direction) {
      if (direction == -1) {
        this.referenceDate = this.moment(this.referenceDate).subtract(
          1,
          "week"
        );
      } else {
        this.referenceDate = this.moment(this.referenceDate).add(1, "week");
      }
      this.$router.push({ path: `/schedule/${this.$route.params.team}/${this.moment(this.referenceDate).format('MMMDD')}-${this.moment(this.referenceDate).add(6, "day").format('MMMDD')}-${this.moment(this.referenceDate).format('YYYY')}`}) 
      this.readAPI();
    },
  },

};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.graphcolumn
{
  background: rgb(58,97,180);
background: linear-gradient(90deg, rgba(58,97,180,1) 0%, rgba(72,171,255,1) 100%);
color:white;
}
.column
{
  border-radius: 5px;
  margin:15px 5px;
}
</style>
