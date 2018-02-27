<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    //
	protected $table = 'process';

    protected $fillable = [
    	'job_id', 'pid', 'process', 'status', 'output', 'submitted_at', 'finished_at',
    ];

    public $timestamps = false;

    public function job(){
    	return $this->belongsTo('App\Job');
    }
}
