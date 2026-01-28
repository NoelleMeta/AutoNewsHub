# AutoNewsHub

AutoNewsHub adalah platform berita otomotif berbasis web yang menyediakan informasi terkini seputar mobil, motor, dan teknologi otomotif. Aplikasi ini dibangun menggunakan PHP dan MySQL dengan tampilan modern, responsif, serta sistem manajemen berita untuk admin.

---

## Fitur Utama

* Berita otomotif terbaru
* Fitur pencarian berita
* Sistem login dan registrasi pengguna
* Role-based access (Admin & User)
* Manajemen berita (CRUD oleh Admin)
* Upload hingga 3 gambar per berita
* Dark dan Light theme
* Desain responsif (desktop, tablet, mobile)
* Keamanan password hashing dan session management

---

## Teknologi yang Digunakan

**Backend:** PHP 7.4+
**Database:** MySQL
**Frontend:** HTML5, CSS3, JavaScript
**Server:** Apache (Laragon / XAMPP / WAMP)

---

## Prasyarat

Pastikan sistem Anda memiliki:

* PHP 7.4 atau lebih tinggi
* MySQL 5.7 atau lebih tinggi
* Apache Web Server
* Atau gunakan Laragon/XAMPP/WAMP

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/NoelleMeta/AutoNewsHub.git
cd AutoNewsHub
```

### 2. Setup Database

Buka phpMyAdmin atau MySQL client lalu jalankan:

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

### 3. Konfigurasi Database

Edit file:

```
includes/db.php
```

Sesuaikan konfigurasi:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "auto_news";
```

### 4. Setup Folder Upload

Pastikan folder:

```
uploads/
```

Memiliki permission write (chmod 755 atau 777).

### 5. Jalankan Aplikasi

Jalankan Apache dan MySQL, lalu buka:

```
http://localhost/AutoNewsHub
```

---

## Struktur Project

```
AutoNewsHub/
├── includes/
│   ├── auth.php
│   ├── db.php
│   ├── header.php
│   └── footer.php
├── css/
│   ├── style.css
│   └── bg.jpg
├── uploads/
├── index.php
├── home.php
├── detail.php
├── login.php
├── register.php
├── logout.php
├── crud.php
├── edit.php
├── delete.php
├── search.php
├── about.php
└── README.md
```

---

## Default Admin Account

Buat akun admin melalui register atau langsung insert ke database:

```sql
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@autonewshub.com', 'HASH_PASSWORD', 'admin');
```

Gunakan `password_hash()` PHP untuk membuat hash password.

---

## Cara Penggunaan

### Pengguna

1. Register atau login
2. Lihat berita di halaman utama
3. Gunakan fitur pencarian
4. Klik "Read More" untuk detail berita

### Admin

1. Login sebagai admin
2. Akses menu "Manage News"
3. Tambah, edit, atau hapus berita
4. Upload hingga 3 gambar per berita

---

## Keamanan

* Password di-hash dengan `password_hash()`
* Session authentication
* Validasi input
* Validasi file upload
* Disarankan menggunakan prepared statements

---

## Kustomisasi

### Warna Tema

Edit:

```
css/style.css
```

```css
:root {
    --accent: #ff6b35;
    --blue-metal: #4a90e2;
    --white: #ffffff;
    --gray: #b0b0b0;
}
```

### Pengaturan Database

Edit:

```
includes/db.php
```

---

## Lisensi

Project ini bersifat open source untuk penggunaan pribadi maupun komersial.

---

## Developer

Jovi Rizal
Email: [jovir463@gmail.com](mailto:jovir463@gmail.com)
Role: Full Stack Developer

---

## Contributing

Kontribusi, issue, dan feature request sangat terbuka. Silakan fork dan kirim pull request.

---

## Support

Jika membutuhkan bantuan:

* Buat issue di repository GitHub
* Hubungi via email: [jovir463@gmail.com](mailto:jovir463@gmail.com)

---

## Pengembangan Selanjutnya

* Sistem komentar
* Like/Favorite berita
* Halaman profil user
* Email notifikasi
* RSS feed
* API untuk aplikasi mobile
* Multi-language
* Filter pencarian lanjutan
* Kategori dan tag berita

---
