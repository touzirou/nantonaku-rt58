#!/bin/sh

##########
#
# シェル内で使用するコマンドが存在するかチェックを行う
#
#####
function checkCommandExist()
{
    if ! type jq >/dev/null 2>&1; then
        echo "jqコマンドがないため、処理を実行できません。"
        return 1
    fi

    return 0
}

##########
#
# シェルのパラメータを取得する
#
#####
function getParameter()
{
    while getopts o: OPT
    do
        case $OPT in
            "o" )
                ;;
            "*" )
                exit 1 ;;
        esac
    done
}

###########
#
#  メイン処理
#
########

echo $1 | jq -r '.text'
