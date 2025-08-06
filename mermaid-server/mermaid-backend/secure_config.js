/**
 * 安全配置 - 外網部署專用
 * 🔒 從環境變數讀取敏感資訊，確保安全
 */

require('dotenv').config(); // 讀取 .env 檔案

const SECURITY_CONFIG = {
    // API 金鑰配置 (從環境變數讀取)
    API_KEYS: [
        process.env.MERMAID_API_KEY1 || 'demo-key-please-change',
        process.env.MERMAID_API_KEY2 || 'backup-key-please-change'
    ].filter(key => key && key !== 'demo-key-please-change'), // 過濾空值和示例值
    
    // IP 白名單 (從環境變數讀取)
    IP_WHITELIST: process.env.ALLOWED_IPS 
        ? process.env.ALLOWED_IPS.split(',').map(ip => ip.trim())
        : [], // 如果沒設定就空白名單 (允許所有)
    
    // 安全限制 (可從環境變數調整)
    RATE_LIMITS: {
        windowMs: 60 * 1000,        // 1分鐘
        maxRequests: parseInt(process.env.MAX_REQUESTS_PER_MINUTE) || 10,
        maxCodeSize: (parseInt(process.env.MAX_CODE_SIZE_KB) || 20) * 1024,
        maxConcurrent: parseInt(process.env.MAX_CONCURRENT_RENDERS) || 3
    },
    
    // 日誌設定
    LOGGING: {
        enabled: true,
        logIPs: true,
        logUserAgent: true,
        maxLogFiles: parseInt(process.env.LOG_RETENTION_DAYS) || 30,
        logLevel: process.env.LOG_LEVEL || 'info'
    },
    
    // 伺服器設定
    SERVER: {
        port: parseInt(process.env.PORT) || 8000,
        host: process.env.HOST || '127.0.0.1'
    }
};

module.exports = SECURITY_CONFIG;