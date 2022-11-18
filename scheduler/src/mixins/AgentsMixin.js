import { mapState } from "vuex";
export default {
    computed:
    {
        ...mapState({
            editorPermission: state => state.editorPermission,
            adminPermission: state => state.adminPermission,
            groupid: state => state.groupid, 
            editorPermissionsGroups: state => state.editorPermissionsGroups
        }),
        canShowEditorContent() {
            return this.adminPermission === 1 || (this.editorPermissionsGroups?.[1]?.includes(this.groupid) === true)
        },
        canShowStats() {
            return this.adminPermission === 1 || (this.editorPermissionsGroups?.[2]?.includes(this.groupid) === true)
        },

    },
    methods: {
        getAgentHeaderById() {
            this.$http
                .get("./scheduleapi/agents/myinfo", { withCredentials: true })
                .then((r) => {
                    if (r.data.response === "success") {
                        this.$store.commit('SetMyAdminId', r.data.admin_id)
                        this.$store.commit('SetMyAdminData', r.data)
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