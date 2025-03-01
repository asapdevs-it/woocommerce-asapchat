<?php 
class Asapchat {

    public function get_api_key(){

    }

    public function check_auth($keyBearer, $keySD){
        $isAuth = $keyBearer === $keySD;
        return $isAuth;
    }

    public function get_type_shop(){
        return "wordpress";
    }

    public function checkConnection($isAuth, $shop_type){
        return [
            "message"=>$isAuth ? "Success" : "Error",
            "data"=>$isAuth ? "Aktywacja prawidłowa" : "Błąd klucza sklepu",
            "type"=>$shop_type
        ];
    }

    public function response_get_one_order_by_number($order){
        return [
            // "mode":"getOneOrderByNumber",
            "message"=> "Success",
            "data"=>$data,
            "type"=>$shop_type,
        ];
    }

    public function response_get_all_orders_by_email($orders){
        return [
            
        ];
    }
}


// {
//     /* „Success" lub "Error" */
//     "message":<String>,
//     "data": {
//      "order_number":<String>,
//      "is_paid":<Bool>,
//      "client_name":<String>,
//      "client_email":<Email>,
//      "product_list":<Array>[{
//      "name":<String>,
//      "sku":<String>,
//      "price":<Float>,
//      "amount":<Int>,
//      "total":<Float>
//      }],
//      "total_price":<Float>,
//      "current_status":<String>
//     }
//     /* "magento", "wordpress", "prestashop" lub "custom" */
//     "type":<String>
//     }

// /* "Success" lub "Error" */
// "message":<String>,
// "data": {
//  "order_number":<String>,
//  "is_paid":<Bool>, 
//  "client_name":<String>,
//  "client_email":<Email>,
//  "product_list":<Array>[{
//  "name":<String>,
//  "sku":<String>,
//  "price":<Float>,
//  "amount":<Int>,
//  "total":<Float>
//  }],
//  "total_price":<Float>,
//  "current_status":<String>,
//  "statuses_history":<Array>[{
//  "name":<String>,
//  "date":<Date format "Y-m-d H:i:s">,
//  }],
//  "lading_number":<String>,
//  "client_phone":<String>,
//  "delivery_address":<String>,
//  "billing_address":<String>,
//  "delivery_method":<String>,
//  "payment_method":<String>
// }
// /* "magento", "wordp