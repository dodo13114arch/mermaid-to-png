const http = require('http');
const fs = require('fs');
const path = require('path');
const { exec } = require('child_process');
const crypto = require('crypto');
const SECURITY_CONFIG = require('./secure_config');

const PORT = 8000;

// 請求計數器 (內存儲存，重啟會重置)
const requestCounters = new Map();
const activeRenders = new Set();

console.log('🔒 啟動安全 Mermaid 服務器...');

// 確保必要目錄存在
['./tmp', './logs', './history'].forEach(dir => {
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
        console.log(`✅ 創建目錄: ${dir}`);
    }
});

// 檢查是否為預設範例
function isDefaultExample(code) {
    // 使用前端實際的範例內容
    const defaultExamples = [
        // flowchart
        `graph TD
    A[開始] --> B{判斷條件}
    B -->|是| C[執行動作]
    B -->|否| D[其他動作]
    C --> E[結束]
    D --> E`,
        
        // sequence
        `sequenceDiagram
    participant A as 使用者
    participant B as 系統
    participant C as 資料庫
    
    A->>B: 發送請求
    B->>C: 查詢資料
    C-->>B: 回傳結果
    B-->>A: 回應結果`,
        
        // gantt
        `gantt
    title 專案進度表
    dateFormat  YYYY-MM-DD
    section 設計階段
    需求分析      :done, des1, 2024-01-01, 2024-01-05
    系統設計      :done, des2, after des1, 5d
    section 開發階段
    前端開發      :active, dev1, 2024-01-10, 10d
    後端開發      :dev2, after des2, 12d`,
        
        // class
        `classDiagram
    class User {
        +String name
        +String email
        +login()
        +logout()
    }
    
    class Product {
        +String title
        +Float price
        +getInfo()
    }
    
    User --> Product : purchases`,
        
        // pie
        `pie title 市場佔有率
    "產品A" : 45
    "產品B" : 30
    "產品C" : 15
    "其他" : 10`,
        
        // gitgraph
        `gitGraph
    commit
    commit
    branch develop
    checkout develop
    commit
    commit
    checkout main
    merge develop
    commit
    commit`,
        
        // journey
        `journey
    title 使用者購物體驗
    section 瀏覽商品
      訪問網站: 5: 用戶
      搜尋商品: 3: 用戶
      查看詳情: 4: 用戶
    section 購買流程
      加入購物車: 2: 用戶
      結帳付款: 1: 用戶, 系統
      確認訂單: 5: 用戶`,
        
        // c4
        `C4Context
    title 系統架構圖
    Person(user, "用戶", "系統使用者")
    System(webapp, "Web 應用", "提供核心功能")
    System(db, "資料庫", "儲存用戶資料")
    Rel(user, webapp, "使用")
    Rel(webapp, db, "讀寫")`,
        
        // mindmap
        `mindmap
  root((專案管理))
    計劃
      需求分析
      時程規劃
      資源分配
    執行
      任務分派
      進度追蹤
      品質控制
    監控
      狀態報告
      風險評估
      調整策略`,
        
        // timeline
        `timeline
    title 產品開發時程
    
    2024-01 : 需求調研
           : 市場分析
    2024-02 : 系統設計
           : 技術選型
    2024-03 : 開發階段
           : 測試階段
    2024-04 : 上線部署
           : 維護支援`,
        
        // er
        `erDiagram
    CUSTOMER {
        string customer_id
        string name
        string email
        date created_at
    }
    ORDER {
        string order_id
        string customer_id
        decimal total_amount
        date order_date
    }
    PRODUCT {
        string product_id
        string name
        decimal price
        int stock
    }
    
    CUSTOMER ||--o{ ORDER : places
    ORDER }|..|{ PRODUCT : contains`,
        
        // state
        `stateDiagram-v2
    [*] --> 待處理
    待處理 --> 處理中 : 開始處理
    處理中 --> 已完成 : 完成
    處理中 --> 暫停 : 暫停
    暫停 --> 處理中 : 繼續
    已完成 --> [*]
    處理中 --> 取消 : 取消
    取消 --> [*]`,
        
        // requirement
        `requirementDiagram

    requirement test_req {
    id: 1
    text: the test text.
    risk: high
    verifymethod: test
    }

    functionalRequirement test_req2 {
    id: 1.1
    text: the second test text.
    risk: low
    verifymethod: inspection
    }

    performanceRequirement test_req3 {
    id: 1.2
    text: the third test text.
    risk: medium
    verifymethod: demonstration
    }

    test_req - derives -> test_req2
    test_req - satisfies -> test_req3`
    ];
    
    // 標準化代碼格式進行比較 (移除多餘空白和換行)
    const normalizedCode = code.trim().replace(/\s+/g, ' ');
    
    const isExample = defaultExamples.some((example, index) => {
        const normalizedExample = example.trim().replace(/\s+/g, ' ');
        const match = normalizedCode === normalizedExample;
        
        // 詳細調試日誌
        if (match) {
            console.log(`🚫 檢測到預設範例 (索引 ${index})，不儲存到歷史記錄`);
            console.log(`📝 代碼長度: ${code.length}, 標準化後: ${normalizedCode.length}`);
        }
        
        return match;
    });
    
    // 如果不是範例，輸出前幾個字符幫助調試
    if (!isExample && code.length < 200) {
        console.log(`📄 用戶代碼 (將儲存): ${code.substring(0, 100)}...`);
    }
    
    return isExample;
}

