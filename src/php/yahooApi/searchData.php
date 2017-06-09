<?php

// 設定ファイル読み込み
$config = parse_ini_file(__DIR__ . '/api.conf');
$hits = 0;

$params = array();
$params["appid"] = $config['APPLICATION_ID'];
$params["category_id"] = 2161;
$params["store_id"] = "wondergoo";
$params["condition"] = "new";
$params["seller"] = "store";
$params["hits"] = 50;

$result = array();

$request_url = create_request_url($params);
get_yahoo_result($request_url);

$offset_idx = 1;
while(50 * $offset_idx < $hits){
    $params["offset"] = 50 * $offset_idx;
    $request_url = create_request_url($params);
    get_yahoo_result($request_url);
    $offset_idx += 1;
}

data_regist($result);

/**
 * リクエスト用の URL を生成する 
 * @author rTsujimoto
 * @param array リクエストパラメータ
 * @return string URL
 */
function create_request_url($params){
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
    
/**
 * リクエストを発行する
 * @author rTsujimoto
 * @param string URL
 * @return array 取得結果
 */
function get_yahoo_result($url) {
    global $hits, $result;

    // XMLをオブジェクトに代入
    $yahoo_xml = simplexml_load_string(@file_get_contents($url));
    
    $hits = $yahoo_xml->attributes()->totalResultsAvailable;

    foreach($yahoo_xml->Result->Hit as $item){
        $result[] = array(
            'janCode'     => (string)$item->JanCode, // Janコード
            'name'        => name_normalize((string)$item->Name),
            'url'         => (string)$item->Url,
            'price'       => (int)$item->Price,
            'releaseDate' => substr((string)$item->ReleaseDate, 0, 10),
            'category_id' => (string)$item->Category->Current->Id,
        );
    }
    return;
}

function data_regist($insert_data){
    global $config;
    $pdo = new PDO('mysql:dbname=' . $config['DATABASE_NAME'] . ';host=' . $config['ACCESS_DB'] . ';charset=utf8mb4', $config['DATABASE_USER'], $config['DATABASE_PASSWORD']);
    $stmt = $pdo->prepare("INSERT INTO YahooItems (jan, name, url, price, releaseDate, categoryId, updatedDate) VALUES (:jan, :name, :url, :price, :releaseDate, :categoryId, NOW())");
    foreach($insert_data as $data){
        $stmt->bindValue(':jan', $data['janCode'], PDO::PARAM_STR);
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':url', $data['url'], PDO::PARAM_STR);
        $stmt->bindValue(':price', $data['price'], PDO::PARAM_INT);
        $stmt->bindValue(':categoryId', $data['category_id'], PDO::PARAM_STR);

        if(empty($data['releaseDate'])){
            $stmt->bindValue(':releaseDate', null, PDO::PARAM_NULL);
        }else{
            $stmt->bindValue(':releaseDate', $data['releaseDate'], PDO::PARAM_STR);
        }

        $stmt->execute();
    }
}

/**
 * 不要な文字列を精査する
 * @author rTsujimoto
 * @param string 対象文字列
 * @return string 精査後の文字列
 */
function name_normalize($name){

    // 特定の文字列を削除
    $name = preg_replace('/\d{8}/i', '', $name);
    $name = preg_replace('/3DS/i', '', $name);
    $name = preg_replace('/PS VITA/i', '', $name);
    $name = preg_replace('/PS4/i', '', $name);

    // カッコとその中身を削除
    $name = preg_replace('/【.*?】/i', '', $name);
    $name = preg_replace('/《.*?》/i', '', $name);
    $name = preg_replace('/＜.*?＞/i', '', $name);
    $name = preg_replace('/（.*?）/i', '', $name);
    $name = preg_replace('/\[.*?\]/i', '', $name);

    // 記号名を削除
    $name = preg_replace('/☆/i', '', $name);
    $name = preg_replace('/♪/i', '', $name);
    $name = preg_replace('/◆/i', '', $name);

    return $name;
}
