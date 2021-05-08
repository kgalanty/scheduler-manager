import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    // schedule_teams: [
    //   {'name': 'Elders', 'id':2, 'members':[
    //     {'agent_id': 3, 'name': 'Stoyan', 'bg': 'purple', 'color':'white'},{'agent_id': 4, 'name': 'Velin'}
    //   ]},
    //   {'name': 'Seniors', 'id':3, 'members':[
    //     {'agent_id': 5, 'name': 'Marino C.'},{'agent_id': 6, 'name': 'Ned'},{'agent_id': 7, 'name': 'Nikola'}
    //   ]},
    // ],
    schedule_teams: [],
    agents:
    [
      {'id' : 2, 'name' : 'Stoyan'},
      {'id' : 3, 'name' : 'Marino C.'},
      {'id' : 4, 'name' : 'Ned'},
      {'id' : 2, 'name' : 'Stoyan'},
      {'id' : 3, 'name' : 'Marino C.'},
      {'id' : 4, 'name' : 'Ned'},
      {'id' : 2, 'name' : 'Stoyan'},
      {'id' : 3, 'name' : 'Marino C.'},
      {'id' : 4, 'name' : 'Ned'},
    ],
    admins: [],
    shifts:[],
    groupshifts: [],
    timetable: [],
    showDel:false
  },
  mutations: {
    setAdmins(state, admins) {
      state.admins = admins
    },
    setShifts(state, shifts) {
      state.shifts = shifts
    },
    setTeams(state, schedule_teams) {
      state.schedule_teams = schedule_teams
    },
    setShiftsForGroup(state, groupshifts) {
      state.groupshifts = groupshifts
    },
    setShowDel(state, val) {
      state.showDel = val
    },
    setTimetable(state, timetable) {
      
      if(typeof state.timetable[timetable.date] == 'undefined')
      {
       // state.timetable[timetable.date] = [];
      }
     // Object.defineProperty( state.timetable, timetable.date, timetable.data);
      //state.timetable[timetable.date] = JSON.stringify(timetable.data)
      state.timetable = {...state.timetable, [timetable.date]: timetable.data}
    },


  },
  actions: {
    getAdmins(context) {
      axios.get('/scheduleapi/agents').then((response) => {
        context.commit('setAdmins', response.data);
      })},
      getShiftsList(context) {
        axios.get('/scheduleapi/shifts').then((response) => {
          context.commit('setShifts', response.data);
      })},
      getTeams(context) {
        axios.get('/scheduleapi/shifts/teams').then((response) => {
          context.commit('setTeams', response.data);
      })},
      
      getShiftsForGroup(context) {
        axios.get('/scheduleapi/shifts/shiftsgroups').then((response) => {
          context.commit('setShiftsForGroup', response.data);
      })},
      loadFromAPI(context, payload)
      {
        return new Promise((resolve) => {
          const refdate = payload.refdateroute != null ? payload.refdateroute : payload.refdate
          const team = payload.teamroute != null ? payload.teamroute : parseInt(payload.team)
        axios
          .get(
            "/scheduleapi/shifts/shiftsgroups/" +
            team +
              "?startdate=" +
              refdate
          )
          .then((response) => {
            context.commit('setTimetable', {data: response.data, date: payload.refdate})
            resolve()
          });
        })
      },
      switchShowDelete(context, payload)
      {
        context.commit('setShowDel', payload.val)
      }

  },
  modules: {
  }
})
