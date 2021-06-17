<template>
  <article class="tile is-parent" @click="openTeam">
    <div class="tile is-child box is-group">
      <p class="title">
            <b-icon
                pack="fa"
                :icon="icon"
                size="is-medium">
            </b-icon>
            </p>
      <p class="subtitle">{{label}}</p>
    </div>
    
  </article >
</template>

<script>
export default {
  name: 'GroupTile',
  props:['label', 'icon', 'path'],
    methods: {
    openTeam() {
      this.$router.push({
        path: this.path,
      });
    },
  },
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.title
{
  margin-top:20px;
  margin-bottom: 20px !important;
}
.title, .subtitle
{
  text-align:center;
}
.is-group
{
  background-color: #7957d5;
  color: #fff;
  -webkit-transition: opacity 1s; /* For Safari 3.0 to 6.0 */
  transition: opacity 1s; /* For modern browsers */
  box-shadow:5px 5px 5px rgb(63, 63, 63);
}
.is-group:hover
{
  opacity: .5;
}
</style>
