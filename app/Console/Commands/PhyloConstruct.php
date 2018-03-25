<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PhyloConstruct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:construct-phylo {method} {samples}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Construct phylogenetic tree from given samples';

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
        // artisan jobs:construct-phylo upgma 1,2,3
        // mean: construct phylogenetic tree using upgma method form sample with id 1, 2, and 3.

        $method = $this->argument('method');
        $samples = explode(",", $this->argument('samples')); // comma-separated, containing information about job id
        $refseq = ""; // todo
        $phylo_id = ""; // todo

        // must create database to handle 'todo'

        $job_dir = config('app.jobsDir');
        
        $variants = "";
        foreach($samples as $sample){
            $variants .= "--variant '$job_dir/$sample/4_filtering/output.filtered.vcf' ";
        }

        $phylo_dir = "/homer/rajamuda/htdocs/mysnp/resources/phylo-tree/$phylo_id";
        $command = " java -Xmx2g -jar GenomeAnalysisTK.jar -T CombineVariants -R '$refseq' $variants -o '$phylo_dir/output.combine.vcf' -genotypeMergeOptions UNIQUIFY && `which vk` phylo tree $method '$phylo_dir/output.combine.vcf' > '$phylo_dir/output.tree.nwk' && sed -i -e 's,:-[0-9\.]\+,:0.0,g' '$phylo_dir/output.tree.nwk'";

        shell_exec($command);

    }
}
