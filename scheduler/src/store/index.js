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
    //canassigneditors: '',
    refDate: '',
    templates: {},
    shiftsTimetable: [],
    shiftToHighlight: '',
    vacationings:[],
    editorPermission:0
  },
  getters: {
    currentShifts: state => {
      return state.shiftsTimetable
    },
    timetable: state =>
    { 
      //  return this.$store.state.timetable[
      //   this.moment(this.$store.state.refDate).format("YYYY-MM-DD")
      // ].t;
     // console.log(state.timetable[moment(state.refDate).format("YYYY-MM-DD")].t)\
     if(!state.refDate) return []
     let ar = [];
   // console.log(Object.values(state.timetable[moment(state.refDate).format("YYYY-MM-DD")].t))
   
   // console.log(x.t)

  Object.values(state.timetable).forEach(function(x) {
    //console.log(x.t)
     Object.values(x.t).forEach(function(element) 
      {
        Object.values(element).forEach(function(el)
        {
          ar.push(...el)
        })
       // console.log(element)
      })
    })
      ar.forEach(function(ell)
      {
          ell.description = ell.agent+' ('+ell.shift+')'
      })
      ar.push({
        date:{ start: new Date(state.refDate), end: new Date(moment(state.refDate).add(6, 'days').format('YYYY-MM-DD')) },
        highlight: {
          start: { fillMode: 'light' },
          base: { fillMode: 'light' },
          end: { fillMode: 'light' },
        },
      })
     // ar[0].dates = { start: new Date(2021, 5, 14), end: new Date(2021, 5, 18) }
      return ar;
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
    setRefdate(state, refDate)
    {
        state.refDate = refDate
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
    setTemplates(state, val)
    {
    //  var ar = new Array([state.groupid]: val)
      state.templates = val
    }
    ,setShiftsTimetable(state,val)
    {
      state.shiftsTimetable = val
    },
    setItemKey(state, val)
    {
      //console.log(val)
      state.shiftToHighlight = val
    },
    setVacationings(state, val)
    {
      state.vacationings = {...state.vacationings, ...val.vacationing}
    },
    editorPermissions(state, val)
    {
      state.editorPermission = parseInt(val)
    }

  },
  actions: {
    loadEditorPermissions(context)
    {
       axios.get("./scheduleapi/verify", { withCredentials: true })
       .then((r) => 
       {
        if (r.data.response === "success") {
          context.commit('editorPermissions', 1)
        }
        else
        {
          context.commit('editorPermissions', 0)
        }
       })
      
    },
    setItemKey(context,payload)
    {
      context.commit('setItemKey', payload)
    },
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
      getDrafts(context, payload)
      {
       // console.log(payload)
        axios
          .get(
            "./scheduleapi/group/"+payload+"/drafts"
          )
          .then((response) => {
            context.commit('setDrafts', {draftexists: response.data.drafts.length > 0 ? true : false, draftentries:response.data.drafts})
          })
      },
      loadVacationings(context, payload)
      {
        return new Promise((resolve, reject) => {
          const refdate = payload.startdate
        axios
          .get(
            "./scheduleapi/vacationing" +
              "?startdate=" +
              refdate
          )
          .then((response) => {
            //console.log(response.data)
            if(response.data.response)
            {
              reject(response.data.response)
              return;
            }
            context.commit('setVacationings', response.data)
            resolve()
          });
        })
      },
      loadFromAPI(context, payload)
      {
        return new Promise((resolve, reject) => {
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
            //console.log(response.data)
            if(response.data.response)
            {
              reject(response.data.response)
              return;
            }
            context.commit('setCurrentGroup', response.data.group)
            context.dispatch('getDrafts', response.data.group.id)
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
      getLogs(context, payload)
      {
        axios
        .get(
          "./scheduleapi/logs?datefrom=" + payload.datefrom + "&dateto="+ payload.dateto
        )
        .then((response) => {
          context.commit('setLogs', response.data)
        })
      }

  },
  modules: {
  }
})
