<template>
  <div id="app">
    <UserHeader />
    <!-- <SearchEngine /> -->
    <!-- <RecentEvents /> -->
    <Browser />
  </div>
</template>

<script>
import UserHeader from "../components/UserHeader.vue";
// import SearchEngine from "../components/SearchEngine.vue";
//import RecentEvents from "../components/RecentEvents.vue";
import Browser from "../components/Browser.vue";

export default {
  name: "App",
  components: {
    UserHeader,
  //  SearchEngine,
   // RecentEvents,
    Browser,
  },
  mounted() {
  },
};
</script>

<style>
html {
  background-color: rgb(181, 201, 255) !important;
}
</style>