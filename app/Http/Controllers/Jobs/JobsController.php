<?php

namespace App\Http\Controllers\Jobs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use WriteiniFile\WriteiniFile;
use Illuminate\Support\Facades\DB;

use App\Job;
use App\Process;
use App\Sequence;

class JobsController extends Controller
{

    /** 
    | Router Specific Controller
    |--------------------------------------
    | snpEffDB          : return JSON containing SnpEff DB
    | create            : insert job submitted by user, validate each input, insert into DB
    | update            : update parameters if current job status is CANCELED or FINISHED
    | getJobs           : get all jobs by current user
    | getJobProcessById : get all processes from current job
    | cancelJobProcess  : cancel current running job
    | resumeJobProcess  : resume current canceled job
    **/

    // get sequences
    public function getSequences(Request $request){
        $user_id = $request->user()->id;

        $refs = Sequence::where(function($q) use($user_id) { $q->where('user_id', $user_id)->orWhere('public', true); })->where('type', 'references')->get(['name']);

        $reads = Sequence::where(function($q) use($user_id) { $q->where('user_id', $user_id)->orWhere('public', true); })->where('type', 'reads')->get(['name']);

        $reads_pair = Sequence::where(function($q) use($user_id) { $q->where('user_id', $user_id)->orWhere('public', true); })->where('type', 'reads_pair')->get(['name']);

        return ['refs' => $refs, 'reads' => $reads, 'reads2' => $reads_pair];
    }

    // get snpeff DB
    public function snpEffDB(Request $request){
        header('Content-Type: application/json');

        $db = config('app.toolsDir.snpeff')."/snpEff.database";
        $query = $request->query('query');

        $count = 0;
        if(file_exists($db)){
            $handle = fopen($db, 'r');
            echo "[";
            while(($line = fgets($handle)) !== false){
                $data = explode(";", $line);

                $data[1] = substr($data[1],0,-1);

                if(stripos($data[1].$data[0], $query) !== false){
                    if($count++ != 0) echo ",";
                    if($data[0] == $data[1]){
                        echo json_encode(['text' => $data[0], 'value' => $data[0]]);
                    }else{
                        echo json_encode(['text' => $data[1]." (".$data[0].")", 'value' => $data[0]]);
                    }
                }
            }
            echo "]";
        }
        

    }