// 安全日誌函數
function securityLog(level, message, req = null) {
    if (!SECURITY_CONFIG.LOGGING.enabled) return;
    
    const timestamp = new Date().toISOString();
    const ip = req ? getClientIP(req) : 'system';
    const userAgent = req ? req.headers['user-agent'] || 'unknown' : 'system';
    
    const logEntry = {
        timestamp,
        level,
        message,
        ip: SECURITY_CONFIG.LOGGING.logIPs ? ip : '[隱藏]',
        userAgent: SECURITY_CONFIG.LOGGING.logUserAgent ? userAgent : '[隱藏]'
    };
    
    const logFile = `./logs/security_${new Date().toISOString().split('T')[0]}.log`;
    fs.appendFileSync(logFile, JSON.stringify(logEntry) + '\n');
    
    if (level === 'warn' || level === 'error') {
        console.log(`🚨 [${level.toUpperCase()}] ${message} - IP: ${ip}`);
    }
}

// 獲取真實 IP (考慮 Cloudflare)
function getClientIP(req) {
    // 嘗試多種方式獲取真實IP
    const cfConnectingIp = req.headers['cf-connecting-ip'];
    const xForwardedFor = req.headers['x-forwarded-for']?.split(',')[0]?.trim();
    const xRealIp = req.headers['x-real-ip'];
    const remoteAddress = req.connection.remoteAddress || req.socket.remoteAddress;
    
    // 調試用：記錄所有可能的IP來源（僅在開發環境）
    const debugMode = process.env.NODE_ENV === 'development';
    if (debugMode) {
        console.log('🔍 IP 調試資訊:', {
            'cf-connecting-ip': cfConnectingIp,
            'x-forwarded-for': req.headers['x-forwarded-for'],
            'x-real-ip': xRealIp,
            'remoteAddress': remoteAddress,
            'user-agent': req.headers['user-agent']?.substring(0, 50) + '...'
        });
    }
    
    return cfConnectingIp || xForwardedFor || xRealIp || remoteAddress || 'unknown';
}

// IP 白名單檢查
function isIPAllowed(ip) {
    if (SECURITY_CONFIG.IP_WHITELIST.length === 0) return true;
    
    // 檢查完全匹配
    if (SECURITY_CONFIG.IP_WHITELIST.includes(ip)) return true;
    
    // 檢查 CIDR 範圍 (簡化版)
    for (const allowedIP of SECURITY_CONFIG.IP_WHITELIST) {
        if (allowedIP.includes('/')) {
            // 簡單的 CIDR 檢查 (可擴展)
            const [network, mask] = allowedIP.split('/');
            // 這裡可以實現更複雜的 CIDR 檢查
        }
    }
    
    return false;
}

// API 金鑰驗證
function validateApiKey(req) {
    const apiKey = req.headers['x-api-key'] || 
                  new URL(req.url, `http://localhost:${PORT}`).searchParams.get('key');
    
    if (!apiKey) {
        securityLog('warn', 'API 金鑰缺失', req);
        return false;
    }
    
    if (!SECURITY_CONFIG.API_KEYS.includes(apiKey)) {
        securityLog('warn', `無效 API 金鑰: ${apiKey.substring(0, 8)}...`, req);
        return false;
    }
    
    return true;
}

// 頻率限制檢查
function checkRateLimit(ip) {
    const now = Date.now();
    const windowMs = SECURITY_CONFIG.RATE_LIMITS.windowMs;
    
    if (!requestCounters.has(ip)) {
        requestCounters.set(ip, []);
    }
    
    const requests = requestCounters.get(ip);
    
    // 清理過期請求
    const validRequests = requests.filter(time => now - time < windowMs);
    
    // 檢查是否超過限制
    if (validRequests.length >= SECURITY_CONFIG.RATE_LIMITS.maxRequests) {
        return false;
    }
    
    // 記錄新請求
    validRequests.push(now);
    requestCounters.set(ip, validRequests);
    
    return true;
}

