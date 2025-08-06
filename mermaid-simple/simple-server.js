const http = require('http');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const crypto = require('crypto');
const mimeTypes = require('mime-types');

const PORT = 3000;
const HOST = 'localhost';

console.log('🎨 啟動 Mermaid Simple 服務器...');
console.log(`📍 本地地址: http://${HOST}:${PORT}`);

// 確保必要目錄存在
['./tmp', './history'].forEach(dir => {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
        console.log(`✅ 創建目錄: ${dir}`);
    }
});

// 檢查是否為預設範例（簡化版本）
function isDefaultExample(code) {
    const defaultExamples = [
        `graph TD
    A[開始] --> B{判斷條件}
    B -->|是| C[執行動作]
    B -->|否| D[其他動作]
    C --> E[結束]
    D --> E`,
        
        `sequenceDiagram
    participant A as 使用者
    participant B as 系統
    participant C as 資料庫
    
    A->>B: 發送請求
    B->>C: 查詢資料
    C-->>B: 回傳結果
    B-->>A: 回應結果`
    ];
    
    const normalizedCode = code.trim().replace(/\s+/g, ' ');
    return defaultExamples.some(example => {
        const normalizedExample = example.trim().replace(/\s+/g, ' ');
        return normalizedCode === normalizedExample;
    });
}

// 渲染 Mermaid 圖表
function handleRender(req, res) {
    let body = '';
    req.on('data', chunk => body += chunk.toString());
    req.on('end', () => {
        try {
            const { code, format = 'svg', theme = 'light', quality = 'web' } = JSON.parse(body);
            
            if (!code || code.trim().length === 0) {
                res.writeHead(400, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify({ error: '請提供 Mermaid 代碼' }));
                return;
            }
            
            // 檢查代碼長度（簡化版本限制 10KB）
            if (code.length > 10240) {
                res.writeHead(400, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify({ error: '代碼長度不能超過 10KB' }));
                return;
            }
            
            const themeFile = theme === 'dark' ? './themes/theme_dark.json' : './themes/theme_light.json';
            const fileName = Date.now() + '_' + crypto.randomBytes(4).toString('hex');
            const inputFile = `./tmp/${fileName}.mmd`;
            const outputFile = `./tmp/${fileName}.${format}`;
            
            fs.writeFileSync(inputFile, code);
            
            // 根據品質設定渲染參數
            let cmd = `mmdc -i "${inputFile}" -o "${outputFile}" -c "${themeFile}" -b transparent`;
            
            if (format === 'png') {
                switch (quality) {
                    case 'a2':
                        cmd += ' --scale 8 --width 2000';
                        break;
                    case 'a4':
                        cmd += ' --scale 4 --width 1400';
                        break;
                    case 'web':
                    default:
                        cmd += ' --scale 1 --width 800';
                        break;
                }
            }
            
            const timeoutMs = quality === 'a2' ? 120000 : (quality === 'a4' ? 60000 : 30000);
            
            exec(cmd, { timeout: timeoutMs }, (error, stdout, stderr) => {
                // 清理輸入檔案
                if (fs.existsSync(inputFile)) fs.unlinkSync(inputFile);
                
                if (error) {
                    const errorMsg = error.code === 'ETIMEDOUT' ? 
                        `渲染超時 (${quality} 品質需要較長時間)` : 
                        '渲染失敗';
                    
                    console.log(`❌ 渲染失敗: ${error.message}`);
                    res.writeHead(500, { 'Content-Type': 'application/json; charset=utf-8' });
                    res.end(JSON.stringify({ error: errorMsg }));
                    return;
                }
                
                if (fs.existsSync(outputFile)) {
                    const result = fs.readFileSync(outputFile);
                    const fileSize = result.length;
                    fs.unlinkSync(outputFile);
                    
                    const contentType = format === 'svg' ? 'image/svg+xml' : 'image/png';
                    res.writeHead(200, { 'Content-Type': contentType });
                    res.end(result);
                    
                    console.log(`✅ ${quality === 'web' ? '網頁' : quality.toUpperCase()} ${format.toUpperCase()}: ${(fileSize / 1024 / 1024).toFixed(2)}MB`);
                    
                    // 儲存歷史記錄（排除預設範例）
                    if (!isDefaultExample(code)) {
                        try {
                            const historyEntry = {
                                timestamp: new Date().toISOString(),
                                code: code,
                                theme: theme,
                                format: format,
                                quality: quality,
                                codeLength: code.length,
                                id: crypto.randomUUID()
                            };
                            
                            const historyFile = `./history/mermaid_history_${new Date().toISOString().split('T')[0]}.jsonl`;
                            fs.appendFileSync(historyFile, JSON.stringify(historyEntry) + '\n');
                        } catch (historyError) {
                            console.log('📝 歷史記錄儲存失敗:', historyError.message);
                        }
                    }
                } else {
                    res.writeHead(500, { 'Content-Type': 'application/json; charset=utf-8' });
                    res.end(JSON.stringify({ error: '檔案生成失敗' }));
                }
            });
            
        } catch (error) {
            console.log(`❌ 請求處理錯誤: ${error.message}`);
            res.writeHead(400, { 'Content-Type': 'application/json; charset=utf-8' });
            res.end(JSON.stringify({ error: '請求格式錯誤' }));
        }
    });
}

