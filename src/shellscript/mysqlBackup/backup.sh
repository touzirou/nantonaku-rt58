#!/bin/sh

# 一時的な出力先
output_path='/tmp'
# バックアップファイル名称
file_name='mysql_backup_'$(date +'%Y%m%d')
# mysql ログイン名
user_name=$1
# mysql ログインパスワード
password=$2

if [ -z "$user_name" ]; then
    echo "パラメータが足りません"
    exit
fi

if [ -z "$password" ]; then
    echo "パラメータが足りません"
    exit
fi

# mysqldump 実行
mysqldump --opt --all-databases --events --default-character-set=binary -u ${user_name} --password=${password} | gzip > ${output_path}/${file_name}.sql.gz

# S3 にアップロード
aws s3 cp ${output_path}/${file_name}.sql.gz s3://backup-nantonaku-rt58/mysql/${file_name}.sql.gz

# 一時ファイル削除
rm ${output_path}/${file_name}.sql.gz
