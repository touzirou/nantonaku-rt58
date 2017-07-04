<?php

##
#
# Purpose     : 対象日付に発売するゲームの一覧を SNS にプッシュする
# Author      : rTsujimochi
# Usage       : php sale_check.php 20170704
#
##

if(count($argv) < 2){
    return;
}

try{
    $mysqli = new mysqli(getenv('REMOTE_DATABASE'), getenv('DATABASE_USER'), getenv('DATABASE_PASSWORD'), getenv('GAME_DATABASE_NAME'));
    $mysqli->set_charset("utf8");
    $game_list = query(search($argv[1]));

    if(count($game_list) == 0){
        echo "発売ゲームなし" . PHP_EOL;
        return;
    }

    push_line(date('Y/m/d',strtotime($argv[1])) . ' 発売のゲーム');
    send_game_list($game_list);

}catch(Exception $e){
    echo $e->getMessage();
}finally{
    $mysqli->close();
}

/**
 * SELECT 文を発行し、結果を返却する
 * @param string $targert_date 対象日付(yyyymmdd)
 * @return array 検索結果
 */
function search($target_date){
    $sql = "SELECT
            	c.name as cName ,i.name
            FROM 
            	YahooItems i JOIN YahooCategory c ON i.categoryId = c.id 
            WHERE 
            	releaseDate IS NOT NULL and releaseDate = '" . $target_date . "'
            GROUP BY
                i.name, c.name
            ORDER BY 
            	cName, name;";
    return $sql;
}

/**
 * SQL文の発行
 * @param string $sql 実行するSQL文
 * @return array SQL実行結果
 */
function query($sql){
    global $mysqli;
    $result = $mysqli->query($sql);
    $count = $result->num_rows;
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->close();
    return $data;
}

/**
 * ゲームのハード機毎に各SNSにプッシュを行う
 * @param array $game_list ゲームデータ
 */
function send_game_list($game_list){
    $target_category = $game_list[0]["cName"];
    $game_array = ['【' . $target_category . '】'];
    foreach($game_list as $game){
        if($target_category != $game["cName"]){
            push_line(implode("\n", $game_array));
            $target_category = $game["cName"];
            $game_array = ['【' . $target_category . '】'];
        }
        array_push($game_array, '・' . $game["name"]);
    }
    push_line(implode("\n", $game_array));
}

/**
 * LINE BOT にメッセージをプッシュする
 * @param string $message メッセージ
 */
function push_line($message){
    // リクエストURL
    $line_url = "https://line.nantonaku-rt58.com";
    // POSTするデータ
    $data = array('events' => array([ "type" => "push", "message" => $message]));
    // リクエスト
    $options = array( 'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n" .
                                "Accept: application/json\r\n",
                    'content' => json_encode( $data )));
    file_get_contents(getenv('LINE_URL'), false, stream_context_create($options));
}