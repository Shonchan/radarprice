<?php

namespace App\Http\Controllers\API;

use App\Landing;
use App\Offer;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Offers extends Controller
{
    public function getModelOffers(Request $request){
        if($request->has('api_id')) {
            $land = Landing::where('api_id', '=', $request->api_id)->first();
        }
        if(isset($land)) {

            if($request->has('model')) {
                $model = $request->model;
                $products = Product::search( $model )->get();

                $pti = 0;

                foreach ( $products as $p ) {
                    $arr1 = explode( ' ', strtolower( $p->search_keys ) );
                    $arr2 = explode( ' ', strtolower( $model ) );

                    $ti = count( array_intersect( $arr2, $arr1 ) );


                    if($ti > $pti) {
                        $mp = $p;
                        $pti = $ti;
                    }

                    if ( $ti / count( $arr1 ) == 1 ) {
                        $product = $p;
                    }
                }

                if(isset($product)) {

                    $offers = Offer::whereIn('id', function($query) use ($product) {
                        $query->select('offer_id')->from('product_offers')
                            ->where('product_id', '=', $product->id);
                    })->orderBy('cpc', 'desc')
                        ->orderBy('views', 'desc')
                        ->limit(10)->get();

                    Offer::whereIn('id', function($query) use ($product) {
                        $query->select('offer_id')->from('product_offers')
                            ->where('product_id', '=', $product->id);
                    })->orderBy('cpc', 'desc')
                        ->orderBy('views', 'desc')
                        ->limit(10)->increment('views');

                } elseif ( isset($mp) ) {

                    //echo $mp->brand->name;
                    $offers = Offer::whereIn('id', function($query) use ($mp) {
                        $query->select('offer_id')->from('product_offers')
                            ->whereIn('product_id', function($q) use ($mp){
                                $q->select('id')->from('products')->where('brand_id', '=', $mp->brand->id)
                                    ->where('category_id', '=', $mp->category->id);
                            });
                    })->orderBy('cpc', 'desc')
                        ->orderBy('views', 'desc')
                        ->limit(10)->get();

                    Offer::whereIn('id', function($query) use ($mp) {
                        $query->select('offer_id')->from('product_offers')
                            ->whereIn('product_id', function($q) use ($mp){
                                $q->select('id')->from('products')->where('brand_id', '=', $mp->brand->id)
                                    ->where('category_id', '=', $mp->category->id);
                            });
                    })->orderBy('cpc', 'desc')
                        ->orderBy('views', 'desc')
                        ->limit(10)->increment('views');
                }





            }

            if(empty($offers)) {
               $offers = Offer::orderBy('cpc', 'desc')
                   ->orderBy('views', 'desc')
                   ->limit(10)->get();

                Offer::orderBy('cpc', 'desc')
                    ->orderBy('views', 'desc')
                    ->limit(10)->increment('views');
            }

            $items = [];

            foreach($offers as $o) {
                $item = new \stdClass();
                $item->name = $o->name;
                $item->id = $o->hash;
                $data = ['api_id'=>$land->api_id];
                $item->url = url('to/'.$o->url.'?'.http_build_query($data));
                $item->image = $o->image;
                $item->price = $o->price;
                $items[] = $item;
            }

            return response()->json($items);
        }

        return response()->json(["error"=>"Landing not found"]);

    }
}
