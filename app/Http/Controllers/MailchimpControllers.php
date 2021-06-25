<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NZTim\Mailchimp\MailchimpFacade as MailChimp;

class MailchimpControllers extends Controller
{
    protected $listId = '98c203f5fa';
    protected $storeId = 'raceon-store';
    //
    public function  user(Request $request)
    {
        $input = json_decode($request->getContent());
        $pinyin = app('pinyin');

        // 處理 Merge Fields

        $merge_fields = [
            'FNAME'      => $input->name,  //姓名
            'ADDRESS'    => is_null($input->address) ? '' : $input->address, //地址
            'PHONE'      => is_null($input->mobile) ? '' : $input->mobile, //電話
            'BIRTHDAY'   => is_null($input->birthday) ? '' : date_format(date_create($input->birthday), 'm/d/Y'), //生日
            'GENDER'     => is_null($input->gender) ? '' : $input->gender, //性別
            'CREATED_AT' => is_null($input->created_at) ? '' : date_format(date_create($input->created_at), 'm/d/Y'), //註冊時間
            'UPDATED_AT' => is_null($input->updated_at) ? '' : date_format(date_create($input->updated_at), 'm/d/Y'), //最後更新時間
            'ID'         => $input->id  //ID
        ];

        // 處理 Data

        $data = [
            'email_address' => $input->email, //Email
            'status'        => $input->accepts_marketing ? 'subscribed' : 'unsubscribed', //訂閱狀態
            'merge_fields'  => $merge_fields,
            'source'  => 'RACE ON', //來源
        ];

        $res = Mailchimp::api('put', "/lists/$this->listId/members/".md5($input->email), $data); // Returns an array.

            // 處理 Tags

            if (array_key_exists("tags", $input)) {
                $tags = [];
                foreach ($input->tags as $key => $tags_data) {
                    $tags[] = [
                        'name' => $pinyin->permalink($tags_data->name),
                        'status' => 'active',
                    ];
                }

                Mailchimp::api('post', "/lists/$this->listId/members/" . md5($input->email) . "/tags", [
                    'tags' => $tags
                ]);
            }

        return response()->json([ 
            'status' => 'success',
            'message' => 'Add or update a customer success!', 
            'data' => $res ]);
    }


    public function  product(Request $request, $add = FALSE)
    {

        $input = json_decode( $add ? $request->editor : $request->getContent());
        //先確認要更新還是要新增

                $product_info = Mailchimp::api('get', "/ecommerce/stores/$this->storeId/products/".$input->id);
                
                $method = isset($product_info["status"]) ? 'post' : 'patch' ;

                //$method = (array_search($input->id, array_column($product_info["products"], 'id')) === FALSE) ? 'post' : 'patch' ;

                // 處理 圖片

                $product_images =[];
                if (array_key_exists("photos", $input)) {
                    foreach ($input->photos as $key => $images_data) {
                        $product_images[] = [
                            "id"  => strval($images_data->id),
                            "url" => "https:".$images_data->url
                        ];
                    }
                }   

                // 處理 Data
                $data = [
                    "id"          => strval($input->id),
                    "title"       => $input->title,
                    "url"         => "https:".$input->product_url,
                    "description" => $input->brief,
                    "type"        => array_key_exists(0,$input->custom_collections) ? $input->custom_collections[0]->title : '',
                    "image_url"   => array_key_exists(0,$input->photos) ? 'https:'.$input->photos[0]->url : '',
                    "images"      => $product_images,
                    "variants"    => [[ "id" => strval($input->id), "title" => $input->title, "price" => $input->price, "url" => 'https:'.$input->product_url, ]],
                    "published_at_foreign" => date(DATE_ISO8601, strtotime($input->created_at)),
                ];

                $update = ($method == 'post') ? '' : '/'.$input->id ;


                $res = Mailchimp::api($method, "/ecommerce/stores/$this->storeId/products".$update, $data);

                return response()->json([ 
                    'status' => 'success',
                    'message' => $method.' a product success!', 
                    'data' => $res ]);

        
    }    

