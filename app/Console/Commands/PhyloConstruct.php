<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Phylo;

class PhyloConstruct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:construct-phylo {id}';

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
        // artisan jobs:construct-phylo 1
        // mean: construct phylogenetic tree using configuration with id 1 in db phylo
        $phylo_id = $this->argument('id');
        $phylo = Phylo::findOrFail($phylo_id);

        $method = $phylo->method;
        $samples = explode(",", $phylo->samples); // comma-separated, containing information about job id
        $refseq = config('app.sequenceDir')."/references/".$phylo->refseq->name; // todo

        $job_dir = config('app.jobsDir');        
        $variants = "";
        foreach($samples as $sample){
            $variants .= "--variant '$job_dir/$sample/5_filtering/output.filtered.vcf' ";
        }

        $phylo_dir = config('app.phyloDir')."/$phylo_id";
        $gatk = config('app.toolsDir.gatk')."/GenomeAnalysisTK.jar";

        if(mkdir($phylo_dir)){
            $phylo_python = config('app.phyloDir')."/Phylo.py";
            $command = " java -Xmx2g -jar $gatk -T CombineVariants -R '$refseq' $variants -o '$phylo_dir/output.combine.vcf' -genotypeMergeOptions UNIQUIFY && `which vk` phylo tree $method '$phylo_dir/output.combine.vcf' > '$phylo_dir/output.tree.nwk' && sed -i -e 's/:-[0-9\.]\+/:0.0/g' '$phylo_dir/output.tree.nwk' && sed -i -e 's/\.variant[0-9]*//g' '$phylo_dir/output.tree.nwk' && `which python` $phylo_python $phylo_dir/output.tree.nwk";

            shell_exec($command);
        }

        $phylo->status = "FINISHED";
        $phylo->finished_at = date("Y-m-d H:i:s");
        $phylo->save();
    }
}
