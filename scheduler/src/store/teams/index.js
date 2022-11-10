import axios from 'axios'

export default {
    namespaced: true,
    state: {
        teams: [],
    },
    mutations: {
        TEAMS(state, value) {
            state.teams = value
        }
    },
    actions: {
        getTeams(context) {
            axios.get('./scheduleapi/teams').then((response) => {
                if (response.data.response === 'success') {
                    context.commit('TEAMS', response.data.teams);
                }
            })
        },
    },
    getters: {
        //teams: state => state.teams,
        teams: (state) => {
            const teams = state.teams
            var teamsSort = []

            teams.forEach(i => {
                if (i.parent === 0) {
                    teamsSort.push(i)
                    let subteams = teams.filter(j => j.parent === i.group_id)
                
                    teamsSort = [...teamsSort, ...subteams]
                }
            })

            return teamsSort
        }
    }
}
