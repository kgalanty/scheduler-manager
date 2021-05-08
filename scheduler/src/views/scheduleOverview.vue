<template>
  <div>
    <div v-for="(team,i) in teams" :key="i">
     <router-link :to="`/schedule/${team.name}/${dateStart}-${dateEnd}`">{{team.name}} ({{dateStart}} - {{dateEnd}})</router-link>
      </div>
  </div>
</template>

<script>
//import scheduleColumn from "./schedulecolumn.vue";
export default {
  components: {
    //scheduleColumn,
  },
  name: "scheduleOverview",
  props: ["team_id"],
  mounted() {
   // this.readAPI();
   
  },
    computed: {
    teams() {
      return this.$store.state.schedule_teams
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
      dateStart: this.moment().day(1).format('MMMDD'),
      dateEnd: this.moment().day(7).format('MMMDD')+'-'+this.moment().day(1).format('YYYY'),
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
      console.log(this.$route.params)
      this.$router.push({ path: `/schedule/${this.$route.params.team}/${this.moment(this.referenceDate).format('MMMDD')}-${this.moment(this.referenceDate).add(1, "week").format('MMMDD')}-${this.moment(this.referenceDate).format('YYYY')}`}) 
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
