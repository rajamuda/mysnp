<template>
	<card :title="'List of Jobs'">
		<nav>
		  <div class="nav nav-tabs" id="nav-tab" role="tablist">
		    <a @click="process('jobs')" class="nav-item nav-link active" id="nav-jobs-tab" data-toggle="tab" href="#nav-jobs" role="tab" aria-controls="nav-jobs" aria-selected="true">{{ $t('create_jobs') }}</a>
		    <a @click="process('phylo')" class="nav-item nav-link" id="nav-phylo-tab" data-toggle="tab" href="#nav-phylo" role="tab" aria-controls="nav-phylo" aria-selected="true">{{ $t('phylo') }}</a>
		  </div>
		</nav>
		<div class="tab-content" id="nav-tabContent">
			<!-- Jobs List -->
			<div class="tab-pane fade show active" id="nav-jobs" role="tabpanel" aria-labelledby="nav-jobs-tab">
				<div v-if='jobs[0]'>
					<div v-for="(job, index) in jobs">
						<div class="card border-primary mb-1 mt-3">	
							<!-- TO DO: add sort by submit date or job's title, show selection & limit result -->		
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
				</div>
				<div v-else>
					<div class="text-center mt-2 mb-2"><i>No SNP jobs were created<br/><router-link :to="{ name: 'jobs.create' }">Create One</router-link></i></div>
				</div>
			</div>
			<!-- Phylos List -->
			<div class="tab-pane" id="nav-phylo" role="tabpanel" aria-labelledby="nav-phylo-tab">
				<div v-if="phylo[0]">	
					<div v-for="(phy, index) in phylo">
						<div class="card border-primary mb-1 mt-3">	
							<!-- TO DO: add sort by submit date or job's title, show selection & limit result -->		
						  <router-link :to="{ name: 'jobs.view_phylo', params: { id: phy.id }}"><div class="card-header text-white bg-primary">{{ phy.name || "Unnamed" }}</div></router-link>
						  <div class="card-body text-dark">
						  	<div class="process-status">
							    <table>
							    	<tr>
							    		<td>{{ $t('status' )}}</td>
							    		<td class="colon"> : </td>
							    		<td>{{ phy.status }}</td>
							    	</tr>
							    	<tr>
							    		<td>{{ $t('start_date') }}</td>
							    		<td class="colon"> : </td>
							    		<td>{{ phy.submitted_at }}</td>
							    	</tr>
							    	<tr>
							    		<td>{{ $t('finish_date') }}</td>
							    		<td class="colon"> : </td>
							    		<td>{{ phy.finished_at }}</td>
							    	</tr>
							    </table>
						  	</div>
						  </div>
						</div>
					</div>
				</div>
				<div v-else>
					<div class="text-center mt-2 mb-2"><i>No phylogenetic trees were constructed<br/><router-link :to="{ name: 'jobs.construct_phylo' }">Create One</router-link></i></div>
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
				jobs_refresher: '',
				phylo: [],
				phylo_refresher: '',
			}
		},

		methods: {
			async getJobs () {
				const { data } = await axios.get('/api/jobs/all')
				this.jobs = data
			},

			async getPhylo () {
				const { data } = await axios.get('/api/jobs/phylo/all')
				this.phylo = data
			},

			process (type) {

				if(type === 'jobs'){
					this.jobs_refresher = setInterval(this.getJobs, 10000)
		  		clearInterval(this.phylo_refresher)
				}else if(type === 'phylo'){
					if(!this.phylo.length) this.getPhylo()
					this.phylo_refresher = setInterval(this.getPhylo, 10000)
		  		clearInterval(this.jobs_refresher)
				}
			}
		},

		created () {
			this.getJobs()
			this.jobs_refresher = setInterval(this.getJobs, 10000)
		},

		beforeDestroy() {
		  clearInterval(this.jobs_refresher)
			clearInterval(this.phylo_refresher)
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