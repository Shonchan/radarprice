<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Url
 *
 * @mixin \Eloquent
 */
class Url extends Model
{
    public function offer(){
        return   $this->belongsTo('App\Offer', 'hash', 'url');
    }
}
