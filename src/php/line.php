<?php

if(count($argv) < 2){
    return;
}

// リクエストURL
$line_url = "https://line.nantonaku-rt58.com";
$data = [];
$url = "";

// POSTするデータ
$data = array('events' => array([ "type" => "push", "message" => $argv[1]]));

print_r($content);
$content_length = strlen($content);
$options = array(   'https' => array(
                  'method' => 'POST',
                  'header' => "Content-Type: application/json\r\n". "Content-Length: $content_length",
                 'content' => $content));
file_get_contents($line_url, false, stream_context_create($options));
