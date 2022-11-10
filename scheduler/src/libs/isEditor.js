export function isEditor() {
    return store.state.editorPermissionsGroup && store.state.editorPermissionsGroup[3]
}