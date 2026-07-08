
# 📺 Live TV Web Page with Token System

A secure, token‑based PHP theme for **Flussonic streaming**.  
This project provides a lightweight web interface with **HLS streaming**, **token authentication**, and optional **CDNBye P2P acceleration**.

---

## ✨ Features
- 🔐 **Token Security** – SHA1 + salt token generation for stream protection.
- 🎥 **HLS Streaming** – Supports DVR playback with secure URLs.
- ⚡ **CDNBye Integration** – Optional P2P acceleration for bandwidth savings.
- 🖥️ **Frontend Templates** – Multiple PHP themes (`index.php`, `index1.php`, `index2.php`).
- 🔄 **Automation** – Batch script (`upload.bat`) for Git commits and updates.
- 🛡️ **Apache Rewrite Rules** – `.htaccess` included for clean URLs.

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

## 📸 Banner Previews

![Screenshot 0](https://github.com/sohag1192/Live-TV-Web-Page-With-Token-Systems/blob/main/demo_img/1.png)  
![Screenshot 1](https://github.com/sohag1192/Live-TV-Web-Page-With-Token-Systems/blob/main/demo_img/2.png)



---

## 📜 License
Licensed under **AGPL-3.0**  
Maintainer: **Md. Sohag Rana (sohag1192)**

---


## 🙋 Contributing

- Issues and pull requests are welcome.  
- If you find bugs or want to suggest improvements, please open an issue or PR.  

📬 **Contact via Mail:** [sohag1192@gmail.com](mailto:sohag1192@gmail.com)

📬 **Contact via Telegram:** [Md_Sohag_Rana](https://t.me/Md_Sohag_Rana)


---

## 🌟 Support

If you enjoy this project, please ⭐ it on GitHub — your support motivates future updates!


