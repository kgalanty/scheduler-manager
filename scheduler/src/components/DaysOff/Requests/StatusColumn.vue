<template>
  <span v-if="request.approve_status == 0">
    <b-button
      type="is-primary"
      v-if="submitFunction"
      @click="submitFunction(request)"
      size="is-small"
      >Submit a review</b-button
    >
    <span v-else>
      <b-tooltip label="Awaiting verification"
        ><b-icon icon="spinner" size="is-small"> </b-icon
      ></b-tooltip>
    </span>
  </span>
  <span v-else>
    <b-tag :icon="iconType.icon" :type="iconType.type"> {{ request.b_firstname }} {{ request.b_lastname }}</b-tag>
    <b-tooltip :label="request.approve_response">
      <b-icon v-if="request.approve_response" icon="comment" type="is-info" size="is-small">
      </b-icon>
    </b-tooltip>
  </span>
</template>
<script>
export default {
  name: "StatusColumn",
  components: {},
  props: ["request", "submitFunction"],
  computed:
  {
    iconType()
    {
      const type = this.request.approve_status == 1 ? 'is-success' : (this.request.approve_status == 2 ? 'is-danger' : 'is-info');
      const icon = this.request.approve_status == 1 ? 'check' : (this.request.approve_status == 2 ? 'ban' : 'spinner');
      return { type, icon }
    }
  }
};
</script>
