<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Jobs\JobProcessController as JP;

class TestJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:test';

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
        //
        print_r(JP::hasProcessError(1,14238));
    }
}
