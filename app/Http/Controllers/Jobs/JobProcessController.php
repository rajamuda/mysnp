<?php

namespace App\Http\Controllers\Jobs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Job;
use App\Process;

class JobProcessController extends Controller
{
    //
    public $job_id;
    public $title;
    public $reads;
    public $reads2;
    public $refs;
    public $index;
    public $jobsDir;
    public $mapper = [];
    public $caller = [];
    public $db_snp;

    public function __construct($job_id = null){
        $this->job_id = $job_id;
    }

    public function start($processID = null){
    	// $job_id = $request->id;

    	$this->jobsDir = config('app.jobsDir')."/{$this->job_id}";
    	$data = parse_ini_file("$this->jobsDir/job.ini");
    	// print_r($data);
        $this->title = $data['title'];
    	$this->mapper = [
    		'tools' => $data['mapper'],
    		'options' => $data['mapper_options'],
    	];
    	$this->caller = [
    		'tools' => $data['caller'],
    		'options' => $data['caller_options'],
    		'filter' => $data['filter_options'],
    	];

        $this->reads = $this->reads2 = "";
        $rd = explode(',', $data['reads1']);
        $i = 0;
        while($i < count($rd)){
            $this->reads .=  config('app.sequenceDir')."/reads/".$rd[$i];
            if(++$i != count($rd)) $this->reads .= ",";
        }

        if($data['reads2'] !== ""){        
            $i = 0;
            $rd = explode(',', $data['reads2']);
            while($i < count($rd)){
                $this->reads2 .=  config('app.sequenceDir')."/reads/".$rd[$i];
                if(++$i != count($rd)) $this->reads2 .= ",";
            }
        }else{
            $this->reads2 = "";
        }

    	$this->refs = config('app.sequenceDir')."/references/".$data['references'];

        if($data['mapper'] == 'bt2'){
            $idx = pathinfo($data['references'], PATHINFO_FILENAME);
    	    $this->index = config('app.sequenceDir')."/bt2_index/".$idx."/".$idx."";
        }
        
        $this->db_snp = $data['db_snp'];
        $this->annotation_db = $data['annotatedb'];

        $process = config('app.process')[$processID];
        $this->insertProcess($process);

        if($process == 'mapping') $this->mapping();
    	else if($process == 'sorting') $this->sorting();
    	else if($process == 'calling') $this->calling();
    	else if($process == 'filtering') $this->filtering();
        else if($process == 'annotation') $this->annotation();
        else if($process == 'storing_to_db') $this->storing_to_db();
        return;

    }

    /**
    | Command Specific
    |-------------------------------
    | runProcess        (int)   : run process in background, return PID of executed process
    | isProcessRunning  (bool)  : check if process with PID is still running or not 
    | killProcess       (-)     : kill process with specific PID
    **/

    public function runProcess($command, $stderr = null){
        return (int)shell_exec(sprintf('nohup bash -c "%s 2> %s" </dev/null >/dev/null 2>/dev/null & echo $!', $command, $stderr));
    }

    public static function isProcessRunning($pid){
        $process = shell_exec(sprintf('ps %d 2>&1', $pid));

        if (count(preg_split("/\n/", $process)) > 2 && !preg_match('/ERROR: Process ID out of range/', $process)) {
            return true;
        }

        return false;
    }

    public static function killProcess($pid){
        exec("kill $pid");
    }


    /**
    | Process pipeline
    |----------------------------------
    | mapping
    | sorting
    | calling
    | filtering
    | annotate
    | storing_to_db
    **/

    public function mapping(){
 	   	if(!is_dir("$this->jobsDir/1_mapping")) mkdir("$this->jobsDir/1_mapping");

    	$output = "$this->jobsDir/1_mapping/output.sam";
        $stderr = "$this->jobsDir/1_mapping/message.out";

    	if($this->mapper['tools'] == 'bt2'){
    		$bowtie2 = config('app.toolsDir.bowtie2')."/bowtie2";
    		$command = "sleep 5 && $bowtie2 {$this->mapper['options']} -x '$this->index' -U '$this->reads' -S '$output'";
    	}

    	$pid = $this->runProcess($command, $stderr);
        file_put_contents("$this->jobsDir/1_mapping/command.txt", $command);
        $this->updateProcess('mapping', 'RUNNING', $pid, $output);
    }

