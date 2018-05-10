package main

import (
	"html/template"
	"io"
	"path/filepath"
	"strconv"

	"db.accessor/app/common/config"
	"db.accessor/app/common/logger"
	"db.accessor/app/controller"
	"github.com/labstack/echo"
)

// Template テンプレート
type Template struct {
	templates *template.Template
}

// Render テンプレート読み込み
func (t *Template) Render(w io.Writer, name string, data interface{}, c echo.Context) error {
	return t.templates.ExecuteTemplate(w, name, data)
}

// ParseAssets テンプレートファイル変換
func ParseAssets(filenames ...string) (*template.Template, error) {
	var ns = template.New("complex")
	for _, filename := range filenames {
		src, err := Asset(filename)
		if err != nil {
			return nil, err
		}
		s := string(src)
		name := filepath.Base(filename)
		_, err = ns.New(name).Parse(s)
		if err != nil {
			return nil, err
		}
	}
	return ns, nil
}

func main() {
	port, err := strconv.ParseInt(config.GetListenPort(), 10, 64)
	if err != nil {
		logger.Error(err.Error())
		return
	}

	t := &Template{
		templates: template.Must(ParseAssets(
			"views/setting.html",
			"views/success.html",
		)),
	}

	e := echo.New()
	e.Renderer = t
	g := e.Group("")

	g.GET("/setting", controller.SettingGet)   // 設定画面
	g.POST("/setting", controller.SettingPost) // 設定保存

	listenPort := ":" + strconv.FormatInt(port, 10)
	logger.Info("use listen port " + listenPort)
	e.Logger.Fatal(e.Start(listenPort))
}
