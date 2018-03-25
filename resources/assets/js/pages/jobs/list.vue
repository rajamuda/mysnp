<template>
	<card :title="'List of Jobs'">		
		<div v-for="(job, index) in jobs">
			<div class="card border-primary mb-1 mt-3">			
			  <router-link :to="{ name: 'jobs.process', params: { id: job.id }}"><div class="card-header text-white bg-primary">{{ job.title }}</div></router-link>
			  <div class="card-body text-dark">
			  	<div class="process-status">
				    <table>
				    	<tr>
				    		<td>{{ $t('status' )}}</td>
				    		<td class="colon"> : </td>
				    		<td>{{ job.status }}</td>
				    	</tr>
				    	<tr>
				    		<td>{{ $t('start_date') }}</td>
				    		<td class="colon"> : </td>
				    		<td>{{ job.submitted_at }}</td>
				    	</tr>
				    	<tr>
				    		<td>{{ $t('finish_date') }}</td>
				    		<td class="colon"> : </td>
				    		<td>{{ job.finished_at }}</td>
				    	</tr>
				    </table>
			  	</div>
			  </div>
			</div>
		</div>
	</card>
</template>

<script>
	// import Vue from 'vue'
	import axios from 'axios'
	import swal from 'sweetalert2'
	
	export default{
		scrollToTop: false,

		data () {
			return{
				jobs: [],
				refresher: '',
			}
		},

		methods: {
			async getJobs () {
				const { data } = await axios.get('/api/jobs/all')
				this.jobs = data
			},
		},

		created () {
			this.getJobs()
			this.refresher = setInterval(this.getJobs, 10000)

		},

		beforeDestroy() {
		  clearInterval(this.refresher)
		},

		metaInfo () {
	    return { title: this.$t('jobs') }
	  },
	}
</script>

<style scoped>
	td {
		vertical-align: bottom;
	}

	.colon{
		padding-left: 0.5em;
		padding-right: 1em;
	}

	.output-message{
		margin-left: 10px;
		margin-right: 10px;
		max-height: 150px;
		overflow: auto;
	}

	.process-status{
		margin-bottom: 20px;
	}

	pre {
		margin-top: 10px;
	}

	code {
		font-size: 12px;
	}
</style>