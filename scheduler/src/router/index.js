import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'

Vue.use(VueRouter)

Vue.mixin({
  methods: {
    checkpointDate() {
      let dateparams = this.$route.params.date.split('-')
      // console.log(this.moment(dateparams[0]+'-'+dateparams[2], 'MMMDD-YYYY'))
       if(dateparams.length != 3 || !this.moment(dateparams[0]+'-'+dateparams[2], 'MMMDD-YYYY', true).isValid() || dateparams[2].length != 4)
       {
                   this.$router.push({path: `/schedule`})
            
               this.$buefy.snackbar.open({
                         duration: 5000,
                         message: 'Wrong date',
                         type: 'is-danger',
                         position: 'is-bottom-left',
                         queue: false,
                     })
                     return
       }
       if(!this.moment(dateparams[1]+'-'+dateparams[2], 'MMMDD-YYYY', true).isValid())
       {
         let newenddate = this.moment(dateparams[0]+'-'+dateparams[2], 'MMMDD-YYYY').add(6, 'day');
         this.$router.push({path: `/schedule/${this.$route.params.team}/${dateparams[0]}-${newenddate.format('MMMDD')}-${dateparams[2]}`})
       }
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
    component: () => import(/* webpackChunkName: "about" */ '../views/scheduleOverview.vue')
  },
  {
    path: '/admin',
    name: 'Admin',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/admin.vue')
  },
   {
     path: '/schedule/:team/:date',
     name: 'ScheduleTeam',
     component: () => import( '../views/schedule.vue')
   },
   {
     path: '/assigneditors',
     name: 'AssignEditors',
     component: () => import( '../views/assigneditors.vue')
   }
]

const router = new VueRouter({
  routes
})

export default router
