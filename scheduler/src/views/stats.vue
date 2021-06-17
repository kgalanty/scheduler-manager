<template>
  <div style="background-color: white;display:flow-root;">
    <section class="hero">
      <div class="hero-body">
        <p class="title">Stats</p>
      </div>
    </section>
          <div class="columns">
        <div class="column">
          <b-field label="Select a date from">
                  <b-datepicker  @input="getStats"
                      v-model="datefrom"
                      placeholder="Click to select..."
                      icon="calendar"
                      trap-focus>
                  </b-datepicker>
              </b-field>
              </div>
              <div class="column">
         <b-field label="Select a date to">
            <b-datepicker  @input="getStats"
                v-model="dateto"
                placeholder="Click to select..."
                icon="calendar"
                trap-focus>
            </b-datepicker>
        </b-field>
        
        </div></div>
 <b-table :data="agentstats" class="">
                <b-table-column
                  field="id"
                  label="Agent"
                  width="40"
                  v-slot="props"
                  centered
                >
                  {{ props.row.firstname }} {{ props.row.lastname }}
                </b-table-column>
                  <b-table-column
                  field="id"
                  label="Shifts"
                  width="40"
                  v-slot="props"
                  centered
                >
                  {{ props.row.allshifts }} (Days: {{props.row.days}})
                 </b-table-column>     <b-table-column
                  field="id"
                  label="Days Off"
                  width="40"
                  v-slot="props"
                  centered
                >
                  {{ daysSpan-props.row.days }}
                </b-table-column>
                <b-table-column
                 field="id"
                  label="Days Working/Days Off"
                  width="40"
                  v-slot="props"
                  centered
                >

 <b-progress format="raw" :max="daysSpan">
            <template #bar>
                <b-progress-bar :value="props.row.days" show-value></b-progress-bar>
                <b-progress-bar :value="daysSpan-props.row.days" type="is-primary" show-value></b-progress-bar>
            </template>
        </b-progress>

                </b-table-column>
                </b-table>
  </div>
</template>
<script>
// import ShiftsList from "../components/ShiftsList.vue";
// import AgentsList from "../components/AgentsList.vue";
export default {
  name: "stats",
  components: {},
  data() {
    return {
      agentstats: [],
      datefrom: this.moment().startOf('month').toDate(),
      dateto: this.moment().endOf('month').toDate()
    };
  },
  watch:
  {
    datefrom(newval,oldval)
    {
      if(this.moment(newval).isAfter(this.dateto))
      {
         this.$buefy.snackbar.open({
                    message: 'Incorrect dates',
                    type: 'is-danger'
                })
        this.datefrom =  this.moment(oldval).toDate()
      }
    },
    dateto(newval,oldval)
    {
      if(this.moment(newval).isBefore(this.datefrom))
      {
         this.$buefy.snackbar.open({
                    message: 'Incorrect dates',
                    type: 'is-danger'
                })
        this.dateto =  this.moment(oldval).toDate()
      }
    }
  },
  computed: {
    daysSpan ()
    {
      return parseInt(this.moment.duration(this.moment(this.dateto).diff(this.moment(this.datefrom))).asDays())+1
    },
    teams() {
      return this.$store.state.schedule_teams;
    },
    admins() {
      return this.$store.state.admins;
    },
    shifts() {
      return this.$store.state.shifts;
    },
    days() {
      return [
        this.moment().day(1).format("ddd DD.MM"),
        this.moment().day(2).format("ddd DD.MM"),
        this.moment().day(3).format("ddd DD.MM"),
        this.moment().day(4).format("ddd DD.MM"),
        this.moment().day(5).format("ddd DD.MM"),
        this.moment().day(6).format("ddd DD.MM"),
        this.moment().day(7).format("ddd DD.MM"),
      ];
    },
  },
  mounted() {
    this.getStats()
  },
  methods: {
    getStats()
    {
      if(this.moment(this.dateto).isAfter(this.datefrom) )
      {
     this.$http
        .get("./scheduleapi/stats", {
            params: {
              datefrom: this.moment(this.datefrom).format('YYYY-MM-DD'),
              dateto: this.moment(this.dateto).format('YYYY-MM-DD')
            }
          })
        .then((r) => {
          if (r.data.response === "success") {
              this.agentstats = r. data.stats
          } else {
          // this.$router.push({ path: `/`})
          }
        });
        }
        else
        {
            
               // this.dateto = this.moment(this.datefrom).add(1, 'day').toDate()
               // this.getStats()
        }
    },
    getFirstDay() {
      this.moment();
    },
  },
};
</script>
<style scoped>
</style>