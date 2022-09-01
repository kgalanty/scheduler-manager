<template>
  <span v-if="request.approve_status == 0">
    <b-button
      type="is-primary"
      v-if="submitFunction"
      @click="submitFunction(request)"
      >Submit a review</b-button
    >
    <span v-else>
      <b-tooltip label="Awaiting verification"
        ><b-icon icon="spinner" size="is-small"> </b-icon
      ></b-tooltip>
    </span>
  </span>
  <span v-else>
   
    <b-tag type="is-info"
      >{{ request.b_firstname }} {{ request.b_lastname }}</b-tag
    >
     <b-icon v-if="request.approve_status == 1" type="is-success" icon="check">
    </b-icon>
    <b-icon v-if="request.approve_status == 2" type="is-danger" icon="ban">
    </b-icon>
    <b-tooltip :label="request.approve_response">
      <b-icon v-if="request.approve_response" icon="comment" type="is-info">
      </b-icon>
    </b-tooltip>
  </span>
</template>
<script>
export default {
  name: "StatusColumn",
  components: {},
  props: ["request", "submitFunction"],
};
</script>
