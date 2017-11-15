<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Offer
 *
 * @mixin \Eloquent
 */
class Offer extends Model
{
    public function products(){
        return $this->belongsToMany('App\Product', 'product_offers');
    }

    public function toUrl(){
        return $this->hasOne('App\Url', 'hash', 'url');
    }

    public function feed(){
        return $this->belongsTo('App\Feed');
    }
}
