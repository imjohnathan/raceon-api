<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;
use NZTim\Mailchimp\MailchimpFacade as MailChimp;

class MailchimpWebControllers extends Controller
{
    public function  products(Request $request)
    {
        $message = [];
        if (isset($_GET["del"])) {
            $res = Mailchimp::api('delete', "/ecommerce/stores/raceon-store/products/".$_GET["del"]); 
            //return redirect('products')->with('status', 'Profile updated!');
            $message = [ "status" => "done", "message" => "Delete Product done!"];
        }elseif (isset($_GET["edit"]) || isset($_GET["add"])) {
            $res = '{"id":"","currency_code":"TWD","title":"","handle":"","url":"https://www.raceon.com.tw/products/vitamins-de","description":"有助維持骨骼、肌肉、神經正常機能\n每日滴，打造更強身體素質\n強化健康第一道防線，提升保護力\n委託PIC/S GMP認證藥廠出品、臺灣生產\n採用全球知名營養品牌原料\n油性劑型設計，吸收率較佳\n跑者使用調查，維生素D指數有效提升33%\n液態滴劑好入口，每滴300IU= 60杯牛奶\n通過運動禁藥檢測，運動員安心使用","type":"Defense 運動防禦修護系列","vendor":"RACE ON","image_url":"https://cdn.cybassets.com/media/W1siZiIsIjEyNTU2L3Byb2R1Y3RzLzI4OTg3NjkwLzE2MDg2OTI5NDFfYzhmOWY0ODU5NDhiOTg5YTg0NmMuanBlZyJdXQ.jpeg?sha=c0a4ca738433b575","variants":[{"id":"28987690","title":"液態盾維生素 D3+E 滴劑 Liquid Shield Vitamin D3+E Drops","url":"https://www.raceon.com.tw/products/vitamins-de","sku":"","price":750,"inventory_quantity":0,"image_url":"","backorders":"","visibility":"","created_at":"2021-06-10T05:33:41+00:00","updated_at":"2021-06-12T14:46:24+00:00"},{"id":"30660511","title":"液態盾維生素 D3+E 滴劑 Liquid Shield Vitamin D3+E Drops","url":"https://www.raceon.com.tw/products/vitamins-de","sku":"FG8250040BB00","price":750,"inventory_quantity":0,"image_url":"","backorders":"","visibility":"","created_at":"2021-06-02T07:39:08+00:00","updated_at":"2021-06-12T14:46:24+00:00"}],"images":[{"id":"2329408","url":"https://cdn.cybassets.com/media/W1siZiIsIjEyNTU2L3Byb2R1Y3RzLzI4OTg3NjkwL-W3peS9nOWNgOWfnyA1X2NhNDc3NTJhMTcxM2EwYjAxZDA2LmpwZWciXV0.jpeg?sha=4b8dd6cc2a0e287c","variant_ids":[]},{"id":"2344479","url":"https://cdn.cybassets.com/media/W1siZiIsIjEyNTU2L3Byb2R1Y3RzLzI4OTg3NjkwL0FydGJvYXJkIDFfNzc1MTgwZjRiNThkNzk3MDg4MTguanBlZyJdXQ.jpeg?sha=10e31bd63edc8e7e","variant_ids":[]},{"id":"3848903","url":"https://cdn.cybassets.com/media/W1siZiIsIjEyNTU2L3Byb2R1Y3RzLzI4OTg3NjkwLzE2MDg2OTI5NDFfYzhmOWY0ODU5NDhiOTg5YTg0NmMuanBlZyJdXQ.jpeg?sha=c0a4ca738433b575","variant_ids":[]},{"id":"3899260","url":"https://cdn.cybassets.com/media/W1siZiIsIjEyNTU2L3Byb2R1Y3RzLzI4OTg3NjkwLzE2MDk5MTE5NzVfNzQyMTkyN2NlYWE4MGIwOGM2OGYuanBlZyJdXQ.jpeg?sha=255e175c5bb4b256","variant_ids":[]},{"id":"3899261","url":"https://cdn.cybassets.com/media/W1siZiIsIjEyNTU2L3Byb2R1Y3RzLzI4OTg3NjkwLzE2MDk5MTE5NzVfODQ0Y2Q5YzZmNjQyMTcyNWM1MDkuanBlZyJdXQ.jpeg?sha=25797871fa0306a8","variant_ids":[]}],"published_at_foreign":"2019-11-28T17:02:05+00:00"}';
            $res = json_decode($res, true) ;
            if (isset($_POST["editor"])) {
                if (isset($_POST["source"]) && $_POST["source"] == "cyberbiz") {
                    $save = app('App\Http\Controllers\MailchimpControllers')->product($request, true);
                }else{   
                    $update =isset($_GET["edit"]) ? '/'.$_GET["edit"] : '';
                    $save = Mailchimp::api( isset($_GET["edit"]) ? 'patch' : 'post' , "/ecommerce/stores/raceon-store/products".$update, json_decode($_POST["editor"], true));  
                }
                $message = [ "status" => "done", "message" => "Update Product done!", "data" => $save ];
            }   
            if (isset($_GET["edit"])) {
                $res = Mailchimp::api('get', "/ecommerce/stores/raceon-store/products/".$_GET["edit"]); 
            }

            return view('mailchimp.products', [
                'component' => 'edit',
                'data'      => $res,
                'message'   => $message
            ]);
            
        }

        $res = Mailchimp::api('get', "/ecommerce/stores/raceon-store/products?count=1000"); 

        return view('mailchimp.products', [
            'component' => 'products_table',
            'products'  => $res, 
            'message'   => $message
        ]);
    }

    public function  orders(Request $request)
    {
        $message = [];

        $res = Mailchimp::api('get', "/ecommerce/orders"); 

        return view('mailchimp.products', [
            'component' => 'orders',
            'data'      => $res,
            'message'   => $message
        ]);
    }    

}
