<template>
	<div class="row">
		<div class="col-lg-12 m-auto">
			<card :title="$t('create_jobs')">
				<form @submit.prevent="create" @keydown="form.onKeydown($event)">
					<!-- Title -->
					<div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">{{ $t('jobs_title') }}</label>
            <div class="col-md-7">
              <input v-model="form.title" type="title" name="title" class="form-control"
                :class="{ 'is-invalid': form.errors.has('title') }">
              <has-error :form="form" field="title"/>
            </div>
          </div>

					<!-- References -->
					<div class="form-group row">
						<label class="col-md-3 col-form-label text-md-right">{{ $t('seq_references') }}</label>
						<div class="col-md-7">
							<select v-model="form.references" name="references" class="form-control">
								<option disabled value="">{{ $t('select_one') }}</option>
								<option v-for="opt in form.refopts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
						</div>
					</div>

					<!-- Reads Type -->
					<div class="form-group row">
						<label class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads_type') }}</label>
						<div class="col-md-7">
							<select v-model="form.reads_type" name="reads_type" class="form-control">
								<option v-for="opt in form.reads_type_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
						</div>
					</div>

					<!-- Reads (dynamic) -->
					<div class="form-group row"	style="margin-bottom:0.25rem" v-for="(read, index) in form.reads1">
						<label v-if="index == 0" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads') }}</label>
						<div v-else class="col-md-3"></div>
						<div class="col-md-7">
							<select v-model="read.value" v-bind:id="'reads1.'+index" name="reads1[]" class="form-control">
								<option disabled value="">{{ $t('select_one') }}</option>
								<option v-for="opt in form.reads_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
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
							<label v-if="index == 0" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads2') }}</label>
							<div v-else class="col-md-3"></div>
							<div class="col-md-7">
								<select v-model="read.value" v-bind:id="'reads2.'+index" name="reads2[]" class="form-control">
									<option disabled value="">{{ $t('select_one') }}</option>
									<option v-for="opt in form.reads_opts" v-bind:value="opt.value">
										{{ opt.text }}
									</option>
								</select>
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
						<label class="col-md-3 col-form-label text-md-right">{{ $t('seq_db_annotate') }}</label>
						<div class="col-md-7">
							<select v-model="form.db_annotate" name="reads_type" class="form-control">
								<option v-for="opt in form.db_annotate_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
						</div>
					</div>

					<!-- Seq Mapper -->
					<div class="form-group row">
						<label class="col-md-3 col-form-label text-md-right">{{ $t('seq_mapper') }}</label>
						<div class="col-md-7">
							<select v-model="form.seq_mapper" name="reads_type" class="form-control">
								<option v-for="opt in form.seq_mapper_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
						</div>
					</div>

					<!-- SNP Caller -->
					<div class="form-group row">
						<label class="col-md-3 col-form-label text-md-right">{{ $t('seq_snp_caller') }}</label>
						<div class="col-md-7">
							<select v-model="form.snp_caller" name="reads_type" class="form-control">
								<option v-for="opt in form.snp_caller_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
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
	</div>
</template>

<script>
	import Form from 'vform'

	export default{
		scrollToTop: false,

		metaInfo () {
	    return { title: this.$t('jobs') }
	  },

	  data: () => ({
	    form: new Form({
		      title: '',
		      references: '',
		      refopts: [],
		      reads_type: 'se',
		      reads_type_opts: [
		      	{text: 'Single-End', value: 'se'}, 
		      	{text: 'Paired-End', value: 'pe'}
		      ],
		      reads1: [{value: ''}],
		      reads2: [{value: ''}],
		      reads_opts: [],
		      db_annotate: '',
		      db_annotate_opts: [],
		      seq_mapper: 'bt2',
		      seq_mapper_opts: [],
		      snp_caller: 'sam',
		      snp_caller_opts: []
		    }),
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

		    this.form.refopts = data
		    this.form.reads_opts = data
		    this.form.db_annotate_opts = data
		    this.form.seq_mapper_opts = mapper
		    this.form.snp_caller_opts = caller
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
	  		alert("You clicked!")
	  	}
	  },

	  mounted () {
	  	this.populateOptions();
	  }
	}
</script>