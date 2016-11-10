#!/bin/sh

if [ $# = 0 ]; then
    echo パラメータが足りませぬ
    exit 1
fi 

for line in `cat ${1}`
do
   if [ `echo $line | grep "^＊＊ＰｉＴａＰａカードご利用小計＊＊"` ]; then
       echo $line
   fi
   if [ `echo $line | grep "^[0-9]\{4\}/[0-9]\{2\}/[0-9]\{2\}"` ]; then
       date=`echo $line | sed -e 's/\///g'`
       
       echo $line
   fi 
done
