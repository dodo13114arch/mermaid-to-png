# 🎨 Mermaid Simple

零配置的 Mermaid 圖表渲染工具，專為個人本地使用設計。

## ✨ 特色

- 🚀 **一鍵啟動**: `npm start` 即可
- 🎯 **零配置**: 無需 API 金鑰或安全設置
- 🌐 **整合服務**: 前後端合一
- 🎨 **完整功能**: SVG 和多品質 PNG 輸出

## 🚀 快速開始

```bash
# 1. 安裝 Mermaid CLI
npm install -g @mermaid-js/mermaid-cli

# 2. 安裝依賴並啟動
npm install
npm start

# 3. 瀏覽器打開 http://localhost:3000
```

## 🎯 使用方法

1. 左側輸入 Mermaid 代碼
2. 點擊「🎨 渲染圖表」
3. 右側預覽結果
4. 點擊「💾 下載」保存

## 🎨 輸出格式

- **SVG**: 向量圖形，無損縮放
- **PNG**: 網頁/A4/A2 三種品質

## 💡 快捷功能

- **範例按鈕**: 快速載入流程圖、序列圖等範例
- **主題切換**: 右上角 🌙/☀️ 切換淺色/深色模式
- **SVG 互動**: 滾輪縮放、拖拽移動
- **歷史記錄**: 點擊「📜 查看歷史」瀏覽今日記錄

## 🐛 故障排除

```bash
# 檢查 Mermaid CLI
mmdc --version

# 檢查 Node.js
node --version

# 重新安裝
npm install
```

## 🔧 與完整版差異

| 特性 | 完整版 | 簡化版 |
|------|--------|--------|
| 部署 | 需要 Web Server | 一鍵啟動 |
| 安全 | API 金鑰 + 頻率限制 | 無（本地） |
| 適用 | 生產環境 | 個人使用 |

## 👨‍💻 作者

**David Chang** - [Mermaid 2 PNG](../README.md) 專案創建者

## 📄 授權

MIT License - 詳見 [LICENSE](LICENSE) 檔案

---

**享受簡單快速的圖表創建！** 🎉