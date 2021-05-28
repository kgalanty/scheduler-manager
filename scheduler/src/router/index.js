import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'

Vue.use(VueRouter)

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
