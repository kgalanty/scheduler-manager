<template>
  <div style="background-color: white; display: flow-root">
    <section class="hero">
      <div class="hero-body">
        <p class="title">Logs</p>
      </div>
    </section>
    <div class="columns">
      <div class="column">
        <b-field label="Select a date from">
          <b-datepicker
            @input="getStats"
            v-model="datefrom"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
          >
          </b-datepicker>
        </b-field>
      </div>
      <div class="column">
        <b-field label="Select a date to">
          <b-datepicker
            @input="getStats"
            v-model="dateto"
            placeholder="Click to select..."
            icon="calendar"
            trap-focus
          >
          </b-datepicker>
        </b-field>
      </div>
    </div>
    <b-table :data="logs" class="" paginated>
       <template #empty>
            <div class="has-text-centered">No records</div>
          </template>
      <b-table-column
        field="id"
        label="Agent"
        width="250"
        v-slot="props"
        centered
      >
       <span :style="getStyle(props.row.bg, props.row.color)" style="padding:3px;border-radius:5px;"> {{ props.row.firstname }} {{ props.row.lastname }}</span>
      </b-table-column>
      <b-table-column
        field="id"
        label="Action"
        width="140"
        v-slot="props"
        centered
      >
        {{ props.row.action }}
      </b-table-column>
      <b-table-column
        field="id"
        label="Date"
        width="200"
        v-slot="props"
        centered
      >
        {{ props.row.date }}
      </b-table-column>
      <b-table-column field="id" label="Entry" v-slot="props">
        {{ props.row.log }} 

         <b-button v-if="props.row.path" @click="openSchedule(props.row.path, props.row)" icon-right="share" type="is-info" size="is-small"></b-button>
      </b-table-column>
    </b-table>
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
export default {
  name: "logs",

  mounted() {
    this.$store.dispatch("getLogs", {datefrom: this.moment(this.datefrom).format('YYYY-MM-DD HH:mm:ss'), dateto: this.moment(this.dateto).format('YYYY-MM-DD HH:mm:ss') });
  },
  methods: {
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
      datefrom: this.moment().startOf("month").toDate(),
      dateto: this.moment().endOf("month").toDate(),
    };
  },
};
</script>
<style scoped>
</style>