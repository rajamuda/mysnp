<template>
	<card :title="$t('view_phylo')">
		<div v-if="tree != null">
			<h3 class="mb-0">{{ $t('phylo') }}</h3>
			<hr class="mt-0">
			<div v-html="tree" class="text-center"></div>

			<div class="row">
				<div class="col-12 text-center">
					<a :href="'/file/dl/phylo_output/image/'+phylo_id"><button type="button" class="btn btn-success">{{ $t('download_image') }}</button></a>
					<a :href="'/file/dl/phylo_output/newick/'+phylo_id"><button type="button" class="btn btn-primary">{{ $t('download_tree') }}</button></a>
				</div>
			</div>

			<h3 class="mb-0">{{ $t('dist_matrix') }}</h3>
			<hr class="mt-0">
			<div class="table-responsive">
				<table class="table table-hover">
					<thead>
						<tr class="light">
							<th class="dark"></th>
							<th v-for="(dist, key) in distance_matrix"  class="text-center">
								{{ key }}
							</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(dist_i, key_i) in distance_matrix">
							<th class="light">{{ key_i }}</th>
							<td v-for="(dist_j, key_j) in distance_matrix"  class="text-center">
								<span v-if="key_i == key_j">0</span>
								{{ distance_matrix[key_i][key_j] }}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div v-else>
			<center>
				<circle5></circle5>
				<h2>Running...</h2>
			</center>
		</div>
	</card>
</template>

<script>
	import axios from 'axios'
	import {Circle5} from 'vue-loading-spinner'
	import swal from 'sweetalert2'

	export default{
		scrollToTop: false,


		data () {
			return{
				tree: null,
				distance_matrix: {},
				phylo_refresher: '',
			}
		},

		methods: {
			async getPhylo () {
				try{
					const { data } = await axios.get('/api/jobs/phylo/'+this.phylo_id+'/view')
					this.tree = data.tree
					this.distance_matrix = data.distance_matrix
				}catch(e){
					// console.error(e)
					if(e.response.status === 404){
						this.$router.go(-1)
					}
				}
			},
		},

		computed: {
			phylo_id () {
				return this.$route.params.id
			}
		},

		mounted () {
			this.getPhylo()
			this.phylo_refresher = setInterval(this.getPhylo, 10000)
		},

		beforeDestroy () {
			clearInterval(this.phylo_refresher)
		},

		metaInfo () {
	    return { title: this.$t('view_phylo') }
	  },

	  components: {
	  	Circle5
	  }

	}
</script>

<style scoped>
	.dark{
		color: #f7f9fb;
		background-color: #212529;
		border-color: #32383e;
	}
	.light{
		color: #495057;
		background-color: #e9ecef;
		border-color: #dee2e6;
	}
</style>