// 查看歷史記錄
function handleHistory(req, res) {
    try {
        const today = new Date().toISOString().split('T')[0];
        const historyFile = `./history/mermaid_history_${today}.jsonl`;
        
        if (!fs.existsSync(historyFile)) {
            res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
            res.end(JSON.stringify({ history: [] }));
            return;
        }
        
        const lines = fs.readFileSync(historyFile, 'utf-8').trim().split('\n');
        const history = lines
            .filter(line => line.trim())
            .map(line => {
                try {
                    return JSON.parse(line);
                } catch (e) {
                    return null;
                }
            })
            .filter(entry => entry !== null)
            .reverse() // 最新的在前面
            .slice(0, 20); // 限制最多20筆
        
        res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
        res.end(JSON.stringify({ history }));
        
        console.log(`📜 歷史查詢: ${history.length} 筆記錄`);
        
    } catch (error) {
        console.log(`❌ 歷史查詢失敗: ${error.message}`);
        res.writeHead(500, { 'Content-Type': 'application/json; charset=utf-8' });
        res.end(JSON.stringify({ error: '歷史查詢失敗' }));
    }
}

// 提供靜態檔案
function serveStaticFile(req, res, filePath) {
    try {
        const fullPath = path.join(__dirname, 'public', filePath);
        
        if (!fs.existsSync(fullPath)) {
            res.writeHead(404);
            res.end('檔案不存在');
            return;
        }
        
        const mimeType = mimeTypes.lookup(fullPath) || 'application/octet-stream';
        const content = fs.readFileSync(fullPath);
        
        res.writeHead(200, { 
            'Content-Type': mimeType,
            'Cache-Control': 'public, max-age=3600'
        });
        res.end(content);
        
    } catch (error) {
        console.log(`❌ 靜態檔案錯誤: ${error.message}`);
        res.writeHead(500);
        res.end('檔案讀取失敗');
    }
}

// 主要服務器
const server = http.createServer((req, res) => {
    // CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }
    
    const url = new URL(req.url, `http://localhost:${PORT}`);
    
    // API 路由
    if (req.method === 'POST' && url.pathname === '/render') {
        handleRender(req, res);
        return;
    }
    
    if (req.method === 'GET' && url.pathname === '/history') {
        handleHistory(req, res);
        return;
    }
    
    // 靜態檔案路由
    let filePath = url.pathname === '/' ? '/index.html' : url.pathname;
    serveStaticFile(req, res, filePath);
});

server.listen(PORT, HOST, () => {
    console.log('🚀 伺服器啟動成功！');
    console.log(`🌐 請打開瀏覽器訪問: http://${HOST}:${PORT}`);
    console.log('');
    console.log('💡 快速開始:');
    console.log('   1. 在瀏覽器中打開上方網址');
    console.log('   2. 輸入 Mermaid 代碼');
    console.log('   3. 點擊渲染按鈕');
    console.log('');
    console.log('🛑 要停止伺服器，請按 Ctrl+C');
});