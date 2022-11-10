<template>
  <div>
    <b-table
      paginated
      :per-page="15"
      :data="admins"
      narrowed
      bordered
      class="agentsListTbl"
      :row-class="(row, index) => 'agentsListTbl'"
      :loading="tblLoading"
    >
      <b-table-column centered label="Agent" searchable field="fullrow">
        <template #searchable="props">
          <b-input
            v-model="props.filters[props.column.field]"
            placeholder="Search by Agent Name"
            icon="search"
          />
        </template>
        <template v-slot="props">
          {{ props.row.fullrow }}
        </template>
      </b-table-column>
      <b-table-column field="color" centered label="Groups" v-slot="props">
        <b-select
          placeholder="Assign a team"
          v-model="adminteams[props.row.id]"
          disabled
          expanded
        >
          <option
            v-for="team in teams"
            :value="team.group_id"
            :key="team.group_id"
            :disabled="team.hasSubteams > 0"
          >
            <span v-if="team.parent > 0">- </span> {{ team.name }}
          </option>
        </b-select>
      </b-table-column>
      <b-table-column field="daysoff" centered label="Days Off" v-slot="props">
        <b-button
          type="is-primary"
          outlined
          @click="OpenDaysOffManagement(props.row.id)"
          >Open Days-Off Management</b-button
        >
      </b-table-column>
    </b-table>
  </div>
</template>

<script>
export default {
  name: "AgentsListEditor",
  computed: {
    admins() {
      // this.test;
      return this.$store.state.admins.filter(i=>this.permissionGroups.includes(i.groupid));
    },
    teams() {
      return this.$store.getters["teams/teams"];
    },
    permissionGroups()
    {
      return this.$store.state?.editorPermissionsGroups[4] ?? []
    },
  },
  data() {
    return {
      adminteams: {},
      test: "",
      tblLoading: false,
    };
  },
  mounted() {},
  watch: {
    admins(n) {
      if (n.length > 0) {
        for (var i = 0; i < n.length; i++) {
          if(this.permissionGroups.includes(n[i].groupid))
          {
          this.adminteams = { ...this.adminteams, [n[i].id]: n[i].groupid };
        }
        }
      }
    },
  },
  methods: {
    setEditor(id, value) {
      this.tblLoading = true
      var endpoint;
      switch (value) {
        case false:
          endpoint = "delete";
          break;
        case true:
          endpoint = "add";
          break;
      }
      if (id && endpoint) {
        this.$http
          .post("./scheduleapi/editors/" + endpoint, {
            agent_id: id,
          })
          .then((response) => {
            if (response.data.response == "success") {
              this.$buefy.toast.open({
                message: "Permission is set",
                type: "is-success",
              });
            } else {
              this.$buefy.toast.open({
                message: response.data.response,
                type: "is-danger",
              });
            }
            this.tblLoading = false
          });
      }
      else
      {
        this.tblLoading = false
      }
    },
    OpenDaysOffManagement(id) {
      this.$router.push({
        path: `/daysoff/${id}`,
      });
    },
    OpenPermissionsManagement(id)
    {
      this.$router.push({
        path: `/permissions/${id}`,
      });
    },
    setTeam(admin) {
      this.$http
        .post("./scheduleapi/agents/assigntogroup", {
          team_id: this.adminteams[admin],
          agent: admin,
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Team has been set",
              type: "is-success",
            });
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
    getColor(color) {
      if (color == null || !color) return "#000000";
      return color;
    },
    setcolor(c, val, admin_id) {
      this.$http
        .post("./scheduleapi/agents/color", {
          color: c,
          value: val,
          admin: admin_id,
        })
        .then((response) => {
          if (response.data.response == "success") {
            this.$buefy.toast.open({
              message: "Color changed!",
              type: "is-success",
            });
            this.$store.dispatch("getAdmins");
          } else {
            this.$buefy.toast.open({
              message: response.data.response,
              type: "is-danger",
            });
          }
        });
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style >
.agentsListTbl > td:first-child {
  text-align: left !important;
  font-size: 1.2em;
}
.agentsListTbl {
  background: white !important;
}
</style>
<style >
.b-table {
  float: left;
  width: 100%;
  padding-bottom: 10px;
}
.b-table td {
  height: auto;
}
.b-table tr {
  height: auto !important;
}
</style>
