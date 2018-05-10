package logger

import (
	"fmt"
	"io"
	"log"
	"os"
	"runtime"
	"strings"

	"github.com/comail/colog"
)

var logDirPath = "./logs/"
var logFileName = "application.log"
var needsSetup = true

// StartLog 処理開始時用のログ出力
func StartLog(pc uintptr, file string, line int, ok bool) {
	arrayFuncName := strings.Split(runtime.FuncForPC(pc).Name(), "/")
	funcName := arrayFuncName[len(arrayFuncName)-1]
	Info(funcName + " is start.")
}

// EndLog 処理開始時用のログ出力
func EndLog(pc uintptr, file string, line int, ok bool) {
	arrayFuncName := strings.Split(runtime.FuncForPC(pc).Name(), "/")
	funcName := arrayFuncName[len(arrayFuncName)-1]
	Info(funcName + " is end.")
}

// Trace レベルのログを出力する
func Trace(text string) {
	logger(fmt.Sprintf("trace: %v", text))
}

// Debug レベルのログを出力する
func Debug(text string) {
	logger(fmt.Sprintf("debug: %v", text))
}

// Info レベルのログを出力する
func Info(text string) {
	logger(fmt.Sprintf("info: %v", text))
}

// Warn レベルのログを出力する
func Warn(text string) {
	logger(fmt.Sprintf("warn: %v", text))
}

// Error レベルのログを出力する
func Error(text string) {
	logger(fmt.Sprintf("error: %v", text))
}

// Alert レベルのログを出力する
func Alert(text string) {
	logger(fmt.Sprintf("alert: %v", text))
}

// Logger を使用するための初期化処理を実行する
func setup() {
	if !needsSetup {
		return
	}
	// ログファイル出力先のディレクトリがなければ作成
	if _, err := os.Stat(logDirPath); err != nil {
		if err := os.Mkdir(logDirPath, 0755); err != nil {
			panic("can not create " + logDirPath + " " + err.Error())
		}
		fmt.Println(">>>>> Create Log Directory")
	}
	logfile, err := os.OpenFile(logDirPath+logFileName, os.O_APPEND|os.O_CREATE|os.O_WRONLY, 0666)
	if err != nil {
		panic("can not open " + logDirPath + logFileName + " " + err.Error())
	}
	colog.SetDefaultLevel(colog.LDebug)
	colog.SetMinLevel(colog.LTrace)
	colog.SetOutput(io.MultiWriter(logfile, os.Stdout))
	colog.Register()
	needsSetup = false
}

func logger(text string) {
	setup()
	log.Printf(text)
}
