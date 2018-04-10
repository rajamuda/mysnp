<?php

namespace App\Http\Controllers\Jobs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Jobs\JobProcessController;
use App\Phylo;
use App\Sequence;
use App\Job;
use Imagick;

class PhyloController extends Controller
{
    //
	public function getRefseqJobs(Request $request){
		$user_id = $request->user()->id;
		
		return Job::join('sequences', 'jobs.refseq_id', 'sequences.id')->select('jobs.refseq_id as value','sequences.name as name')->where('jobs.user_id',$user_id)->distinct()->get();
	}

    public function getJobsByRefseq(Request $request){
		$user_id = $request->user()->id;
    	$refseq_id = $request->refseq_id;

    	return Job::select('id as value', 'title as name')->where([['user_id', '=', $user_id],['refseq_id', '=', $refseq_id]])->get();
    }

    public function getAllPhylo(Request $request){
        return Phylo::where('user_id', $request->user()->id)->get();
    }

    public function viewPhylo(Request $request){
    	$phylo_id = (int)$request->id;
    	$user_id = $request->user()->id;

    	$phylo = Phylo::findOrFail($phylo_id);
    	if($phylo->user_id == $user_id){
            if($phylo->status === "FINISHED"){
        		$phylo_dir = config('app.phyloDir')."/$phylo_id";
        		$distance_matrix = json_decode(file_get_contents($phylo_dir."/distance_matrix.json"), true);
        		$image = file_get_contents($phylo_dir."/phylo_tree.svg");
                return ['status' => 'FINISHED', 'distance_matrix' => $distance_matrix, 'tree' => $image];
            }else if($phylo->status === "WAITING" || $phylo->status === "RUNNING"){
                return ['status' => 'RUNNING'];
            }else{
                abort(404, 'File not found');
            }
        }else{
    		abort(403, 'Forbidden Access');
    	}
    }	

    public static function getPhyloNewick($phylo_id, $user_id){
    	$phylo = Phylo::findOrFail($phylo_id);

    	if($phylo->id == $user_id){
    		$tree = config('app.phyloDir')."/$phylo_id/output.tree.nwk";
    		if(file_exists($tree)){
    			header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename='.basename($tree));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tree));
                ob_clean();
                flush();
                readfile($tree);
                exit;
    		}else{
    			abort(404, 'File Not Found');
    		}
    	}else{
    		abort(403, 'Forbidden Access');
    	}
    }

    public static function getPhyloImage($phylo_id, $user_id){
        $phylo = Phylo::findOrFail($phylo_id);

        if($phylo->id == $user_id){
            $tree = config('app.phyloDir')."/$phylo_id/phylo_tree.svg";
            if(file_exists($tree)){
                $im = new Imagick();
                $svg = file_get_contents($tree);

                $im->readImageBlob($svg);
                $im->setImageFormat("png24");

                header('Content-type: image/png');
                echo $im;

                $im->clear();
                $im->destroy();
                exit;
            }else{
                abort(404, 'File Not Found');
            }
        }else{
            abort(403, 'Forbidden Access');
        }
    }

    public function constructPhylo(Request $request){
    	$this->validate($request, [
    		'refseq' => 'required',
    		'samples' => 'required|array|min:2',
    		'method' => 'required|in:upgma,nj'
    	]);

    	$user_id = $request->user()->id;
    	$phylo_name = $request->name;
    	$refseq_id = $request->refseq;
    	$method = $request->method;
    	
    	$samples = [];
    	foreach($request->samples as $sample){
    		$samples[] = $sample['value'];
    	}
    	sort($samples);
    	$samples = implode(",", $samples);
    	
    	$info = [
    		'user_id' => $user_id,
    		'refseq_id' => $refseq_id,
    		'samples' => $samples,
    		'method' => $method,
    	];

    	$isExist = $this->isAlreadySubmitted($info);
    	if(!$isExist){
	    	$phylo = new Phylo;
	    	$phylo->name = $phylo_name;
	    	$phylo->user_id = $user_id;
	    	$phylo->refseq_id = $refseq_id;
	    	$phylo->samples = $samples;
	    	$phylo->method = $method;
	    	$phylo->status = "WAITING";
	    	$phylo->submitted_at = date("Y-m-d H:i:s");
	    	$phylo->save();

            // panggil command untuk menajalankan perintah construct phylo

            return ["status" => true, "message"=> "Job submitted", 'phylo_id' => $phylo->id];
	    }else{
	    	$submit_date = date_parse_from_format('Y-m-d H:i:s', $isExist->submitted_at);
	    	$submit_date = "{$submit_date['year']}{$submit_date['month']}{$submit_date['day']}{$submit_date['hour']}{$submit_date['minute']}";
	    	$name = $isExist->name ?? $submit_date."_".$isExist->method."_".pathinfo($isExist->refseq->name, PATHINFO_FILENAME);
	    	abort(409, "Phylogenetic Tree Already Exist (<a href='/jobs/view-phylo/$isExist->id'>$name</a>)");
	    }
    }

    public function isAlreadySubmitted($info){
    	$data = Phylo::where([['user_id', '=', $info['user_id']],['refseq_id', '=', $info['refseq_id']], ['samples', '=', $info['samples']], ['method', '=', $info['method']]])->first();
    	return $data;
    }

    public static function getSubmittedPhylo(){
        return Phylo::where('status', 'WAITING')->get();
    }


    public static function runPhyloConstruction($id){ 
        $phylo = Phylo::findOrFail($id);
        $artisan = config('app.rootDir')."/artisan";
        $command = "sleep 5 && $artisan jobs:construct-phylo $id > /tmp/status_phylo";

        $jp = new JobProcessController;
        $pid = $jp->runProcess($command, '/tmp/run_phylo');

        $phylo->status = "RUNNING";
        $phylo->pid = $pid;
        $phylo->save();
    }

}
