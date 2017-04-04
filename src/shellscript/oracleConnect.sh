#!/bin/sh

###
# Oracle サーバ(12c)に SQL を実行する
#####

# DB接続
CONNECT_DB="connect root/password@listener"

# SQL 実行 Function
execution_sql() {
    result=`sqlplus -s /nolog <<EOF
        whenever sqlerror exit 100
        set head off;
        $CONNECT_DB
        SELECT * FROM nantonaku_tbl;
        quit
EOF`

    if [ $? -eq 100 ] ; then
        return 1
    fi
    echo $result
}

ret=`execution_sql`

if [ $? -ne 0 ] ; then
    echo 'error'
fi

echo $ret
