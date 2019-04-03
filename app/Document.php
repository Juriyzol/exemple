<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
	public $timestamps = false;
	
    public function files() {
    	return $this->hasMany('App\File','documents_id');
    }	
}
