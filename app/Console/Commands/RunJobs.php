<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Jobs\ProcessController;
use Illuminate\Support\Facades\DB;
use Cocur\BackgroundProcess\BackgroundProcess;

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
    protected $description = 'Command description';

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
            $submittedJobs = $this->getSubmittedJobs();
            foreach($submittedJobs as $job){
                echo date('Y-m-d H:i:s')." Starting jobs {$job->id}...\n";

                $this->updateJobProcess($job->id);
                $job_process = new ProcessController($job->id);
                $job_process->start(1); // start mapping process
                echo date('Y-m-d H:i:s')." Running 'mapping'\n";
            }

            // cek job yang sedang run
            $runningJobs = $this->getRunningJobs();
            foreach($runningJobs as $job){
                $process = substr($job->status,9);
                $processID = array_search($process, app('config')->get('app')['process']);

                $maxProcess = count(app('config')->get('app')['process']);

                // cek apakah job dengan process id masih berjalan             
                if($this->getRunningJobProcess($process, $job->id)->status == 'FINISHED'){
                    $processID++;

                    if($processID == $maxProcess){
                        $this->setJobFinished($job->id);
                        echo date('Y-m-d H:i:s')." Finishing jobs '{$job->id}'\n";
                    }else{
                        $this->updateJobProcess($job->id, $processID);
                        $job_process = new ProcessController($job->id);
                        $job_process->start($processID); // start process
                        echo date('Y-m-d H:i:s')." Running '".app('config')->get('app')['process'][$processID]."'\n";
                    }
                }
            }

            // cek apakah proses pada suatu job telah selesai atau belum
            $runningProcess = $this->getRunningProcess();
            foreach($runningProcess as $process){
                if(!BackgroundProcess::createFromPID($process->pid)->isRunning()){
                    $this->setProcessFinished($process->job_id, $process->pid);
                    echo date('Y-m-d H:i:s')." Finishing process '{$process->process}' of job '{$process->job_id}' with pid '{$process->pid}'\n";
                }
            }

            sleep(5);
        }
    }

    public function getSubmittedJobs(){
        return DB::table('jobs')->where('status', 'WAITING')->get(['id']);
    }   

    public function getRunningJobs(){
        return DB::table('jobs')->where('status', 'like' ,'RUNNING%')->get(['id', 'status']);
    }

    public function getRunningProcess(){
        return DB::table('process')->where('status', 'RUNNING')->get(['id','job_id','pid', 'process']);
    }

    public function setJobFinished($id){
        DB::table('jobs')->where('id', $id)->update(['status' => 'FINISHED', 'finished_at' => date('Y-m-d H:i:s')]);
    }

    public function setProcessFinished($job_id, $pid){
        DB::table('process')->where([['job_id', '=', $job_id], ['pid', '=', $pid]])->update(['status' => 'FINISHED', 'finished_at' => date('Y-m-d H:i:s')]);
    }

    public function getRunningJobProcess($process, $job_id){
        return DB::table('process')->where([['process', '=', $process], ['job_id', '=', $job_id]])->first(['status']);
    }

    public function updateJobProcess($id, $processID = 1){
        $process = app('config')->get('app')['process'][$processID];
        $status = "RUNNING: $process";

        DB::table('jobs')->where('id', $id)->update(['status' => $status]);
    }
}
