<?php

/**
 * YahooAPI を使用して、商品データを Mysql に登録する
 * @author rTsujimoto
 */

$hits = 0;
$result = array();

$params = create_parameter();
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
 * パラメータの作成
 * @author rTsujimoto
 * @return array パラメータ
 */
function create_parameter(){
    $params = array();
    $params["appid"] = getenv('YAHOO_APP_ID');
    $params["category_id"] = 2161;
    $params["store_id"] = "wondergoo";
    $params["condition"] = "new";
    $params["seller"] = "store";
    $params["hits"] = 50;
    $params["query"] = str_replace("%7E", "~", rawurlencode(''));
    return $params;
}

/**
 * リクエスト用の URL を生成する 
 * @author rTsujimoto
 * @param array リクエストパラメータ
 * @return string URL
 */
function create_request_url($params){

    // リクエストURL
    $base_url = '';
    switch(getenv('YAHOO_FORMAT_TYPE')){
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
            'janCode'     => (string)$item->JanCode,
            'name'        => name_normalize((string)$item->Name),
            'url'         => (string)$item->Url,
            'price'       => (int)$item->Price,
            'releaseDate' => substr((string)$item->ReleaseDate, 0, 10),
            'category_id' => (string)$item->Category->Current->Id,
        );
    }
    return;
}

/**
 * MySQLにデータを登録する
 * @author rTsujimoto
 * @param array insert_data
 */
function data_regist($insert_data){
    $pdo = new PDO('mysql:dbname=' . getenv('GAME_DATABASE_NAME') . ';host=' . getenv('REMOTE_DATABASE') . ';charset=utf8mb4', getenv('DATABASE_USER'), getenv('DATABASE_PASSWORD'));
    $insert_stmt = $pdo->prepare("INSERT INTO YahooItems (jan, name, url, price, releaseDate, categoryId, updatedDate) VALUES (:jan, :name, :url, :price, :releaseDate, :categoryId, NOW())");
    $delete_stmt = $pdo->prepare("DELETE FROM YahooItems WHERE jan = :jan");
    foreach($insert_data as $data){
        // バインド変数をセット
        $delete_stmt->bindValue(':jan', $data['janCode'], PDO::PARAM_STR);
        $insert_stmt->bindValue(':jan', $data['janCode'], PDO::PARAM_STR);
        $insert_stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $insert_stmt->bindValue(':url', $data['url'], PDO::PARAM_STR);
        $insert_stmt->bindValue(':price', $data['price'], PDO::PARAM_INT);
        $insert_stmt->bindValue(':categoryId', $data['category_id'], PDO::PARAM_STR);

        if(empty($data['releaseDate'])){
            $insert_stmt->bindValue(':releaseDate', null, PDO::PARAM_NULL);
        }else{
            $insert_stmt->bindValue(':releaseDate', $data['releaseDate'], PDO::PARAM_STR);
        }

        $delete_stmt->execute();
        $insert_stmt->execute();
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
