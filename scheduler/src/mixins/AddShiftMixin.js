
export default {
    methods: {
        PostShift(agent_id, force)
        {
          return     this.$http
            .post("./scheduleapi/shifts/timetable", {
              date: this.date,
              agent_id: agent_id,
              shift_id: this.shift_id,
              group_id: this.group_id,
              force: force
            })
        },
        ForceAddDutyConfirm(r, agent_id, refdate) {
            this.$buefy.snackbar.open({
                duration: 10000,
                message: r.data.response,
                type: 'is-danger',
                position: 'is-top',
                queue: false,
                actionText: r.data.action ?? null,
                onAction: () => {
                    const loadingComponent = this.$buefy.loading.open({
                        container: null,
                    });

                    this.PostShift(agent_id, true)
                        .then((r) => {
                            if (r.data.response === "success") {
                                
                                //this.today[this.date].push({'agent':AgentItem.name, 'bg':AgentItem.bg, 'color':AgentItem.color})
                                this.$store.dispatch("loadFromAPI", {
                                    teamroute: this.$route.params.team,
                                    refdate: refdate,
                                    refdateroute: this.$route.params.date,
                                });
                            }
                            else
                            {
                                this.$buefy.toast.open({
                                    duration: 5000,
                                    message: r.data.response,
                                    type: 'is-danger'
                                })
                            }
                            loadingComponent.close();
                        })
                }
            })
        }
    }
}