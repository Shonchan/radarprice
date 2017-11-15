<?php

namespace App\Http\Controllers;

use App\Landing;
use App\Unique;
use App\Url;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UrlsController extends Controller
{
    public function toUrl($hash, Request $request){
        if ($url = Url::where('hash', '=', $hash)->first()){
                if(strpos($url->url, 'admitad') > 0) {
                    $data = ['subid'=>'', 'subid2'=>''];
                }
                    $link = $url->url;
                    if ($request->has('api_id')) {
                        if($land = Landing::where('api_id', '=', $request->api_id)->first()) {
                            $land->increment('clicks');
                        }
                    }


                    $offer = $url->offer;

                    $offer->increment('clicks');

                    if(isset($data)){
                        $data['subid'] = $land->id;
                        $data['subid2'] = $offer->id;
                        $link .= '&'.http_build_query($data);
                    }

                    if($this->isUnique($request->ip(), $offer->feed->user->id)){
                       // echo 'unique';
                    }

                    return redirect($link);


        } else {
            return abort(404);
        }
    }

    private function isUnique($ip, $user_id){
        //\DB::enableQueryLog();
        $click = Unique::where('ip', '=', $ip)
            ->where('user_id', '=', $user_id)
            ->where('created_at', '>', Carbon::now()->subDays(1))
            ->first();
        //print_r(\DB::getQueryLog());
        //\DB::disableQueryLog();
        if(isset($click)){
                return false;
        }

        $click = new Unique();
        $click->ip = $ip;
        $click->user_id = $user_id;
        $click->save();
        return true;
    }
}