    // create new jobs
    public function create(Request $request)
    {
    	$user = $request->user();
    	
    	$this->validate($request, [
            'title' => 'required',
            'references' => 'required',
            'reads_type' => 'required|in:se,pe',
            'reads1' => 'required',
            'reads2' => 'required_if:reads_type,==,pe',
            'db_annotate' => 'required',
            'seq_mapper' => 'required|in:bt2,bwa',
            'snp_caller' => 'required|in:sam,gatk',
        ]);

    	/*
    	| Variable Information
    	|------------------------------------------------------
		| Title 			: $input['title']
		| References		: $input['references']
		| Reads Type		: $input['reads_type']
		| Reads				: $input['reads1'][~index~]['name']
		| Reads 2 (pair)	: $input['reads2'][~index~]['name']
		| Annotation DB		: $input['db_annotate']['value']
		| Alignment Tools	: $input['seq_mapper']
		| SNP Caller		: $input['snp_caller']
		| -----------------------------------------------------
		| To Do: list array of parameters settings
		|
    	*/
    	$input = $request->all();
        // print_r($input);die();
        $defaultParams = config('app.defaultParams');
        
        /*
        | Processing jobs request
        | - list changed parameters
        | - create INI file contain information about jobs (tools used and parameters)
        | NEXT: validate each input (security reason! IMPORTANT)
        */

        if($input['seq_mapper'] == 'bt2'){ // Bowtie2
            $this->validateSubmittedParams($input['bowtie2'],'bowtie2');

            $mapperOptions = "";

            if($input['reads_type'] == 'pe') $mapperOptions .= "--fr "; // forward-reverse for paired-end reads

            $getParams = $input['bowtie2'];
            $bowtie2DefaultParams = $defaultParams['bowtie2'];

            // list each changed params
            foreach($bowtie2DefaultParams as $key => $value){
                if($bowtie2DefaultParams[$key] != $getParams[$key]){
                    if($bowtie2DefaultParams[$key] === false){
                        $mapperOptions .= "$key ";
                    }else{
                        $mapperOptions .= "$key $getParams[$key] ";
                    }
                }
            }

        }else if($input['seq_mapper'] == 'bwa'){ // BWA
            $this->validateSubmittedParams($input['bwa'],'bwa');

            $mapperOptions = [
                'aln' => "",
                'sampe' => "",
            ];
            $getParams = $input['bwa'];
            $bwaDefaultParams = $defaultParams['bwa'];


            // list each changed params
            // 1 - bwa aln
            foreach($bwaDefaultParams['aln'] as $key => $value){
                if($bwaDefaultParams['aln'][$key] != $getParams['aln'][$key]){        
                    $mapperOptions['aln'] .= "$key ".$getParams['aln'][$key]." ";                    
                }
            }

            // 2 - bwa sampe (if paired ends)
            foreach($bwaDefaultParams['sampe'] as $key => $value){
                if($bwaDefaultParams['sampe'][$key] != $getParams['sampe'][$key]){        
                    $mapperOptions['sampe'] .= "$key ".$getParams['sampe'][$key]." ";                    
                }
            }

        }

        if($input['snp_caller'] == 'sam'){ // Samtools (BCFtools)  
            $this->validateSubmittedParams($input['samtools'],'samtools');
            $this->validateSubmittedParams($input['vcfutils'],'vcfutils');

            $callerOptions = "";
            $filterOptions = "";

            // caller option (samtools/bcftools)
            $getParams = $input['samtools'];
            $samtoolsDefaultParams = $defaultParams['samtools'];

            // list each changed params
            foreach($samtoolsDefaultParams as $key => $value){
                if($samtoolsDefaultParams[$key] != $getParams[$key]){
                    if($key == "-I") continue;

                    if($samtoolsDefaultParams[$key] === false){
                        $callerOptions .= "$key ";
                    }else{
                        $callerOptions .= "$key $getParams[$key] ";
                    }
                }
            }
            if($getParams['-I'] === true){ // if perform SNP calling only
                $callerOptions .= "-I ";
            }

            // filter option (vcfutils)
            $getParams = $input['vcfutils'];
            $vcfutilsDefaultParams = $defaultParams['vcfutils'];

            foreach($vcfutilsDefaultParams as $key => $value){
                if($vcfutilsDefaultParams[$key] != $getParams[$key]){
                    $filterOptions .= "$key $getParams[$key] ";
                }
            }


        }else{ // GATK (Picard)
            $this->validateSubmittedParams($input['gatk'],'gatk');
            $this->validateSubmittedParams($input['picard'],'picard');

            $callerOptions = [
                'caller' => '',
                'type' => 'snp',
            ];
            $filterOptions = "";

            // caller options (gatk haplotypecaller)
            $getParams = $input['gatk'];
            $gatkDefaultParams = $defaultParams['gatk'];

            // list each changed params
            foreach($gatkDefaultParams as $key => $value){
                if($key === "snp_only") continue;
                if($gatkDefaultParams[$key] != $getParams[$key]){
                    $callerOptions['caller'] .= "$key $getParams[$key] ";                    
                }
            }
            if($getParams['snp_only'] === false) $callerOptions['type'] = "all";

            // filter option (picard)
            $getParams = $input['picard'];
            $picardDefaultParams = $defaultParams['picard'];

            foreach($picardDefaultParams as $key => $value){
                if($picardDefaultParams[$key] != $getParams[$key]){
                    $filterOptions .= "$key=$getParams[$key] ";
                }
            }
        }

        // $snphyloOptions = "";

        $reads1 = [];
        foreach($input['reads1'] as $r){
            array_push($reads1, $r['name']);
        }

        $reads2 = [];
        foreach($input['reads2'] as $r){
            array_push($reads2, $r['name']);
        }

        $data = [
            'config' => [
                'title' => $input['title'],
                'references' => $input['references'],
                'reads1' => implode(',', $reads1),
                'reads2' => implode(',', $reads2),
                'db_snp' => Sequence::select('dbSnp')->where('name', $input['references'])->first()->dbSnp, // ?? $input['db_snp']
                'annotatedb' => $input['db_annotate']['value'],
                'mapper' => $input['seq_mapper'],
                'caller' => $input['snp_caller'],
                'mapper_options' => $mapperOptions,
                'caller_options' => $callerOptions,
                'filter_options' => $filterOptions,
            ]
        ];


        DB::beginTransaction();
        try{
            // perform sql query to submit jobs and get id of jobs to create dir for jobs INI file.
            DB::table('jobs')->insert([
                [
                    'title' => $input['title'],
                    'user_id' => $request->user()->id,
                    'mapper' => $input['seq_mapper'],
                    'caller' => $input['snp_caller'],
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'refseq_id' => Sequence::select('id')->where('name', $input['references'])->first()->id
                ]
            ]);

            $job_id = DB::getPdo()->lastInsertId();

            $jobsDir = config('app.jobsDir')."/".$job_id;
            
            $old = umask(0);
            if(mkdir($jobsDir, 0777)){
                umask($old);
                $ini = new WriteiniFile("$jobsDir/job.ini");
                $ini->create($data);
                $ini->write();
            }

            DB::commit();
            
            return ["status" => true, "message"=> "Job submitted", "job_id" => $job_id];
          // return json_encode($input);
        }catch(\Exception $e){
            DB::rollback();
            return ["status" => false, "message"=> $e];        
        }
        // return "benar";
    }

