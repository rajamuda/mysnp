<template>
  <card :title="$t('home')">
    <div class="text-center">
	    <h1 style="font-size:7rem;"> {{ appName }} </h1>
	    <div class="mb-4" style="color:grey">{{ $t('tagline') }}</div>
		  <button type="button" @click="goTo('snp')" class="btn btn-success"><fa :icon="'plus-circle'" fixed-width/> {{ $t('create_jobs') }}</button>
		  <button type="button" @click="goTo('phylo')" class="btn btn-primary"><fa :icon="'tree'" fixed-width/> {{ $t('construct_phylo') }}</button>
		</div>
  </card>
</template>

<script>
export default {
  middleware: 'auth',

  metaInfo () {
    return { title: this.$t('home') }
  },

  data: () => ({
  	appName: window.config.appName
  }),

  methods: {
  	goTo (page) {
  		if(page === 'snp'){
  			this.$router.push({name: 'jobs.create'});
  		}else if(page === 'phylo'){
  			this.$router.push({name: 'jobs.construct_phylo'});
  		}
  	}
  },
}
</script>
