<template>
  <div>
    <div
      class="draftExists"
      v-if="draftExists "
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

          <li style="border-top: 1px dashed black; padding: 5px 5px">
            <b-button
              @click="apply(true)"
              style="float: left"
              type="is-primary"
              icon-left="check"
              >Commit all</b-button
            >
            <b-button
              @click="apply(false)"
              style="float: left; margin-left: 5px"
              type="is-warning"
              icon-left="check"
              >Commit all (no notifications)</b-button
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
    <h2 class="groupname">
      <span
        style="
          vertical-align: text-top;
          display: inline-flex;
          margin-right: 5px;
        "
        >{{ groupname }}</span
      >
      <b-taglist attached style="display: inline-flex" v-if="days && loading === null">
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
      <b-button @click="readAPI" type="is-info" icon-left="sync-alt"></b-button>
      <b-button
        @click="CalendarOpened = !CalendarOpened"
        type="is-info"
        icon-left="calendar-alt"
      ></b-button>
      <b-tooltip
        v-if="canShowEditorContent"
        label="This will send slack notifications to all people who has shifts this week"
        ><b-button @click="confirmSchedule" type="is-success" icon-right="check"
          >Send notifications</b-button
        ></b-tooltip
      >

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
        <div v-for="(shift, index) in shiftsTimetable" :key="index + 'tt'">
          <span v-if="shift.hide == 0">
            <strong
              style="
                display: block;
                width: 100%;
                font-weght: bold;
                font-size: 20px;
              "
            >
              <h1
                style="margin-right: 10px; color: black; display: inline-flex"
              >
                <b-taglist
                  attached
                  v-if="shift.from === 'on' && shift.to === 'call'"
                >
                  <b-tag type="is-dark"
                    ><b-icon icon="phone-volume" size="is-small"></b-icon
                  ></b-tag>
                  <b-tag type="is-success" style="color: black">On Call</b-tag>
                </b-taglist>
                <b-taglist attached v-else>
                  <b-tag type="is-dark" size="is-medium"
                    ><b-icon icon="user-clock" size="is-small"></b-icon
                  ></b-tag>

                  <b-tag type="is-primary" size="is-medium"
                    >{{ shift.from }} - {{ shift.to }}</b-tag
                  >
                </b-taglist>
                <b-tooltip label="Add a shift" v-if="canShowEditorContent">
                  <b-button
                    type="is-success"
                    size="is-small"
                    @click="OpenAddAgentModal(shift.id, shift.group_id)"
                    icon-right="plus"
                    ><b-icon icon="user-clock" size="is-small">
                    </b-icon></b-button
                ></b-tooltip>
                <b-tooltip
                  label="Hide this shift from this week"
                  v-if="canShowEditorContent"
                >
                  <b-button
                    type="is-info"
                    size="is-small"
                    @click="hideShift(shift.id)"
                    icon-left="eye-slash"
                    >Hide</b-button
                  ></b-tooltip
                >
              </h1>
            </strong>
            <p></p>
            <div class="columns">
              <scheduleColumn
                :ref="day + '-' + shift.id"
                class="graphcolumn"
                v-for="(day, ind) in days"
                :key="day + '-' + shift.id"
                :ind="ind"
                :groupindex="index"
                :day="day"
                :shift="shift.id"
                :group="shiftsTimetable[0].group_id"
                :refdate="referenceDate"
              ></scheduleColumn>
            </div>
          </span>
        </div>
      </div>
    </div>

    <ul v-if="canShowEditorContent">
      <div v-for="(shift, index) in shiftsTimetable" :key="index + 'tt'">
        <span v-if="shift.hide == 1">
          <li
            style="
              display: block;
              width: 100%;
              font-weght: bold;
              font-size: 20px;
            "
          >
            <h1 style="margin-right: 10px; color: black; display: inline-flex">
              <b-taglist
                attached
                v-if="shift.from === 'on' && shift.to === 'call'"
              >
                <b-tag type="is-dark"
                  ><b-icon icon="phone-volume" size="is-small"></b-icon
                ></b-tag>
                <b-tag type="is-success" style="color: black">On Call</b-tag>
              </b-taglist>
              <b-taglist attached v-else>
                <b-tag type="is-dark" size="is-medium"
                  ><b-icon icon="user-clock" size="is-small"></b-icon
                ></b-tag>

                <b-tag type="is-primary" size="is-medium"
                  >{{ shift.from }} - {{ shift.to }}</b-tag
                >
              </b-taglist>
            </h1>

            <b-tooltip
              label="Show this shift off this week"
              v-if="canShowEditorContent"
            >
              <b-button
                type="is-primary is-light"
                size="is-small"
                @click="showShift(shift.id, shift.group_id)"
                icon-left="eye"
                >Show</b-button
              ></b-tooltip
            >
          </li>
        </span>
      </div>
    </ul>
    <div
      v-show="shiftsTimetable.length == 0 && this.loading == null"
      style="text-align: center"
    >
      <b-message title="Info" type="is-info" has-icon :closable="false">
        No shifts defined for this team yet.
        <router-link :to="`/admin`" v-if="canShowEditorContent"
          >Add one.</router-link
        >
      </b-message>
    </div>
    <b-skeleton
      style="float: left"
      animated
      v-if="this.loading != null"
    ></b-skeleton>
    <b-skeleton
      style="float: left"
      animated
      v-if="this.loading != null"
    ></b-skeleton>
    <b-skeleton
      style="float: left"
      animated
      v-if="this.loading != null"
    ></b-skeleton>
  </div>
