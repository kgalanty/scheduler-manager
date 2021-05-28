import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import moment from 'moment'
Vue.prototype.moment = moment
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
    showDel:false,
    groupname:'',
    groupid: '',
    draftexists: false,
    draftentries: [],
    logs: [],
    canassigneditors: '',
    refDate: '',
    templates: {},
    shiftsTimetable: []
  },
  getters: {
    currentShifts: state => {
      // if (
      //  state.timetable[
      //     moment(moment().day(1)).format("YYYY-MM-DD")
      //   ] &&
      // state.timetable[
      //     moment(moment().day(1)).format("YYYY-MM-DD")
      //   ].shifts
      // )
      //   return state.timetable[
      //     moment(moment().day(1)).format("YYYY-MM-DD")
      //   ].shifts;
      // return [];
      return state.shiftsTimetable
    }
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
    setCurrentGroup(state, val)
    {
      state.groupname = val.group
      state.groupid = val.id
    },
    setTimetable(state, timetable) {
      
      if(typeof state.timetable[timetable.date] == 'undefined')
      {
       // state.timetable[timetable.date] = [];
      }
     // Object.defineProperty( state.timetable, timetable.date, timetable.data);
      //state.timetable[timetable.date] = JSON.stringify(timetable.data)
      state.refDate = timetable.date
      state.timetable = {...state.timetable, [timetable.date]: timetable.data}
      //Vue.set(state.timetable, timetable.date, timetable.data);
    },
    setDrafts(state, draft)
    {
      state.draftexists = draft.draftexists
      state.draftentries = draft.draftentries
    },
    setLogs(state, val) {
      state.logs = val
    },
    setCanAssignEditors(state, val)
    {
      state.canassigneditors = val
    },
    setTemplates(state, val)
    {
    //  var ar = new Array([state.groupid]: val)
      state.templates = val
    }
    ,setShiftsTimetable(state,val)
    {
      state.shiftsTimetable = val
    }

  },
  actions: {
    getAdmins(context) {
      axios.get('./scheduleapi/agents').then((response) => {
        context.commit('setAdmins', response.data);
      })},
      getShiftsList(context) {
        axios.get('./scheduleapi/shifts').then((response) => {
          context.commit('setShifts', response.data);
      })},
      getTeams(context) {
        axios.get('./scheduleapi/shifts/teams').then((response) => {
          context.commit('setTeams', response.data);
      })},
      
      getShiftsForGroup(context) {
        axios.get('./scheduleapi/shifts/shiftsgroups').then((response) => {
          context.commit('setShiftsForGroup', response.data);
      })},
      getTemplates(context, payload)
      {
        axios
        .get(
          "./scheduleapi/templates/"+payload.val
        )
        .then((response) => {
          context.commit('setTemplates', response.data.list)
        })
      },
      loadFromAPI(context, payload)
      {
        return new Promise((resolve) => {
          const refdate = payload.refdateroute != null ? payload.refdateroute : payload.refdate
          const team = payload.teamroute != null ? payload.teamroute : parseInt(payload.team)
        axios
          .get(
            "./scheduleapi/shifts/shiftsgroups/" +
            team +
              "?startdate=" +
              refdate
          )
          .then((response) => {
            context.commit('setCurrentGroup', response.data.group)
            let isdraft = false
            let drafts = []
            for(var i in response.data.t)
            {
              for(var day in response.data.t[i])
              {
                for(var item in response.data.t[i][day])
                {
                  if(response.data.t[i][day][item].draft == 1 || response.data.t[i][day][item].deldraftauthor)
                  {
                    isdraft = true
                    drafts.push({item:response.data.t[i][day][item], day: day})
                  }
                  
                }
              }            
            }
            drafts.sort((a,b) => (a.day > b.day) ? 1 : ((b.day > a.day) ? -1 : 0))
            context.commit('setDrafts', {draftexists: isdraft, draftentries:drafts})
            context.commit('setTimetable', {data: response.data, date: response.data.refdate})
            context.commit('setShiftsTimetable', response.data.shifts)
            resolve()
          });
        })
      },
      switchShowDelete(context, payload)
      {
        context.commit('setShowDel', payload.val)
      },
      getLogs(context)
      {
        axios
        .get(
          "./scheduleapi/logs"
        )
        .then((response) => {
          context.commit('setLogs', response.data)
        })
      }

  },
  modules: {
  }
})
