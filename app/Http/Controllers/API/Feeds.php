<?php

namespace App\Http\Controllers\API;

use App\Feed;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Feeds extends Controller
{
    private $optionsFile = './config/sop.json';

    public function getList()
    {
        return response()->json(Feed::all());
    }

    public function getFeed($id)
    {
        $feed = Feed::find($id);
        $feed->filter = json_decode($feed->filter);
        return response()->json($feed);
    }

    public function addFeed()
    {
        $xml = json_decode(file_get_contents('php://input'));
        if(!empty($xml)){

            $xml->filter = json_encode($xml->filter);

            if($xml->logo && (substr($xml->logo, 0, 7) == 'http://')){

                $destinationPath =  public_path().'/files/feeds/';
                $arr = explode('.', $xml->logo);
                $fname = str_random(20);

                while(file_exists($destinationPath.$fname.'.'.end($arr))) {
                    $fname = str_random(20);
                }

                $filename = $fname.'.'.end($arr);
                \Image::make($xml->logo)->save($destinationPath.$filename);

                $xml->logo = $filename;
            }

            if($xml->id == null) {
                unset($xml->id);
                //return print_r($xml);
                //return response()->json($xml);
                $feed = Feed::create((array)$xml);
            } else {
                $feed = Feed::find($xml-id)->update($xml);
            }

            return response()->json($feed);

        }



        //return response()->json(json_decode('{"id":1}'));
    }

    public function deleteFeed($id)
    {

            $feed = Feed::find($id);
            if($feed) {
                $feed->delete();
                return response()->json(true);
            }
            return response()->json(false);

    }

    public function getOptions()
    {
        if ( file_exists($this->optionsFile) ) {
            $file = file_get_contents($this->optionsFile);
            $json = json_decode($file);
            return response()->json($json);
        } else response()->json();
    }

    public function saveOptions()
    {
        $data = json_decode(file_get_contents('php://input'));

        file_put_contents($this->optionsFile, json_encode($data));
        return response()->json(true);
    }

    public function newFeed(){
        $arr = new \stdClass();
        $arr->id = null;
        $arr->name = 'Новый';
        $arr->filename = 'novyi';
        $arr->url = 'http://hitech2.ru';
        $arr->cpc = 4.0;
        $arr->key_field = 0;
        $arr->logo = '';
        $arr->currency = '';
        $arr->filter = 'dsdsds';

        $feed = Feed::create((array)$arr);
        dd($feed);
    }
}
