<?php

// 設定ファイル読み込み
$config = parse_ini_file(__DIR__ . '/api.conf');

$request_url = create_request_url('艦これ');

print_r(get_yahoo_result($request_url));

function create_request_url($keyword){
    global $config;

    // リクエストURL
    $base_url = '';
    switch($config['RESULT_FORMAT_TYPE']){
        case 'XML' :
            $base_url = "http://shopping.yahooapis.jp/ShoppingWebService/V1/itemSearch";
            break;
        case 'PHP' :
            $base_url = "http://shopping.yahooapis.jp/ShoppingWebService/V1/php/itemSearch";
            break;
        case 'JSON' :
            $base_url = "http://shopping.yahooapis.jp/ShoppingWebService/V1/json/itemSearch";
            break;
    }

    // リクエストパラメータ作成
    $params = array();
    $params["appid"] = $config['APPLICATION_ID'];
    $params["query"] = str_replace("%7E", "~", rawurlencode($keyword));
    
    $param_string = "";
    foreach ($params as $key => $value) {
        $param_string .= "&" . $key . "=" . $value;
    }
    // 先頭の'&'を除去
    $param_string = substr($param_string, 1);
    
    // URL を作成
    $request_url = $base_url . "?" . $param_string;
        
    return $request_url;
}
    

function get_yahoo_result($url) {
    // XMLをオブジェクトに代入
    $yahoo_xml = simplexml_load_string(@file_get_contents($url));
    
    $items = array();
    foreach($yahoo_xml->Result->Hit as $item){
    
        $items[] = array(
        'name' => (string)$item->Name,
        'url' => (string)$item->Url,
        'img' => (string)$item->Image->Medium,
        'price' => (string)$item->Price,
        );
    
    }
    return $items;
}
