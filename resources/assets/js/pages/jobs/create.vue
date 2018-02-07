<template>
	<div class="row">
		<div class="col-lg-12 m-auto">
			<card :title="$t('create_jobs')">
				<!-- {{ form.errors }} -->
				<!-- <alert-error :form="form" message="There were some problems with your input."></alert-error> -->
				<form @submit.prevent="create" @keydown="form.onKeydown($event)">
					<!-- Title -->
					<div class="form-group row">
            <label v-tooltip="'Title for this jobs'" class="col-md-3 col-form-label text-md-right">{{ $t('jobs_title') }}</label>
            <div class="col-md-7">
              <input v-model="form.title" type="title" name="title" class="form-control"
                :class="{ 'is-invalid': form.errors.has('title') }">
              <has-error :form="form" field="title"/>
            </div>
          </div>

					<!-- References -->
					<div class="form-group row">
						<label v-tooltip="'Sequences references If you have new references, you can upload in \'Upload\' section'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_references') }}</label>
						<div class="col-md-7">
							<select v-model="form.references" name="references" class="form-control" :class="{ 'is-invalid': form.errors.has('references') }">
								<option disabled value="">{{ $t('select_one') }}</option>
								<option v-for="opt in refopts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="references"/>
						</div>
					</div>

					<!-- Reads Type -->
					<div class="form-group row">
						<label v-tooltip="'Type of reads, whether it single reads or paired reads'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads_type') }}</label>
						<div class="col-md-7">
							<select v-model="form.reads_type" name="reads_type" class="form-control" :class="{ 'is-invalid': form.errors.has('reads_type') }">
								<option v-for="opt in reads_type_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="reads_type"/>
						</div>
					</div>

					<!-- Reads (dynamic) -->
					<div class="form-group row"	style="margin-bottom:0.25rem" v-for="(read, index) in form.reads1">
						<label v-tooltip="'Choose sequence from NGS machine reads. You can add more than one reads. If you have new reads, you can upload in \'Upload\' section'" v-if="index == 0" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads') }}</label>
						<div v-else class="col-md-3"></div>
						<div class="col-md-7">
							<select v-model="read.value" v-bind:id="'reads1.'+index" name="reads1[]" class="form-control" :class="{ 'is-invalid': form.errors.has('reads1.'+index+'.value') }">
								<option disabled value="">{{ $t('select_one') }}</option>
								<option v-for="opt in reads_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="reads1.0.value"/>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-md-7 offset-md-3">
							<input type="button" @click="addReads(1)" class="btn btn-sm btn-default" value="(+) Add">
							<input v-if="form.reads1.length > 1" type="button" @click="removeReads(1)" class="btn btn-sm btn-default" value="(-) Remove">
						</div>
					</div>

					<div v-if="form.reads_type == 'pe'">
						<div class="form-group row"	style="margin-bottom:0.25rem" v-for="(read, index) in form.reads2">
							<label v-tooltip="'Choose sequence from NGS machine reads. You can add more than one reads. If you have new reads, you can upload in \'Upload\' section'" v-if="index == 0" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads2') }}</label>
							<div v-else class="col-md-3"></div>
							<div class="col-md-7">
								<select v-model="read.value" v-bind:id="'reads2.'+index" name="reads2[]" class="form-control" :class="{ 'is-invalid': form.errors.has('reads2.'+index+'.value') }">
									<option disabled value="">{{ $t('select_one') }}</option>
									<option v-for="opt in reads_opts" v-bind:value="opt.value">
										{{ opt.text }}
									</option>
								</select>
              	<has-error :form="form" field="reads2.0.value"/>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-7 offset-md-3">
								<input type="button" @click="addReads(2)" class="btn btn-sm btn-default" value="(+) Add">
								<input v-if="form.reads2.length > 1" type="button" @click="removeReads(2)" class="btn btn-sm btn-default" value="(-) Remove">
							</div>
						</div>
					</div>

					<!-- Annotation DB -->
					<div class="form-group row">
						<label v-tooltip="'Choose a database to annotate variant'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_db_annotate') }}</label>
						<div class="col-md-7">
							<select v-model="form.db_annotate" name="db_annotate" class="form-control" :class="{ 'is-invalid': form.errors.has('db_annotate') }">
								<option disabled value="">{{ $t('select_one') }}</option>
								<option v-for="opt in db_annotate_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="db_annotate"/>
						</div>
					</div>

					<!-- Seq Mapper -->
					<div class="form-group row">
						<label v-tooltip="'Choose an alignment tools to map references with each reads.'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_mapper') }}</label>
						<div class="col-md-7">
						<!-- 	<v-select v-model="form.seq_mapper" :options="seq_mapper_opts"></v-select> -->
							<select v-model="form.seq_mapper" name="seq_mapper" class="form-control" :class="{ 'is-invalid': form.errors.has('seq_mapper') }">
								<option v-for="opt in seq_mapper_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="seq_mapper"/>
						</div>
					</div>

					<!-- SNP Caller -->
					<div class="form-group row">
						<label v-tooltip="'Choose a variant calling tools to identify variant'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_snp_caller') }}</label>
						<div class="col-md-7">
							<select v-model="form.snp_caller" name="reads_type" class="form-control" :class="{ 'is-invalid': form.errors.has('snp_caller') }">
								<option v-for="opt in snp_caller_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="snp_caller"/>
						</div>
					</div>

					<!-- Button -->
					<div class="form-group row">
		        <div class="col-md-9 ml-md-auto">
		          <v-button type="success" :loading="form.busy">{{ $t('create_jobs') }}</v-button>
		        </div>
		      </div>
				</form>
			</card>
		</div>

		<div class="col-lg-12 m-auto">
			<card :title="$t('advanced_param')">
				<h1>Advanced</h1>
				<button v-tooltip="'hello'">Hover me</button>
			</card>
		</div>
	</div>
