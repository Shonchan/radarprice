<?php

namespace App\Console\Commands;

use App\Feed;
use App\Offer;
use App\Product;
use App\Url;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Nathanmac\Utilities\Parser\Parser;

class GetFeeds extends Command
{

    private $path = '/files/feeds/';
    private $exist_filter;
    /**
     *
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:feeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test';


    private $optionsFile = '/config/sop.json';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->exist_filter = $this->getJson();

        $this->clearOffers();
        //dd('test');

        $destinationPath =  public_path().$this->path;
        $feeds = Feed::all();
       /* foreach ($feeds as $f) {
           file_put_contents($destinationPath.$f->filename.'.xml', fopen($f->url, 'r'));
           $f->downloaded = date('Y-m-d H:i:s', time());
           $f->save();
        }*/

        foreach ( $feeds as $f ) {
            $f->filter = json_decode($f->filter);
            $parser = new Parser();
            $data = file_get_contents($destinationPath.$f->filename.'.xml');

            //$d = \Parser::xml($data);
            $mask = $mask = [
                'offer' => '*'
            ];

            //$parser->mask($mask);
            $d = $parser->xml($data);
            //$cat = $d->mask($mask);
            $cats = [];
            $cat_phone_id = [];
            //print_r($d['shop']['offers']['offer']);
            foreach ( $d['shop']['categories']['category'] as $c ) {

                if (in_array(mb_convert_case($c["#text"], MB_CASE_LOWER, "UTF-8"), $f->filter->categories))  {
                    $cats[] = $c['@id'];
                    $cat_phone_id[] = $c['@id'];
                }
                if(isset($c['@parentId']))
                    if(in_array($c['@parentId'], $cat_phone_id)){
                        $cats[] = $c['@id'];
                        $cat_phone_id[] = $c['@id'];
                    }
            }

            $offers = [];

            $ids = array();

            foreach ($d['shop']['offers']['offer'] as $offer) {
                if(in_array($offer['categoryId'], $cats)) {
                    $offers[] = $offer;
                }
            }



            //print(count($offers));
            $ids = array();

            foreach ($offers as &$o) {

                switch ($f->key_field){
                    case 0:
                        $o['name'] = $o['name'];
                        break;
                    case 1:
                        $o['name'] = $o['model'];
                        break;
                    case 2:
                        $o['name'] = $o['name'].' '.$o['model'];
                        break;
                    default:
                        $o['name'] = $o['name'];

                }
                $offer = $this->parseOffer($o, $f);
                $ids[] = $offer->id;

            }

            print(count($ids)."\n");

            \DB::table('product_offers as po')
                ->join('offers as o', 'po.offer_id', '=', 'o.id')
                ->whereNotIn('po.offer_id', $ids)
                ->where('o.feed_id', '=', $f->id)
                ->delete();

            Offer::whereNotIn('id', $ids)
                ->where('feed_id', '=', $f->id)
                ->delete();
            print("time\n");

            $time = date('Y-m-d H:i:s', time());
            Feed::whereId($f->id)->update(['updated' => $time]);
            print("end\n");
        }



    }

    private function notExistFilter($key){
        $not_exist = true;
        $words = explode(' ', strtolower($key));
        foreach($words as $word){
            if(in_array($word, $this->exist_filter->exclude)) $not_exist = false;
        }
        return $not_exist;

    }

    private function getJson()
    {
        $path = public_path().$this->optionsFile;
        if ( file_exists($path) ) {
            $file = file_get_contents($path);
            $json = json_decode($file);
            return $json;
        } else false;
    }


    private function parseOffer($o, $f) {
        $key = strtolower(str_replace(',', '', $o['name']));

        foreach($f->filter->replaces as $r){
            $key = str_replace($r->search, $r->replace, $key);
        }

        foreach($f->filter->pregReplaces as $pr){
            $key = preg_replace($pr->search, $pr->replace, $key);
        }

        foreach ($this->exist_filter->replaces as  $rep) {
            $key = str_replace(mb_convert_case($rep->search, MB_CASE_LOWER, "UTF-8"), $rep->replace, $key);
        }

        $key = trim($key);

        if ($this->notExistFilter($key)) {

            $products = Product::search( $key )->get();

            foreach ( $products as $p ) {
                $arr1 = explode( ' ', strtolower( $p->search_keys ) );
                $arr2 = explode( ' ', $key );

                $ti = count( array_intersect( $arr2, $arr1 ) );

                if ( $ti / count( $arr1 ) == 1 ) {
                    $o[ 'product_id' ] = $p->id;
                    //dd ($o[ 'product_name' ]);
                }

            }
        }

        $offer = Offer::where('feed_id', '=', $f->id)
                    ->where('hash', '=', md5($o['name']))->first();
        if (!$offer)
            $offer = new Offer();

        $offer->feed_id = $f->id;
        $offer->name = $o['name'];
        $offer->price = $o['price'];
        $offer->cpc = 0.00;
        $offer->hash = md5($o['name']);
        if(isset($o['stock']))
            $offer->stock = (int)filter_var($o['stock'], FILTER_VALIDATE_BOOLEAN);
        if(isset($o['delivery']))
            $offer->delivery = (int)filter_var($o['delivery'], FILTER_VALIDATE_BOOLEAN);
        if(isset($o['manufacturer_warranty']))
            $offer->warranty = (int)filter_var($o['manufacturer_warranty'], FILTER_VALIDATE_BOOLEAN);
        if(isset($o['pickup']))
            $offer->pickup = (int)filter_var($o['pickup'], FILTER_VALIDATE_BOOLEAN);
        if(isset($o['store']))
            $offer->store = (int)filter_var($o['store'], FILTER_VALIDATE_BOOLEAN);
        if(isset($o['description']))
            $offer->description = $o['description'];
        if( isset( $o[ 'picture' ] ) ) {
            if ( is_array( $o[ 'picture' ] ) )
                $offer->image = $o[ 'picture' ][ 0 ];
            else
                $offer->image = $o[ 'picture' ];
        }


        if ( $url = Url::where('hash', '=', md5(urlencode($o['url'])))->first() )


            $offer->url = $url->hash;
        else {
            $url = new Url();
            $url->url = $o['url'];
            $url->hash = md5(urlencode($o['url']));
            $url->save();
            $offer->url = $url->hash;
        }

        $offer->save();

        if (isset($o['product_id'])) {
            //\DB::table('product_offers')->firstOrCreate(['product_id' => $o['product_id'], 'offer_id' => $offer->id]);
           // echo('test');
            $po = \DB::table('product_offers')
                        ->where('product_id', '=', $o['product_id'])
                        ->where('offer_id', '=' , $offer->id)
                        ->first();
            if(!$po) {
                \DB::table('product_offers')->insert(
                    ['product_id' => $o['product_id'], 'offer_id' => $offer->id]
                );
            }


        }


        return $offer;
    }

    private function clearOffers(){
        //$query = \DB::table('offers')->where('updated', '<', 'DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 DAY)');
        //\DB::enableQueryLog();
        \DB::table('product_offers')->whereIn('offer_id', function($query){
            $query->select('id')->from('offers')
                ->where('updated_at', '<', Carbon::now()->subDays(5));
        })->delete();
        //print_r(\DB::getQueryLog());
        //\DB::disableQueryLog();
        //print_r($pos);
        Offer::where('updated_at', '<', Carbon::now()->subDays(5))->delete();
    }




}
