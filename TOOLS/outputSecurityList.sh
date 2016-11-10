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

    if ! type aws >/dev/null 2>&1; then
        echo "awsコマンドがないため、処理を実行できません。"
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
    while getopts p:r: OPT
    do
        case $OPT in
            "p" ) 
                profile="$OPTARG" ;;
            "r" )
                targetRegion="$OPTARG" ;;
            "*" )
                exit 1 ;;
        esac
    done
}

##########
#
# イン / アウトバウンドを出力する
#
#####
function outputBoundRule()
{
    echo "Protocol,Port,CidrIP"
    for outBoundRule in $@
    do
        ipProtocol=`echo $outBoundRule | jq -r '.IpProtocol'`
        fromPort=`echo $outBoundRule | jq -r '.FromPort'`
        toPort=`echo $outBoundRule | jq -r '.ToPort'`
 
        for cidrIp in `echo $outBoundRule | jq -r '.IpRanges[] | .CidrIp'`
        do
            echo ${ipProtocol},${fromPort}-${toPort},${cidrIp}
        done
    done
}


########
#
#  メイン処理
#
########

# AWS コマンド実行時の出力結果を JSON 形式にする
export AWS_DEFAULT_OUTPUT=json

profile='default'
targetRegion=''

getParameter $@

# 全リージョンのセキュリティグループを表示
regions=`aws ec2 describe-regions --query 'Regions[*].RegionName' | tr -d "\[\]\,\""`
for region in $regions
do

    if  [ ! $targetRegion = '' ] && [ ! $targetRegion = $region  ]; then
        continue
    fi

    echo ■■■■■■■■■■■
    echo ${region} 
    export AWS_DEFAULT_REGION=$region

    # 全セキュリティグループのグループIDを取得
    SGIDS=`aws --profile ${profile} ec2 describe-security-groups --query 'SecurityGroups[*].GroupId'| tr -d "\[\]\,\""`

    for SGID in `echo $SGIDS`
    do
        # GroupId から SecurityGroup 情報を取得する
        sGroup=`aws --profile ${profile} ec2 describe-security-groups --query 'SecurityGroups[0]' --filters "Name=group-id,Values=$SGID"`

        groupName=`echo $sGroup | jq -r '.GroupName'`
        echo ${groupName}\(${SGID}\)

        vpcId=`echo $sGroup | jq -r '.VpcId '`
        echo VpcId:${vpcId}

        echo "インバウンド  -----------"
        outputBoundRule `echo $sGroup | jq -c '.IpPermissions[]'`

        echo "アウトバウンド-----------"
        outputBoundRule `echo $sGroup | jq -c '.IpPermissionsEgress[]'`

        echo ""
    done

done
