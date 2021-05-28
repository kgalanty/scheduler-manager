<template>
  <div>
     <span v-if="day == 1">Monday</span>
                  <span v-else-if="day == 2">Tuesday</span>
                  <span v-else-if="day == 3">Wednesday</span>
                  <span v-else-if="day == 4">Thursday</span>
                  <span v-else-if="day == 5">Friday</span>
                  <span v-else-if="day == 6">Saturday</span>
                  <span v-else-if="day == 7">Sunday</span>
  </div>
</template>

<script>
export default {
  name: "DayOfWeek",
  props: ['day'],
  data() {
    return {
    };
  },

};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
