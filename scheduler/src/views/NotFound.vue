<template>
  <div style="background-color: white; text-align:center; display:block;height:content-fit;" class="agentListContainer container">
    <b-message type="is-danger" has-icon size="is-large">
            Page not found.
        </b-message>
  </div>
</template>
<script>

export default {
  name: "NotFound",
  components: {

  },
  data() {

    return {
    
    };
  },
  computed: {
  
  },
  mounted() {
  

  },
  methods: {
    
  },
};
</script>
<style scoped>
.title
{
  text-decoration: underline;
}
.agentListContainer {
   display: flow-root;
}
.agentsListTbl
{
  border:1px solid black;
}
</style>
<style >
.notification a:not(.button):not(.dropdown-item) {
  text-decoration: none !important;
}
.section:first-child {
  margin-right: 5px;
}
.section {
    -webkit-flex: 1; /* Safari */
    -ms-flex: 1; /* IE 10 */
    flex: 1; /* Standard syntax */
  border: 1px solid rgb(211, 211, 211);
  border-radius: 5px;
}
.subtitle {
  text-align: center;
}
.table tr {
  height: 40px;
}
.sectionscontainer
{
    display: -webkit-flex; /* Safari */     
    display: flex; /* Standard syntax */
}
</style>