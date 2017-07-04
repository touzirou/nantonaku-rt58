<?php

if(count($argv) < 2){
    return;
}

// リクエストURL
$line_url = "https://line.nantonaku-rt58.com";

// POSTするデータ
$data = array('events' => array([ "type" => "push", "message" => $argv[1]]));

// リクエスト
$options = array(   'http' => array(
                  'method' => 'POST',
                  'header' => "Content-Type: application/json\r\n" .
                                "Accept: application/json\r\n",
                 'content' => json_encode( $data )));
file_get_contents($line_url, false, stream_context_create($options));
