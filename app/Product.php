<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;

/**
 * App\Product
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    use Eloquence;

    protected $searchableColumns = [
        'search_keys' => 20,
    ];

    public function offers(){
        $this->belongsToMany('App\Offer', 'product_offers');
    }

    public function brand() {
        return $this->belongsTo('App\Brand');
    }

    public function category() {
        return $this->belongsTo('App\Category');
    }
}
