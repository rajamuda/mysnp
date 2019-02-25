<template>
	<div class="row">
		<div v-if="user_status === 1" class="col-lg-12 m-auto">
			<card :title="$t('create_jobs')">
				<!-- {{ form.errors }} -->
				<!-- <alert-error :form="form" message="There were some problems with your input."></alert-error> -->
				<form @submit.prevent="createJobs" @keydown="form.onKeydown($event)">
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
							<select v-model="form.references" name="references" class="custom-select" :class="{ 'is-invalid': form.errors.has('references') }" @change="getDefaultSnpEffDB($event)">
								<option disabled value="">{{ $t('select_one') }}</option>
								<option v-for="opt in refopts" v-bind:value="opt.name">
									{{ opt.name }}
								</option>
							</select>
              <has-error :form="form" field="references"/>
						</div>
					</div>

					<!-- Reads Type -->
					<div class="form-group row">
						<label v-tooltip="'Type of reads, whether it single reads or paired reads'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads_type') }}</label>
						<div class="col-md-7">
							<select v-model="form.reads_type" name="reads_type" class="custom-select" :class="{ 'is-invalid': form.errors.has('reads_type') }">
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
						<div class="col-md-7" :class="{ 'invalid': form.errors.has('reads1') }">
							<multiselect v-model="form.reads1" :options="reads_opts" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" :preserve-search="true" :placeholder="$t('select_min_one')" label="name" track-by="name"></multiselect>
              <has-error :form="form" field="reads1"/>
						</div>
					</div>

					<div v-if="form.reads_type == 'pe'">
						<div class="form-group row">
							<label v-tooltip="'Choose sequence from NGS machine reads. You can add more than one reads. If you have new reads, you can upload in \'Upload\' section'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_reads2') }}</label>
							<div class="col-md-7" :class="{ 'invalid': form.errors.has('reads2') }">
								<multiselect v-model="form.reads2" :options="reads2_opts" :multiple="true" :close-on-select="false" :clear-on-select="false" :hide-selected="true" :preserve-search="true" :placeholder="$t('select_min_one')" label="name" track-by="name"></multiselect>

	              <has-error :form="form" field="reads2"/>
							</div>
						</div>
					</div>

					<!-- Annotation DB -->
					<div class="form-group row">
						<label v-tooltip="'Choose a database to annotate variant'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_db_annotate') }}</label>
						<div class="col-md-7" :class="{ 'invalid': form.errors.has('db_annotate') }">
							<multiselect v-model="form.db_annotate" id="ajax" label="text" track-by="text" :placeholder="$t('search_snpeff')" open-direction="bottom" :options="db_annotate_opts" :multiple="false" :searchable="true" :loading="db_loading" :internal-search="true" :close-on-select="true" :options-limit="300" :limit="3" :max-height="600" :show-no-results="false" @search-change="populateSnpEffDB"></multiselect>
              <has-error :form="form" field="db_annotate"/>
						</div>
					</div>

					<!-- Seq Mapper -->
					<!-- <div class="form-group row">
						<label v-tooltip="'Choose an alignment tools to map references with each reads.'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_mapper') }}</label>
						<div class="col-md-7">
							<select v-model="form.seq_mapper" name="seq_mapper" class="custom-select" :class="{ 'is-invalid': form.errors.has('seq_mapper') }">
								<option v-for="opt in seq_mapper_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="seq_mapper"/>
						</div>
					</div> -->

					<!-- SNP Caller -->
					<div class="form-group row">
						<label v-tooltip="'Choose a variant calling tools to identify variant'" class="col-md-3 col-form-label text-md-right">{{ $t('seq_snp_caller') }}</label>
						<div class="col-md-7">
							<select v-model="form.snp_caller" name="reads_type" class="custom-select" :class="{ 'is-invalid': form.errors.has('snp_caller') }">
								<option v-for="opt in snp_caller_opts" v-bind:value="opt.value">
									{{ opt.text }}
								</option>
							</select>
              <has-error :form="form" field="snp_caller"/>
						</div>
					</div>

					<!-- Advanced -->
					<v-button type="default" native-type="button" class="col-md-12 collapsed" data-toggle="collapse" data-target="#advancedParameters">{{ $t('advanced_param') }} <fa icon="caret-square-down" fixed-width/></v-button>
					<div class="collapse" id="advancedParameters">
					  <div class="card card-body">
							<div v-if="form.errors.has('0')" class="alert alert-danger"> {{ form.errors.get('0') }}</div>
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

							    <!-- Filtering -->
							    <a v-if="form.snp_caller == 'sam'" class="nav-item nav-link" id="nav-vcfutils-tab" data-toggle="tab" href="#nav-vcfutils" role="tab" aria-controls="nav-vcfutils" aria-selected="false">VCFutils (Filtering)</a>
							    <a v-else-if="form.snp_caller == 'gatk'" class="nav-item nav-link" id="nav-picard-tab" data-toggle="tab" href="#nav-picard" role="tab" aria-controls="nav-picard" aria-selected="false">Picard (Filtering)</a>
							    <a v-else>Nothing</a>
							  </div>
							</nav>
							<div class="tab-content" id="nav-tabContent">
							  <div v-if="form.seq_mapper == 'bt2'" class="tab-pane fade show active" id="nav-bt2" role="tabpanel" aria-labelledby="nav-bt2-tab">
							  	<p>Settings for Bowtie2 Aligner (refer to <a href="http://bowtie-bio.sourceforge.net/bowtie2/manual.shtml#command-line" target="_blank">the manual</a>)</p>
	                <strong>Alignment</strong>
	                <br/>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>-N</code> Max # of mismatches in seed alignment <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['-N']" type="text" style="width: 50px;" name="bowtie2[-N]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>-L</code> Length of seed substrings <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['-L']" type="text" style="width: 50px;" name="bowtie2[-L]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>-i</code> Interval between seed substrings w.r.t. read length <i class="params-type">(func)</i><label v-tooltip="'info func'">?</label></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['-i']" type="text" style="width: 70px;" name="bowtie2[-i]"/></div>
	                </div>                
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--n-ceil</code> Function for max # of non-A/C/G/Ts permitted in alignment <i class="params-type">(func)</i><label v-tooltip="'info func'">?</label></div>
	                    <div class="col-md-2"><input type="text" v-model="form.bowtie2['--n-ceil']" style="width: 70px;" name="bowtie2[--n-ceil]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--dpad</code> Include N extra reference chars on sides of DP table <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input type="text" v-model="form.bowtie2['--dpad']" style="width: 50px;" name="bowtie2[--dpad]"/></div>
	                </div>   
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--gbar</code> Disallow gaps within N nucleotides of read extremes <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input type="text" v-model="form.bowtie2['--gbar']" style="width: 50px;" name="bowtie2[--gbar]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--ignore-quals</code> Treat all quality values as 30 on Phred scale</div>
	                    <div class="col-md-2"><input type="checkbox" v-model="form.bowtie2['--ignore-quals']"/></div>
	                </div>                   
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--nofw</code> Do not align forward (original) version of read</div>
	                    <div class="col-md-2"><input type="checkbox" v-model="form.bowtie2['--nofw']"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--norc</code> Do not align reverse-complement version of read</div>
	                    <div class="col-md-2"><input type="checkbox" v-model="form.bowtie2['--norc']"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--local</code> Local alignment; ends might be soft clipped</div>
	                    <div class="col-md-2"><input type="checkbox" v-model="form.bowtie2['--local']"/></div>
	                </div>
	                <div v-if="form.reads_type == 'pe'" id="paired-params">
                    <strong>Paired-end</strong>
                    <br/>
                    <div class="row params-set">
                        <div class="col-md-10"><code>-I</code> Minimum fragment length <i class="params-type">(int)</i></div>
                        <div class="col-md-2"><input v-model="form.bowtie2['-I']" type="text" style="width: 50px;" name="bowtie2[-I]"/></div>
                    </div>
                    <div class="row params-set">
                        <div class="col-md-10"><code>-X</code> Maximum fragment length <i class="params-type">(int)</i></div>
                        <div class="col-md-2"><input v-model="form.bowtie2['-X']" type="text" style="width: 50px;" name="bowtie2[-X]"/></div>
                    </div>
                    <div class="row params-set">
                        <div class="col-md-10"><code>--no-mixed</code> Suppress unpaired alignments for paired reads</div>
                        <div class="col-md-2"><input v-model="form.bowtie2['--no-mixed']" type="checkbox"/></div>
                    </div>                    
                    <div class="row params-set">
                        <div class="col-md-10"><code>--no-discordant</code> Suppress discordant alignments for paired reads</div>
                        <div class="col-md-2"><input v-model="form.bowtie2['--no-discordant']" type="checkbox"/></div>
                    </div>                    
                    <div class="row params-set">
                        <div class="col-md-10"><code>--no-dovetail</code> Not concordant when mates extend past each other</div>
                        <div class="col-md-2"><input v-model="form.bowtie2['--no-dovetail']" type="checkbox"/></div>
                    </div>                    
                    <div class="row params-set">
                        <div class="col-md-10"><code>--no-contain</code> Not concordant when one mate alignment contains other</div>
                        <div class="col-md-2"><input v-model="form.bowtie2['--no-contain']" type="checkbox"/></div>
                    </div>                    
                    <div class="row params-set">
                        <div class="col-md-10"><code>--no-overlap</code> Not concordant when mates overlap at all</div>
                        <div class="col-md-2"><input v-model="form.bowtie2['--no-overlap']" type="checkbox"/></div>
                    </div>                    
	                </div>                
	                <strong>Scoring</strong>
	                <br/>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--ma</code> Match bonus <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['--ma']" type="text" style="width: 50px;" name="bowtie2[--ma]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--mp</code> Max penalty for mismatch <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['--mp']" type="text" style="width: 50px;" name="bowtie2[--mp]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--np</code> Penalty for non-A/C/G/Ts in read/reference <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['--np']" type="text" style="width: 50px;" name="bowtie2[--np]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--rdg</code> Read gap open, extend penalties <i class="params-type">(int,int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['--rdg']" type="text" style="width: 50px;" name="bowtie2[--rdg]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--rfg</code> Reference gap open, extend penalties <i class="params-type">(int,int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['--rfg']" type="text" style="width: 50px;" name="bowtie2[--rfg]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>--score-min</code> Minimum acceptable alignment score w.r.t. read length <i class="params-type">(func)</i><label v-tooltip="'info func'">?</label></div>
	                    <div class="col-md-2"><input type="text" v-model="form.bowtie2['--score-min']" style="width: 70px;" name="bowtie2[--score-min]"/></div>
	                </div>
	                <strong>Effort</strong>
	                <br/>         
	                <div class="row params-set">
	                    <div class="col-md-10"><code>-D</code> Give up extending after N failed extends in a row <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['-D']" type="text" style="width: 50px;" name="bowtie2[-D]"/></div>
	                </div>
	                <div class="row params-set">
	                    <div class="col-md-10"><code>-R</code> For reads with repetitive seeds, try N sets of seeds <i class="params-type">(int)</i></div>
	                    <div class="col-md-2"><input v-model="form.bowtie2['-R']" type="text" style="width: 50px;" name="bowtie2[-R]"/></div>
	                </div>          
								</div>
							  <div v-else-if="form.seq_mapper == 'bwa'" class="tab-pane fade show active" id="nav-bwa" role="tabpanel" aria-labelledby="nav-bwa-tab">
							  	<p>Settings for BWA Aligner (refer to <a href="http://bio-bwa.sourceforge.net/bwa.shtml" target="_blank">the manual</a>)</p>
	                <strong>Alignment</strong>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-n</code> Maximum edit distance <i class="params-type">(float)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-n']" type="text" style="width: 50px;" name="bwa_aln[-n]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-o</code> Maximum number of gap opens <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-o']" type="text" style="width: 50px;" name="bwa_aln[-o]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-e</code> Maximum number of gap extension <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-e']" type="text" style="width: 50px;" name="bwa_aln[-e]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-d</code> Number of deletion tail <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-d']" type="text" style="width: 50px;" name="bwa_aln[-d]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-i</code> Number of disallow indel towards the ends <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-i']" type="text" style="width: 50px;" name="bwa_aln[-i]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-k</code> Maximum edit distance in the seed <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-k']" type="text" style="width: 50px;" name="bwa_aln[-k]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-l</code> Subsequence length for seed <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-l']" type="text" style="width: 50px;" name="bwa_aln[-l]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-t</code> Number of threads <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-t']" type="text" style="width: 50px;" name="bwa_aln[-t]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-M</code> Mismatch penalty <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-M']" type="text" style="width: 50px;" name="bwa_aln[-M]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-O</code> Gap open penalty <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-O']" type="text" style="width: 50px;" name="bwa_aln[-O]"/></div>
	                </div>
	                <div class="row params-set">
	                	<div class="col-md-10"><code>-E</code> Gap extension penalty <i class="params-type">(int)</i></div>
	                	<div class="col-md-2"><input v-model="form.bwa.aln['-E']" type="text" style="width: 50px;" name="bwa_aln[-E]"/></div>
	                </div>
	                <br/>
	                <div v-if="form.reads_type == 'pe'">
	                	<strong>Paired-end reads</strong>
	                	<br/>
	                	<div class="row params-set">
		                	<div class="col-md-10"><code>-a</code> Maximum insert size to be considered being mapped <i class="params-type">(int)</i></div>
		                	<div class="col-md-2"><input v-model="form.bwa.sampe['-a']" type="text" style="width: 50px;" name="bwa_sampe[-a]"/></div>
		                </div>
		                <div class="row params-set">
		                	<div class="col-md-10"><code>-o</code> Maximum occurrences of a read for pairing <i class="params-type">(int)</i></div>
		                	<div class="col-md-2"><input v-model="form.bwa.sampe['-o']" type="text" style="width: 70px;" name="bwa_sampe[-o]"/></div>
		                </div>
	              	</div>
							  </div>
							  <div 	v-else-if="form.seq_mapper == 'novo'" class="tab-pane fade show active" id="nav-novo" role="tabpanel" aria-labelledby="nav-novo-tab">...</div>
							  <div v-else>Nothing</div>

							  <div v-if="form.snp_caller == 'sam'" class="tab-pane fade" id="nav-sam" role="tabpanel" aria-labelledby="nav-sam-tab">
							  	<p>Settings for Bcftools Variant Calling (<i>mpileup</i>). Refer to <a href="http://www.htslib.org/doc/bcftools.html#mpileup" target="_blank">Manual</a></p>
									<strong>Input options</strong>
									<br/>
									<div class="row params-set">
									    <div class="col-md-10"><code>-A</code> Count anomalous read pairs</div>
									    <div class="col-md-2"><input v-model="form.samtools['-A']" type="checkbox" name="samtools[-A]"/></div>
									</div>                
									<div class="row params-set">
									    <div class="col-md-10"><code>-B</code> Disable BAQ computation</div>
									    <div class="col-md-2"><input v-model="form.samtools['-B']" type="checkbox" name="samtools[-B]"/></div>
									</div>                
									<div class="row params-set">
									    <div class="col-md-10"><code>-R</code> Ignore RG tags</div>
									    <div class="col-md-2"><input v-model="form.samtools['-R']" type="checkbox" name="samtools[-R]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-C</code> Parameter for adjusting mapQ; 0 to disable <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-C']" type="text" style="width: 50px;" name="samtools[-C]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-M</code> Cap mapping quality at <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-M']" type="text" style="width: 50px;" name="samtools[-M]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-q</code> Skip alignments with mapQ smaller than <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-q']" type="text" style="width: 50px;" name="samtools[-q]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-Q</code> Skip bases with baseQ/BAQ smaller than <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-Q']" type="text" style="width: 50px;" name="samtools[-Q]"/></div>
									</div>
									<strong>SNP/INDEL genotype likelihoods options</strong>
									<br/>
									<div class="row params-set">
									    <div class="col-md-10"><code>-I</code> Do not perform indel calling</div>
									    <div class="col-md-2"><input v-model="form.samtools['-I']" type="checkbox" name="samtools[-I]"/></div>
									</div>                
									<div class="row params-set">
									    <div class="col-md-10"><code>-e</code> Phred-scaled gap extension seq error probability <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-e']" type="text" style="width: 50px;" name="samtools[-e]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-F</code> Minimum fraction of gapped reads for candidates <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-F']" type="text" style="width: 50px;" name="samtools[-F]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-h</code> Coefficient for homopolymer errors <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-h']" type="text" style="width: 50px;" name="samtools[-h]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-L</code> Max per-sample depth for INDEL calling <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-L']" type="text" style="width: 50px;" name="samtools[-L]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-m</code> Minimum gapped reads for indel candidates <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-m']" type="text" style="width: 50px;" name="samtools[-m]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-o</code> Phred-scaled gap open sequencing error probability <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-o']" type="text" style="width: 50px;" name="samtools[-o]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-P</code> Comma separated list of platforms for indels <i class="params-type">(string)</i></div>
									    <div class="col-md-2"><input v-model="form.samtools['-P']" type="text" style="width: 90px;" name="samtools[-P]"/></div>
									</div>
							  </div>
							  <div v-else-if="form.snp_caller == 'gatk'" class="tab-pane fade" id="nav-gatk" role="tabpanel" aria-labelledby="nav-gatk-tab">
							  	<p>Settings for GATK variant calling (HaplotypeCaller). Read the <a href="https://software.broadinstitute.org/gatk/documentation/tooldocs/3.6-0/org_broadinstitute_gatk_tools_walkers_haplotypecaller_HaplotypeCaller.php" target="_blank">Manual</a></p>
							  	<input v-model="form.gatk['snp_only']" type="checkbox" name="gatk[snp_only]"/> Extract SNPs only <br>
							  	<strong>Parameters options</strong>
							  	<br/>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>-contamination</code> Fraction of contamination in sequencing data to remove <i class="params-type">(float)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['-contamination']" type="text" style="width: 50px;" name="gatk[-contamination]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>-hets</code> Heterozygosity value used to compute prior likelihoods for any locus <i class="params-type">(float)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['-hets']" type="text" style="width: 50px;" name="gatk[-hets]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>-indelHeterozygosity</code> Heterozygosity for indel calling <i class="params-type">(float)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['-indelHeterozygosity']" type="text" style="width: 70px;" name="gatk[-indelHeterozygosity]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>--maxReadsInRegionPerSample</code> Maximum reads in an active region <i class="params-type">(int)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['--maxReadsInRegionPerSample']" type="text" style="width: 70px;" name="gatk[--maxReadsInRegionPerSample]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>--min_base_quality_score</code> Minimum base quality to consider a base for calling <i class="params-type">(int)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['--min_base_quality_score']" type="text" style="width: 50px;" name="gatk[--min_base_quality_score]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>--minReadsPerAlignmentStart</code> Minimum number of reads sharing the same alignment start for each genomic location in an active region <i class="params-type">(int)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['--minReadsPerAlignmentStart']" type="text" style="width: 50px;" name="gatk[--minReadsPerAlignmentStart]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>-ploidy</code> Ploidy (number of chromosomes) per sample <i class="params-type">(int)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['-ploidy']" type="text" style="width: 50px;" name="gatk[-ploidy]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>-stand_call_conf</code> The minimum phred-scaled confidence threshold at which variants should be called <i class="params-type">(float)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['-stand_call_conf']" type="text" style="width: 50px;" name="gatk[-stand_call_conf]"></div>
							  	</div>
							  	<div class="row params-set">
							  		<div class="col-md-10"><code>-stand_emit_conf</code> The minimum phred-scaled confidence threshold at which variants should be emitted <i class="params-type">(float)</i></div>
							  		<div class="col-md-2"><input v-model="form.gatk['-stand_emit_conf']" type="text" style="width: 50px;" name="gatk[-stand_emit_conf]"></div>
							  	</div>
							  </div>
							  <div v-else>Nothing</div>

							  <div v-if="form.snp_caller == 'sam'" class="tab-pane fade" id="nav-vcfutils" role="tabpanel" aria-labelledby="nav-vcfutils-tab">
							  	<p>Settings for VCFutils SNP Filtering (<i>varFilter</i>)</p>
									<strong>varFilter options</strong>
									<br/>            
									<div class="row params-set">
									    <div class="col-md-10"><code>-Q</code> Minimum RMS mapping quality for SNPs <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.vcfutils['-Q']" type="text" style="width: 50px;" name="vcfutils[-Q]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-d</code> Minimum read depth <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.vcfutils['-d']" type="text" style="width: 50px;" name="vcfutils[-d]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-D</code> Maximum read depth <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.vcfutils['-D']" type="text" style="width: 80px;" name="vcfutils[-D]"/></div>
									</div>
									<div class="row params-set">
									    <div class="col-md-10"><code>-a</code> Minimum number of alternate bases <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.vcfutils['-a']" type="text" style="width: 50px;" name="vcfutils[-a]"/></div>
									</div>                
									<div class="row params-set">
									    <div class="col-md-10"><code>-w</code> SNP within N base pairs around a gap to be filtered <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.vcfutils['-w']" type="text" style="width: 50px;" name="vcfutils[-w]"/></div>
									</div>                
									<div class="row params-set">
									    <div class="col-md-10"><code>-W</code> Window size for filtering adjacent gaps <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.vcfutils['-W']" type="text" style="width: 50px;" name="vcfutils[-W]"/></div>
									</div>  
							  </div>
							  <div v-else-if="form.snp_caller == 'gatk'" class="tab-pane fade" id="nav-picard" role="tabpanel" aria-labelledby="nav-picard-tab">
							  	<p>Settings for Picard Variant Filtering (<i>FilterVcf</i>). Read the <a href="http://broadinstitute.github.io/picard/command-line-overview.html#FilterVcf" target="_blank">Manual</a></p>
									<strong>FilterVcf options</strong>
									<br/>
									<div class="row params-set">
									    <div class="col-md-10"><code>MIN_AB</code> Minimum allele balance. <i class="params-type">(float)</i></div>
									    <div class="col-md-2"><input v-model="form.picard['MIN_AB']" type="text" style="width: 50px;" name="picard[MIN_AB]"/></div>
									</div> 
									<div class="row params-set">
									    <div class="col-md-10"><code>MIN_DP</code> Minimum sequencing depth <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.picard['MIN_DP']" type="text" style="width: 50px;" name="picard[MIN_DP]"/></div>
									</div> 
									<div class="row params-set">
									    <div class="col-md-10"><code>MIN_GQ</code> Minimum genotype quality <i class="params-type">(int)</i></div>
									    <div class="col-md-2"><input v-model="form.picard['MIN_GQ']" type="text" style="width: 50px;" name="picard[MIN_GQ]"/></div>
									</div> 
									<div class="row params-set">
									    <div class="col-md-10"><code>MAX_FS</code> Maximum phred scaled fisher strand value <i class="params-type">(float)</i></div>
									    <div class="col-md-2"><input v-model="form.picard['MAX_FS']" type="text" style="width: 90px;" name="picard[MAX_FS]"/></div>
									</div> 
									<div class="row params-set">
									    <div class="col-md-10"><code>MIN_QD</code> Minimum QD value <i class="params-type">(float)</i></div>
									    <div class="col-md-2"><input v-model="form.picard['MIN_QD']" type="text" style="width: 50px;" name="picard[MIN_QD]"/></div>
									</div>  
							  </div>
							  <div v-else>Nothing</div>

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
		<div v-else class="col-lg-12 m-auto">
			<p class="alert alert-warning">
      	Your account is not activated yet. Wait for admin to activate your account or send an e-mail to us.          
      </p>
		</div>
	</div>
