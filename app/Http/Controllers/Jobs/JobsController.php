<?php

namespace App\Http\Controllers\Jobs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use WriteiniFile\WriteiniFile;
use Illuminate\Support\Facades\DB;
use Cocur\BackgroundProcess\BackgroundProcess;

class JobsController extends Controller
{
    // get all jobs created by user
    public function get(Request $request){
        $user_id = $request->user()->id;

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
            'seq_mapper' => 'required|in:bt2,bwa,novo',
            'snp_caller' => 'required|in:sam,gatk',
        ]);

    	/*
    	| Variable Information
    	|------------------------------------------------------
		| Title 			: $input['title']
		| References		: $input['references']
		| Reads Type		: $input['reads_type']
		| Reads				: $input['reads1'][~index~]['value']
		| Reads 2 (pair)	: $input['reads2'][~index~]['value']
		| Annotation DB		: $input['db_annotate']
		| Alignment Tools	: $input['seq_mapper']
		| SNP Caller		: $input['snp_caller']
		| -----------------------------------------------------
		| To Do: list array of parameters settings
		|
    	*/
    	$input = $request->all();
        $defaultParams = app('config')->get('app')['defaultParams'];
        
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

        }else{ // Novoalign

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

        $snphyloOptions = "";

        $reads1 = [];
        foreach($input['reads1'] as $r){
            array_push($reads1, $r['value']);
        }

        $reads2 = [];
        foreach($input['reads2'] as $r){
            array_push($reads2, $r['value']);
        }

        $data = [
            'config' => [
                'title' => $input['title'],
                'references' => $input['references'],
                'reads1' => implode(',', $reads1),
                'reads2' => implode(',', $reads2),
                'annotatedb' => $input['db_annotate'],
                'mapper' => $input['seq_mapper'],
                'caller' => $input['snp_caller'],
                'mapper_options' => $mapperOptions,
                'caller_options' => $callerOptions,
                'filter_options' => $filterOptions,
                'snphylo_options' => $snphyloOptions,
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
                    'submitted_at' => date('Y-m-d H:i:s')
                ]
            ]);

            $job_id = DB::getPdo()->lastInsertId();

            $jobsDir = app('config')->get('app')['jobsDir']."/".$job_id;
            
            if(mkdir($jobsDir, 0755)){
                $ini = new WriteiniFile("$jobsDir/job.ini");
                $ini->create($data);
                $ini->write();
            }

            DB::commit();
            
            return json_encode(["status" => true, "message"=> "Job submitted"]);
          // return json_encode($input);
        }catch(\Exception $e){
            DB::rollback();
            return json_encode(["status" => false, "message"=> $e]);        
        }
        // return "benar";
    }

    // update submitted and unprocessed jobs
    public function update(Request $request){

    }

    public function validateSubmittedParams($submittedParams, $tools){
        $defaultParamsType = app('config')->get('app')['defaultParamsType'][$tools];
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

    public function getParams(Request $request){
        return DB::table('default_params')->where('tools', $request->tools)->pluck('default_value');
    }

    public function coba(Request $request){
        // $data = parse_ini_file('/home/rajamuda/htdocs/mysnp/resources/jobs/job.ini');
        // print_r($data);

        // print_r($request->user()->id);
        // if(is_double(floatval("0.02"))) echo "bener";
        // if (preg_match('/^(-)?[0-9]+(\.[0-9]+)$/', "-0.001")){
        //     echo "bener";
        // }
        if(!BackgroundProcess::createFromPID(5517)->isRunning()){
         echo "Gak Jalan";           
        }
    }

}