</template>

<script>
import { mapState } from "vuex";
import scheduleColumn from "../components/schedulecolumn.vue";
import AddAgentToShiftForm from "../forms/AddAgentToShiftForm.vue";
import AgentsMixin from "../mixins/AgentsMixin";

export default {
  components: {
    scheduleColumn,
  },
  mixins: [AgentsMixin],
  name: "GroupSchedule",
  props: ["team_id"],

  mounted() {
    //  let that = this
    //   document.addEventListener("visibilitychange", function _listener(){
    //     that.visibilityEvent()
    //     document.removeEventListener('visibilitychange', _listener, true)
    //   }, true);
    // console.log(this.$route.params.date)
    var interval = setInterval(() => {
      if (this.$refs.calendar) {
        this.loadCalendar();
        clearInterval(interval);
      }
    }, 100);
    this.readAPI();
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
      return this.expandDaysWeekMixin(this.referenceDate);
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

      teams: [],
      schedule: [],
      loading: null,
      group_name: "",
      pendingchangeswidget: false,
      referenceDate: this.moment(this.$store.state.refDate),
      shiftsTimetable: [],
      group_id: 0,
    };
  },
  methods: {
    hideShift(shiftid) {
      this.$http
        .post(
          "./scheduleapi/shift/" +
            shiftid +
            "/hide?refdate=" +
            this.$store.state.refDate
        )
        .then((response) => {
          if (response.data.result == "success") {
            this.readAPI();
            this.$buefy.toast.open({
              message: "Done",
              type: "is-success",
            });
          } else {
            this.$buefy.toast.open({
              message: response.data.result,
              type: "is-danger",
            });
          }
        });
    },
    showShift(shiftid) {
      this.$http
        .post(
          "./scheduleapi/shift/" +
            shiftid +
            "/show?refdate=" +
            this.$store.state.refDate
        )
        .then((response) => {
          if (response.data.result == "success") {
            this.readAPI();
            this.$buefy.toast.open({
              message: "Done",
              type: "is-success",
            });
          } else {
            this.$buefy.toast.open({
              message: response.data.result,
              type: "is-danger",
            });
          }
        });
    },
    confirmSchedule() {
      this.$buefy.dialog.confirm({
        message: "Are you sure?",
        onConfirm: () => this.confirmConfirmSchedule(),
      });
    },
    confirmConfirmSchedule() {
      this.$http
        .post(
          "./scheduleapi/group/" +
            this.group_id +
            "/notifications/" +
            this.referenceDate.format("YYYY-MM-DD")
        )
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Done",
              type: "is-success",
            });
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
    visibilityEvent() {
      if (document.visibilityState === "visible" && this.$route.params?.team) {
        this.readAPI();
      }
    },
    loadAgentsTeams() {
      this.$store
        .dispatch("loadAgentsForGroup", {
          //  team: this.group,
          topteam: this.group_id,
        })
        .then(() => {});
    },
    OpenAddAgentModal(shift_id, group_id) {
      const modal = this.$buefy.modal.open({
        parent: this,
        component: AddAgentToShiftForm,
        hasModalCard: true,
        trapFocus: true,
        props: { shift_id: shift_id, group_id: group_id },
      });
      modal.$on("reloadapi", () => {
        this.readAPI();
      });
    },
    markShift() {
      if (this.$store.state.shiftToHighlight) {
        //console.log( this.$refs )
        // console.log(this.$store.getters.currentShifts)
        let shiftinfo = this.$store.state.shiftToHighlight.shift.split("-");
        let shiftid = this.$store.getters.currentShifts.filter(
          (x) => x.from == shiftinfo[0] && x.to == shiftinfo[1]
        );
        let dateSchedule = this.moment(
          this.$store.state.shiftToHighlight.date
        ).format("ddd DD.MM");
        let refname = dateSchedule + "-" + shiftid[0].id;
        // console.log(this.moment(this.$store.state.shiftToHighlight.date).format("ddd DD.MM"))
        //console.log( this.$refs )
        if (!this.$refs[refname]) {
          setTimeout(() => {
            this.$refs[refname][0].$el.classList.add("graphcolumnanimation");
            setTimeout(() => {
              this.$refs[refname][0].$el.classList.remove(
                "graphcolumnanimation"
              );
            }, 2000); // 2s
          }, 1000);
        }
        this.$store.dispatch("setItemKey", "");
      }
    },
    apply(notifications = false) {
      this.$http
        .post("./scheduleapi/shifts/commit", { notifications: notifications })
        .then((response) => {
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
    loadCalendar() {
      const c = this.$refs.calendar;
      //console.log(c)
      if (c) {
        c.move(this.referenceDate.format("YYYY-MM-DD"));
      }
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
        .then(
          () => {
            this.group_name = this.$store.state?.group?.group;
            this.loading.close();
            this.loading = null;
            this.$set(
              this,
              "shiftsTimetable",
              this.$store.state.shiftsTimetable
            );
            this.referenceDate = this.moment(this.$store.state.refDate);
            this.loadCalendar();
            this.markShift();
            this.group_id = this.shiftsTimetable[0].group_id;
            this.loadAgentsTeams();
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
    moveWeek(direction) {
      if (direction == -1) {
        this.referenceDate = this.moment(this.referenceDate).subtract(
          1,
          "week"
        );
      } else {
        this.referenceDate = this.moment(this.referenceDate).add(1, "week");
      }
      this.$store.commit('SetGroupShiftsDrop', []);
      this.loadCalendar();
      // let alreadyInStore = false;
      // if (
      //   this.$store.state.timetable[
      //     this.moment(this.referenceDate).format("YYYY-MM-DD")
      //   ]
      // ) {
      //   alreadyInStore = true;
      // }
      for (var i = 1; i < 8; i++) {
        if (
          this.$store.state.timetable[
            this.moment(this.referenceDate).add(1, "day").format("YYYY-MM-DD")
          ]
        ) {
          //alreadyInStore = true;
        }
      }

      this.$router.push({
        path: `/schedule/${this.$route.params.team}/${this.moment(
          this.referenceDate
        ).format("MMMDD")}-${this.moment(this.referenceDate)
          .add(6, "day")
          .format("MMMDD")}-${this.moment(this.referenceDate).format("YYYY")}`,
      });

      //if (!alreadyInStore) {
      this.readAPI();
      // } else {
      //  this.$store.commit("setRefdate", this.referenceDate);
      //}
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
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
  background: linear-gradient(90deg, rgb(58, 103, 201) 0%, #4062aa 100%);
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
.graphcolumnanimation {
  background-position: 600px 190px;
}
</style>
<style>
.modal-card-foot {
  margin: unset;
}
.active
  {
    background: grey;
  }
</style>