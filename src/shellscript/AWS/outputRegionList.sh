#!/bin/sh

##########
#
# シェル内で使用するコマンドが存在するかチェックを行う
#
#####

function checkCommandExist()
{
    if ! type aws >/dev/null 2>&1; then
        echo "awsコマンドがないため、処理を実行できません。"
        return 1
    fi
    return 0
}

########
#
#  メイン処理
#
########

# AWS コマンド実行時の出力結果を JSON 形式にする
export AWS_DEFAULT_OUTPUT=json

checkCommandExist

# 全リージョンのセキュリティグループを表示
echo `aws ec2 describe-regions --query 'Regions[*].RegionName' | tr -d "\[\]\,\""`
