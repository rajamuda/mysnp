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
					<div class="form-group row">
						<label v-tooltip="'Choose sequence from NGS machine reads. You can add more than one reads. If you have new reads, you can upload in \'Upload\' section'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads') }}</label>
						<div class="col-md-7">
							<multi-select :options="reads_opts" :selected-options="form.reads1" placeholder="Select one or more" class="form-control" :is-error="form.errors.has('reads1')" @select="onSelectReads1"></multi-select>
              <has-error :form="form" field="reads1"/>
						</div>
					</div>

					<div v-if="form.reads_type == 'pe'">
						<div class="form-group row">
							<label v-tooltip="'Choose sequence from NGS machine reads. You can add more than one reads. If you have new reads, you can upload in \'Upload\' section'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads2') }}</label>
							<div class="col-md-7">
								<multi-select :options="reads_opts" :selected-options="form.reads2" placeholder="Select one or more" class="form-control" :is-error="form.errors.has('reads2')" @select="onSelectReads2"></multi-select>
	              <has-error :form="form" field="reads2"/>
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

					<!-- SNP Phylogenetic Tree Generator -->
					<div class="form-group row">
						<label v-tooltip="'Tools to generate phylogenetic tree of SNP'" class="col-md-3 col-form-label text-md-right">{{ $t('phylo_generator') }}</label>
						<div class="col-md-7">
							<input type="text" value="SNPhylo" class="form-control" disabled />
						</div>
					</div>

					<!-- Advanced -->
					<v-button type="default" native-type="button" class="col-md-12 collapsed" data-toggle="collapse" data-target="#advancedParameters">{{ $t('advanced_param') }} <fa icon="caret-square-down" fixed-width/></v-button>
					<div class="collapse" id="advancedParameters">
					  <div class="card card-body">
					    <nav>
							  <div class="nav nav-tabs" id="nav-tab" role="tablist">
							    <!--Alignment Tools Parameters Settings -->
							    <a v-if="form.seq_mapper == 'bt2'" class="nav-item nav-link active" id="nav-bt2-tab" data-toggle="tab" href="#nav-bt2" role="tab" aria-controls="nav-bt2" aria-selected="true">Bowtie2</a>
							    <a v-else-if="form.seq_mapper == 'bwa'" class="nav-item nav-link active" id="nav-bwa-tab" data-toggle="tab" href="#nav-bwa" role="tab" aria-controls="nav-bwa" aria-selected="true">BWA</a>
							    <a v-else-if="form.seq_mapper == 'novo'" class="nav-item nav-link active" id="nav-novo-tab" data-toggle="tab" href="#nav-novo" role="tab" aria-controls="nav-novo" aria-selected="true">Novoalign</a>
							    <a v-else>Nothing</a>
							    
							    <!-- SNP Caller Parameters Settings -->
							    <a v-if="form.snp_caller == 'sam'" class="nav-item nav-link" id="nav-sam-tab" data-toggle="tab" href="#nav-sam" role="tab" aria-controls="nav-sam" aria-selected="false">BCFtools</a>
							    <a v-else-if="form.snp_caller == 'gatk'" class="nav-item nav-link" id="nav-gatk-tab" data-toggle="tab" href="#nav-gatk" role="tab" aria-controls="nav-gatk" aria-selected="false">GATK</a>
							    <a v-else>Nothing</a>

							    <!-- VCFutils -->
							    <a class="nav-item nav-link" id="nav-filter-tab" data-toggle="tab" href="#nav-filter" role="tab" aria-controls="nav-filter" aria-selected="false">VCFutils (VarFilter)</a>
							    
							    <!-- SNPhylo -->
							    <a class="nav-item nav-link" id="nav-phylo-tab" data-toggle="tab" href="#nav-phylo" role="tab" aria-controls="nav-phylo" aria-selected="false">SNPhylo</a>
							  </div>
							</nav>
							<div class="tab-content" id="nav-tabContent">
							  <div v-if="form.seq_mapper == 'bt2'" class="tab-pane fade show active" id="nav-bt2" role="tabpanel" aria-labelledby="nav-bt2-tab">...</div>
							  <div v-else-if="form.seq_mapper == 'bwa'" class="tab-pane fade show active" id="nav-bwa" role="tabpanel" aria-labelledby="nav-bwa-tab">...</div>
							  <div  v-else-if="form.seq_mapper == 'novo'" class="tab-pane fade show active" id="nav-novo" role="tabpanel" aria-labelledby="nav-novo-tab">...</div>
							  <div v-else>Nothing</div>

							  <div v-if="form.snp_caller == 'sam'" class="tab-pane fade" id="nav-sam" role="tabpanel" aria-labelledby="nav-sam-tab">...</div>
							  <div v-else-if="form.snp_caller == 'gatk'" class="tab-pane fade" id="nav-gatk" role="tabpanel" aria-labelledby="nav-gatk-tab">...</div>
							  <div v-else>Nothing</div>

							  <div class="tab-pane fade" id="nav-filter" role="tabpanel" aria-labelledby="nav-filter-tab">...</div>
							  <div class="tab-pane fade" id="nav-phylo" role="tabpanel" aria-labelledby="nav-phylo-tab">...</div>
							</div>
					  </div>
					</div>
					<!-- End Adavanced -->

					<!-- Submit -->
					<div class="form-group row" style="margin-top:1rem">
		        <div class="col-md-10 text-right">
		          <router-link :to="{ name: 'jobs.list'}" class="btn btn-danger">{{ $t('cancel_jobs') }}</router-link>
		          <v-button type="success" :loading="form.busy">{{ $t('create_jobs') }}</v-button>
		        </div>
		      </div>
				</form>
			</card>
		</div>
	</div>
</template>

<script>
	import Form from 'vform'
	import Vue from 'vue'
	import VTooltip from 'v-tooltip'
  import { MultiSelect } from 'vue-search-select'

	Vue.use(VTooltip)
	Vue.component('multi-select', MultiSelect)

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
		      reads1: [],
		      reads2: [],
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
		    	{ text: 'Novoalign', value: 'novo' }
		    ]

		    var caller = [
		    	{ text: 'BCFtools (Samtools)', value: 'sam' },
		    	{ text: 'GATK', value: 'gatk' }
		    ]

		    this.refopts = data
		    this.reads_opts = data
		    this.db_annotate_opts = data
		    this.seq_mapper_opts = mapper
		    this.snp_caller_opts = caller
	  	},

	  	create () {
	  		this.form.post('/api/jobs/create')
	  			.then(({data}) => {
	  				console.log(data)
	  			})
	  			.catch(e => {
	  				console.log(e)
	  			})
	  	},

      onSelectReads1 (items, lastSelectItem) {
        this.form.reads1 = items
      },

      onSelectReads2 (items, lastSelectItem) {
        this.form.reads2 = items
      },
	  },

	  mounted () {
	  	this.populateOptions();
	  },
	}
</script>

<style scoped>
.help-block.invalid-feedback {
	display: block !important;
}
</style>