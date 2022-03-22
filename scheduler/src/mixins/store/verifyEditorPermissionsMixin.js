

export default {
    methods: {
        verifyPermissions() {
            return this.$http
                .get("./scheduleapi/verify", { withCredentials: true })
        }
    }
}