<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        /*$gatk = [
			[
				'tools' => 'gatk',
				'param_name'=>'-contamination',
				'value_type'=>'number',
				'default_value'=>'0.0',
				'value_range'=>'',
				'param_description'=>'Fraction of contamination in sequencing data to remove',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		  	[
				'tools' => 'gatk',
				'param_name'=>'-hets',
				'value_type'=>'number',
				'default_value'=>'0.001',
				'value_range'=>'',
				'param_description'=>'Heterozygosity value used to compute prior likelihoods for any locus',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		    [
				'tools' => 'gatk',
				'param_name'=>' -indelHeterozygosity',
				'value_type'=>'number',
				'default_value'=>'1.25E-4',
				'value_range'=>'',
				'param_description'=>'Heterozygosity for indel calling',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		    [
				'tools' => 'gatk',
				'param_name'=>'--maxReadsInRegionPerSample',
				'value_type'=>'number',
				'default_value'=>'10000',
				'value_range'=>'',
				'param_description'=>'Maximum reads in an active region',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		    [
				'tools' => 'gatk',
				'param_name'=>'--min_base_quality_score',
				'value_type'=>'number',
				'default_value'=>'10',
				'value_range'=>'',
				'param_description'=>'Minimum base quality required to consider a base for calling',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		    [
				'tools' => 'gatk',
				'param_name'=>'--minReadsPerAlignmentStart',
				'value_type'=>'number',
				'default_value'=>'10',
				'value_range'=>'',
				'param_description'=>'Minimum number of reads sharing the same alignment start for each genomic location in an active region',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		    [
				'tools' => 'gatk',
				'param_name'=>'-ploidy',
				'value_type'=>'number',
				'default_value'=>'2',
				'value_range'=>'',
				'param_description'=>'Ploidy (number of chromosomes) per sample',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		    [
				'tools' => 'gatk',
				'param_name'=>'-stand_call_conf',
				'value_type'=>'number',
				'default_value'=>'30.0',
				'value_range'=>'',
				'param_description'=>'The minimum phred-scaled confidence threshold at which variants should be called',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		    [
				'tools' => 'gatk',
				'param_name'=>'-stand_emit_conf',
				'value_type'=>'number',
				'default_value'=>'30.0',
				'value_range'=>'',
				'param_description'=>'The minimum phred-scaled confidence threshold at which variants should be emitted',
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
		        'objective'=>'',
		    ],
		];*/

		// DB::table('default_params')->insert($gatk);
		$sequences = [
			[
				'name' => 'glycine_max_sim_100K.fastq',
				'type' => 'reads',
				'user_id' => 1,
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
			],
			[
				'name' => 'glycine_max_chr10.fa',
				'type' => 'references',
				'user_id' => 1,
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
			],
			[
				'name' => 'Saccharomyces_cerevisiae.fa',
				'type' => 'references',
				'user_id' => 1,
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
			],
			[
				'name' => 'yeast_1.fastq',
				'type' => 'reads',
				'user_id' => 1,
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
			],
			[
				'name' => 'yeast_2.fastq',
				'type' => 'reads',
				'user_id' => 1,
				'created_at' => date('Y-m-d H:i:s'),
		        'updated_at' => date('Y-m-d H:i:s'),
			],
		];

		// DB::table('sequences')->insert($sequences);
    }
}
