<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Jobs\JobProcessController as JobProcess;

/*
|--------------------------------------------------------------------------
| File Routes
|--------------------------------------------------------------------------
|
| Here is where you can register File routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "File" middleware group. Enjoy building your File!
|
*/

Route::group(['middleware' => 'auth:file'], function () {
    Route::get('show/stats/{job_id}', function (Request $request) {
       foreach($request->user()->job as $job){
    		if($job->id == $request->job_id)
        		return readfile(config('app.jobsDir')."/{$request->job_id}/5_annotation/snpEff_summary.html");
   		}

    	abort(403, 'Forbidden Access');
    });

    Route::get('show/genes/{job_id}', function (Request $request) {
    	foreach($request->user()->job as $job){
    		if($job->id == $request->job_id){
		    	$file = config('app.jobsDir')."/{$request->job_id}/5_annotation/snpEff_genes.txt";

        		$fp = fopen($file, 'r');
        		$line_count = 0;
        		echo "<html>";
        		echo "<a href='/file/dl/genes/$request->job_id'><button style='margin-bottom: 10px;'>Download</button></a>";
        		echo "<table style='border: 1px solid; border-collapse: collapse'>";
				while ( !feof($fp) ){
				    $line = fgets($fp, 2048);
				    if($line_count++ === 0) continue;

				    $data = str_getcsv($line, "\t");

				    echo "<tr>";
				    if($line_count == 2){
				    	foreach($data as $col){
				    		echo "<th style='border: 1px solid; border-collapse: collapse; padding: 10px; '>";
				    		echo $col;
				    		echo "</th>";
				    	}
				    }else{
				    	foreach($data as $col){
				    		echo "<td style='border: 1px solid; border-collapse: collapse; padding: 10px;'>";
				    		echo $col;
				    		echo "</td>";
				    	}
				    }
				    echo "</tr>";

				    ++$line_count;
				}              
				echo "</table>"; 
				echo "</html>";               
				fclose($fp);
				return null;
    		}
    	}

    	abort(403, 'Forbidden Access');
    });


    Route::get('dl/genes/{job_id}', function (Request $request) {
    	foreach($request->user()->job as $job){
    		if($job->id == $request->job_id){
		    	$file = config('app.jobsDir')."/{$request->job_id}/5_annotation/snpEff_genes.txt";
				header('Content-Description: File Transfer');
			    header('Content-Type: application/octet-stream');
			    header('Content-Disposition: attachment; filename="'.basename($file).'"');
			    header('Expires: 0');
			    header('Cache-Control: must-revalidate');
			    header('Pragma: public');
			    header('Content-Length: ' . filesize($file));
				
				return readfile($file);
			}
		}

    	abort(403, 'Forbidden Access');

    });

    Route::get('dl/process_output/{process_id}', function (Request $request) {
    	return JobProcess::getFile($request->process_id, $request->user()->id);
    });
});

// Route::get('login', function () {
//     return redirect('login');
// })->name('login');
