<?php

// 設定ファイル読み込み
$config = parse_ini_file(__DIR__ . '/api.conf');

$request_url = create_request_url('splatoon');

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
    $params["category_id"] = 2161;
    $params["store_id"] = "wondergoo";
    $params["condition"] = "new";
    $params["seller"] = "store";
    $params["hits"] = 50;
    //$params["query"] = str_replace("%7E", "~", rawurlencode($keyword));
    
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
    
    print_r($yahoo_xml->attributes()->totalResultsAvailable);

    $items = array();
    foreach($yahoo_xml->Result->Hit as $item){
    
        if(empty((string)$item->ReleaseDate)){
            continue;
        }

        $items[] = array(
            'name' => (string)$item->Name,
            'after_name' => name_normalize((string)$item->Name),
            'url' => (string)$item->Url,
            'price' => (string)$item->Price,
            'releaseDate' => (string)$item->ReleaseDate,
            'store_id' => (string)$item->Store->Id,
            'store_name' => (string)$item->Store->Name,
            'category_id' => (string)$item->Category->Current->Id,
            'category_name' => (string)$item->Category->Current->Name,
        );
    
    }
    return $items;
}

function name_normalize($name){
    $name = preg_replace('/【.*?】/i', '', $name);
    $name = preg_replace('/《.*?》/i', '', $name);
    $name = preg_replace('/\[.*?\]/i', '', $name);

    $name = preg_replace('/3DS/i', '', $name);
    $name = preg_replace('/PS VITA/i', '', $name);
    $name = preg_replace('/PS4/i', '', $name);
    return $name;
}