// 並發渲染限制
function checkConcurrentLimit() {
    return activeRenders.size < SECURITY_CONFIG.RATE_LIMITS.maxConcurrent;
}

const server = http.createServer((req, res) => {
    const ip = getClientIP(req);
    
    // CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, X-API-KEY');
    
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }
    
    // IP 白名單檢查
    if (!isIPAllowed(ip)) {
        securityLog('warn', `IP 未授權存取`, req);
        res.writeHead(403, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: 'IP 未授權' }));
        return;
    }
    
    // 頻率限制檢查
    if (!checkRateLimit(ip)) {
        securityLog('warn', `頻率限制觸發`, req);
        res.writeHead(429, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: '請求過於頻繁' }));
        return;
    }
    
    console.log(`${req.method} ${req.url} - IP: ${ip}`);
    
    if ((req.url === '/render' || req.url === '/mermaid-api/render') && req.method === 'POST') {
        handleRender(req, res);
    } else if ((req.url === '/history' || req.url === '/mermaid-api/history') && req.method === 'GET') {
        handleHistory(req, res);
    } else if (req.url === '/' || req.url === '/index.php') {
        serveFile('index.php', res);
    } else {
        const filePath = '.' + req.url;
        serveFile(filePath, res);
    }
});

function serveFile(filePath, res) {
    if (fs.existsSync(filePath)) {
        const content = fs.readFileSync(filePath);
        const ext = path.extname(filePath);
        
        let contentType = 'text/plain';
        if (ext === '.html' || ext === '.php') contentType = 'text/html';
        if (ext === '.js') contentType = 'text/javascript';
        if (ext === '.css') contentType = 'text/css';
        if (ext === '.json') contentType = 'application/json';
        
        res.writeHead(200, { 'Content-Type': contentType });
        res.end(content);
    } else {
        res.writeHead(404);
        res.end('File not found');
    }
}

function handleRender(req, res) {
    const ip = getClientIP(req);
    
    // API 金鑰驗證
    if (!validateApiKey(req)) {
        res.writeHead(403, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: '無效的 API 金鑰' }));
        return;
    }
    
    // 並發限制檢查
    if (!checkConcurrentLimit()) {
        securityLog('warn', `並發限制觸發`, req);
        res.writeHead(503, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: '服務器忙碌，請稍後再試' }));
        return;
    }
    
    let body = '';
    req.on('data', chunk => { body += chunk.toString(); });
    
    req.on('end', async () => {
        const renderKey = crypto.randomUUID();
        activeRenders.add(renderKey);
        
        try {
            const { code, format = 'svg', theme = 'light', skipHistory = false, quality = 'web' } = JSON.parse(body);
            
            if (!code) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: '缺少程式碼' }));
                return;
            }
            
            // 程式碼大小限制
            if (code.length > SECURITY_CONFIG.RATE_LIMITS.maxCodeSize) {
                securityLog('warn', `程式碼過大: ${code.length} bytes`, req);
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: '程式碼過大' }));
                return;
            }
            
            const themeFile = theme === 'dark' ? 'theme_dark.json' : 'theme_light.json';
            const fileName = Date.now() + '_' + crypto.randomBytes(4).toString('hex');
            const inputFile = `./tmp/${fileName}.mmd`;
            const outputFile = `./tmp/${fileName}.${format}`;
            
            fs.writeFileSync(inputFile, code);
            
            // 根據品質設定不同的渲染參數
            let cmd = `mmdc -i "${inputFile}" -o "${outputFile}" -c "${themeFile}" -b transparent`;
            
            if (format === 'png') {
                // 根據品質設定不同解析度 - 使用 scale 和 width 組合
                switch (quality) {
                    case 'a2':
                        // A2 海報: 600dpi 超高解析度 (420mm x 594mm)
                        cmd += ' --scale 8 --width 2000';
                        break;
                    case 'a4':
                        // A4 印刷: 600dpi 高解析度 (210mm x 297mm)  
                        cmd += ' --scale 4 --width 1400';
                        break;
                    case 'web':
                    default:
                        // 網頁使用: 1920x1080 標準解析度
                        cmd += ' --scale 1 --width 800';
                        break;
                }
            }
            
            // 為高解析度渲染設定較長的超時時間
            const timeoutMs = quality === 'a2' ? 120000 : (quality === 'a4' ? 60000 : 30000);  // 增加超時時間
            
            exec(cmd, { timeout: timeoutMs }, (error, stdout, stderr) => {
                // 清理輸入檔案
                if (fs.existsSync(inputFile)) fs.unlinkSync(inputFile);
                
                if (error) {
                    const errorMsg = error.code === 'ETIMEDOUT' ? 
                        `渲染超時 (${quality} 品質需要較長時間，請稍後再試)` : 
                        '渲染失敗';
                    
                    securityLog('error', `渲染失敗: ${error.message}`, req);
                    console.log(`❌ mermaid 命令: ${cmd}`);
                    console.log(`❌ 錯誤輸出: ${stderr}`);
                    res.writeHead(500, { 'Content-Type': 'application/json' });
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
            securityLog('info', `渲染成功: ${format}(${quality}), 輸出: ${(fileSize / 1024 / 1024).toFixed(2)}MB`, req);
                    
                    // 儲存成功的 Mermaid 代碼作為備忘錄 (排除預設範例和歷史記錄查看)
                    if (!skipHistory && !isDefaultExample(code)) {
                        try {
                            const historyEntry = {
                                timestamp: new Date().toISOString(),
                                code: code,
                                theme: theme,
                                format: format,
                                codeLength: code.length,
                                ip: getClientIP(req),
                                id: crypto.randomUUID()
                            };
                            
                            const historyFile = `./history/mermaid_history_${new Date().toISOString().split('T')[0]}.jsonl`;
                            fs.appendFileSync(historyFile, JSON.stringify(historyEntry) + '\n');
                        } catch (historyError) {
                            console.log('📝 備忘錄儲存失敗:', historyError.message);
                        }
                            } else if (skipHistory) {
            console.log('📜 跳過歷史儲存');
        }
                } else {
                    res.writeHead(500, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: '檔案生成失敗' }));
                }
            });
            
        } catch (error) {
            securityLog('error', `請求處理錯誤: ${error.message}`, req);
            res.writeHead(400, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ error: '請求格式錯誤' }));
        } finally {
            activeRenders.delete(renderKey);
        }
    });
}

