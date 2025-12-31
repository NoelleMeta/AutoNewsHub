# 🚗 AutoNewsHub

AutoNewsHub adalah platform berita otomotif yang menyediakan update terkini tentang dunia mobil, motor, dan teknologi otomotif. Dibangun dengan PHP dan MySQL, platform ini menawarkan pengalaman membaca berita yang modern dan user-friendly.

## ✨ Fitur

- 📰 **Berita Otomotif Terbaru** - Dapatkan update terkini tentang industri otomotif
- 🔍 **Pencarian Berita** - Cari berita dengan mudah menggunakan fitur search
- 👤 **Sistem Autentikasi** - Login dan registrasi untuk pengguna
- 🔐 **Role-based Access** - Admin dapat mengelola berita melalui panel admin
- 📝 **CRUD Operations** - Admin dapat membuat, membaca, update, dan hapus berita
- 🖼️ **Upload Gambar** - Support upload hingga 3 gambar per berita
- 🌓 **Dark/Light Theme** - Toggle antara tema gelap dan terang
- 📱 **Responsive Design** - Akses dari desktop, tablet, atau mobile
- 🔒 **Secure** - Password hashing dan session management

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Server**: Apache (Laragon/XAMPP/WAMP)

## 📋 Prasyarat

Sebelum memulai, pastikan Anda telah menginstall:

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache Web Server
- Atau gunakan Laragon/XAMPP/WAMP yang sudah include semua

## 🚀 Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/NoelleMeta/AutoNewsHub.git
   cd AutoNewsHub
   ```

2. **Setup Database**
   - Buka phpMyAdmin atau MySQL client
   - Buat database baru dengan nama `auto_news`
   - Import file SQL (jika ada) atau buat tabel secara manual:
   
   ```sql
   CREATE DATABASE auto_news;
   
   USE auto_news;
   
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(50) UNIQUE NOT NULL,
       email VARCHAR(100) UNIQUE NOT NULL,
       password VARCHAR(255) NOT NULL,
       role ENUM('user', 'admin') DEFAULT 'user',
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   
   CREATE TABLE news (
       id INT AUTO_INCREMENT PRIMARY KEY,
       title VARCHAR(255) NOT NULL,
       description TEXT NOT NULL,
       image1 VARCHAR(255),
       image2 VARCHAR(255),
       image3 VARCHAR(255),
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

3. **Konfigurasi Database**
   - Edit file `includes/db.php` dan sesuaikan dengan konfigurasi database Anda:
   
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "auto_news";
   ```

4. **Setup Uploads Folder**
   - Pastikan folder `uploads/` memiliki permission write (chmod 755 atau 777)
   - Folder ini digunakan untuk menyimpan gambar yang diupload

5. **Jalankan Aplikasi**
   - Jika menggunakan Laragon/XAMPP/WAMP, pastikan Apache dan MySQL sudah running
   - Buka browser dan akses: `http://localhost/AutoNewsHub`

## 📁 Struktur Project

```
AutoNewsHub/
├── includes/
│   ├── auth.php          # Authentication functions
│   ├── db.php            # Database connection
│   ├── header.php        # Header template
│   └── footer.php        # Footer template
├── css/
│   ├── style.css         # Main stylesheet
│   └── bg.jpg            # Background image
├── uploads/              # Uploaded images (gitignored)
├── index.php             # Entry point
├── home.php              # Home page (news listing)
├── detail.php            # News detail page
├── login.php             # Login page
├── register.php          # Registration page
├── logout.php            # Logout handler
├── crud.php              # Admin panel (CRUD operations)
├── edit.php              # Edit news page
├── delete.php            # Delete news handler
├── search.php            # Search functionality
├── about.php             # About page
└── README.md             # This file
```

## 👤 Default Admin Account

Setelah setup database, buat akun admin pertama melalui register atau langsung insert ke database:

```sql
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@autonewshub.com', '$2y$10$...', 'admin');
```

**Note**: Gunakan `hash.php` untuk generate password hash, atau gunakan password hashing function PHP.

## 🎯 Cara Penggunaan

### Untuk Pengguna Biasa:
1. Register akun baru atau login
2. Browse berita di halaman home
3. Gunakan search untuk mencari berita tertentu
4. Klik "Read More" untuk melihat detail berita lengkap

### Untuk Admin:
1. Login dengan akun admin
2. Akses menu "Manage News" di navbar
3. Tambah berita baru dengan mengisi form dan upload gambar (max 3 gambar)
4. Edit atau hapus berita yang sudah ada
5. Semua perubahan akan langsung terlihat di halaman home

## 🔒 Keamanan

- Password di-hash menggunakan PHP `password_hash()`
- Session management untuk autentikasi
- Input validation dan sanitization
- File upload validation (type dan size)
- SQL injection protection dengan prepared statements (disarankan untuk implementasi)

## 🎨 Customization

### Mengubah Theme Colors
Edit file `css/style.css` dan ubah CSS variables:
```css
:root {
    --accent: #ff6b35;
    --blue-metal: #4a90e2;
    --white: #ffffff;
    --gray: #b0b0b0;
    /* ... */
}
```

### Mengubah Database Settings
Edit file `includes/db.php` sesuai konfigurasi server Anda.

## 📝 License

Project ini adalah open source dan tersedia untuk penggunaan pribadi dan komersial.

## 👨‍💻 Developer

**Jovi Rizal**
- Email: jovir463@gmail.com
- Phone: +62 852-9529-0661
- Role: Full Stack Developer & Automotive Enthusiast

## 🤝 Contributing

Contributions, issues, dan feature requests sangat diterima! Jangan ragu untuk fork project ini dan submit pull request.

## 📞 Support

Jika Anda memiliki pertanyaan atau butuh bantuan, silakan:
- Buat issue di GitHub repository
- Hubungi developer melalui email: jovir463@gmail.com

## 🔮 Future Improvements

- [ ] Comment system untuk berita
- [ ] Like/Favorite berita
- [ ] User profile page
- [ ] Email notifications
- [ ] RSS feed
- [ ] API untuk mobile app
- [ ] Multi-language support
- [ ] Advanced search filters
- [ ] News categories/tags

---

**Made with ❤️ for Automotive Enthusiasts**
