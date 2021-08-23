<template>
  <div style="background-color: white; display: flow-root">
    <section class="hero">
      <div class="hero-body">
        <p class="title">Calendar Synchronization</p>
      </div>
    </section>
     <section class="hero"><div class="notification is-primary"> <b-field label="">
             Your Calendar URL
            <b-input :loading="loading" style="" expanded v-if="link||loading" v-model="link"></b-input>
            </b-field>
        </div>
     </section>
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
export default {
  name: "calendar",

  mounted() {
    this.generate()
   // this.$store.dispatch("getLogs", {datefrom: this.moment(this.datefrom).format('YYYY-MM-DD HH:mm:ss'), dateto: this.moment(this.dateto).format('YYYY-MM-DD HH:mm:ss') });
  },
  methods: {
    generate()
    {
      this.loading = true
   this.$http
      .get("./scheduleapi/calendar/generate", { withCredentials: true })
      .then((r) => {
        if (r.data.response === "success") {
          this.link = r.data.link
        } else {
         this.$router.push({ path: `/`})
        }
        this.loading = false
      });
    },
    getStyle(bg, color)
    {
       if(!bg && !color)
      {
         return {
        'background-color' : 'white',
        'color': 'black'
      }
      }
      return {
        'background-color' : bg,
        'color': color
      }
    },
    openSchedule(path, log) {
      var shift = log.log.split("Shift: ")[1];
      if (shift) {
        this.$store.dispatch("setItemKey", {
          shift: shift,
          date: log.event_date,
        });
      }
      // console.log(name);
      this.$router.push({
        path: `${path}`,
      });
    },
    getStats() {
      if (this.moment(this.dateto).isAfter(this.datefrom) || this.datefrom === this.dateto) {
        this.$store.dispatch("getLogs", {datefrom: this.moment(this.datefrom).format('YYYY-MM-DD HH:mm:ss'), dateto: this.moment(this.dateto).format('YYYY-MM-DD HH:mm:ss') });
      } else {
        // this.dateto = this.moment(this.datefrom).add(1, 'day').toDate()
        // this.getStats()
      }
    },
  },
  computed: {
    logs() {
      return this.$store.state.logs;
    },
  },
  data() {
    return {
       loading:false,
       link: ''
    };
  },
};
</script>
<style scoped>
</style>