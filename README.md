
---

## 📌 Project Overview
- **Name:** Live-Tv-Server-theme (Flussonic token systems)  
- **Maintainer:** Md. Sohag Rana  
- **Languages:** PHP (~97.5%), Batchfile (~2.5%)  
- **Purpose:** Secure live TV streaming with tokenized URLs for Emby/Jellyfin or standalone web servers.  
- **Demo:** Provides screenshots and frontend templates for live TV pages.  [Github](https://github.com/sohag1192/Live-Tv-Server-theme/)

---

## 🚀 Features
- **🔐 Secure Token Generation:** Uses random salt + SHA1 hashing for unique Flussonic tokens.  
- **🎥 HLS Streaming:** Supports DVR playback with secure URLs.  
- **⚡ Performance:** Optional CDNBye P2P acceleration for bandwidth savings.  
- **🌍 Flexible IP Handling:** Works with CDN/P2P setups.  
- **🖥️ Simple PHP Theme:** Lightweight frontend templates (`index.php`, `index1.php`, `index2.php`).  
- **📂 Automation:** Includes `upload.bat` for Git commits and `.htaccess` for Apache rewrite rules.  [Github](https://github.com/sohag1192/Live-Tv-Server-theme/)

---

## 📂 File Structure
- **index.php / index1.php / index2.php** → Frontend templates for live TV pages  
- **stream.php** → Token generator and stream URL builder  
- **.htaccess** → Apache rewrite rules for clean URLs  
- **upload.bat** → Batch script for Git automation  
- **img/** → Screenshots and assets  [Github](https://github.com/sohag1192/Live-Tv-Server-theme/)

---

## ⚙️ Installation
1. **Clone the repository:**
   ```bash
   git clone https://github.com/sohag1192/Live-Tv-Server-theme.git
   cd Live-Tv-Server-theme
   ```
2. **Configure Flussonic server:**
   - Set server address and secret key in `stream.php`.
3. **Deploy PHP files:**
   - Place files on Apache/Nginx with PHP support.
4. **Access streams:**
   ```bash
   https://yourserver/stream.php?stream=channel_name&type=live
   ```
   Returns JSON with secure token + HLS URL.  [Github](https://github.com/sohag1192/Live-Tv-Server-theme/)

---

## 🔧 Example Usage
```bash
https://yourserver/stream.php?stream=channel1&type=live
```
- Response: JSON object containing secure token and HLS playback URL.  [Github](https://github.com/sohag1192/Live-Tv-Server-theme/)

---

## 📜 License & Notes
- **Maintained by:** Md. Sohag Rana  
- **Important:** Keep your Flussonic secret key private.  
- **Releases:** Latest version v2.0 (Jan 30, 2026).  [Github](https://github.com/sohag1192/Live-Tv-Server-theme/)

---

## ✅ Summary Table

| Component       | Purpose                                      |
|-----------------|----------------------------------------------|
| `stream.php`    | Token generator + stream URL builder         |
| `index.php`     | Frontend template for live TV page           |
| `.htaccess`     | Apache rewrite rules                         |
| `upload.bat`    | Git automation script                        |
| `img/`          | Screenshots and assets                       |

---


---

## 📸 Banner Previews

![Screenshot 0](https://github.com/sohag1192/Live-TV-Web-Page-With-Token-Systems/blob/main/demo_img/1.png)  
![Screenshot 1](https://github.com/sohag1192/Live-TV-Web-Page-With-Token-Systems/blob/main/demo_img/2.png)



---

---

## 🙋 Contributing

- Issues and pull requests are welcome.  
- If you find bugs or want to suggest improvements, please open an issue or PR.  

📬 **Contact via Mail:** [sohag1192@gmail.com](mailto:sohag1192@gmail.com)

📬 **Contact via Telegram:** [Md_Sohag_Rana](https://t.me/Md_Sohag_Rana)

---

## 🌟 Support

If you enjoy this project, please ⭐ it on GitHub — your support motivates future updates!

