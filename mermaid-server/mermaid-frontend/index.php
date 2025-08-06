<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="David Chang">
    <meta name="description" content="Mermaid 2 PNG - 專業的 Mermaid 圖表渲染服務">
    <title>Mermaid 2 PNG</title>
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="shortcut icon" href="favicon.ico">
    <style>
        :root {
            --bg-color: #f8f9fa;
            --text-color: #333;
            --panel-bg: white;
            --panel-shadow: rgba(0,0,0,0.1);
            --border-color: #ddd;
            --primary-color:rgb(212, 106, 57);
            --primary-text: white;
            --success-color:rgb(250, 177, 20);
        }

        .dark-mode {
            --bg-color: #212529;
            --text-color: #e9ecef;
            --panel-bg: #343a40;
            --panel-shadow: rgba(255,255,255,0.1);
            --border-color: #495057;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'PingFang TC', 'Microsoft JhengHei', Arial, sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            transition: background 0.3s, color 0.3s;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--panel-bg);
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--panel-shadow);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .panel {
            background: var(--panel-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px var(--panel-shadow);
        }
        
        .panel h2 {
            margin-bottom: 15px;
            color: var(--text-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 5px;
        }
        
        #code-input {
            width: 100%;
            height: 400px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-color);
            border-radius: 4px;
            padding: 10px;
            resize: vertical;
        }
        
        .controls {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: var(--primary-text);
        }
        
        .btn-primary:hover:not(:disabled) {
            filter: brightness(0.9);
        }
        
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        
        .btn-success:hover:not(:disabled) {
            filter: brightness(0.9);
        }

        .theme-switcher .btn {
            background-color: var(--panel-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .theme-switcher .btn.active {
            background-color: var(--primary-color);
            color: var(--primary-text);
            border-color: var(--primary-color);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .api-key-input {
            flex: 1;
            padding: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-color);
            border-radius: 4px;
            font-size: 14px;
        }
        
        .png-quality-select {
            padding: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-color);
            border-radius: 4px;
            font-size: 14px;
            margin-right: 10px;
        }
        
        .preview-area {
            min-height: 400px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 20px;
            background: var(--bg-color);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: auto;
        }
        
        .preview-area svg {
            max-width: 100%;
            max-height: 100%;
        }
        
        .preview-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 5px;
            display: none;
            z-index: 10;
            box-shadow: 0 2px 8px var(--panel-shadow);
        }
        
        .preview-area:hover .preview-controls {
            display: flex;
        }
        
        .zoom-btn {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 4px 8px;
            margin: 0 2px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            min-width: 30px;
        }
        
        .zoom-btn:hover {
            background: var(--primary-color);
            color: var(--primary-text);
        }
        
        .preview-svg {
            transition: transform 0.3s ease;
            cursor: grab;
        }
        
        .preview-svg:active {
            cursor: grabbing;
        }
        
        .preview-area svg .node rect,
        .preview-area svg .node circle,
        .preview-area svg .node ellipse,
        .preview-area svg .node polygon {
            rx: 5px;
            ry: 5px;
        }

        .status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
            display: none;
        }
        
        .status.error {
            background: rgba(255, 107, 107, 0.1);
            color: #d63031;
            border: 1px solid rgba(255, 107, 107, 0.3);
        }
        
        .status.success {
            background: rgba(0, 184, 148, 0.1);
            color: #00b894;
            border: 1px solid rgba(0, 184, 148, 0.3);
        }
        
        .examples {
            background: var(--panel-bg);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px var(--panel-shadow);
        }
        
        .example-btn {
            margin: 5px;
            padding: 8px 15px;
            background: var(--bg-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .example-btn:hover {
            filter: brightness(0.95);
        }
        
        .history-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 10px;
            background: var(--bg-color);
        }
        
        .history-item {
            margin: 8px 0;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: var(--panel-bg);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .history-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px var(--panel-shadow);
            border-color: var(--primary-color);
        }
        
        .history-meta {
            font-size: 12px;
            color: var(--text-color);
            opacity: 0.8;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .history-time {
            font-weight: 500;
        }
        
        .history-theme {
            background: var(--primary-color);
            color: var(--primary-text);
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        
        .history-code {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            max-height: 80px;
            overflow: hidden;
            background: var(--bg-color);
            padding: 8px;
            border-radius: 3px;
            border-left: 3px solid var(--primary-color);
        }
        
        .history-code-title {
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 4px;
        }
        
        .history-pagination {
            text-align: center;
            margin-top: 15px;
        }
        
        .page-btn {
            background: var(--panel-bg);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .page-btn:hover {
            background: var(--primary-color);
            color: var(--primary-text);
        }
        
        .page-btn.active {
            background: var(--primary-color);
            color: var(--primary-text);
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mermaid 2 PNG</h1>
            <p>簡潔高效的流程圖、架構圖生成工具</p>
        </div>
        
        <div class="main-content">
            <div class="panel">
                <h2>編輯區</h2>
                <textarea id="code-input" placeholder="在此輸入 Mermaid 程式碼...">graph TD
    A[開始] --> B{判斷條件}
    B -->|是| C[執行動作]
    B -->|否| D[其他動作]
    C --> E[結束]
    D --> E</textarea>
                
                <div class="controls">
                    <input type="text" id="api-key" class="api-key-input" placeholder="Enter your API key">
                    <button class="btn btn-primary" onclick="renderDiagram()">渲染</button>
                </div>
                
                <div class="controls theme-switcher">
                    <button id="theme-light" class="btn active" onclick="setTheme('light')">淺色</button>
                    <button id="theme-dark" class="btn" onclick="setTheme('dark')">深色</button>
                </div>

                <div id="status" class="status"></div>
            </div>
            
            <div class="panel">
                <h2>預覽區</h2>
                <div id="preview" class="preview-area">
                    <p style="color: var(--text-color); opacity: 0.7;">請點擊「渲染」按鈕生成圖表預覽</p>
                    <div class="preview-controls" id="preview-controls">
                        <button class="zoom-btn" onclick="zoomSVG(0.8)" title="縮小">−</button>
                        <button class="zoom-btn" onclick="resetZoom()" title="重置縮放">100%</button>
                        <button class="zoom-btn" onclick="zoomSVG(1.2)" title="放大">+</button>
                    </div>
                </div>
                
                <div class="controls">
                    <select id="png-quality" class="png-quality-select">
                        <option value="web">網頁使用 (1920x1080)</option>
                        <option value="a4">A4 印刷 (4961x7016, 600DPI)</option>
                        <option value="a2">A2 海報 (9921x14032, 600DPI)</option>
                    </select>
                    <button class="btn btn-success" id="download-png" onclick="downloadPNG()" disabled>下載 PNG</button>
                    <button class="btn btn-success" id="download-svg" onclick="downloadSVG()" disabled>下載 SVG</button>
                    <button class="btn" style="background-color: #6c757d; color: white;" onclick="loadHistory()">查看歷史</button>
                </div>
            </div>
        </div>
        
        <div class="examples">
            <h2>範例</h2>
            <p>點擊下方按鈕載入範例：</p>
            
            <button class="example-btn" onclick="loadExample('flowchart')">流程圖</button>
            <button class="example-btn" onclick="loadExample('sequence')">序列圖</button>
            <button class="example-btn" onclick="loadExample('gantt')">甘特圖</button>
            <button class="example-btn" onclick="loadExample('class')">類別圖</button>
            <button class="example-btn" onclick="loadExample('pie')">圓餅圖</button>
            <button class="example-btn" onclick="loadExample('gitgraph')">Git 圖</button>
            <button class="example-btn" onclick="loadExample('journey')">用戶旅程</button>
            <button class="example-btn" onclick="loadExample('c4')">C4 架構圖</button>
            <button class="example-btn" onclick="loadExample('mindmap')">思維導圖</button>
            <button class="example-btn" onclick="loadExample('timeline')">時間線</button>
            <button class="example-btn" onclick="loadExample('er')">實體關係圖</button>
            <button class="example-btn" onclick="loadExample('state')">狀態圖</button>
            <button class="example-btn" onclick="loadExample('requirement')">需求圖</button>
        </div>
        
        <div class="examples" id="history-section" style="display: none;">
            <h2>歷史記錄</h2>
            <p>今日的 Mermaid 代碼歷史 (預設範例不會儲存)：</p>
            <div class="history-container">
                <div id="history-list"></div>
                <div id="history-pagination" class="history-pagination"></div>
            </div>
        </div>
    </div>

    <script>
        let currentSVG = null;
        let currentTheme = 'light';
        let currentZoom = 1;
        let isDragging = false;
        let dragStart = { x: 0, y: 0 };
        let svgPosition = { x: 0, y: 0 };

        function setTheme(theme) {
            currentTheme = theme;
            document.body.className = theme === 'dark' ? 'dark-mode' : '';
            document.getElementById('theme-light').classList.toggle('active', theme === 'light');
            document.getElementById('theme-dark').classList.toggle('active', theme === 'dark');

            // Re-render the diagram with the new theme
            if (currentSVG) {
                renderDiagram();
            }
        }
        
        // 範例模板
        const examples = {
            flowchart: `graph TD
    A[開始] --> B{判斷條件}
    B -->|是| C[執行動作]
    B -->|否| D[其他動作]
    C --> E[結束]
    D --> E`,
            
            sequence: `sequenceDiagram
    participant A as 使用者
    participant B as 系統
    participant C as 資料庫
    
    A->>B: 發送請求
    B->>C: 查詢資料
    C-->>B: 回傳結果
    B-->>A: 回應結果`,
            
            gantt: `gantt
    title 專案進度表
    dateFormat  YYYY-MM-DD
    section 設計階段
    需求分析      :done, des1, 2024-01-01, 2024-01-05
    系統設計      :done, des2, after des1, 5d
    section 開發階段
    前端開發      :active, dev1, 2024-01-10, 10d
    後端開發      :dev2, after des2, 12d`,
            
            class: `classDiagram
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
            
            pie: `pie title 市場佔有率
    "產品A" : 45
    "產品B" : 30
    "產品C" : 15
    "其他" : 10`,
            
            gitgraph: `gitGraph
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
            
            journey: `journey
    title 使用者購物體驗
    section 瀏覽商品
      訪問網站: 5: 用戶
      搜尋商品: 3: 用戶
      查看詳情: 4: 用戶
    section 購買流程
      加入購物車: 2: 用戶
      結帳付款: 1: 用戶, 系統
      確認訂單: 5: 用戶`,
            
            c4: `C4Context
    title 系統架構圖
    Person(user, "用戶", "系統使用者")
    System(webapp, "Web 應用", "提供核心功能")
    System(db, "資料庫", "儲存用戶資料")
    Rel(user, webapp, "使用")
    Rel(webapp, db, "讀寫")`,
            
            mindmap: `mindmap
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
            
            timeline: `timeline
    title 產品開發時程
    
    2024-01 : 需求調研
           : 市場分析
    2024-02 : 系統設計
           : 技術選型
    2024-03 : 開發階段
           : 測試階段
    2024-04 : 上線部署
           : 維護支援`,
            
            er: `erDiagram
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
            
            state: `stateDiagram-v2
    [*] --> 待處理
    待處理 --> 處理中 : 開始處理
    處理中 --> 已完成 : 完成
    處理中 --> 暫停 : 暫停
    暫停 --> 處理中 : 繼續
    已完成 --> [*]
    處理中 --> 取消 : 取消
    取消 --> [*]`,
            
            requirement: `requirementDiagram

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
        };
        
        function loadExample(type) {
            if (examples[type]) {
                document.getElementById('code-input').value = examples[type];
            }
        }
        
        function showStatus(message, isError = false) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status ' + (isError ? 'error' : 'success');
            status.style.display = 'block';
            
            setTimeout(() => {
                status.style.display = 'none';
            }, 5000);
        }
        
        async function renderDiagram() {
            const code = document.getElementById('code-input').value.trim();
            const apiKey = document.getElementById('api-key').value.trim();
            
            if (!code) {
                showStatus('請輸入 Mermaid 程式碼', true);
                return;
            }
            
            if (!apiKey) {
                showStatus('請輸入 API 金鑰', true);
                return;
            }
            
            const renderBtn = document.querySelector('.btn-primary');
            const downloadBtns = document.querySelectorAll('.btn-success');
            
            // 禁用按鈕
            renderBtn.disabled = true;
            renderBtn.textContent = '渲染中...';
            downloadBtns.forEach(btn => btn.disabled = true);
            
            try {
                const response = await fetch('https://darchc.com/mermaid-api/render', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-KEY': apiKey
                    },
                    body: JSON.stringify({
                        code: code,
                        format: 'svg',
                        theme: currentTheme,
                        skipHistory: window.isHistoryRender || false
                    })
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || `HTTP ${response.status}`);
                }
                
                const svgContent = await response.text();
                currentSVG = svgContent;
                
                // 顯示預覽
                const preview = document.getElementById('preview');
                preview.innerHTML = svgContent;
                
                // 為 SVG 添加縮放和拖拽功能
                const svgElement = preview.querySelector('svg');
                if (svgElement) {
                    setupSVGInteraction(svgElement);
                }
                
                // 重新添加控制按鈕（確保在新內容載入後存在）
                if (!preview.querySelector('.preview-controls')) {
                    const controls = document.createElement('div');
                    controls.className = 'preview-controls';
                    controls.id = 'preview-controls';
                    controls.innerHTML = `
                        <button class="zoom-btn" onclick="zoomSVG(0.8)" title="縮小">−</button>
                        <button class="zoom-btn" onclick="resetZoom()" title="重置縮放">100%</button>
                        <button class="zoom-btn" onclick="zoomSVG(1.2)" title="放大">+</button>
                    `;
                    preview.appendChild(controls);
                }
                
                // 啟用下載按鈕
                downloadBtns.forEach(btn => btn.disabled = false);
                
                showStatus('圖表渲染成功！');
                
            } catch (error) {
                console.error('渲染錯誤:', error);
                showStatus('渲染失敗: ' + error.message, true);
                
                const preview = document.getElementById('preview');
                preview.innerHTML = '<p style="color: var(--text-color); opacity: 0.7;">渲染失敗，請檢查程式碼語法</p>';
                currentSVG = null;
            } finally {
                // 恢復按鈕
                renderBtn.disabled = false;
                renderBtn.textContent = '渲染';
                
                // 重置歷史記錄標記
                window.isHistoryRender = false;
            }
        }
        
        async function downloadPNG() {
            if (!currentSVG) {
                showStatus('請先渲染圖表', true);
                return;
            }
            
            const code = document.getElementById('code-input').value.trim();
            const apiKey = document.getElementById('api-key').value.trim();
            const quality = document.getElementById('png-quality').value;
            
            // 顯示下載中狀態
            const downloadBtn = document.getElementById('download-png');
            const originalText = downloadBtn.textContent;
            downloadBtn.disabled = true;
            downloadBtn.textContent = '生成中...';
            
            try {
                const response = await fetch('https://darchc.com/mermaid-api/render', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-KEY': apiKey
                    },
                    body: JSON.stringify({
                        code: code,
                        format: 'png',
                        theme: currentTheme,
                        quality: quality,
                        skipHistory: true  // PNG 下載不記錄歷史
                    })
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || `HTTP ${response.status}`);
                }
                
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                
                // 根據品質設定檔名
                const qualityNames = {
                    'web': '網頁版-1080p',
                    'a4': 'A4印刷版-600DPI',
                    'a2': 'A2海報版-600DPI'
                };
                
                const a = document.createElement('a');
                a.href = url;
                a.download = `diagram-${qualityNames[quality]}-${new Date().toISOString().slice(0,19).replace(/:/g,'-')}.png`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                showStatus(`${qualityNames[quality]} PNG 下載完成！`);
                
            } catch (error) {
                console.error('下載錯誤:', error);
                showStatus('PNG 下載失敗: ' + error.message, true);
            } finally {
                // 恢復按鈕
                downloadBtn.disabled = false;
                downloadBtn.textContent = originalText;
            }
        }
        
        function downloadSVG() {
            if (!currentSVG) {
                showStatus('請先渲染圖表', true);
                return;
            }
            
            const blob = new Blob([currentSVG], { type: 'image/svg+xml' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `diagram-${new Date().toISOString().slice(0,19).replace(/:/g,'-')}.svg`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            showStatus('SVG 下載完成！');
        }
        
        // 鍵盤快捷鍵
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                renderDiagram();
            }
        });
        
        async function loadHistory() {
            const apiKey = document.getElementById('api-key').value.trim();
            
            if (!apiKey) {
                showStatus('請輸入 API 金鑰', true);
                return;
            }
            
            try {
                const response = await fetch('https://darchc.com/mermaid-api/history', {
                    method: 'GET',
                    headers: {
                        'X-API-KEY': apiKey
                    }
                });
                
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || `HTTP ${response.status}`);
                }
                
                const data = await response.json();
                // 重置分頁到第一頁
                currentPage = 1;
                displayHistory(data.history);
                
            } catch (error) {
                console.error('歷史查詢錯誤:', error);
                showStatus('歷史查詢失敗: ' + error.message, true);
            }
        }
        
        // 分頁相關變數
        let currentPage = 1;
        const itemsPerPage = 10;
        
        function extractDiagramInfo(code) {
            const lines = code.trim().split('\n');
            let title = '';
            let preview = '';
            
            // 嘗試提取標題
            if (lines[0].includes('title ')) {
                title = lines[0].replace(/.*title\s+/, '').trim();
            } else if (lines[1] && lines[1].includes('title ')) {
                title = lines[1].replace(/.*title\s+/, '').trim();
            }
            
            // 如果沒有標題，嘗試提取圖表類型
            if (!title) {
                const firstLine = lines[0].trim();
                if (firstLine.startsWith('graph')) title = '流程圖';
                else if (firstLine.startsWith('sequenceDiagram')) title = '序列圖';
                else if (firstLine.startsWith('gantt')) title = '甘特圖';
                else if (firstLine.startsWith('classDiagram')) title = '類別圖';
                else if (firstLine.startsWith('pie')) title = '圓餅圖';
                else if (firstLine.startsWith('gitGraph')) title = 'Git 圖';
                else if (firstLine.startsWith('journey')) title = '用戶旅程';
                else if (firstLine.startsWith('C4Context')) title = 'C4 架構圖';
                else if (firstLine.startsWith('mindmap')) title = '思維導圖';
                else if (firstLine.startsWith('timeline')) title = '時間線';
                else if (firstLine.startsWith('erDiagram')) title = '實體關係圖';
                else if (firstLine.startsWith('stateDiagram')) title = '狀態圖';
                else if (firstLine.startsWith('requirementDiagram')) title = '需求圖';
                else title = '未知圖表';
            }
            
            // 生成預覽（前3行）
            preview = lines.slice(0, 3).join('\n');
            if (lines.length > 3) preview += '\n...';
            
            return { title, preview };
        }
        
        function displayHistory(history) {
            const historySection = document.getElementById('history-section');
            const historyList = document.getElementById('history-list');
            const historyPagination = document.getElementById('history-pagination');
            
            if (history.length === 0) {
                historyList.innerHTML = '<p style="color: var(--text-color); opacity: 0.7;">今日暫無歷史記錄</p>';
                historyPagination.innerHTML = '';
            } else {
                // 計算分頁
                const totalPages = Math.ceil(history.length / itemsPerPage);
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const currentItems = history.slice(startIndex, endIndex);
                
                // 顯示當前頁面的項目
                historyList.innerHTML = currentItems.map(item => {
                    const date = new Date(item.timestamp);
                    const dateTimeStr = date.toLocaleString('zh-TW', { 
                        month: '2-digit', 
                        day: '2-digit', 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: false 
                    });
                    
                    const { title, preview } = extractDiagramInfo(item.code);
                    const ipDisplay = item.ip ? item.ip.replace(/\d+$/, 'xxx') : 'unknown'; // 隱藏 IP 最後一段
                    
                    return `
                        <div class="history-item" onclick="loadHistoryItem('${item.id}')">
                            <div class="history-meta">
                                <span class="history-time">${dateTimeStr}</span>
                                <div>
                                    <span class="history-theme">${item.theme === 'dark' ? '深色' : '淺色'}</span>
                                    <span style="margin-left: 8px; font-size: 10px; opacity: 0.6;">${ipDisplay}</span>
                                </div>
                            </div>
                            <div class="history-code">
                                <div class="history-code-title">${title}</div>
                                <div>${preview}</div>
                            </div>
                        </div>
                    `;
                }).join('');
                
                // 顯示分頁控制
                if (totalPages > 1) {
                    let paginationHTML = '';
                    
                    // 上一頁
                    if (currentPage > 1) {
                        paginationHTML += `<button class="page-btn" onclick="changePage(${currentPage - 1})">‹ 上一頁</button>`;
                    }
                    
                    // 頁碼
                    for (let i = 1; i <= totalPages; i++) {
                        if (i === currentPage) {
                            paginationHTML += `<button class="page-btn active">${i}</button>`;
                        } else {
                            paginationHTML += `<button class="page-btn" onclick="changePage(${i})">${i}</button>`;
                        }
                    }
                    
                    // 下一頁
                    if (currentPage < totalPages) {
                        paginationHTML += `<button class="page-btn" onclick="changePage(${currentPage + 1})">下一頁 ›</button>`;
                    }
                    
                    historyPagination.innerHTML = paginationHTML;
                } else {
                    historyPagination.innerHTML = '';
                }
            }
            
            historySection.style.display = 'block';
            historySection.scrollIntoView({ behavior: 'smooth' });
            
            // 儲存歷史數據供後續使用
            window.historyData = history;
        }
        
        function changePage(page) {
            currentPage = page;
            displayHistory(window.historyData);
        }
        
        // SVG 縮放和拖拽功能
        function setupSVGInteraction(svgElement) {
            svgElement.classList.add('preview-svg');
            
            // 滾輪縮放
            svgElement.addEventListener('wheel', function(e) {
                e.preventDefault();
                const zoomFactor = e.deltaY > 0 ? 0.9 : 1.1;
                zoomSVG(zoomFactor);
            });
            
            // 拖拽功能
            svgElement.addEventListener('mousedown', function(e) {
                isDragging = true;
                dragStart.x = e.clientX - svgPosition.x;
                dragStart.y = e.clientY - svgPosition.y;
                svgElement.style.cursor = 'grabbing';
            });
            
            document.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                
                svgPosition.x = e.clientX - dragStart.x;
                svgPosition.y = e.clientY - dragStart.y;
                
                updateSVGTransform();
            });
            
            document.addEventListener('mouseup', function() {
                isDragging = false;
                const svgElement = document.querySelector('.preview-svg');
                if (svgElement) {
                    svgElement.style.cursor = 'grab';
                }
            });
        }
        
        function zoomSVG(factor) {
            currentZoom *= factor;
            // 限制縮放範圍
            currentZoom = Math.max(0.1, Math.min(5, currentZoom));
            updateSVGTransform();
        }
        
        function resetZoom() {
            currentZoom = 1;
            svgPosition = { x: 0, y: 0 };
            updateSVGTransform();
        }
        
        function updateSVGTransform() {
            const svgElement = document.querySelector('.preview-svg');
            if (svgElement) {
                svgElement.style.transform = `translate(${svgPosition.x}px, ${svgPosition.y}px) scale(${currentZoom})`;
                
                // 更新縮放按鈕顯示
                const zoomDisplay = document.querySelector('.zoom-btn:nth-child(2)');
                if (zoomDisplay) {
                    zoomDisplay.textContent = Math.round(currentZoom * 100) + '%';
                }
            }
        }
        
        function loadHistoryItem(id) {
            if (!window.historyData) return;
            
            const item = window.historyData.find(h => h.id === id);
            if (item) {
                document.getElementById('code-input').value = item.code;
                setTheme(item.theme);
                showStatus(`已載入歷史記錄 (${new Date(item.timestamp).toLocaleTimeString('zh-TW')})`);
                
                // 滾動到編輯區
                document.querySelector('.main-content').scrollIntoView({ behavior: 'smooth' });
                
                // 標記為歷史記錄渲染，自動渲染但不保存
                window.isHistoryRender = true;
                renderDiagram();
            }
        }
        
        // 頁面載入完成後預設載入範例
        window.addEventListener('load', function() {
            loadExample('flowchart');
        });
    </script>
    
    <footer style="position: fixed; bottom: 10px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 3px; font-size: 10px; z-index: 1000;">
        © 2024 David Chang
    </footer>
</body>
</html>