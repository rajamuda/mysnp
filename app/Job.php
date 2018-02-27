<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'user_id', 'mapper', 'caller', 'status', 'submitted_at', 'finished_at',
    ];

    public $timestamps = false;
    
    public function user(){
    	return $this->belongsTo('App\User');
    }

    public function process(){
    	return $this->hasMany('App\Process');
    }
}
