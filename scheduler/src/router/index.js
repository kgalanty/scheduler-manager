import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import NotFound from '../views/NotFound.vue'

Vue.use(VueRouter)

Vue.mixin({
  methods: {
    expandDaysWeekMixin(referenceDate, format = 'ddd DD.MM', giveobject = false) {
      let days = [];
      var reference = this.moment(referenceDate);
      if (giveobject === true) {
        days.push({ 'show': reference.format(format), 'date': reference.format('YYYY-MM-DD') })
      }
      else {
        days.push(reference.format(format));
      }
      for (var i = 1; i < 7; i++) {
        reference = reference.add(1, "day");
        if (giveobject === true) {
          days.push({ 'show': reference.format(format), 'date': reference.format('YYYY-MM-DD') })
        }
        else {
          days.push(reference.format(format));
        }
      }
      return days;
    },
    checkpointDate(defpath = 'schedule') {
      let dateparams = this.$route.params.date.split('-')
      // console.log(this.moment(dateparams[0]+'-'+dateparams[2], 'MMMDD-YYYY'))
      if (dateparams.length != 3 || !this.moment(dateparams[0] + '-' + dateparams[2], 'MMMDD-YYYY', true).isValid() || dateparams[2].length != 4) {
        this.$router.push({ path: `/schedule` })

        this.$buefy.snackbar.open({
          duration: 5000,
          message: 'Wrong date',
          type: 'is-danger',
          position: 'is-bottom-left',
          queue: false,
        })
        return
      }
      if (!this.moment(dateparams[1] + '-' + dateparams[2], 'MMMDD-YYYY', true).isValid()) {
        let newenddate = this.moment(dateparams[0] + '-' + dateparams[2], 'MMMDD-YYYY').add(6, 'day');
        this.$router.push({ path: `/${defpath}}/${this.$route.params.team}/${dateparams[0]}-${newenddate.format('MMMDD')}-${dateparams[2]}` })
      }
      this.$store.commit("setRefdate", this.moment(dateparams[0] + '-' + dateparams[2], 'MMMDD-YYYY'));
    }
  }
})
const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/schedule',
    name: 'Schedule',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "scheduleOverview" */ '../views/scheduleOverview.vue')
  },
  {
    path: '/admin',
    name: 'Admin',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "admin" */ '../views/admin.vue')
  },
  {
    path: '/stats',
    name: 'Stats',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "stats" */ '../views/stats.vue')
  },
  {
    path: '/stats/tickets',
    name: 'StatsTickets',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "stats" */ '../views/statstickets.vue')
  },
  {
    path: '/stats/ticketspersonal',
    name: 'StatsTicketsPersonal',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "stats" */ '../views/statsticketspersonal.vue')
  },
  {
    path: '/schedule/:team/:date',
    name: 'ScheduleTeam',
    component: () => import(/* webpackChunkName: "schedule" */ '../views/schedule.vue')
  },
  {
    path: '/vacationing/:date',
    name: 'Vacationing',
    component: () => import(/* webpackChunkName: "vacationing" */ '../views/vacationing.vue')
  },
  {
    path: '/daysoff/:agentid',
    name: 'DaysOff',
    component: () => import(/* webpackChunkName: "vacationing" */ '../views/daysoff.vue')
  },
  {
    path: '/calendar',
    name: 'Calendar',
    component: () => import(/* webpackChunkName: "calendar" */ '../views/calendar.vue')
  },
  {
    path: '/leave',
    name: 'Leave',
    component: () => import(/* webpackChunkName: "calendar" */ '../views/leave.vue')
  },
  {
    path: '/logs',
    name: 'Logs',
    component: () => import(/* webpackChunkName: "logs" */ '../views/logs.vue')
  },
  {
    path: '*',
    name: 'NotFound',
    component: NotFound
  }
]

const router = new VueRouter({
  routes
})
router.beforeEach((to, from, next) => {
  if(document && document.getElementById("mainwindow"))
  {
    document.getElementById("mainwindow").style.marginLeft = ""
    document.getElementById("mainwindow").style.width = ""
  }
  next()
})
export default router
