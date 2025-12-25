# 🚀 PeerLearn
**Platform Penghubung Siswa & Tutor Mahasiswa Berprestasi**

> **Final Project Praktikum Pemrograman Web - Kelompok 21**  
> Tema: *Innovation in Education*

---

## 👥 Anggota Kelompok

| No | Nama | NPM |
| :--- | :--- | :--- | 
| 1. | **Muhammad Farhan** | **23150610..** |
| 2. | Dhito Aryo Trengginas | 2315061015 |
| 3. | Nuzaim Birri | 23150610.. | 
| 4. | Ramadhani Ahmad | 2365061001 | 
| 5. | Sultan Bani Hakim | 23150610.. | 

---

## 📖 Deskripsi Proyek

**PeerLearn** adalah platform marketplace jasa edukasi yang dirancang untuk menjembatani kesenjangan antara siswa sekolah yang membutuhkan bimbingan belajar dengan mahasiswa universitas berprestasi yang mencari pengalaman mengajar.

Aplikasi ini dibangun menggunakan arsitektur **MVC Sederhana** dengan pemisahan logika (Backend) dan tampilan (Frontend) untuk menjaga kerapian kode.

### Fitur Utama:
1. **Admin:** Validasi pendaftaran tutor (Cek KTM) & manajemen user.
2. **Public:** Pencarian tutor real-time (AJAX) berdasarkan mata pelajaran & harga.
3. **Tutor (Mahasiswa):** Kelola iklan kelas, upload dokumen verifikasi, terima pesanan.
4. **Learner (Siswa):** Booking jadwal tutor, riwayat belajar, rating & review.

---

## 🛠️ Teknologi yang Digunakan

* **Frontend:** HTML5, CSS3, JavaScript (Native)
* **Backend:** PHP (Native/Procedural)
* **Database:** MySQL
* **Version Control:** Git & GitHub

---

## 📂 Struktur Folder

Struktur repositori ini mengikuti aturan tugas akhir laboratorium:

```text
kelompok_21/
├── database/               # File SQL (peerlearn.sql) & ERD
├── images/                 # Bukti tampilan aplikasi & screenshot database
├── src/                    # Source code utama
│   ├── config/             # Koneksi database
│   ├── backend/            # Logika PHP (Auth, Admin, Tutor, Learner)
│   ├── frontend/           # Tampilan (Assets, Layouts, Pages)
│   └── index.php           # Entry point
└── README.md               # Dokumentasi ini
```

---

## 🗄️ Database Schema

### ERD (Entity Relationship Diagram)
![ERD PeerLearn](images/erd-peerlearn.png)

### Struktur Tabel Database

Database **peerlearn** terdiri dari 7 tabel utama:

#### 1. **users** - Tabel User Management
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT (PK) | ID unik user |
| name | VARCHAR(100) | Nama lengkap |
| email | VARCHAR(100) | Email (unique) |
| password | VARCHAR(255) | Password (hashed) |
| role | ENUM | admin/tutor/learner |
| status | ENUM | active/pending/banned |
| created_at | TIMESTAMP | Waktu registrasi |

#### 2. **siswa** - Data Learner (Siswa)
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT (PK) | ID unik siswa |
| nim | VARCHAR(20) | Nomor Induk |
| nama_lengkap | VARCHAR(100) | Nama siswa |
| email | VARCHAR(100) | Email siswa |
| jenjang | ENUM | SD/SMP/SMA |
| sekolah | VARCHAR(100) | Nama sekolah |
| kelas | VARCHAR(50) | Kelas saat ini |
| minat | TEXT | Minat belajar |
| status | ENUM | Aktif/Cuti/Non-Aktif |

#### 3. **tutor** - Data Tutor (Mahasiswa)
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT (PK) | ID unik tutor |
| nama_lengkap | VARCHAR(100) | Nama tutor |
| email | VARCHAR(100) | Email tutor |
| keahlian | VARCHAR(50) | Mata pelajaran utama |
| pendidikan | VARCHAR(100) | Universitas & jurusan |
| harga_per_sesi | INT | Harga per sesi (Rupiah) |
| rating | DECIMAL(3,2) | Rating tutor (1-5) |
| pengalaman_mengajar | INT | Tahun pengalaman |
| status | ENUM | Aktif/Cuti/Non-Aktif |

#### 4. **tutor_mapel** - Mata Pelajaran per Tutor
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT (PK) | ID unik |
| tutor_id | INT (FK) | Relasi ke tutor |
| nama_mapel | VARCHAR(50) | Nama mata pelajaran |
| jenjang | ENUM | SD/SMP/SMA/Umum |

#### 5. **subjects** - Daftar Kelas/Subject
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT (PK) | ID unik subject |
| tutor_id | INT (FK) | Tutor pengajar |
| subject_name | VARCHAR(100) | Nama subject |
| price | DECIMAL(10,2) | Harga per sesi |
| description | TEXT | Deskripsi kelas |

