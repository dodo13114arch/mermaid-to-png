# 🎨 Mermaid 2 PNG

快速地將 Mermaid 圖表轉換為 PNG/SVG 的小工具，預設支援多種輸出品質，並自動儲存原始碼以供日後修改。

## to do list

- [ ] 比對歷史紀錄，避免儲存重複的圖（重複的不重新記錄）
- [ ] 建立可依日期瀏覽的歷史紀錄介面（類似 iPhone 行事曆）
- [ ] 按下渲染鍵時，自動將渲染結果（SVG 或 PNG）儲存至伺服器端以避免重複運算
- [ ] 銜接調用 LLM API 為圖形生成摘要，並顯示於歷史紀錄中
- [ ] 理解並比較php跟其他語言的優劣(個人筆記)

## ✨ 核心功能

- **多格式輸出**: SVG 和透明背景 PNG
- **高品質渲染**: 網頁、A4印刷、A2海報三種解析度
- **主題切換**: 淺色/深色主題
- **即時預覽**: SVG 支援縮放和拖拽
- **歷史記錄**: 自動保存渲染記錄
- **安全機制**: API 金鑰驗證、頻率限制、IP 監控

## 🚀 快速開始

> 💡 **個人使用推薦**: [🎯 簡化版本](#-簡化版本---個人使用) - 零配置一鍵啟動

### 完整版（生產環境）

```bash
# 1. 安裝 Mermaid CLI
npm install -g @mermaid-js/mermaid-cli

# 2. 後端設置
cd "mermaid-server/mermaid-backend"
npm install
# 創建 .env 檔案（參考 env.example）
npm start

# 3. 前端設置
# 將 mermaid-frontend/ 放到 Web Server（Apache/Nginx/XAMPP）

# 4. 訪問
# 前端: http://localhost/index.php
# 後端: http://localhost:8000
```

### 環境變數配置

創建 `.env` 檔案：
```env
MERMAID_API_KEY1=your_secret_api_key
PORT=8000
MAX_REQUESTS_PER_MINUTE=10
```

## 🎯 簡化版本 - 個人使用

**零配置一鍵啟動版本**：

```bash
cd mermaid-simple/
npm install
npm start
# 訪問 http://localhost:3000
```

### 版本比較

| 特性 | 完整版 | 簡化版 |
|------|--------|--------|
| **部署** | 需要配置 Web Server + API 金鑰 | 一鍵啟動 |
| **安全** | 完整安全機制 | 本地使用 |
| **適用** | 生產環境、團隊 | 個人開發 |

## 🎨 PNG 品質選項

| 品質 | 解析度 | 適用場景 |
|------|--------|----------|
| 網頁 | 1920×1080 | 簡報、網頁 |
| A4 | 4961×7016, 600 DPI | 文件印刷 |
| A2 | 9921×14032, 600 DPI | 海報展示 |

## 🔧 API 使用

```bash
curl -X POST http://localhost:8000/render \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: your_api_key" \
  -d '{
    "code": "graph TD\n    A[開始] --> B[結束]",
    "format": "png",
    "theme": "light",
    "quality": "web"
  }'
```

## ⚠️ 安全注意事項

### 完整版（生產環境）安全提醒

> **隱私警告**: 完整版的 `/history` API 會回傳使用者的 IP 位址資訊，這可能涉及使用者隱私問題。

**建議措施**：
- 在部署前評估是否需要記錄 IP 位址
- 考慮在後端對 IP 進行脫敏處理（如：`192.168.x.x`）
- 定期清理歷史記錄檔案
- 確保只有授權人員能存取 `/history` API

**影響範圍**：
- ✅ 簡化版：不受影響，僅供本地使用
- ⚠️ 完整版：需要注意隱私保護

### 其他安全建議

- 定期更換 API 金鑰
- 設定適當的 IP 白名單
- 監控 `logs/` 目錄的日誌檔案
- 確保 `.env` 檔案不被提交到版本控制

## 👨‍💻 作者

**David Chang** - 偷懶者
**Cursor Claude 4 Sonnet** - 打字機

## 📄 授權條款

MIT License - 詳見 [LICENSE](LICENSE) 檔案