    public function sorting(){
 	   	if(!is_dir("$this->jobsDir/2_sorting")) mkdir("$this->jobsDir/2_sorting");

 	   	$output = "$this->jobsDir/2_sorting/output";
        $stderr = "$this->jobsDir/2_sorting/message.out";

 	   	if($this->caller['tools'] == 'sam'){
 	   		$samtools = config('app.toolsDir.samtools')."/samtools";
 	   		$command = "sleep 5 && $samtools sort -O bam -o '{$output}.raw.bam' '$this->jobsDir/1_mapping/output.sam' && $samtools markdup '{$output}.raw.bam' '{$output}.bam' && $samtools index '{$output}.bam'";
 	   	}

 	   	$pid = $this->runProcess($command, $stderr);
        file_put_contents("$this->jobsDir/2_sorting/command.txt", $command);
        $this->updateProcess('sorting', 'RUNNING', $pid, $output.'.bam');
    }

    public function calling(){
 	   	if(!is_dir("$this->jobsDir/3_calling")) mkdir("$this->jobsDir/3_calling");

 	   	$output = "$this->jobsDir/3_calling/output.vcf";
        $stderr = "$this->jobsDir/3_calling/message.out";

        $reg = str_replace("/", "\/", "$this->jobsDir/2_sorting/output.bam");

 	   	if($this->caller['tools'] == 'sam'){
 	   		$bcftools = config('app.toolsDir.bcftools')."/bcftools";
            if($this->db_snp != ""){
                $db_snp = config('app.dbSnpDir')."/".$this->db_snp;
                $command = "sleep 5 && $bcftools mpileup {$this->caller['options']} -Ou -f '{$this->refs}' '$this->jobsDir/2_sorting/output.bam' | $bcftools call -vmO v | $bcftools view -Oz -o '$output.gz' && $bcftools index '$output.gz' && $bcftools annotate --annotations '$db_snp' --columns ID -o '$output' -O v '$output.gz' && sed -i -e 's/$reg/{$this->title}/g' '$output'";
 	   	    }else{
                $command = "sleep 5 && $bcftools mpileup {$this->caller['options']} -Ou -f '{$this->refs}' '$this->jobsDir/2_sorting/output.bam' | $bcftools call -vmO v -o '$output' && sed -i -e 's/$reg/{$this->title}/g' '$output'";
            }
        }

 	   	$pid = $this->runProcess($command, $stderr);
        file_put_contents("$this->jobsDir/3_calling/command.txt", $command);
        $this->updateProcess('calling', 'RUNNING', $pid, $output);
    }

    public function filtering(){
        if(!is_dir("$this->jobsDir/4_filtering")) mkdir("$this->jobsDir/4_filtering");

 	   	$output = "$this->jobsDir/4_filtering/output.filtered.vcf";
        $stderr = "$this->jobsDir/4_filtering/message.out";

 	   	if($this->caller['tools'] == 'sam'){
 	   		$vcfutils = config('app.toolsDir.vcfutils')."/vcfutils.pl";
 	   		$command = "sleep 5 && $vcfutils varFilter {$this->caller['filter']} '$this->jobsDir/3_calling/output.vcf' > '$output'";
 	   	}

 	   	$pid = $this->runProcess($command, $stderr);
        file_put_contents("$this->jobsDir/4_filtering/command.txt", $command);
        $this->updateProcess('filtering', 'RUNNING', $pid, $output);
    }

    public function annotation(){
        if(!is_dir("$this->jobsDir/5_annotation")) mkdir("$this->jobsDir/5_annotation");

        $output = "$this->jobsDir/5_annotation";
        $stderr = "$this->jobsDir/5_annotation/message.out";

        // $reg = str_replace("/", "\/", "$output/snpEff_genes.txt");

        $snpeff = config('app.toolsDir.snpeff');
        $command = "sleep 5 && cd '$output' && java -Xmx2g -jar $snpeff/snpEff.jar eff -c '$snpeff/snpEff.config' -v '$this->annotation_db' '$this->jobsDir/4_filtering/output.filtered.vcf' > '{$output}/output.eff.vcf' && sed -i -e 's/snpEff_genes.txt/\/file\/show\/genes\/{$this->job_id}/g' '$output/snpEff_summary.html'";

        $pid = $this->runProcess($command, $stderr);
        file_put_contents("$this->jobsDir/5_annotation/command.txt", $command);
        $this->updateProcess('annotation', 'RUNNING', $pid, $output.'/output.eff.vcf');
    }

