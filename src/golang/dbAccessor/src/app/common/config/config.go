package config

import (
	"encoding/json"
	"io/ioutil"
	"os"

	"db.accessor/app/common/logger"
)

// Config 構造体定義
type Config struct {
	DBUser     string `json:"DB_USER"`
	DBPassword string `json:"DB_PASSWORD"`
	RemoteHost string `json:"REMOTE_HOST"`
	DBPort     string `json:"DB_PORT"`
	Database   string `json:"DATABASE"`
	ListenPort string `json:"LISTEN_PORT"`
}

var configDirPath = "./config/"
var configFileName = "config.json"

// GetDBUser DBユーザ取得
func GetDBUser() string {
	dbUser := readConfig().DBUser
	if len(dbUser) == 0 {
		return "mysql"
	}
	return dbUser
}

// GetDBPassword DBパスワード取得
func GetDBPassword() string {
	dbPassword := readConfig().DBPassword
	if len(dbPassword) == 0 {
		return "mysql"
	}
	return dbPassword
}

// GetRemoteHost DBホスト取得
func GetRemoteHost() string {
	remoteHost := readConfig().RemoteHost
	if len(remoteHost) == 0 {
		return "localhost"
	}
	return remoteHost
}

// GetDBPort DBポート番号取得
func GetDBPort() string {
	dbPort := readConfig().DBPort
	if len(dbPort) == 0 {
		return "3306"
	}
	return dbPort
}

// GetDatabase DB名称取得
func GetDatabase() string {
	database := readConfig().Database
	if len(database) == 0 {
		return "mysql"
	}
	return database
}

// GetListenPort ツールのリッスンポート取得
func GetListenPort() string {
	listenPort := readConfig().ListenPort
	if len(listenPort) == 0 {
		return "8080"
	}
	return listenPort
}

// configファイル読み込み
func readConfig() *Config {
	jsonString, err := ioutil.ReadFile(configDirPath + configFileName)
	c := new(Config)
	if err != nil {
		logger.Warn(err.Error())
		return c
	}
	err = json.Unmarshal(jsonString, c)
	if err != nil {
		logger.Error(err.Error())
		return c
	}
	return c
}

// SaveConfig ファイル保存
func SaveConfig(config Config) error {
	// configファイル保存先のディレクトリがなければ作成
	if _, err := os.Stat(configDirPath); err != nil {
		if err := os.Mkdir(configDirPath, 0755); err != nil {
			logger.Error(err.Error())
			return err
		}
	}
	// jsonエンコード
	outputJSON, err := json.Marshal(&config)
	if err != nil {
		logger.Error(err.Error())
		return err
	}

	// jsonデータを出力
	ioutil.WriteFile(configDirPath+configFileName, outputJSON, os.ModePerm)
	return nil
}
