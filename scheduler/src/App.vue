<template>
  <div id="app">
    
    <Header />

    <SidebarRight v-if="$route.matched.some(({ name }) => name.includes('Schedule'))"></SidebarRight >
    <TemplatesSidebar v-if="$route.matched.some(({ name }) => name.includes('Schedule'))"></TemplatesSidebar >
    <div class="container is-fullhd notification">
      <router-view/>
    </div>
  </div>
</template>
<script>
import Header from './components/Header.vue'
import SidebarRight from './components/SidebarRight.vue'
import TemplatesSidebar from './components/TemplatesSidebar.vue'
export default {
  name: 'App',
  components: {
    Header, SidebarRight,TemplatesSidebar
   
  }
}
</script>
<style>
.is-fullhd
{
  max-width: 1752px !important;
}
html
{
  background-color: rgb(181, 201, 255) !important;
}
</style>