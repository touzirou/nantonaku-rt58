<?php

if(count($argv) < 2){
    return;
}

try{
    $mysqli = new mysqli(getenv('REMOTE_DATABASE'), getenv('DATABASE_USER'), getenv('DATABASE_PASSWORD'), getenv('GAME_DATABASE_NAME'));
    $mysqli->set_charset("utf8");
    $game_list = query(search($argv[1]));

    if(count($game_list) == 0){
        echo "発売ゲームなし";
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
 * @author rTsujimoto
 * @param string 対象日付
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
 * @author rTsujimoto
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