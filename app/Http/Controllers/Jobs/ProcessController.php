<?php

namespace App\Http\Controllers\Jobs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\DB;
use Cocur\BackgroundProcess\BackgroundProcess;

class ProcessController extends Controller
{
    //
    public $job_id;
    // public $user_id;
    public $reads;
    public $refs;
    public $index;
    public $jobsDir;
    public $mapper = [];
    public $caller = [];

    public function __construct($job_id, $user_id = null){
        $this->job_id = $job_id;
        // $this->user_id = $user_id;
    }

    public function start($processID = null){
    	// $job_id = $request->id;

    	$this->jobsDir = app('config')->get('app')['jobsDir']."/{$this->job_id}";
    	$data = parse_ini_file("$this->jobsDir/job.ini");
    	// print_r($data);
    	$this->mapper = [
    		'tools' => $data['mapper'],
    		'options' => $data['mapper_options'],
    	];
    	$this->caller = [
    		'tools' => $data['caller'],
    		'options' => $data['caller_options'],
    		'filter' => $data['filter_options'],
    	];

    	$this->reads = app('config')->get('app')['sequenceDir']."/reads/glycine_max_sim_100K.fastq";
    	$this->refs = app('config')->get('app')['sequenceDir']."/references/glycine_max_chr10.fa";
    	$this->index = app('config')->get('app')['sequenceDir']."/bt2_index/glycine_max_chr10/glycine_max_chr10";
        $this->annotation_db = 'Glycine_max' ?? $data['annotatedb'];

        $process = app('config')->get('app')['process'][$processID];

        $this->insertProcess($process);

        if($process == 'mapping') $this->mapping();
    	else if($process == 'sorting') $this->sorting();
    	else if($process == 'calling') $this->calling();
    	else if($process == 'filtering') $this->filtering();
        else if($process == 'annotation') $this->annotation();

        return;

    }

    public function run($command, $stderr = null){
  //   	$process = new Process($command);
		// $process->start();

		// $pid = $process->getPid();
		// echo "PID: $pid\n";
		// // foreach ($process as $type => $data) {
		// //     if ($process::OUT === $type) {
		// //         echo "\nRead from stdout: ".$data;
		// //     } else { // $process::ERR === $type
		// //         echo "\nRead from stderr: ".$data;
		// //     }
		// // }

  //       // wait until process is completed
  //       echo "Running... $command\n";
  //       while($process->isRunning()){}

  //       $stderr = $process->getOutput()? : $process->getErrorOutput();
  //       file_put_contents($message, $stderr);
        
  //       echo "$stderr\n\n";
        $process = new BackgroundProcess($command); // run command in background
        $process->run($stderr); // stdout or stderr

        // return pid of current running process
        return $process->getPid();
    }

    public function insertProcess($process){
        echo "{{{{{ job $this->job_id with $process }}}}}   ";
        DB::table('process')->insert([
            'job_id' => $this->job_id,
            'process' => $process,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateProcess($process, $status, $pid = null, $output = null){
        if($status == 'RUNNING'){
            DB::table('process')->where([
                ['job_id', '=', $this->job_id],
                ['process', '=', $process],
            ])->update([
                'pid' => $pid,
                'status' => $status,
                'output' => $output,
            ]);
        }else{
            DB::table('process')->where([
                ['job_id', '=', $this->job_id],
                ['process', '=', $process],
            ])->update([
                'status' => $status,
                'finished_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function mapping(){
 	   	if(!is_dir("$this->jobsDir/1_mapping")) mkdir("$this->jobsDir/1_mapping");

    	$output = "$this->jobsDir/1_mapping/output.sam";
        $stderr = "$this->jobsDir/1_mapping/message.out";

    	if($this->mapper['tools'] == 'bt2'){
    		$bowtie2 = app('config')->get('app')['toolsDir']['bowtie2']."/bowtie2";
    		$command = "sleep 5 && $bowtie2 {$this->mapper['options']} -x '$this->index' -U '$this->reads' -S '$output'";
    	}

    	$pid = $this->run($command, $stderr);
        file_put_contents("$this->jobsDir/progress.txt", '15');
        $this->updateProcess('mapping', 'RUNNING', $pid, $output);
    }

    public function sorting(){
 	   	if(!is_dir("$this->jobsDir/2_sorting")) mkdir("$this->jobsDir/2_sorting");

 	   	$output = "$this->jobsDir/2_sorting/output";
        $stderr = "$this->jobsDir/2_sorting/message.out";

 	   	if($this->caller['tools'] == 'sam'){
 	   		$samtools = app('config')->get('app')['toolsDir']['samtools']."/samtools";
 	   		$command = "sleep 5 && $samtools sort -O bam -o '{$output}.raw.bam' '$this->jobsDir/1_mapping/output.sam' && $samtools markdup '{$output}.raw.bam' '{$output}.bam' && $samtools index '{$output}.bam'";
 	   	}

 	   	$pid = $this->run($command, $stderr);
        file_put_contents("$this->jobsDir/progress.txt", '35');
        $this->updateProcess('sorting', 'RUNNING', $pid, $output);
    }

    public function calling(){
 	   	if(!is_dir("$this->jobsDir/3_calling")) mkdir("$this->jobsDir/3_calling");

 	   	$output = "$this->jobsDir/3_calling/output.raw.vcf";
        $stderr = "$this->jobsDir/3_calling/message.out";

 	   	if($this->caller['tools'] == 'sam'){
 	   		$bcftools = app('config')->get('app')['toolsDir']['bcftools']."/bcftools";
 	   		$command = "sleep 5 && $bcftools mpileup {$this->caller['options']} -Ou -f '{$this->refs}' '$this->jobsDir/2_sorting/output.bam' | $bcftools call -vmO v -o '$output'";
 	   	}

 	   	$pid = $this->run($command, $stderr);
        file_put_contents("$this->jobsDir/progress.txt", '70');
        $this->updateProcess('calling', 'RUNNING', $pid, $output);
    }

    public function filtering(){
        if(!is_dir("$this->jobsDir/4_filtering")) mkdir("$this->jobsDir/4_filtering");

 	   	$output = "$this->jobsDir/4_filtering/output.vcf";
        $stderr = "$this->jobsDir/4_filtering/message.out";

 	   	if($this->caller['tools'] == 'sam'){
 	   		$vcfutils = app('config')->get('app')['toolsDir']['vcfutils']."/vcfutils.pl";
 	   		$command = "sleep 5 && $vcfutils varFilter {$this->caller['filter']} '$this->jobsDir/3_calling/output.raw.vcf' > '$output'";
 	   	}

 	   	$pid = $this->run($command, $stderr);
        file_put_contents("$this->jobsDir/progress.txt", '80');
        $this->updateProcess('filtering', 'RUNNING', $pid, $output);
    }

    public function annotation(){
        if(!is_dir("$this->jobsDir/5_annotation")) mkdir("$this->jobsDir/5_annotation");

        $output = "$this->jobsDir/5_annotation";
        $stderr = "$this->jobsDir/5_annotation/message.out";

        $snpeff = app('config')->get('app')['toolsDir']['snpeff'];
        $command = "sleep 5 && cd '$output' && java -jar $snpeff/snpEff.jar eff -c '$snpeff/snpEff.config' -v 'Glycine_max' '$this->jobsDir/4_filtering/output.vcf' > '{$output}/output.eff.vcf'";

        $pid = $this->run($command, $stderr);
        file_put_contents("$this->jobsDir/progress.txt", '99');
        $this->updateProcess('annotation', 'RUNNING', $pid, $output);
    }
}
