#!/bin/sh

idx=0

## 接続回数
end=`expr $RANDOM % 100`

echo "[`date`]${end}回" >> /Users/rtsujimoto/tool/logs/GA.log

while [ $idx -lt $end ]
do
    open -g -a "/Applications/Safari.app" http://localhost
    idx=$((idx + 1))
    
    sleepTime=`expr $RANDOM % 2`
    sleep $sleepTime
done