</template>

<script>
	import Form from 'vform'
	import Vue from 'vue'
	import VTooltip from 'v-tooltip'
	import vSelect from 'vue-select'

	Vue.component('v-select', vSelect)
	Vue.use(VTooltip)

	export default{
		scrollToTop: false,

		metaInfo () {
	    return { title: this.$t('jobs') }
	  },

	  data: () => ({
	    form: new Form({
		      title: '',
		      references: '',
		      reads_type: 'se',
		      reads1: [{value: ''}],
		      reads2: [{value: ''}],
		      db_annotate: '',
		      seq_mapper: 'bt2',
		      snp_caller: 'sam',
		    }),
		    refopts: [],
	    	reads_type_opts: [
	      	{text: 'Single-End', value: 'se'}, 
	      	{text: 'Paired-End', value: 'pe'}
	      ],
	      reads_opts: [],
		    db_annotate_opts: [],
		    seq_mapper_opts: [],
		    snp_caller_opts: [],
	  }),

	  methods: {
	  	populateOptions () {
	  		var data = [
	  			{ text: 'One', value: 'A' },
		      { text: 'Two', value: 'B' },
		      { text: 'Three', value: 'C' }
		    ]

		    var mapper = [
		    	{ text: 'Bowtie2', value: 'bt2' },
		    	{ text: 'BWA', value: 'bwa' },
		    	{ text: 'Novoalign', value: 'na' }
		    ]

		    var caller = [
		    	{ text: 'Samtools', value: 'sam' },
		    	{ text: 'GATK', value: 'gatk' }
		    ]

		    this.refopts = data
		    this.reads_opts = data
		    this.db_annotate_opts = data
		    this.seq_mapper_opts = mapper
		    this.snp_caller_opts = caller
	  	},

	  	addReads (id) {
	  		if(id == 1){
	  			this.form.reads1.push({value: ''})
	  		}else{
	  			this.form.reads2.push({value: ''})
	  		}
	  	},

	  	removeReads (id) {
	  		if(id == 1){
	  			this.form.reads1.pop()
	  		}else{
	  			this.form.reads2.pop()
	  		}
	  	},

	  	create () {
	  		this.form.post('/api/jobs/create')
	  			.then(({data}) => {
	  				console.log(data)
	  			})
	  			.catch(e => {
	  				console.log(e)
	  			})
	  	}
	  },

	  mounted () {
	  	this.populateOptions();
	  }
	}
</script>