    public function  OrderCreate(Request $request)  {

        //確認訂單狀態
        if (!$request->headers->has('x-cyberbiz-event')) {

            return response()->json([ 
                'status' => 'error',
                'message' => 'No event in header.'
            ],403);

        }

        $method = $request->header('x-cyberbiz-event') == 'orders/create' ? 'post' : 'patch';

        function  financial_status($status)  {
            switch ($status) {
                case 'paid':
                case 'cod': 
                    return 'paid';
                  break;
                case 'pending':
                case 'remitted': 
                case 'failed':     
                    return 'pending';
                  break;
                case 'pending_refund':
                case 'pending_partial_refund': 
                case 'partial_refunded':   
                case 'refunded':      
                    return 'refunded';
                  break;
                default:
                    return '';
              }
        }

        function  fulfillment_status($status)  {
            switch ($status) {
                case 'unshipped':
                case 'preparing': 
                    return 'pending';
                  break;
                case 'partial':
                case 'fulfilled': 
                case 'arrived':
                case 'received':
                case 'problem':           
                    return 'shipped';
                  break;
                case 'returned':
                case 'expired':  
                    return 'refunded';
                  break;
                default:
                    return '';
              }
        }


        $input = json_decode($request->getContent());

        //  處理購買清單
        $line_items =[];
        if (array_key_exists("line_items", $input)) {
            foreach ($input->line_items as $key => $items_data) {
                $line_items[] = [
                    "id"                 => "$items_data->id",
                    "product_id"         => "$items_data->product_id",
                    "product_variant_id" => "$items_data->product_id",
                    "quantity"           =>  $items_data->quantity,
                    "price"              =>  $items_data->price,
                    "discount"           =>  $items_data->total_discount,
                ];
            }
        }   

        // 處理 Data
        $data = [
            "id" => "$input->order_number",
            "customer" => [
                "id" => strval($input->customer->id),
                "email_address" => $input->customer->email,
                "opt_in_status" => $input->customer->accepts_marketing,
                "company" => isset($input->customer->address->company)?:"",
                "first_name" => $input->customer->name,
                "order_total" => $input->prices->total_line_items_price,
                "currency_code" => "TWD",
                "address" => [
                    "address1" => $input->customer->address->address,
                    "city" => $input->customer->address->detail_address->city,
                    "province" => $input->customer->address->detail_address->province,
                    "postal_code" => $input->customer->address->detail_address->zip,
                    "country" => isset($input->customer->address->detail_address->country)?:"Taiwan",
                    "country_code" => "TW"
                ],
                "created_at" => date(DATE_ISO8601, strtotime($input->customer->created_at)),
                "updated_at" => date(DATE_ISO8601, strtotime($input->customer->updated_at)),
            ],
            "lines" => $line_items,
           // "campaign_id" => "839488a60b",
           // "landing_site" => "http://www.example.com?source=abc",
            "financial_status" => financial_status($input->statuses->financial_status),
            "fulfillment_status" => fulfillment_status($input->statuses->fulfillment_status),
            "currency_code" => "TWD",
            "order_total" => $input->prices->total_price,
            "order_url" => "https:".$input->payment_url,
            "discount_total" => $input->prices->total_line_items_price - $input->prices->total_price,
            "shipping_total" => $input->prices->shipping_rate_price,
            "tracking_code" => "prec",
            "processed_at_foreign" => date(DATE_ISO8601, strtotime($input->created_at)),
            "cancelled_at_foreign" => isset($input->timings->cancelled_at) ? date(DATE_ISO8601, strtotime($input->timings->cancelled_at)) : '',
            "updated_at_foreign"   => date(DATE_ISO8601, strtotime($input->updated_at)),
            "shipping_address" => [
                "name" => $input->receiver->name,
                "address1" => $input->receiver->address,
                "city" => $input->receiver->detail_address->city,
                "province" => $input->receiver->detail_address->province,
                "postal_code" => $input->receiver->detail_address->zip,
                "country" => isset($input->receiver->detail_address->country)?:'',
                "phone" => $input->receiver->detail_address->zip,
            ],
            "billing_address" => [
                "name" => $input->customer->name,
                "address1" => $input->customer->address->address,
                "city" => $input->customer->address->detail_address->city,
                "province" => $input->customer->address->detail_address->province,
                "postal_code" => $input->customer->address->detail_address->zip,
                "country" => isset($input->customer->address->detail_address->country)?:'Taiwan',
                "phone" => $input->customer->mobile,
                "company" => isset($input->customer->address->company)?:'',
            ],
            "promos" => isset($input->prices->discounts->coupon_discount)? [
                [
                    "code" => $input->prices->discounts->coupon_discount->code,
                    "amount_discounted" => $input->prices->discounts->coupon_discount->amount,
                    "type" => "fixed"
                ]
            ]:"",
        ]; 

        $res = Mailchimp::api($method, "/ecommerce/stores/$this->storeId/orders", $data);
        
        return response($res);

    }
}
