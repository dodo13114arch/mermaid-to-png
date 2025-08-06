@echo off
chcp 65001
echo ========================================
echo   🎨 Mermaid Simple 啟動工具
echo ========================================
echo.

echo 檢查 Node.js...
node --version
if errorlevel 1 (
    echo ❌ Node.js 沒有安裝！
    echo 請先安裝 Node.js: https://nodejs.org/
    pause
    exit
)

echo 檢查 Mermaid CLI...
mmdc --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Mermaid CLI 沒有安裝！
    echo 請先執行: npm install -g @mermaid-js/mermaid-cli
    echo.
    set /p install="是否現在安裝 Mermaid CLI? (y/n): "
    if /i "%install%"=="y" (
        echo 正在安裝 Mermaid CLI...
        npm install -g @mermaid-js/mermaid-cli
        if errorlevel 1 (
            echo ❌ 安裝失敗！
            pause
            exit
        )
    ) else (
        echo 請手動安裝後重新啟動
        pause
        exit
    )
)

echo ✅ 環境檢查完成
echo.

echo 正在啟動 Mermaid Simple...
echo 🌐 服務地址: http://localhost:3000
echo 🛑 要停止服務，請按 Ctrl+C
echo.

node simple-server.js
pause