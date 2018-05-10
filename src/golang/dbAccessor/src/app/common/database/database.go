package database

import (
	"database/sql"
	"fmt"

	"db.accessor/app/common/config"
	"db.accessor/app/common/converter"
	"db.accessor/app/common/logger"
	_ "github.com/go-sql-driver/mysql" // 必要な気がする
)

// Query SQL実行
func Query(query string) {
	logger.Debug(converter.RemoveNewLineString(query))

	dbUser := config.GetDBUser()
	dbPwd := config.GetDBPassword()
	host := config.GetRemoteHost()
	port := config.GetDBPort()
	database := config.GetDatabase()

	db, err := sql.Open("mysql", fmt.Sprintf("%v:%v@tcp(%v:%v)/%v", dbUser, dbPwd, host, port, database))
	if err != nil {
		panic(err.Error())
	}
	defer db.Close() // 関数がリターンする直前に呼び出される
	_, err = db.Query(query)
	if err != nil {
		panic(err.Error())
	}
	return
}
