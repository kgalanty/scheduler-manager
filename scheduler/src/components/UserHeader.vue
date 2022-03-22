<template>
  <div>
    <div class="content">
      <h1 v-if="info">Hello {{ info }}</h1>
    </div>
  </div>
</template>

<script>
export default {
  mixins: [],
  name: "UserHeader",
  data() {
    return {
      info: "",
    };
  },
  mounted() {
    this.$http
      .get("./scheduleapi/agents/myinfo", { withCredentials: true })
      .then((r) => {
        if (r.data.response === "success") {
          this.info = r.data.info;
        } else {
          this.$buefy.snackbar.open({
            duration: 5000,
            message: r.data.msg,
            type: "is-danger",
            position: "is-bottom-left",
            queue: false,
          });
        }
      });
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
