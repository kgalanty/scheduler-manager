<template>
  <span>
    <b-tag icon="umbrella-beach" v-if="request.request_type===1">Vacation</b-tag>
        <b-tag icon="people-arrows" v-if="request.request_type===2">Shift Change</b-tag>
        <b-tag icon="notes-medical" v-if="request.request_type===3">Sick Leave</b-tag>
  </span>
</template>
<script>
export default {
  name: "TypeColumn",
  components: {},
  props: {
    request: Object
  }
};
</script>
