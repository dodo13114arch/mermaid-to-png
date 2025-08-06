@echo off
chcp 65001
echo 檢查 Node.js...
node --version
if errorlevel 1 (
    echo Node.js 沒有安裝！
    pause
    exit
)

echo 切換到當前目錄...
cd /d "%~dp0"

echo 檢查檔案...
if not exist secure_server.js (
    echo 找不到 secure_server.js！
    pause
    exit
)

echo 啟動服務器...
node secure_server.js
pause