    public function storing_to_db(){
        if(!is_dir("$this->jobsDir/6_storing_to_db")) mkdir("$this->jobsDir/6_storing_to_db");

        $output = "$this->jobsDir/6_storing_to_db";
        $stderr = "$this->jobsDir/6_storing_to_db/message.out";

        $artisan = config('app.rootDir')."/artisan";
        $command = "sleep 5 && $artisan jobs:store-db $this->job_id $this->refs";

        $pid = $this->runProcess($command, $stderr);
        file_put_contents("$this->jobsDir/6_storing_to_db/command.txt", $command);
        $this->updateProcess('storing_to_db', 'RUNNING', $pid, $output.'/output.flank.fa');
    }


    /**  
    | Database method
    |------------------------------
    | insertProcess
    | updateProcess
    | getSubmittedJobs (static)
    | getRunningJobs (static)
    | getRunningProcess (static)
    | getRunningJobProcess (static)
    | setJobFinished (static)
    | setProcessFinished (static)
    | updateJobProcess (static)
    **/
    
    public function insertProcess($process){
        echo "{{{{{ job $this->job_id with $process }}}}}   ";

        $processDB = new Process;
        $processDB->job_id = $this->job_id;
        $processDB->process = $process;
        $processDB->submitted_at = date('Y-m-d H:i:s');
        $processDB->save();
        // Process::insert([
        //     'job_id' => $this->job_id,
        //     'process' => $process,
        //     'submitted_at' => date('Y-m-d H:i:s'),
        // ]);
    }

    public function updateProcess($process, $status, $pid = null, $output = null){
        if($status == 'RUNNING'){
            Process::where([
                ['job_id', '=', $this->job_id],
                ['process', '=', $process],
            ])->update([
                'pid' => $pid,
                'status' => $status,
                'output' => $output,
            ]);
        }
        else{
            Process::where([
                ['job_id', '=', $this->job_id],
                ['process', '=', $process],
            ])->update([
                'status' => $status,
                'finished_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /* GET */
    public static function getSubmittedJobs(){
        return Job::where('status', 'WAITING')->get(['id']);
    }   

    public static function getRunningJobs(){
        return Job::where('status', 'like' ,'RUNNING%')->get(['id', 'status']);
    }

    public static function getRunningProcess(){
        return Process::where('status', 'RUNNING')->get(['id','job_id','pid', 'process']);
    }

    public static function getRunningJobProcess($process, $job_id){
        return Process::where([['process', '=', $process], ['job_id', '=', $job_id]])->first(['status']);
    }

    /* SET */
    public static function setJobFinished($id){
        Job::where('id', $id)->update(['status' => 'FINISHED', 'finished_at' => date('Y-m-d H:i:s')]);
        self::progress($id, ['100', 'FINISHED', date('Y-m-d H:i:s')]);
    }

    public static function setProcessFinished($job_id, $pid){
        Process::where([['job_id', '=', $job_id], ['pid', '=', $pid]])->update(['status' => 'FINISHED', 'finished_at' => date('Y-m-d H:i:s')]);
    }

    public static function updateJobProcess($id, $processID = 1){
        $list_process = config('app.process');
        $process = $list_process[$processID];
        $status = "RUNNING: $process";

        Job::where('id', $id)->update(['status' => $status]);
        
        $progress = ($processID/count($list_process)) * 100;
        $message = [$progress, $status, date('Y-m-d H:i:s')];
        self::progress($id, $message);
    }

    /* SAVE PROGRESS */
    public static function progress($job_id, $message){
        $dir = config('app.jobsDir')."/$job_id/progress.txt";
        $message = implode(";", $message);
        file_put_contents("$dir", "$message\n", FILE_APPEND);        
    }

    /** File Getter **/
    public static function getFile($processID, $user_id){
        $process = Process::findOrFail($processID);

        $file = $process->output;
        if (file_exists($file)) {
            if($process->job->user_id == $user_id){
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                ob_clean();
                flush();
                readfile($file);
                exit;
            }else{
                abort(403, 'Forbidden Access');
            }
        } else {
            abort(404);
        }    
    }


}
