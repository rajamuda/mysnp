<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Phylo extends Model
{
    protected $table = 'phylo';

    protected $fillable = [
    	'user_id', 'refseq_id', 'samples', 'method', 'status', 'submitted_at', 'finished_at',
    ];

    public $timestamps = false;

    public function refseq(){
    	return $this->belongsTo('App\Sequence');
    }
}