function handleHistory(req, res) {
    const ip = getClientIP(req);
    
    // API 金鑰驗證
    if (!validateApiKey(req)) {
        res.writeHead(403, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: '無效的 API 金鑰' }));
        return;
    }
    
    try {
        const today = new Date().toISOString().split('T')[0];
        const historyFile = `./history/mermaid_history_${today}.jsonl`;
        
        if (!fs.existsSync(historyFile)) {
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ history: [] }));
            return;
        }
        
        const lines = fs.readFileSync(historyFile, 'utf-8').trim().split('\n');
        const history = lines
            .filter(line => line.trim())
            .map(line => {
                try {
                    const entry = JSON.parse(line);
                    // 回傳必要資訊，IP 做脫敏處理
                    return {
                        id: entry.id,
                        timestamp: entry.timestamp,
                        code: entry.code,
                        theme: entry.theme,
                        format: entry.format,
                        codeLength: entry.codeLength,
                        ip: entry.ip // IP 會在前端做脫敏顯示
                    };
                } catch (e) {
                    return null;
                }
            })
            .filter(entry => entry !== null)
            .reverse() // 最新的在前面
            .slice(0, 50); // 限制最多50筆
        
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ history }));
        
        securityLog('info', `歷史查詢成功: ${history.length} 筆記錄`, req);
        
    } catch (error) {
        securityLog('error', `歷史查詢失敗: ${error.message}`, req);
        res.writeHead(500, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ error: '查詢失敗' }));
    }
}

server.listen(PORT, () => {
    console.log('');
    console.log('🔒 安全 Mermaid 服務器已啟動！');
    console.log(`📍 內網: http://localhost:${PORT}`);
    console.log(`🌐 外網: 透過 Cloudflare Tunnel 存取`);
    console.log(`🔑 API 金鑰數量: ${SECURITY_CONFIG.API_KEYS.length}`);
    console.log(`🛡️ IP 白名單: ${SECURITY_CONFIG.IP_WHITELIST.length} 個`);
    console.log('');
    console.log('💡 按 Ctrl+C 停止服務器');
    console.log('');
    
    securityLog('info', '安全服務器啟動');
});

server.on('error', (err) => {
    console.error('❌ 服務器錯誤:', err.message);
    securityLog('error', `服務器錯誤: ${err.message}`);
    process.exit(1);
});

// 優雅關閉
process.on('SIGINT', () => {
    console.log('\n🛑 正在關閉服務器...');
    securityLog('info', '服務器關閉');
    process.exit(0);
});