#### 6. **bookings** - Transaksi Booking
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT (PK) | ID booking |
| learner_id | INT (FK) | ID siswa |
| tutor_id | INT (FK) | ID tutor |
| subject_id | INT (FK) | ID subject |
| booking_date | DATE | Tanggal booking |
| booking_time | TIME | Waktu booking |
| duration | INT | Durasi (menit) |
| status | ENUM | pending/confirmed/completed/cancelled |

#### 7. **reviews** - Rating & Review
| Field | Type | Keterangan |
|-------|------|------------|
| id | INT (PK) | ID review |
| booking_id | INT (FK) | ID booking terkait |
| learner_id | INT (FK) | ID siswa penilai |
| tutor_id | INT (FK) | ID tutor dinilai |
| rating | INT | Rating 1-5 |
| review_text | TEXT | Komentar review |

### Screenshot Database

#### Tabel Users
![Tabel Users](images/db-users.png)

#### Tabel Siswa
![Tabel Siswa](images/db-siswa.png)

#### Tabel Tutor
![Tabel Tutor](images/db-tutor.png)

#### Tabel Bookings
![Tabel Bookings](images/db-bookings.png)

#### Tabel Reviews
![Tabel Reviews](images/db-reviews.png)

---

## 📸 Screenshot Aplikasi

### 🏠 Halaman Public

#### Landing Page
![Landing Page](images/landing-page.png)

#### Halaman Kategori
![Kategori Mata Pelajaran](images/categories.png)

#### Pencarian Tutor
![Search Result](images/search-result.png)

#### Detail Profil Tutor
![Detail Tutor](images/tutor-detail.png)

#### Testimoni Siswa
![Testimoni Siswa](images/testi.png)

---

### 👨‍🎓 Halaman Learner (Siswa)

#### Dashboard Siswa
![Dashboard Siswa](images/dashboard-siswa.png)

#### Booking Tutor
![Booking](images/booking-process.png)

#### Sesi Belajar Saya
![Sesi Saya](images/sesi-saya.png)

#### Riwayat Booking
![Riwayat](images/riwayat-booking.png)

#### Profil Siswa
![Profil Siswa](images/profil-siswa.png)

---

### 👨‍🏫 Halaman Tutor (Mahasiswa)

#### Dashboard Tutor
![Dashboard Tutor](images/dashboard-tutor.png)

#### Kelola Kelas
![Kelola Kelas](images/kelola-kelas.png)

#### Pesanan Masuk
![Pesanan Masuk](images/pesanan-masuk.png)

#### Profil Tutor
![Profil Tutor](images/profil-tutor.png)

---

### 🔐 Halaman Auth

#### Tampilan Login
![Login](images/login.png)

#### Tampilan Register Siswa
![Register](images/regis-siswa.png)

#### Tampilan Register Tutor
![Register](images/regis-tutor.png)

---

### 🔐 Halaman Admin

#### Dashboard Admin
![Dashboard Admin](images/dashboard-admin.png)

#### Verifikasi Tutor
![Verifikasi Tutor](images/verifikasi-tutor.png)

#### Manajemen Siswa
![Manajemen Siswa](images/admin-siswa.png)

#### Manajemen Tutor
![Manajemen Tutor](images/admin-tutor.png)

---

## 🚀 Cara Menjalankan Aplikasi

### Prasyarat
- XAMPP/Laragon (PHP 7.4+, MySQL 5.7+)
- Browser modern (Chrome, Firefox, Edge)
- Git (untuk clone repository)

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   cd C:\laragon\www
   git clone https://github.com/dhitoary/TUBES_PRK_PEMWEB_2025.git
   cd TUBES_PRK_PEMWEB_2025/kelompok/kelompok_21
   ```

2. **Setup Database**
   - Buka phpMyAdmin (http://localhost/phpmyadmin)
   - Import file `database/peerlearn.sql`
   - Database akan otomatis dibuat dengan nama `peerlearn`

3. **Konfigurasi Database**
   - Buka file `src/config/database.php`
   - Sesuaikan kredensial database jika diperlukan:
   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $database = "peerlearn";
   ```

4. **Jalankan Aplikasi**
   - Pastikan Apache & MySQL sudah running
   - Akses: `http://localhost/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_21/src/`

### Akun Default untuk Testing

#### Admin
- Email: `admin@peerlearn.com`
- Password: `admin123`

#### Tutor (Mahasiswa)
- Email: `zemverse@gmail.com`
- Password: `password`

#### Learner (Siswa)
- Email: `dhito@gmail.com`
- Password: `password`

---

## ✨ Fitur Unggulan

### 1. **Real-time Search dengan AJAX**
- Pencarian tutor tanpa reload halaman
- Filter berdasarkan jenjang (SD/SMP/SMA/Kuliah/Lihat Semua)
- Filter rating (1-5 bintang)
- Filter harga dengan slider
- Pagination 20 item per halaman

