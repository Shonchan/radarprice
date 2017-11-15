<?php

namespace App\Http\Controllers\API;



use App\Brand;
use App\Category;
use App\Landing;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Products extends Controller
{
    private  $last_id = './config/last_id.json';

    private function loadLastId(){
        if ( file_exists($this->last_id) ) {
            $file = file_get_contents($this->last_id);
            $json = json_decode($file);
            return $json->last_id;
        } else return 0;
    }

    private function saveLatId($id){
        $json = new \stdClass();
        $json->last_id = $id;
        file_put_contents($this->last_id, json_encode($json));
    }

    private function getProductsList($id){
        $url = "http://phonepark.ru/tools/get_data.php?method=products&id=".$id;
        $file = file_get_contents($url);

        return json_decode($file);
    }

    private function addProduct($item){
        $product = new Product();
        if ($brand = Brand::where('name', '=', $item->brand)->first()) {

            $product->brand_id = $brand->id;
        } else {
            $brand = new Brand();
            $brand->name = $item->brand;
            $brand->save();
            $product->brand_id = $brand->id;
        }

        if ($cat = Category::where('name', '=', $item->category)->first()) {

            $product->category_id = $cat->id;
        } else {
            $cat = new Category();
            $cat->name = $item->category;
            $cat->save();
            $product->category_id = $cat->id;
        }

        $product->name = $item->name;
        $product->search_keys = $brand->name.' '.$product->name;
        $destinationPath =  public_path().'/files/images/';
        $arr = explode('.', $item->image);
        $fname = str_random(20);

        while(file_exists($destinationPath.$fname.'.'.end($arr))) {
            $fname = str_random(20);
        }
        $filename = $fname.'.'.end($arr);
            \Image::make($item->image)->save($destinationPath.$filename);
            $product->image = $filename;
        //dd($product);
        $product->save();

    }

    public function updateProducts(){
        $id = $this->loadLastId();
        $products = $this->getProductsList($id);
        while ($item = end($products)) {
            foreach ($products as $product){
                $this->addProduct($product);
            }
            $id = $item->id;
            $products = $this->getProductsList($id);
        }

        $this->saveLatId($id);

    }

    public function newLand(Request $request){
        $l = new Landing();
        $l->name = 'Areviews.ru';
        $l->url = 'http://areview.ru';
        $l->api_id = str_random(20);
        $l->user_id = $request->user()->id;
        $l->save();
    }
}
