<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type', 'public', 'user_id', 'dbSeq',
    ];
    
    public function user(){
    	return $this->belongsTo('App\User');
    }
}
