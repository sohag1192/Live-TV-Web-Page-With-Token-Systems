
# 📺 Live TV Web Page with Token System

Secure, token-based PHP theme for **Flussonic streaming**.  
This project provides a lightweight web interface with **HLS streaming**, **token authentication**, and **CDNBye P2P acceleration**.

---

## ✨ Features
- 🔐 **Secure Token System** – Protects streams with SHA1 + salt token generation.
- 🎥 **HLS Streaming** – Supports DVR playback with secure URLs.
- ⚡ **CDNBye Integration** – Optional P2P acceleration for bandwidth savings.
- 🖥️ **Frontend Templates** – Multiple PHP themes (`index.php`, `index1.php`, `index2.php`).
- 🔄 **Automation** – Batch script (`upload.bat`) for Git commits and updates.
- 🛡️ **Apache Rewrite Rules** – `.htaccess` included for clean URLs.

---

## 📂 Project Structure
| File/Folder       | Description                                      |
|-------------------|--------------------------------------------------|
| `stream.php`      | 🔑 Core token generator + secure stream builder  |
| `index.php`       | 🎨 Main frontend template                        |
| `index1.php`      | 🎨 Alternative frontend template                 |
| `index2.php`      | 🎨 Alternative frontend template                 |
| `.htaccess`       | 🛡️ Apache rewrite rules                         |
| `upload.bat`      | ⚙️ Git automation script                        |
| `img/`            | 🖼️ Screenshots and assets                       |

---

## ⚙️ Installation
1. **Clone Repository**
   ```bash
   git clone https://github.com/sohag1192/Live-TV-Web-Page-With-Token-Systems.git
   cd Live-TV-Web-Page-With-Token-Systems
   ```

2. **Configure Flussonic**
   - Edit `stream.php` and set:
     - Server address
     - Secret key (keep private!)

3. **Deploy PHP Files**
   - Place files on Apache/Nginx with PHP support.

4. **Access Streams**
   ```url
   https://yourserver/stream.php?stream=channel_name&type=live
   ```

---

## 📊 Example Output
```json
{
  "token": "secure_generated_token",
  "url": "https://yourserver/channel1/index.m3u8?token=..."
}
```

---

## 🛡️ Security Notes
- Keep your **Flussonic secret key private**.
- Do not expose `stream.php` logic publicly.
- Use HTTPS for secure delivery.

---

## 📸 Screenshots
(Add your screenshots here using Markdown image syntax)

---

## 📜 License
Licensed under **AGPL-3.0**.  
Maintainer: **Md. Sohag Rana (sohag1192)**

---
