<template>
  <div>
    <div
      class="draftExists"
      v-if="draftExists && editorPermission===1"
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
          <li v-for="(draft, i) in draftList" :key="i" style="text-align: left">
            <b-tag type="is-danger" v-if="draft.deldraftauthor">Deletion</b-tag>
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
            <b-button
              @click="apply"
              style="float: left"
              type="is-primary"
              icon-left="check"
              >Commit all</b-button
            >
            <b-button
              @click="revert"
              style="float: right"
              type="is-danger"
              icon-left="undo"
              >Revert all</b-button
            >
          </li>
        </ul>
      </div>
    </div>
    <strong
      style="
        margin: 0 auto;
        text-align: center;
        display: block;
        margin: 0 auto;
        width: 100%;
        font-weght: bold;
        font-size: 26px;
      "
    >
      <h1 style="color: black; display: inline-flex">
        Vacationing
        <b-taglist attached style="display: inline-flex">
          <b-tag type="is-dark">{{ days[0] }}</b-tag>
          <b-tag type="is-info">{{ days[6] }}</b-tag>
        </b-taglist>
      </h1>
    </strong>
    <p></p>
    <div class="weekmovingActions">
      <b-button
        @click="moveWeek(-1)"
        type="is-primary"
        icon-left="angle-double-left"
        >1 week back</b-button
      >
      <!-- <b-button @click="readAPI" type="is-info" icon-left="sync-alt"></b-button> -->
      <b-button
        @click="CalendarOpened = !CalendarOpened"
        type="is-info"
        icon-left="calendar-alt"
      ></b-button>

      <b-button
        style="float: right"
        type="is-primary"
        @click="moveWeek(1)"
        icon-right="angle-double-right"
        >1 week forward</b-button
      >
      <b-notification
        has-icon
        icon="calendar-alt"
        v-model="CalendarOpened"
        type="is-info"
        aria-close-label="Close notification"
        style="text-align: center"
      >
        <vc-calendar
          :attributes="attributes"
          :masks="masks"
          ref="calendar"
        ></vc-calendar>
      </b-notification>

      <div class="schedulerow">
        <div class="columns" v-if="days">
          <vacationcolumn
            :ref="day"
            class="graphcolumn"
            v-for="(day, index) in days"
            :key="day"
            :ind="index"
            :day="day"
           
          ></vacationcolumn>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex';
//import SidebarRight from '../components/SidebarRight.vue'
import vacationcolumn from "../components/vacationcolumn.vue";
export default {
  components: { vacationcolumn },
  data() {
    return {
      activeTab: null,
      CalendarOpened: false,
      selectedDate: "",
      calendardates: [
        {
          key: "2021-06-01",
          highlight: true,
          dates: new Date("2021-06-01"),
        },
      ],
      masks: {
        weekdays: "WWW",
      },
      referenceDate: this.moment(this.$store.state.refDate),
    };
  },
  computed: {
   ...mapState(["editorPermission"]),
    attributes() {
      return [
        // Attributes for todos
        ...this.$store.getters.timetable.map((todo) => ({
          dates: todo.date,
          dot: !todo.highlight
            ? {
                color: "blue",
              }
            : undefined,
          highlight: todo.highlight ?? [],
          popover: !todo.highlight
            ? {
                visibility: "hover",
                label: todo.description,
              }
            : undefined,
          customData: todo,
        })),
      ];
    },
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
      if (!reference.isValid()) return [];
      days.push(reference.format("ddd DD.MM"));
      for (var i = 1; i < 7; i++) {
        reference = reference.add(1, "day");
        days.push(reference.format("ddd DD.MM"));
      }
      return days;
    },
  },
  mounted() {
    //this.activeTab = Object.keys(this.teams)[0]
    // console.log(this.$route.params)
    this.checkpointDate("vacationing"); ///mixin
    this.referenceDate = this.moment();
    this.loadAgentsTeams()
    this.readVacationAPI()
  },
  methods: {
    readVacationAPI() {
      this.loading = this.$buefy.loading.open({
        container: null,
      });

      this.$store
        .dispatch("loadVacationings", {
          //  team: this.team_id,
          startdate: this.$route.params.date,
          //refdateroute: this.$route.params.date,
          //refdate: this.referenceDate.format("YYYY-MM-DD"),
        })
        .then(
          () => {
            this.loading.close();
            this.loading = null;
            // this.$set(
            //   this,
            //   "shiftsTimetable",
            //   this.$store.state.shiftsTimetable
            // );
            this.referenceDate = this.moment(this.$store.state.refDate);
            this.loadAgentsTeams()
            // this.loadCalendar();
            // this.markShift();
          },
          (reason) => {
            this.loading.close();
            this.loading = null;
            this.$router.push({ path: `/schedule` });

            this.$buefy.snackbar.open({
              duration: 5000,
              message: reason,
              type: "is-danger",
              position: "is-bottom-left",
              queue: false,
            });
          }
        );
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
    loadAgentsTeams() {
      this.$store
        .dispatch("loadAgentsForGroupAll")
        .then(() => {});
    },
    getFirstDay() {
      this.moment();
    },
    async moveWeek(direction) {
      if (direction == -1) {
        this.referenceDate = await this.moment(this.referenceDate).subtract(
          1,
          "week"
        );
      } else {
        this.referenceDate = await this.moment(this.referenceDate).add(1, "week");
      }
      
     // this.loadCalendar();
      // let alreadyInStore = false;
      // if (
      //   this.$store.state.timetable[
      //     this.moment(this.referenceDate).format("YYYY-MM-DD")
      //   ]
      // ) {
      //   alreadyInStore = true;
      // }
      // for (var i = 1; i < 8; i++) {
      //   if (
      //     this.$store.state.timetable[
      //       this.moment(this.referenceDate).add(1, "day").format("YYYY-MM-DD")
      //     ]
      //   ) {
      //     alreadyInStore = true;
      //   }
      // }

      await this.$router.push({
        path: `/vacationing/${this.moment(
          this.referenceDate
        ).format("MMMDD")}-${this.moment(this.referenceDate)
          .add(6, "day")
          .format("MMMDD")}-${this.moment(this.referenceDate).format("YYYY")}`,
      });

     // if (!alreadyInStore) {
        this.$store.commit("setRefdate", this.referenceDate);

        this.readVacationAPI();
        
     // } else {
     //   this.$store.commit("setRefdate", this.referenceDate);
    //  }
    },
  },
};
</script>
<style>
.columndragenter {
  border-style: dashed;
  opacity: 0.5;
}
.column li {
  border: 1px solid black;
  border-bottom: 0;
}
.column li:last-child {
  border: 1px solid black;
}

.table tr {
  height: 40px;
}
.weekmovingActions button {
  margin-left: 5px;
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
}
.groupname {
  width: 100%;
  text-align: center;
  font-size: 1.6em;
}
.graphcolumn {
  -webkit-transition: all 0.6s ease-in; /* Chrome 1-25, Safari 3.2+ */
  -moz-transition: all 0.6s ease-in; /* Firefox 4-15 */
  -o-transition: all 0.6s ease-in; /* Opera 10.50–12.00 */
  transition: all 0.6s ease-in; /* Chrome 26, Firefox 16+, IE 10+, Opera 12.10+ */
  background: rgb(58, 97, 180);
  background-position: 0 -30px;
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