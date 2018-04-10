<template>
  <div class="row">
    <div class="col-md-3">
      <card :title="$t('jobs')" class="jobs-card">
        <ul class="nav flex-column nav-pills">
          <li v-for="tab in tabs" class="nav-item">
            <router-link :to="{ name: tab.route }" class="nav-link" active-class="active">
              <fa :icon="tab.icon" fixed-width/>
              {{ tab.name }}
            </router-link>
          </li>
        </ul>
      </card>
    </div>

    <div class="col-md-9">
      <transition name="fade" mode="out-in">
        <router-view/>
      </transition>
    </div>
  </div>
</template>

<script>
export default {
  middleware: 'auth',

  computed: {
    tabs () {
      return [
        {
          icon: 'tasks',
          name: this.$t('list_jobs'),
          route: 'jobs.list'
        },
        {
          icon: 'plus-circle',
          name: this.$t('create_jobs'),
          route: 'jobs.create'
        },
        {
          icon: 'tree',
          name: this.$t('phylo'),
          route: 'jobs.construct_phylo'
        }
      ]
    }
  }
}
</script>

<style>
.jobs-card .card-body {
  padding: 0;
}
</style>
