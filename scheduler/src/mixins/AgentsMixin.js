
export default {
    methods: {
        getAgentHeaderById(agentid) {
            this.$http
                .get("./scheduleapi/agents/myinfo", { withCredentials: true })
                .then((r) => {
                    if (r.data.response === "success") {
                        return r.data.info;
                    } else {
                        this.$buefy.snackbar.open({
                            duration: 5000,
                            message: r.data.msg,
                            type: "is-danger",
                            position: "is-bottom-left",
                            queue: false,
                        });
                    }
                });
        }
    }
}