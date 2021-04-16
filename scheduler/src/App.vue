<template>
  <div id="app">
    <Header />
    <div class="container is-fullhd notification">
      <UserHeader />
      <SearchEngine />
      <RecentEvents />
      <Browser />
    </div>
  </div>
</template>

<script>
import Header from './components/Header.vue'
import UserHeader from './components/UserHeader.vue'
import SearchEngine from './components/SearchEngine.vue'
import RecentEvents from './components/RecentEvents.vue'
import Browser from './components/Browser.vue'

export default {
  name: 'App',
  components: {
    Header,
    UserHeader,
    SearchEngine,
    RecentEvents,
    Browser
  }
}
</script>

<style>
html
{
  background-color: rgb(181, 201, 255) !important;
}
</style>
