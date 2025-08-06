# 🎨 Mermaid 2 PNG

A handy tool for quickly converting Mermaid diagrams into PNG/SVG formats. Supports various output qualities and automatically saves source code for future edits.

## ✨ Core Features

* **Multi-Format Output**: SVG and PNG with transparent background
* **High-Quality Rendering**: Resolutions for web, A4 print, and A2 posters
* **Theme Switching**: Light/Dark theme support
* **Live Preview**: SVG zoom and drag support
* **History Tracking**: Auto-save render history
* **Security Features**: API key validation, rate limiting, IP monitoring

## 🚀 Getting Started

> 💡 **Recommended for Personal Use**: [🎯 Simplified Version](#-simplified-version---personal-use) - Zero config, one-click start

### Full Version (Production Use)

```bash
# 1. Install Mermaid CLI
npm install -g @mermaid-js/mermaid-cli

# 2. Backend Setup
cd "mermaid-server/mermaid-backend"
npm install
# Create .env file (see env.example)
npm start

# 3. Frontend Setup
# Deploy mermaid-frontend/ to your Web Server (Apache/Nginx/XAMPP)

# 4. Access
# Frontend: http://localhost/index.php
# Backend: http://localhost:8000
```

### Environment Variables

Create a `.env` file:

```env
MERMAID_API_KEY1=your_secret_api_key
PORT=8000
MAX_REQUESTS_PER_MINUTE=10
```

## 🎯 Simplified Version - Personal Use

**Zero-configuration, one-click start version**:

```bash
cd mermaid-simple/
npm install
npm start
# Visit http://localhost:3000
```

### Version Comparison

| Feature        | Full Version                  | Simplified Version   |
| -------------- | ----------------------------- | -------------------- |
| **Deployment** | Requires Web Server + API Key | One-click start      |
| **Security**   | Full security suite           | Local use only       |
| **Best for**   | Production, teams             | Personal development |

## 🎨 PNG Quality Options

| Quality | Resolution          | Use Case           |
| ------- | ------------------- | ------------------ |
| Web     | 1920×1080           | Presentations, Web |
| A4      | 4961×7016, 600 DPI  | Document printing  |
| A2      | 9921×14032, 600 DPI | Poster display     |

## 🔧 API Usage

```bash
curl -X POST http://localhost:8000/render \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: your_api_key" \
  -d '{
    "code": "graph TD\n    A[Start] --> B[End]",
    "format": "png",
    "theme": "light",
    "quality": "web"
  }'
```

## ⚠️ Security Notes

### Full Version (Production) Security Notice

> **Privacy Warning**: The `/history` API in the full version returns user IP addresses, which may raise privacy concerns.

**Recommended Actions**:

* Evaluate whether IP logging is necessary before deployment
* Consider anonymizing IPs on the backend (e.g., `192.168.x.x`)
* Regularly clean up history records
* Restrict `/history` API access to authorized users only

**Impact Scope**:

* ✅ Simplified Version: Not affected (local use only)
* ⚠️ Full Version: Privacy protection required

### Additional Security Recommendations

* Rotate API keys regularly
* Configure IP whitelists
* Monitor the `logs/` directory
* Ensure `.env` is excluded from version control

## 👨‍💻 Authors

**David Chang** – Lazy Dev
**Cursor Claude 4 Sonnet** – Typewriter

## 📄 License

MIT License – See [LICENSE](LICENSE) for details.

---

Let me know if you need this as a `.md` file or if you'd like any formatting tweaks!
