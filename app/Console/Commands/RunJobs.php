<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Jobs\JobProcessController as JobProcess;
use App\Http\Controllers\Jobs\PhyloController as Phylo;
use Illuminate\Support\Facades\DB;

class RunJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run submitted SNP identification job as daemon';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while(true){
            /*
                TO DO: cek job yang di-submit..
                Untuk setiap job yang di-submit (status: WAITING), ambil job_id nya
            */
            $submittedJobs = JobProcess::getSubmittedJobs();
            foreach($submittedJobs as $job){
                echo date('Y-m-d H:i:s')." Starting jobs {$job->id}...\n";

                JobProcess::updateJobProcess($job->id);
                $job_process = new JobProcess($job->id);
                $job_process->start(1); // start mapping process
                echo date('Y-m-d H:i:s')." Running 'mapping'\n";
            }

            // cek job yang sedang run
            $runningJobs = JobProcess::getRunningJobs();
            foreach($runningJobs as $job){
                $process = substr($job->status,9); // 'RUNNING: proses' => 'proses'
                $processID = array_search($process, app('config')->get('app')['process']);

                $maxProcess = count(app('config')->get('app')['process']);

                // cek apakah job dengan process id masih berjalan             
                if(JobProcess::getRunningJobProcess($process, $job->id)->status == 'FINISHED'){
                    $processID++; // kalau sudah selesai, lanjutkan ke proses berikutnya

                    if($processID >= $maxProcess){
                        JobProcess::setJobFinished($job->id);
                        echo date('Y-m-d H:i:s')." Finishing jobs '{$job->id}'\n";
                    }else{
                        JobProcess::updateJobProcess($job->id, $processID);
                        $job_process = new JobProcess($job->id);
                        $job_process->start($processID); // start next process
                        echo date('Y-m-d H:i:s')." Running '".app('config')->get('app')['process'][$processID]."'\n";
                    }
                }
            }

            // cek apakah proses pada suatu job telah selesai atau belum
            $runningProcess = JobProcess::getRunningProcess();
            foreach($runningProcess as $process){
                if(!JobProcess::isProcessRunning($process->pid)){
                    if(JobProcess::hasProcessError($process->job_id, $process->pid)){
                        JobProcess::setProcessFailure($process->job_id, $process->pid);
                        echo date('Y-m-d H:i:s')."An error occured within '{$process->process}' of job '{$process->job_id}'";
                    }else{
                        JobProcess::setProcessFinished($process->job_id, $process->pid);
                        echo date('Y-m-d H:i:s')." Finishing process '{$process->process}' of job '{$process->job_id}' with pid '{$process->pid}'\n";
                    }
                }
            }

            // cek jobs untuk konstruksi pohon filogenetik dan jalankan
            $submittedPhylo = Phylo::getSubmittedPhylo();
            foreach($submittedPhylo as $phylo){
                echo date('Y-m-d H:i:s')." Starting constructing phylo {$phylo->id}...\n";
                Phylo::runPhyloConstruction($phylo->id);
            }

            sleep(5);
        }
    }
}
