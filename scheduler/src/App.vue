<template>
  <div id="app">
    <Header />

    <SidebarRight
      v-if="
        $route.matched.some(
          ({ name }) =>
            name.includes('Vacationing') || name.includes('Schedule')
        )
      "
    ></SidebarRight>
    <TemplatesSidebar
      v-if="
        canShowEditorContent &&
        $route.matched.some(({ name }) => name.includes('Schedule'))
      "
    ></TemplatesSidebar>
    <div class="container is-fullhd notification " id="mainwindow">
      <router-view class="p-5" />
    </div>
  </div>
</template>
<script>
import Header from "./components/Header.vue";
import SidebarRight from "./components/SidebarRight.vue";
import TemplatesSidebar from "./components/TemplatesSidebar.vue";
import AgentsMixin from "./mixins/AgentsMixin";

export default {
  name: "App",
  mixins: [AgentsMixin],
  components: {
    Header,
    SidebarRight,
    TemplatesSidebar,
  },
  computed: {
  },
  mounted() {
    this.getAgentHeaderById();
  },
};
</script>
<style>
.is-fullhd {
  max-width: 1752px !important;
}
html {
  background-color: rgb(181, 201, 255) !important;
}
.pagination-link.is-current {
  color: #fff !important;
}
.modal-card-foot {
  margin: unset;
}
</style>