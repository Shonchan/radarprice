<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Feed
 *
 * @mixin \Eloquent
 */
class Feed extends Model
{
    protected $fillable = ['id', 'name', 'filename', 'url', 'cpc', 'logo', 'filter', 'key_field', 'currency'];
    public $timestamps = false;

    public function offers(){
        return $this->hasMany('App\Offer');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }
}