    // update submitted and unprocessed jobs
    public function update(Request $request){
        // TODO
    }

    public function getJobs(Request $request){
        return Job::where('user_id', $request->user()->id)->orderBy('submitted_at', 'DESC')->get();
    }

    public function getJobProcessById(Request $request){
        try{
            $job = Job::where('id', $request->id)->where('user_id', $request->user()->id)->firstOrFail();

            $job->mapper = config('app.toolsAlias')[$job->mapper];
            $job->caller = config('app.toolsAlias')[$job->caller];

            if(strpos($job->status, "RUNNING") !== false) $job->status = "RUNNING";

            foreach($job->process as $process){
                $process->output = config('app.rootDir')."/".$process->output;
                $process->file_size = exec("du -h '{$process->output}' | awk '{print $1}'");
                $process->program_message = shell_exec("cat '".dirname($process->output)."/message.out'") ?? "EMPTY";   
            }

            $job_config = parse_ini_file(config('app.jobsDir')."/".$job->id."/job.ini");
            $config_txt = "";

            $config_txt .= "<b><u>Sequences</u></b><br/>";
            $config_txt .= "Read(s) to identify SNP: <code>".$job_config['reads1']."</code>";

            if($job_config['reads2'] != ""){
                $config_txt .= " -- paired with <code>".$job_config['reads2']."</code>";
            }

            $config_txt .="<br/>Reference Sequence: <code>".$job_config['references']."</code><br/>";

            $config_txt .= "<br/><b><u>Tools Configuration</u></b><br/>";
            if($job_config['mapper'] == 'bt2'){ // Bowtie2
                $config_txt .= "$job->mapper: <code>'".$job_config['mapper_options']."'</code><br/>";
            }else{ // BWA

            }

            if($job_config['caller'] == 'sam'){ // Bcftools
                $config_txt .= "$job->caller: <code>'".$job_config['caller_options']."'</code><br/>";
                $config_txt .= "VCFutils VarFilter: <code>'".$job_config['filter_options']."'</code>";
            }else{ // GATK

            }

            return ['job' => $job, 'process' => $job->process, 'config' => $config_txt];

        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            abort(403, 'Forbidden Access');
        }catch(\Exception $e){
            abort(500, 'Error(s) Occured with '.$e->getMessage());
        }
    }
    
    public function cancelJobProcess(Request $request){
        try{
            $job = Job::findOrFail($request->id)->where('user_id', $request->user()->id)->first();

            if($job){
                if($job->status == 'WAITING'){
                    $job->status = "CANCELED";
                    $job->save();
                }
                
                if(substr($job->status,0,7) == 'RUNNING'){
                    foreach($job->process as $process){
                        if(substr($job->status, 9) == $process->process){
                            JobProcessController::killProcess($process->pid);
                            $process->status = "CANCELED";
                            $process->save();
                        }
                    } 
                    $job->status = "CANCELED";
                    // if(!$job->save()){
                    //     abort(500, 'Something went wrong');
                    // } 
                    $job->save();
                }
                // return $job;
            }else{
                abort(403, 'Forbidden Access');
            }

        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            abort(403, 'Forbidden Access');
        }catch(\Exception $e){
            abort(500, 'Error(s) Occured with '.$e->getMessage());
        }
    }

