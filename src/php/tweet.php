<?php

if(count($argv) < 2){
    return;
}

// リクエストURL
$tweet_url = "http://twitter.nantonaku-rt58.com/post";
$data = [];
$url = "";

// POSTするデータ
$data = array('tweet' => $argv[1]);

$content = http_build_query($data);
$content_length = strlen($content);
$options = array(   'http' => array(
                  'method' => 'POST',
                  'header' => "Content-Type: application/x-www-form-urlencoded\r\n". "Content-Length: $content_length",
                 'content' => $content));
file_get_contents($tweet_url, false, stream_context_create($options));
