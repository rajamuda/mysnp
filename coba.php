<?php

$vcf_file = "/home/rajamuda/htdocs/mysnp/resources/jobs/3/4_filtering/output.vcf";
$vcf_handle = fopen($vcf_file, "r");
$vcf_data = [];

$refseq = "/home/rajamuda/htdocs/mysnp/resources/sequence/references/Saccharomyces_cerevisiae.fa";

$vcfprimers = "/home/rajamuda/htdocs/mysnp/resources/tools/vcflib/bin/vcfprimers";
$flank_file = "/home/rajamuda/htdocs/mysnp/resources/jobs/3/4_filtering/output.vcf.flank.fa";
$flank_length = 100;

$command = "$vcfprimers -f '{$refseq}' -l {$flank_length} '{$vcf_file}' > '{$flank_file}'";
shell_exec($command);

// echo $command;
// $flank_handle = fopen($flank_file, "r");
// while(!feof($vcf_handle)){
// 	$line = fgets($vcf_handle, 4096);

// 	$line = explode("\t", $line);

//     if (!empty($line[0][0]) && $line[0][0] != '#'){
//             $vcf_data[] = $line;

//     }
// }

// fclose($vcf_handle);
// fclose($flank_handle);
// echo count($vcf_data)."\n";
// print_r($vcf_data[0]);
// echo "\n";