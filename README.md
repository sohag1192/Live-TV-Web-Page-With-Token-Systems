# 📺 Live-TV Web Page With Token Systems (Flussonic)  ![Badge](https://hitscounter.dev/api/hit?url=https%3A%2F%2Fgithub.com%2Fsohag1192%2FLive-TV-Web-Page-With-Token-Systems&label=&icon=github&color=%23198754&message=&style=flat&tz=UTC)
## 📌 Project Overview
- **Name:** Live-Tv-Server-theme (Flussonic token systems)  
- **Maintainer:** Md. Sohag Rana  
- **Languages:** PHP (~97.5%), Batchfile (~2.5%)  
- **Purpose:** Secure live TV streaming with tokenized URLs for Emby/Jellyfin or standalone web servers.  
- **Demo:** Provides screenshots and frontend templates for live TV pages.

---

## 🚀 Features
- **🔐 Secure Token Generation:** Uses random salt + SHA1 hashing for unique Flussonic tokens.  
- **🎥 HLS Streaming:** Supports DVR playback with secure URLs.  
- **⚡ Performance:** Optional CDNBye P2P acceleration for bandwidth savings.  
- **🌍 Flexible IP Handling:** Works with CDN/P2P setups.  
- **🖥️ Simple PHP Theme:** Lightweight frontend templates (`index.php`).  
- **📂 Automation:** Includes `upload.bat` for Git commits and `.htaccess` for Apache rewrite rules. 
---

## 📂 File Structure
- **index.php ** → Frontend templates for live TV pages  
- **stream.php** → Token generator and stream URL builder  
- **.htaccess** → Apache rewrite rules for clean URLs  
- **upload.bat** → Batch script for Git automation  
- **img/** → Screenshots and assets 

---

---

## 🚀 Features
- One‑command installation of Apache2 + PHP  
- Auto‑configuration of Apache to allow all files  
- Deployment of a PHP test page (`info.php`)  
- Easy uninstallation script to clean up packages and configs  

---

## 📦 Installation
Apache2-PHP Installer  and run the installer click the url:

https://github.com/sohag1192/Apache2-PHP


---

## ⚙️ Installation
1. **Clone the repository:**
   ```bash
   git clone https://github.com/sohag1192/Live-TV-Web-Page-With-Token-Systems.git
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


---

## 🔧 Example Usage
```bash
https://yourserver/stream.php?stream=channel1&type=live
```
- Response: JSON object containing secure token and HLS playback URL. 

---

## 📜 License & Notes
- **Maintained by:** Md. Sohag Rana  
- **Important:** Keep your Flussonic secret key private.  


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

