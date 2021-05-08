<template>
  <div id="app">
    
    <Header />

    <SidebarRight v-if="$route.matched.some(({ name }) => name.includes('Schedule'))"></SidebarRight >
    <div class="container is-fullhd notification">
      <router-view/>
    </div>
  </div>
</template>
<script>
import Header from './components/Header.vue'
import SidebarRight from './components/SidebarRight.vue'

export default {
  name: 'App',
  components: {
    Header, SidebarRight
   
  }
}
</script>
<style>

html
{
  background-color: rgb(181, 201, 255) !important;
}
</style>