<template>
	<card :title="$t('construct_phylo')">
		<div class="alert alert-danger" v-if="form.errors.has('error')" v-html="form.errors.errors.error"></div>
		<form @submit.prevent="submit" @keydown="form.onKeydown($event)">
			<div class="form-group row">
				<label class="col-md-3 col-form-label text-md-right">{{ $t('phylo_name') }} <small>({{ $t('optional') }})</small></label>
				<div class="col-md-7">
					<input v-model="form.name" type="name" name="name" class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
          <has-error :form="form" field="name"/>	
				</div>
			</div>
			<div class="form-group row">
				<label class="col-md-3 col-form-label text-md-right">{{ $t('seq_references') }}</label>
				<div class="col-md-7">
					<select v-model="form.refseq" name="refseq" class="custom-select" :class="{ 'is-invalid': form.errors.has('refseq') }">
						<option disabled value="">{{ $t('select_one') }}</option>
						<option v-for="opt in refseq_opts" v-bind:value="opt.value">
							{{ opt.name }}
						</option>
					</select>
          <has-error :form="form" field="refseq"/>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-md-3 col-form-label text-md-right">{{ $t('samples') }}</label>
				<div class="col-md-7" :class="{ 'invalid': form.errors.has('samples') }">
					<multiselect v-model="form.samples" :options="samples_opts" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" :preserve-search="true" :placeholder="$t('select_min_two')" label="name" track-by="name"></multiselect>
          <has-error :form="form" field="samples"/>
				</div>
			</div>
			<div class="form-group row">
				<label class="col-md-3 col-form-label text-md-right">{{ $t('phylo_method') }}</label>
				<div class="col-md-7">
					<select v-model="form.method" name="method" class="custom-select" :class="{ 'is-invalid': form.errors.has('method') }">
						<option disabled value="">{{ $t('select_one') }}</option>
						<option v-for="opt in method_opts" v-bind:value="opt.value">
							{{ opt.name }}
						</option>
					</select>
          <has-error :form="form" field="method"/>
				</div>
			</div>
			<!-- Submit -->
			<div class="form-group row" style="margin-top:1rem">
        <div class="col-md-10 text-right">
          <router-link :to="{ name: 'jobs.list'}" class="btn btn-danger">{{ $t('cancel') }}</router-link>
          <v-button type="success" :loading="form.busy">{{ $t('construct_phylo') }}</v-button>
        </div>
      </div>
		</form>
	</card>
</template>

<script>
	import Vue from 'vue'
	import axios from 'axios'
	import Form from 'vform'
  import Multiselect from 'vue-multiselect'
  import 'vue-multiselect/dist/vue-multiselect.min.css'

	export default{
		scrollToTop: false,


		data () {
			return{
				form: new Form({
					name: '',
					refseq: '',
					method: 'upgma',
					samples: [],
				}),
				refseq_opts: [],
				method_opts: [
					{name: 'UPGMA', value: 'upgma'},
					{name: 'Neighbor-Joining', value: 'nj'},
				],
				samples_opts: [],
			}
		},

		watch: {
			'form.refseq': function(newval) {
				this.getJobsByRefseq()
			}
		},

		methods: {
			async getRefseqJobs () {
				const { data } = await axios.get('/api/jobs/refseq')

				this.refseq_opts = data
			},

			async getJobsByRefseq () {
				const { data } = await axios.get('/api/jobs/refseq/'+this.form.refseq)

				this.samples_opts = data

			},

			submit () {
				this.form.post('/api/jobs/phylo/construct')
					.then(({ data }) => {
						console.log(data)
	  				this.$router.push({name: 'jobs.view_phylo', params: {id: data.phylo_id}})
					})
					.catch((e) => {
						console.error(e)
					})
			}
		},

		mounted () {
			this.getRefseqJobs()
		},

		metaInfo () {
	    return { title: this.$t('construct_phylo') }
	  },

	  components: {
	  	Multiselect
	  },
	}
</script>

<style>
	.help-block.invalid-feedback {
		display: block !important;
	}
	.invalid .multiselect > .multiselect__tags {
		border-color: #dc3545 !important;
	}
</style>