### 2. **Sistem Verifikasi Tutor**
- Admin dapat memverifikasi tutor berdasarkan KTM
- Status: Pending → Active/Banned
- Riwayat keputusan verifikasi dengan timestamp

### 3. **Dashboard Interaktif**
- Chart.js untuk visualisasi data
- Line chart untuk trend pendaftaran (12 bulan)
- Doughnut chart untuk distribusi keahlian tutor
- Real-time statistics
- Responsive design

### 4. **Sistem Rating & Review**
- Siswa dapat memberikan rating 1-5 bintang
- Review text untuk feedback detail
- Rating otomatis update di profil tutor menggunakan AVG()
- One review per booking

### 5. **Booking Management**
- Sistem status booking (pending/confirmed/completed/cancelled)
- Notifikasi booking untuk tutor
- Riwayat lengkap untuk siswa
- Durasi sesi custom (default 60 menit)

### 6. **Data Management**
- Database dengan 50+ tutor aktif
- 50+ siswa terdaftar
- 60+ booking history
- 50+ reviews
- Distribusi keahlian realistis (Matematika, Fisika, Bahasa Inggris, Koding, dll)

---

## 🎨 Design Highlights

- **Color Scheme:** 
  - Primary: `#ff6b35` (Orange)
  - Accent: `#FF6B35` (Orange)
  - Secondary: `#ff9329` (Light Orange)
  - Success: `#10b981` (Green)
  - Warning: `#ffb866` (Orange Medium)
  
- **Typography:** 
  - Font Family: 'Segoe UI', Tahoma, sans-serif
  - Responsive font sizing
  - Font weights: 400, 500, 600, 700, 800

- **UI Components:**
  - Modern card design with hover effects
  - Pill-shaped filter buttons
  - Gradient backgrounds
  - Shadow & border-radius for depth
  - Avatar colors generated from names
  - Bootstrap Icons integration

---

## 📝 Catatan Pengembangan

### Challenges & Solutions

1. **Problem:** Data tutor muncul duplikat saat filter jenjang
   - **Solution:** Mengubah struktur data dari multi-entry per jenjang menjadi single entry dengan array jenjang (`jenjang_list`)

2. **Problem:** Rating tidak update otomatis setelah review
   - **Solution:** Menambahkan UPDATE query dengan `AVG()` dan `COALESCE()` setelah insert review

3. **Problem:** Pagination tidak konsisten saat filter berubah
   - **Solution:** Implementasi state management dengan `currentPage` & `itemsPerPage`, reset ke page 1 saat filter berubah

4. **Problem:** Waktu verifikasi menampilkan nilai negatif (-415 menit)
   - **Solution:** Menggunakan `abs()` untuk absolute value dan menambahkan format waktu yang lebih lengkap (baru saja, menit, jam, hari, minggu, bulan)

5. **Problem:** Kategori tutor tidak terdistribusi realistis
   - **Solution:** Menggunakan UPDATE query untuk mengubah keahlian tutor agar mata pelajaran populer (Matematika, Bahasa Inggris, Koding) memiliki lebih banyak tutor

6. **Problem:** Filter rating hanya menampilkan 4.0+ dan 4.5+
   - **Solution:** Menambahkan filter 1-5 bintang untuk fleksibilitas pencarian

### Technical Highlights

- **Database Optimization:**
  - Indexed foreign keys untuk performa query
  - `GROUP_CONCAT` untuk aggregate jenjang
  - `COALESCE` untuk default values
  - Unique constraint pada `booking_id` di reviews

- **Frontend Optimization:**
  - AJAX search tanpa page reload
  - Client-side pagination untuk fast navigation
  - Avatar color generation dari nama
  - Responsive grid dengan `auto-fill` dan `minmax`

- **Code Quality:**
  - Separation of concerns (MVC pattern)
  - Reusable components (header, footer)
  - Clean URL structure
  - Error handling dengan mysqli_error

### Future Improvements

- [ ] Implementasi payment gateway (Midtrans/Xendit)
- [ ] Real-time chat tutor-siswa dengan WebSocket
- [ ] Mobile app version (React Native/Flutter)
- [ ] Video conference integration (Zoom API)
- [ ] Automated matching algorithm (ML-based)
- [ ] Email notification system
- [ ] Export data to Excel/PDF
- [ ] Multi-language support (ID/EN)

---

## 📄 Lisensi

Project ini dibuat untuk keperluan akademik **Praktikum Pemrograman Web** - Universitas Lampung (2025).

---

## 🙏 Acknowledgments

- **Dosen Pengampu:** [Resty Annisa, S.ST., M.Kom]
- **References:** 
  - Bootstrap Icons (https://icons.getbootstrap.com)
  - Chart.js (https://www.chartjs.org)
  - PHP Documentation (https://www.php.net)
  - MySQL Documentation (https://dev.mysql.com/doc)

---

**© 2025 PeerLearn**  
*Dibuat dengan ❤️ untuk Manajemen Proyek Teknologi Informasi*
