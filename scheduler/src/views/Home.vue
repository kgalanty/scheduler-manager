<template>
  <div id="app">
    <UserHeader />
    <SearchEngine />
    <RecentEvents />
    <Browser />
  </div>
</template>

<script>
import UserHeader from "../components/UserHeader.vue";
import SearchEngine from "../components/SearchEngine.vue";
import RecentEvents from "../components/RecentEvents.vue";
import Browser from "../components/Browser.vue";

export default {
  name: "App",
  components: {
    UserHeader,
    SearchEngine,
    RecentEvents,
    Browser,
  },
  mounted() {
    if (this.$store.state.canassigneditors == "") {
      this.$http
        .get("./scheduleapi/verify", { withCredentials: true })
        .then((r) => {
          if (r.data.response === "success") {
            this.$store.commit("setCanAssignEditors", 1);
          } else {
            this.$store.commit("setCanAssignEditors", 0);
          }
        });
    }
  },
};
</script>

<style>
html {
  background-color: rgb(181, 201, 255) !important;
}
</style>