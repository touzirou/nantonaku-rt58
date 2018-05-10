package controller

import (
	"net/http"
	"runtime"

	"db.accessor/app/common/config"
	"db.accessor/app/common/logger"
	"github.com/labstack/echo"
)

// SettingRenderData 画面項目値
type SettingRenderData struct {
	DbUser     string
	DbPassword string
	RemoteHost string
	DbPort     string
	Database   string
}

// SettingGet 設定画面
func SettingGet(con echo.Context) error {
	logger.StartLog(runtime.Caller(0))
	settingRenderData := SettingRenderData{
		config.GetDBUser(),
		config.GetDBPassword(),
		config.GetRemoteHost(),
		config.GetDBPort(),
		config.GetDatabase(),
	}
	logger.EndLog(runtime.Caller(0))
	return con.Render(http.StatusOK, "setting", settingRenderData)
}

// SettingPost 設定画面
func SettingPost(con echo.Context) error {
	logger.StartLog(runtime.Caller(0))
	setting := config.Config{
		DBUser:     con.FormValue("user"),
		DBPassword: con.FormValue("password"),
		RemoteHost: con.FormValue("remote_host"),
		DBPort:     con.FormValue("port"),
		Database:   con.FormValue("database"),
		ListenPort: config.GetListenPort(),
	}
	config.SaveConfig(setting)
	logger.EndLog(runtime.Caller(0))
	return con.Render(http.StatusOK, "success", "")
}
