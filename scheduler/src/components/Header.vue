<template>
  <nav class="navbar is-info" role="navigation" aria-label="main navigation">
    <div id="navbarBasicExample" class="navbar-menu">
      <div class="navbar-start">
        
          <router-link class="navbar-item" :to="`/`">
           <b-tooltip label="Home" position="is-bottom"> <b-icon icon="home" size="is-medium"> </b-icon> </b-tooltip>
          </router-link>
       
        <!-- <router-link class="navbar-item" :to="`/assigneditors`" v-if="canAssignEditors==1">
       <b-icon
                icon="clipboard-list"
                size="is-medium">
            </b-icon>
      </router-link> -->
        <router-link
          class="navbar-item"
          :to="`/admin`"
          v-if="editorPermission === 1"
        >
          <b-tooltip label="Configuration" position="is-bottom"> <b-icon icon="cogs" size="is-medium"> </b-icon></b-tooltip>
        </router-link>
        <router-link class="navbar-item" :to="`/stats`">
          <b-tooltip label="Stats" position="is-bottom"> <b-icon icon="chart-bar" size="is-medium"> </b-icon></b-tooltip>
        </router-link>
        <router-link class="navbar-item" :to="`/logs`">
           <b-tooltip label="Logs" position="is-bottom"> <b-icon icon="file-alt" size="is-medium"> </b-icon></b-tooltip>
        </router-link>
        <router-link class="navbar-item" :to="`/calendar`">
           <b-tooltip label="Sync with your calendar" position="is-bottom"><b-icon icon="calendar-week" size="is-medium"> </b-icon></b-tooltip>
        </router-link>
      </div>
      <div class="navbar-end">
        <a class="navbar-item" href="https://my.tmdhosting.com/admin/">
          <b-icon pack="fa" icon="chevron-left" size="is-medium"> </b-icon>
        </a>
      </div>
    </div>
    <div class="navbar-brand">
      <a
        role="button"
        class="navbar-burger"
        aria-label="menu"
        aria-expanded="false"
        data-target="navbarBasicExample"
      >
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
        <span aria-hidden="true"></span>
      </a>
    </div>
  </nav>
</template>

<script>
import { mapState } from "vuex";
export default {
  name: "Header",
  computed: {
    ...mapState(["editorPermission"]),
  },
  mounted() {
    this.$store.dispatch("loadEditorPermissions");
  },
};
document.addEventListener("DOMContentLoaded", () => {
  // Get all "navbar-burger" elements
  const $navbarBurgers = Array.prototype.slice.call(
    document.querySelectorAll(".navbar-burger"),
    0
  );

  // Check if there are any navbar burgers
  if ($navbarBurgers.length > 0) {
    // Add a click event on each of them
    $navbarBurgers.forEach((el) => {
      el.addEventListener("click", () => {
        // Get the target from the "data-target" attribute
        const target = el.dataset.target;
        const $target = document.getElementById(target);

        // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
        el.classList.toggle("is-active");
        $target.classList.toggle("is-active");
      });
    });
  }
});
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
h3 {
  margin: 40px 0 0;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  display: inline-block;
  margin: 0 10px;
}
a {
  color: #42b983;
}
</style>
