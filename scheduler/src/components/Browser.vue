<template>
  <div style="width:80%;margin:0 auto;">
    <h1 class="">TMD Teams Schedules Browser</h1>

    <div class="tile is-ancestor" v-for="(item, i) in teamsrows" :key="i">
        <GroupTile v-for="(item2, ii) in teamsrows[i]"
        :key="ii + 'team'"
        :path="'/schedule/'+item2.name+'/'+dateStart+'-'+dateEnd"
        :label="item2.name"
        icon="users"
        
       
      />
    </div>
    <!-- <div class="tile is-ancestor">
        <GroupTile label="Technical Support" icon="users" />
        <GroupTile label="DevOps & Cloud" icon="users" />
        <GroupTile label="System Administrators" icon="users" />
     </div> -->
    <div class="tile is-ancestor" >
      <GroupTile label="Feedback" icon="meh" />
      <GroupTile label="Trainings" icon="graduation-cap" />
      <GroupTile label="Vacationing" icon="sun" 
      :path="'/vacationing/'+dateStart+'-'+dateEnd"/>
    </div>
  </div>
</template>

<script>
import GroupTile from "./GroupTile.vue";
export default {
  name: "Browser",
  components: {
    GroupTile,
  },
  data() {
    return {
      dateStart: this.moment().isoWeekday(1).format("MMMDD"),
      dateEnd:
        this.moment().day(7).format("MMMDD") +
        "-" +
        this.moment().day(1).format("YYYY"),
    };
  },
  methods: {
    openTeam(name) {
     // console.log(name);
      this.$router.push({
        path: `/schedule/${name}/${this.dateStart}-${this.dateEnd}`,
      });
    },
  },
  computed: {
    rows() {
      return Math.round(Object.keys(this.teams).length / 3);
    },
    teamsrows() {
      let r = [];
      let teams = Object.values(this.teams);
      // const n = 3\
      const wordsPerLine = Math.ceil(teams.length / 3);
      for (let line = 0; line < wordsPerLine; line++) {
        r.push([]);
        for (let i = 0; i < 3; i++) {
          const value = teams[i + line * 3];
          // console.log(value)
          if (!value) continue; //avoid adding "undefined" values
          r[line].push(value);
        }
      }

      return r;
    },
    teams() {
      return this.$store.state.schedule_teams;
    },
  },
  mounted() {
    this.$store.dispatch("getTeams");
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
