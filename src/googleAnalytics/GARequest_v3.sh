#!/bin/sh

filename=/Users/rtsujimoto/tool/GA.conf

cat ${filename} | while read line
do 
  idx=0

  ## 接続回数
end=`expr $RANDOM % 11`

  echo "[`date`]${line}へ${end}回" >> /Users/rtsujimoto/tool/logs/GA.log

  while [ $idx -lt $end ]
  do
      open -g -a "/Applications/Safari.app" http://localhost/${line}
      idx=$((idx + 1))
    
      sleepTime=`expr $RANDOM % 6`
      sleep $sleepTime
  done
done
echo "[`date`]終わり" >> /Users/rtsujimoto/tool/logs/GA.log
