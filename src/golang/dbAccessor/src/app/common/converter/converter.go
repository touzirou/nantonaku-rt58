package converter

import "strings"

// RemoveNewLineString 文字列内にある改行コードを削除する
func RemoveNewLineString(targetStr string) string {
	return ReplaceNewLineString(targetStr, "")
}

// ReplaceNewLineString 文字列内にある改行コードを特定の文字列に変換する
func ReplaceNewLineString(targetStr string, replaceCode string) string {
	return strings.NewReplacer("\r\n", replaceCode, "\r", replaceCode, "\n", replaceCode).Replace(targetStr)
}
