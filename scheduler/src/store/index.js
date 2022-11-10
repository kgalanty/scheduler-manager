import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import moment from 'moment'
Vue.prototype.moment = moment
Vue.use(Vuex)

import DaysoffRequestsStore from './daysoff/index'
import TeamsStore from './teams/index'

export default new Vuex.Store({
  state: {

    schedule_teams: [],
    agents:
      [
        { 'id': 2, 'name': 'Stoyan' },
        { 'id': 3, 'name': 'Marino C.' },
        { 'id': 4, 'name': 'Ned' },
        { 'id': 2, 'name': 'Stoyan' },
        { 'id': 3, 'name': 'Marino C.' },
        { 'id': 4, 'name': 'Ned' },
        { 'id': 2, 'name': 'Stoyan' },
        { 'id': 3, 'name': 'Marino C.' },
        { 'id': 4, 'name': 'Ned' },
      ],
    admins: [],
    shifts: [],
    groupshifts: [],
    timetable: [],
    showDel: false,
    groupname: '',
    groupid: '',
    draftexists: false,
    draftentries: [],
    logs: [],
    //canassigneditors: '',
    refDate: '',
    templates: {},
    shiftsTimetable: [],
    shiftToHighlight: '',
    vacationings: [],
    editorPermission: 0,
    editorPermissionsGroups: {},
    adminPermission: 0,
    agentsGroups: [],
    ShowOnTopbarShift: null,

    myadminid: 0,
    myadmindata: [],

    groupShiftsDrop: [],

  },
  getters: {
    topteams: (state, getters) => (variant) =>
    {
      if(state.adminPermission === 1 && variant === 'Admin')
      {
        return getters['teams/teams'].filter(i=>i.parent==0).sort((a,b) => b.group_id - a.group_id)
      }
      else if(getters.canShowEditorCP && variant == 'Editor')
      {
        return getters['teams/teams'].filter(i=>i.parent==0)
          .filter(i=>state.editorPermissionsGroups && state.editorPermissionsGroups[3] && state.editorPermissionsGroups[3]
          .includes(i.group_id))

      }
    },
    currentShifts: state => {
      return state.shiftsTimetable
    },
    timetable: state => {
      //  return this.$store.state.timetable[
      //   this.moment(this.$store.state.refDate).format("YYYY-MM-DD")
      // ].t;
      // console.log(state.timetable[moment(state.refDate).format("YYYY-MM-DD")].t)\
      if (!state.refDate) return []
      let ar = [];
      // console.log(Object.values(state.timetable[moment(state.refDate).format("YYYY-MM-DD")].t))

      // console.log(x.t)

      Object.values(state.timetable).forEach(function (x) {
        //console.log(x.t)
        Object.values(x.t).forEach(function (element) {
          Object.values(element).forEach(function (el) {
            ar.push(...el)
          })
          // console.log(element)
        })
      })
      ar.forEach(function (ell) {
        ell.description = ell.agent + ' (' + ell.shift + ')'
      })
      ar.push({
        date: { start: new Date(state.refDate), end: new Date(moment(state.refDate).add(6, 'days').format('YYYY-MM-DD')) },
        highlight: {
          start: { fillMode: 'light' },
          base: { fillMode: 'light' },
          end: { fillMode: 'light' },
        },
      })
      // ar[0].dates = { start: new Date(2021, 5, 14), end: new Date(2021, 5, 18) }
      return ar;
    },
    canShowEditorCP(state)
    {
      return state.editorPermissionsGroups && (state.editorPermissionsGroups[3]?.length > 0 || state.editorPermissionsGroups[4]?.length > 0)
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
    setCurrentGroup(state, val) {
      state.groupname = val.group
      state.groupid = val.id
    },
    setRefdate(state, refDate) {
      state.refDate = refDate
    },
    setTimetable(state, timetable) {

      if (typeof state.timetable[timetable.date] == 'undefined') {
        // state.timetable[timetable.date] = [];
      }
      // Object.defineProperty( state.timetable, timetable.date, timetable.data);
      //state.timetable[timetable.date] = JSON.stringify(timetable.data)
      state.refDate = timetable.date
      state.timetable = { ...state.timetable, [timetable.date]: timetable.data }
      //Vue.set(state.timetable, timetable.date, timetable.data);
    },
    setDrafts(state, draft) {
      state.draftexists = draft.draftexists
      state.draftentries = draft.draftentries
    },
    setLogs(state, val) {
      state.logs = val
    },
    setTemplates(state, val) {
      //  var ar = new Array([state.groupid]: val)
      state.templates = val
    }
    , setShiftsTimetable(state, val) {
      state.shiftsTimetable = val
    },
    setItemKey(state, val) {
      //console.log(val)
      state.shiftToHighlight = val
    },
    setVacationings(state, val) {
      state.vacationings = val.vacationing
    },
    editorPermissions(state, val) {
      state.editorPermission = parseInt(val)
    },
    editorPermissionsGroups(state, val) {
      state.editorPermissionsGroups = val
    },
    adminPermissions(state, val) {
      state.adminPermission = val
    },
    SetAgentsGroups(state, val) {
      state.agentsGroups = val
    },
    SetShowOnTopbarShift(state, val) {
      state.ShowOnTopbarShift = val
    },
    SetMyAdminId(state, val) {
      state.myadminid = val
    },
    SetMyAdminData(state, val) {
      state.myadmindata = val
    },
    SetGroupShiftsDrop(state, val)
    {
      state.groupShiftsDrop = val
    },

  },
  actions: {
    loadEditorPermissions(context) {
      axios.get("./scheduleapi/verify", { withCredentials: true })
        .then((r) => {
          if (r.data.response === "success") {
            context.commit('editorPermissions', 1)
            context.commit('editorPermissionsGroups', r.data.gr)
            if (r.data.admin === 1) {
              context.commit('adminPermissions', 1)
            }
          }
          else {
            context.commit('editorPermissions', 0)
            // 0
          }
          // try
          // {
          //   var json = JSON.parse(r.data);
        
          // }
          // catch(error)
          // {
          //   var iframe = document.createElement('div');
          //     iframe.innerHTML = encodeURI(json);
          //     document.body.appendChild(iframe);
              
          //    // setTimeout(() => document.location.reload(), 1000)
          // }
        })
    },
    setItemKey(context, payload) {
      context.commit('setItemKey', payload)
    },
    getAdmins(context) {
      axios.get('./scheduleapi/agents').then((response) => {
        context.commit('setAdmins', response.data);
      })
    },
    getShiftsList(context) {
      axios.get('./scheduleapi/shifts').then((response) => {
        context.commit('setShifts', response.data);
      })
    },
    getTeams(context) {
      axios.get('./scheduleapi/shifts/teams').then((response) => {

        context.commit('setTeams', response.data);
      })
    },

    getShiftsForGroup(context) {
      axios.get('./scheduleapi/shifts/shiftsgroups').then((response) => {
        context.commit('setShiftsForGroup', response.data);
      })
    },
    getTemplates(context, payload) {
      axios
        .get(
          "./scheduleapi/templates/" + payload.val
        )
        .then((response) => {
          context.commit('setTemplates', response.data.list)
        })
    },
    getDrafts(context, payload) {
      // console.log(payload)
      axios
        .get(
          "./scheduleapi/group/" + payload + "/drafts"
        )
        .then((response) => {
          context.commit('setDrafts', { draftexists: response.data.drafts.length > 0 ? true : false, draftentries: response.data.drafts })
        })
    },
    loadVacationings(context, payload) {
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
            if (response.data.response) {
              reject(response.data.response)
              return;
            }
            context.commit('setVacationings', response.data)
            resolve()
          });
      })
    },
    loadAgentsForGroup(context, payload) {
      return new Promise((resolve, reject) => {
        axios
          .get(
            "./scheduleapi/group/" + payload.topteam + "/agents?refdate=" + context.state.refDate
          )
          .then((response) => {
            context.commit('SetAgentsGroups', response.data)
            resolve()
          }).
          catch((error) => {
            reject(error)
          })
      })
    },
    loadAgentsForGroupAll(context) {
      return new Promise((resolve, reject) => {
        axios
          .get(
            "./scheduleapi/groups/agents?refdate=" + context.state.refDate.format('YYYY-MM-DD')
          )
          .then((response) => {
            context.commit('SetAgentsGroups', response.data)
            resolve()
          }).
          catch((error) => {
            reject(error)
          })
      })
    },
    loadFromAPI(context, payload) {
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
            if (response.data.response) {
              reject(response.data.response)
              return;
            }
            context.commit('setCurrentGroup', response.data.group)
            context.dispatch('getDrafts', response.data.group.id)
            context.commit('setTimetable', { data: response.data, date: response.data.refdate })
            context.commit('setShiftsTimetable', response.data.shifts)
            resolve()
          });
      })
    },
    switchShowDelete(context, payload) {
      context.commit('setShowDel', payload.val)
    },
    getLogs(context, payload) {
      axios
        .get(
          "./scheduleapi/logs?datefrom=" + payload.datefrom + "&dateto=" + payload.dateto
        )
        .then((response) => {
          context.commit('setLogs', response.data)
        })
    },
    getShowOnTopbarShift(context) {
      return new Promise((resolve) => {
        axios
          .get("./scheduleapi/shift/showontopbar")
          .then((response) => {
            if (response.data.response == "success") {
              context.commit('SetShowOnTopbarShift', response.data.shiftid)

            }
            resolve()
          });
      })
    },

  },
  modules: {
    daysoff: DaysoffRequestsStore,
    teams: TeamsStore,
  }
})
