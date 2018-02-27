<template>
	<card :title="'Progress of \'' + job.title +'\''">
		<table v-if="job">
			<tr>
				<td>{{ $t('status') }}</td>
				<td class="colon"> : </td>
				<td>{{ job.status }}</td>
			</tr>
			<tr>
				<td>{{ $t('seq_mapper') }}</td>
				<td class="colon"> : </td>
				<td>{{ job.mapper }}</td>
			</tr>
			<tr>
				<td>{{ $t('seq_snp_caller') }}</td>
				<td class="colon"> : </td>
				<td>{{ job.caller }}</td>
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
			<tr v-if='progress < 100'>
				<td>Current progress</td>
    		<td class="colon"> : </td>
				<td v-if='job_process.length > 0'>{{ $t(job_process[job_process.length-1].process) }}</td>
			</tr>
		</table>
		<div class="progress">
			<div class="progress-bar progress-bar-striped bg-success" role="progressbar" :style="{width: progress + '%'}" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"> {{ progress }}% </div>
		</div>
		<
		<!-- {{ job }} -->
		<div v-for="(jp, index) in job_process" v-if="jp.process != 'storing_to_db'">
			<div class="card border-primary mb-1 mt-3">			
			  <div class="card-header text-white bg-primary">{{ $t(jp.process) }}</div>
			  <div class="card-body text-dark">
			  	<div class="process-status">
				    <table>
				    	<tr>
				    		<td>{{ $t('status' )}}</td>
				    		<td class="colon"> : </td>
				    		<td>{{ jp.status }}</td>
				    	</tr>
				    	<tr>
				    		<td>{{ $t('start_date') }}</td>
				    		<td class="colon"> : </td>
				    		<td>{{ jp.submitted_at }}</td>
				    	</tr>
				    	<tr>
				    		<td>{{ $t('finish_date') }}</td>
				    		<td class="colon"> : </td>
				    		<td>{{ jp.finished_at }}</td>
				    	</tr>
				    	<tr>
				    		<td>{{ $t('output_file') }}</td>
				    		<td class="colon"> : </td>
				    		<td><a :href="'/dl/process_output/' + jp.id" target="_blank">{{ baseName(jp.output) }}</a> ({{ jp.file_size }})</td>
				    	</tr>
				    </table>
			  	</div>
			  	<v-button type="secondary" native-type="button" class="btn-sm col-md-12 collapsed" data-toggle="collapse" :data-target="'#outputMessage' + index">{{ $t('output_message') }} <fa icon="caret-square-down" fixed-width/></v-button>
					<div class="collapse" :id="'outputMessage' + index">
						<div class="output-message">
			    		<pre><code>{{ jp.program_message }}</code></pre>
			    	</div>
			    </div>
			  </div>
			</div>
		</div>
	</card>
</template>

<script>
	import Vue from 'vue'
	import VueSocketio from 'vue-socket.io'
	import axios from 'axios'

	Vue.use(VueSocketio, 'http://localhost:4200')
	
	export default{
		scrollToTop: false,

		sockets:{
	    connect: function(){
	      console.log('socket connected')
	    },
	    stream: function(val){
	      // console.log('this method was fired by the socket server. eg: io.emit("customEmit", data)')
	      console.log(val)
	    	// this.$socket.emit('stream', 1)
	    	if(val.message != '0'){
		    	this.socket_progress = val.message.split('\n').slice(0,-1);
		    	this.progress = parseInt(this.socket_progress[this.socket_progress.length-1].split(';')[0]);
		    	this.checkProgress();
		    }else{
		    	this.progress = 0;
		    }
	    }
	  },

		data () {
			return{
				socket_progress: '',
				progress: 0,
				job: {},
				job_process: [],
			}
		},

		methods: {
			async checkProgress (refreshed = false) {
				try{
					const { data } = await axios.get('/api/jobs/1/process')
					const maxProcess = window.config.processType.length
					if(refreshed == true){
						if(data.process.length == maxProcess-1 && data.process[data.process.length-1].status == 'FINISHED'){
							this.progress = 100
						}else{
							this.progress = parseInt((data.process.length/maxProcess)*100)
						}
					
						this.$socket.emit('join', 1)
					}

					this.job = data.job;
					this.job_process = data.process;
				}catch(e){
					console.error(e);
				}
			},

			baseName (str) {
				// if(str == null) return
				var base = new String(str).substring(str.lastIndexOf('/') + 1); 
		   	return base;
			}

		},

		mounted () {
			// this.$socket.emit('join', 1)
			this.checkProgress(true)
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