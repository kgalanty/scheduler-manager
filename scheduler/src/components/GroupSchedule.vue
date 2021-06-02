<template>
  <div>
    <div
      class="draftExists"
      v-if="draftExists"
      @mouseover="pendingchangeswidget = true"
      @mouseleave="pendingchangeswidget = false"
    >
      <b-icon
        icon="arrows-alt-v"
        style="float: left"
        class="pendingmenubar"
      ></b-icon>
      Pending changes
      <b-icon icon="arrows-alt-v" style="float: right"></b-icon>
      <div v-show="pendingchangeswidget" class="pendingmenu" style="">
        <ul style="overflow: auto; max-height: inherit; height: 100%">
          <li
            v-for="(draft, i) in draftList"
            :key="i"
            style="text-align: left"
          >
            <b-tag type="is-danger" v-if="draft.deldraftauthor"
              >Deletion</b-tag
            >
            <b-tag type="is-success" v-if="!draft.deldraftauthor"
              >Addition</b-tag
            >
            {{ draft.day }}
            <b-taglist attached style="display: inline-block">
              <b-tag type="is-dark"
                ><b-icon icon="user-clock" size="is-small"></b-icon
              ></b-tag>
              <b-tag type="is-link">{{ draft.from }}-{{ draft.to }} </b-tag>
              {{ draft.firstname }} {{ draft.lastname }}
            </b-taglist>
          </li>

          <li style="border-top: 1px dashed black; padding: 5px 0">
            <b-button @click="apply" style="float: left" type="is-primary" icon-left="check"
              >Commit all</b-button
            >
            <b-button @click="revert" style="float: right" type="is-danger" icon-left="undo"
              >Revert all</b-button
            >
          </li>
        </ul>
      </div>
    </div>

    <h2 class="groupname">
      <span style="vertical-align: text-top;display:inline-flex;margin-right:5px;">{{ groupname }}</span>
      <b-taglist attached style="display: inline-flex;">
        <b-tag type="is-dark">{{ days[0] }}</b-tag>
        <b-tag type="is-info">{{ days[6] }}</b-tag>
      </b-taglist>
    </h2>

    <div v-if="shiftsTimetable.length > 0" class="weekmovingActions">
      <b-button
        @click="moveWeek(-1)"
        type="is-primary"
        icon-left="angle-double-left"
        >1 week back</b-button
      >
     <b-button
        @click="readAPI"
        type="is-info"
        icon-left="sync-alt"
        ></b-button
      >
      <b-button
        style="float: right"
        type="is-primary"
        @click="moveWeek(1)"
        icon-right="angle-double-right"
        >1 week forward</b-button
      >

      <div
        class="schedulerow"
        v-for="(shift, index) in shiftsTimetable"
        :key="index + 'tt'"
      >
        <strong
          style="display: block; width: 100%; font-weght: bold; font-size: 20px"
        >
          <h1 style="margin-right: 10px; color: black; display: inline-flex">
            <b-taglist attached>
              <b-tag type="is-dark"
                ><b-icon icon="user-clock" size="is-small"></b-icon
              ></b-tag>
              <b-tag type="is-link">{{ shift.from }} - {{ shift.to }}</b-tag>
            </b-taglist>
          </h1>
        </strong>
        <p></p>
        <div class="columns">
          <scheduleColumn
            class="graphcolumn"
            v-for="(day, index) in days"
            :key="day"
            :ind="index"
            :day="day"
            :shift="shift.id"
            :group="shiftsTimetable[0].group_id"
            :refdate="referenceDate"
          ></scheduleColumn>
        </div>
      </div>
    </div>
    <div
      v-show="shiftsTimetable.length == 0 && this.loading == null"
      style="text-align: center"
    >
      <b-message title="Info" type="is-info" has-icon :closable="false">
        No shifts defined for this team yet.
        <router-link :to="`/admin`">Add one.</router-link>
      </b-message>
    </div>
    <b-skeleton
      style="float: left"
      animated
      v-if="this.loading != null"
    ></b-skeleton>
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
   // console.log(this.$route.params.date)
    this.readAPI();
  },

  computed: {

    draftExists() {
      return this.$store.state.draftexists;
    },
    draftList() {
      return this.$store.state.draftentries;
    },
    groupname() {
      return this.$store.state.groupname;
    },
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
        this.moment(this.$store.state.refDate).format("YYYY-MM-DD")
      ].t;
    },
    shifts() {
      if (
        this.$store.state.timetable[this.referenceDate.format("YYYY-MM-DD")] &&
        this.$store.state.timetable[this.referenceDate.format("YYYY-MM-DD")]
          .shifts
      )
        return this.$store.state.timetable[
          this.referenceDate.format("YYYY-MM-DD")
        ].shifts;
      return [];
    },
    //   shiftsTimetable()
    // {
    //   return this.$store.state.shiftsTimetable
    // }
    // referenceDate()
    // {
    //   return this.moment(this.$store.state.refDate)
    // }
  },
  data() {
    return {
      teams: [],
      schedule: [],
      loading: null,
      group_name: '',
      pendingchangeswidget: false,
      referenceDate: this.moment(this.$store.state.refDate),
      shiftsTimetable: [],
    };
  },
  methods: {
    apply() {
      this.$http.post("./scheduleapi/shifts/commit").then((response) => {
        if (response.data.response == "success") {
          this.$buefy.toast.open({
            message: "Done",
            type: "is-success",
          });
          this.readAPI();
        } else {
          this.$buefy.toast.open({
            message: response.data.response,
            type: "is-danger",
          });
        }
      });
    },
    revert() {
      this.$http.post("./scheduleapi/shifts/revert").then((response) => {
        if (response.data.response == "success") {
          this.$buefy.toast.open({
            message: "Done",
            type: "is-success",
          });
          this.readAPI();
        } else {
          this.$buefy.toast.open({
            message: response.data.response,
            type: "is-danger",
          });
        }
      });
    },
    readAPI() {
      this.loading = this.$buefy.loading.open({
        container: null,
      });

      this.$store
        .dispatch("loadFromAPI", {
          //  team: this.team_id,
          teamroute: this.$route.params.team,
          refdateroute: this.$route.params.date,
          //refdate: this.referenceDate.format("YYYY-MM-DD"),
        })
        .then(() => {
          this.group_name = this.$store.state?.group?.group;
          this.loading.close();
          this.loading = null;
          this.$set(this, "shiftsTimetable", this.$store.state.shiftsTimetable);
          this.referenceDate = this.moment(this.$store.state.refDate);
        }, (reason) =>
        {
           this.loading.close();
          this.loading = null;
          this.$router.push({path: `/schedule`})
         
            this.$buefy.snackbar.open({
                      duration: 5000,
                      message: reason,
                      type: 'is-danger',
                      position: 'is-bottom-left',
                      queue: false,
                  })
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
     let alreadyInStore = false
        if(this.$store.state.timetable[this.moment(this.referenceDate).format('YYYY-MM-DD')])
        {
          alreadyInStore = true
        }
      for(var i=1;i<8;i++)
      {
        if(this.$store.state.timetable[this.moment(this.referenceDate).add(1, "day").format('YYYY-MM-DD')])
        {
          alreadyInStore = true
        }
      }

      this.$router.push({
        path: `/schedule/${this.$route.params.team}/${this.moment(
          this.referenceDate
        ).format("MMMDD")}-${this.moment(this.referenceDate)
          .add(6, "day")
          .format("MMMDD")}-${this.moment(this.referenceDate).format("YYYY")}`,
      });
      if(!alreadyInStore) this.readAPI();
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.weekmovingActions button{
  margin-left:5px;
}
.pendingmenu {
  border-top: 1px solid black;
  padding: 5px;
  height: auto;
  overflow: hidden;
  max-height: 300px;
}

.draftExists {
  position: absolute;
  top: -55px;
  z-index: 111;
  left: 25%;
  border: 2px solid black;
  width: 50%;
  color: #7957d5;
  background-color: white;
  font-weight: 500;
  text-align: center;
  border-radius: 5px;
  background-color: ;
}
.groupname {
  display: block;
  width: 100%;
  text-align: center;
  font-size: 1.6em;
}
.graphcolumn {
  background: rgb(58, 97, 180);
  background: linear-gradient(
    90deg,
    rgba(58, 97, 180, 1) 0%,
    rgba(72, 171, 255, 1) 100%
  );
  color: white;
}
.column {
  border-radius: 5px;
  margin: 15px 5px;
}
.schedulerow {
  padding: 5px 15px;
  margin: 15px 0;
  background: rgb(206, 206, 206);
  border-radius: 10px;
  box-shadow: 6px 3px 3px rgb(179, 179, 179);
}
</style>