</template>

<script>
	import Form from 'vform'
	import axios from 'axios'
	import Vue from 'vue'
	import VTooltip from 'v-tooltip'
  import Multiselect from 'vue-multiselect'
  import 'vue-multiselect/dist/vue-multiselect.min.css'

	Vue.use(VTooltip)
	Vue.component('multiselect', Multiselect)

	export default{
		scrollToTop: false,

		metaInfo () {
	    return { title: this.$t('create_jobs') }
	  },

	  data: () => ({
	    form: new Form({
		      title: '',
		      references: '',
		      reads_type: 'se',
		      reads1: [],
		      reads2: [],
		      bowtie2: window.config.defaultParams.bowtie2,
		      samtools: window.config.defaultParams.samtools,
		      vcfutils: window.config.defaultParams.vcfutils,
		      bwa: window.config.defaultParams.bwa,
		      gatk: window.config.defaultParams.gatk,
		      picard: window.config.defaultParams.picard,
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
      reads2_opts: [],
	    db_annotate_opts: [],
	    seq_mapper_opts: [],
	    snp_caller_opts: [],
	    user_status: 1,
	    db_loading: false,
	  }),

	  methods: {
	  	async populateOptions () {

		    const {data} = await axios.get('/api/data/sequences');

		    const mapper = [
		    	{ text: 'Bowtie2', value: 'bt2' },
		    	{ text: 'BWA', value: 'bwa' }
		    ]

		    const caller = [
		    	{ text: 'Bcftools (Samtools)', value: 'sam' },
		    	// { text: 'GATK', value: 'gatk' }
		    ]

		    this.refopts = data.refs
		    this.reads_opts = data.reads
		    this.reads2_opts = data.reads2
		    this.seq_mapper_opts = mapper
		    this.snp_caller_opts = caller
	  	},

	  	populateSnpEffDB (query) {
	  		this.db_loading = true
	  		axios.get('/api/data/snpeff?query='+query)
	  			.then(({data}) => {
	  				this.db_annotate_opts = data
	  				this.db_loading = false
	  			})
	  			.catch(e => {
	  				console.error(e)
	  			})
	  	},

			getDefaultSnpEffDB (query) {
				let refseq_name = query.target.value

				axios.get('/api/data/default-snpeff/'+refseq_name)
					.then(({data}) => {
						this.form.db_annotate = {text: data.default_snpeffdb, value: data.default_snpeffdb}
					})
					.catch(e => {
	  				console.error(e)
	  			})
			},

	  	createJobs () {
	  		console.log('creating jobs')
	  		this.form.post('/api/jobs/create')
	  			.then(({data}) => {
	  				console.log(data)
	  				const id = data.job_id
	  				this.$router.push({name: 'jobs.process', params: {id}})
	  			})
	  			.catch((e) => {
	  				console.error(e)
	  			})
	  	},

      onSelectReads1 (items, lastSelectItem) {
        this.form.reads1 = items
      },

      onSelectReads2 (items, lastSelectItem) {
        this.form.reads2 = items
      },

      checkStatus () {
      	axios.get('/api/user/activation_status')
      		.then(({data}) => {
      			this.user_status = data.status
      		})
      		.catch((e) => {
      			console.error(e)
      		})
      }
	  },

	  mounted () {
	  	this.checkStatus();
	  	this.populateOptions();
	  },
	}
</script>

<style>
	.help-block.invalid-feedback {
		display: block !important;
	}

	.invalid .multiselect > .multiselect__tags {
		border-color: #dc3545;
	}
</style>

<style scoped>
	code{
		margin-right: 2px;
		padding: 2px 10px 2px 10px;
		border-radius: 5px;
		background-color: #f7f9fb;
		box-shadow: inset 0 0 1px 1px #ccc;
	}

	.params-set{
		margin-bottom: 0.5rem;
	}

	.params-type{
		font-family: monospace;
		font-size: 10px;
	}

	.tab-pane{
		margin: 10px 20px 10px 20px;
	}
</style>