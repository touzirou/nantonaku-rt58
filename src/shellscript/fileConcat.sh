#!/bin/sh

###
# 特定ディレクトリ配下のファイル内容をまとめて表示
#####

if [ $# = 0 ]; then
   echo パラメータ不足
fi 

fileList=`ls ${1}`

for fileName in $fileList
do
    cat $fileName
done