    public function resumeJobProcess(Request $request){
        try{
            $job = Job::findOrFail($request->id)->where('user_id', $request->user()->id)->first();

            if($job){
                if($job->status == 'CANCELED'){

                    /* METHOD 1*/
                    $process_elapsed = count($job->process);
                    if($process_elapsed > 1){
                        $prev_process = $job->process[$process_elapsed-2]->process;
                    }else{
                        $prev_process = config('app.process')[1]; // mapping
                    }

                    // Destroy canceled process (remove from DB);
                    Process::destroy($job->process[$process_elapsed-1]->id);

                    /* METHOD 2 (permission issue) */
                    // $processID = array_search($current_process, config('app.process'));
                    // $job_process = new JobProcessController($job->id);
                    // $job_process->start($processID);
                    if($prev_process == 'mapping')
                        $job->status = "WAITING";
                    else
                        $job->status = "RUNNING: $prev_process";
                    $job->save();
                }
            }else{
                abort(403, 'Forbidden Access');
            }
            
        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            abort(403, 'Forbidden Access');
        }catch(\Exception $e){
            abort(500, 'Error(s) Occured with '.$e->getMessage());
        }
    }


    /*
        | Validate parameters that user submitted |
    */
    public function validateSubmittedParams($submittedParams, $tools){
        $defaultParamsType = config('app.defaultParamsType')[$tools];
        foreach($defaultParamsType as $key => $value){

            // exclude
            if($tools == 'bowtie2'){
                if($key == '--rdg' || $key == '--rfg'){
                    $ps = explode(",",$submittedParams[$key]);
                    foreach($ps as $p){
                        if(!preg_match('/^[0-9]+$/', $p)){
                            $error = \Illuminate\Validation\ValidationException::withMessages(["Parameter '$key' assigned for $tools is invalid (integer only)"]);
                            throw $error;
                        }
                    }
                }

                else if($defaultParamsType[$key] == 'func' && !preg_match('/^(C|L|S|G)(,(-)?[0-9]+(.[0-9]+)?){2}$/', $submittedParams[$key])){
                    $error = \Illuminate\Validation\ValidationException::withMessages(["Parameter '$key' assigned for $tools is invalid (valid: L/C/S/G,float,float)"]);
                    throw $error;
                }
                continue;
            }

            if($tools == 'bwa'){
                foreach ($defaultParamsType[$key] as $key2 => $value2){
                    if($key2 == "-l" && $submittedParams[$key][$key2] == 'inf') continue;

                    if($defaultParamsType[$key][$key2] == 'int' && !preg_match('/^(-)?[0-9]+$/', $submittedParams[$key][$key2])){
                        $error = \Illuminate\Validation\ValidationException::withMessages(["Parameter '$key2' assigned for $tools is invalid (integer only, i.e: 2/10/50)"]);
                        throw $error;
                    }else if($defaultParamsType[$key][$key2] == 'float' && !preg_match('/^(-)?[0-9]+(\.[0-9]+)?$/', $submittedParams[$key][$key2])){
                        $error = \Illuminate\Validation\ValidationException::withMessages(["Parameter '$key2' assigned for $tools is invalid (float only, i.e: 0.1/0.002/1.25)"]);
                        throw $error;
                    }
                }
                continue;
            }

            // check invalid attribute's type
            if($defaultParamsType[$key] == 'int' && !preg_match('/^(-)?[0-9]+$/', $submittedParams[$key])){
                $error = \Illuminate\Validation\ValidationException::withMessages(["Parameter '$key' assigned for $tools is invalid (integer only, i.e: 2/10/50)"]);
                throw $error;
            }else if($defaultParamsType[$key] == 'float' && !preg_match('/^(-)?[0-9]+(\.[0-9]+)?$/', $submittedParams[$key])){
                $error = \Illuminate\Validation\ValidationException::withMessages(["Parameter '$key' assigned for $tools is invalid (float only, i.e: 0.1/0.002/1.25)"]);
                throw $error;
            }else if($defaultParamsType[$key] == 'bool' && !is_bool(($submittedParams[$key]))){
                $error = \Illuminate\Validation\ValidationException::withMessages(["Parameter '$key' assigned for $tools is invalid (boolean only)"]);
                throw $error;
            }
        }
    }

    public function coba(Request $request){
        return Sequence::select('id')->where('name', 'Saccharomyces_cerevisiae.fa')->first()->id ?? "NULL";
    }

}
