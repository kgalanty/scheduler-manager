import { mapState } from "vuex";

export default {
    methods: {
        verifyPermissions() {
            return this.$http
                .get("./scheduleapi/verify", { withCredentials: true })
        },

    },
    computed:
    {
        hasAccessToEditor()
        {
          return this.$store.getters.canShowEditorCP
        },
        ...mapState(["editorPermission", "adminPermission", 'editorPermissionsGroups']),
    }
}