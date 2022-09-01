
export default {
    methods: {
        getAgentHeaderById() {
            this.$http
                .get("./scheduleapi/agents/myinfo", { withCredentials: true })
                .then((r) => {
                    if (r.data.response === "success") {
                        this.$store.commit('SetMyAdminId', r.data.admin_id)
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