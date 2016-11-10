<?php
if(count($argv) <= 1){
    echo 'パラメータ不足' . PHP_EOL;
    return;
}

$fileDetail = file_get_contents($argv[1]);

echo $fileDetail;
