-- ========================================
-- PEERLEARN DATABASE SCHEMA
-- Platform Tutor Peer-to-Peer
-- ========================================

use peerlearn;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ========================================
-- HAPUS TABEL LAMA (Jika Ada)
-- ========================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `iklan_tutor`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `tutor_mapel`;
DROP TABLE IF EXISTS `learner`;
DROP TABLE IF EXISTS `siswa`;
DROP TABLE IF EXISTS `tutor`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- 1. TABEL USERS (Login System)
-- ========================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','tutor','learner') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 2. TABEL TUTOR (Data Pengajar)
-- ========================================
CREATE TABLE IF NOT EXISTS `tutor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `telepon` varchar(15) DEFAULT NULL,
  `keahlian` varchar(50) DEFAULT NULL,
  `pendidikan` varchar(100) DEFAULT NULL,
  `pengalaman_mengajar` int(11) DEFAULT 0,
  `harga_per_sesi` int(11) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `status` enum('Aktif','Non-Aktif','Cuti') DEFAULT 'Non-Aktif',
  `rating` decimal(3,2) DEFAULT 0.00,
  `foto_profil` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email_notification` tinyint(1) DEFAULT 1,
  `booking_notification` tinyint(1) DEFAULT 1,
  `reminder_notification` tinyint(1) DEFAULT 1,
  `availability_status` enum('Tersedia','Sibuk','Tidak Tersedia') DEFAULT 'Tersedia',
  `availability_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 3. TABEL SISWA (Data Siswa/Learner)
-- ========================================
CREATE TABLE IF NOT EXISTS `siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nim` varchar(20) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `jenjang` enum('SD','SMP','SMA') DEFAULT NULL,
  `sekolah` varchar(100) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `minat` text DEFAULT NULL,
  `status` enum('Aktif','Cuti','Non-Aktif') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 4. TABEL TUTOR_MAPEL (Mata Pelajaran & Jenjang)
-- ========================================
CREATE TABLE IF NOT EXISTS `tutor_mapel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `nama_mapel` varchar(50) NOT NULL,
  `jenjang` enum('SD','SMP','SMA','Umum') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`),
  CONSTRAINT `fk_tutor_mapel` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 5. TABEL SUBJECTS (Kelas/Subject yang Ditawarkan)
-- ========================================
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`),
  CONSTRAINT `fk_subjects_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 6. TABEL BOOKINGS (Transaksi Booking)
-- ========================================
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `learner_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `duration` int(11) DEFAULT 60,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `learner_id` (`learner_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `fk_bookings_learner` FOREIGN KEY (`learner_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 7. TABEL IKLAN_TUTOR (Iklan Tutor)
-- ========================================
CREATE TABLE IF NOT EXISTS `iklan_tutor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `subject` varchar(100) NOT NULL,
  `jenjang` enum('SD','SMP','SMA','Umum') NOT NULL,
  `harga` int(11) NOT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `pengalaman` varchar(255) DEFAULT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  `foto` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`),
  CONSTRAINT `fk_iklan_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 8. TABEL REVIEWS (Rating & Review)
-- ========================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `learner_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `learner_id` (`learner_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `fk_reviews_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutor` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_learner` FOREIGN KEY (`learner_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- DATA SAMPLE
-- ========================================

-- ADMIN USER
INSERT INTO `users` (`nama_lengkap`, `email`, `password`, `role`) VALUES
('Administrator', 'admin@peerlearn.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- TUTOR USERS (50 Tutor)
INSERT INTO `users` (`nama_lengkap`, `email`, `password`, `role`) VALUES
('Rizky Ramadhan', 'rizky@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Aulia Putri Santoso', 'aulia@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Dimas Wahyu Pratama', 'dimas@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Nadia Fitri Maharani', 'nadia@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Farhan Akbar Wijaya', 'farhan@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Sinta Dewi Kusuma', 'sinta@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Adi Pratama Putra', 'adi@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Maya Sari Lestari', 'maya@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Rahman Hakim', 'rahman@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Putri Ayu Lestari', 'putri.ayu@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Arif Budiman', 'arif@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Sari Indah Permata', 'sari.indah@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Bima Sakti', 'bima@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Dewi Kartika', 'dewi.kartika@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Eko Prasetyo', 'eko@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Fitria Nurhaliza', 'fitria@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Gilang Ramadan', 'gilang@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Hana Safira', 'hana@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Irfan Maulana', 'irfan@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Julia Rahmawati', 'julia@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Kevin Wijaya', 'kevin@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Lisa Andriani', 'lisa@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Muhammad Rizal', 'rizal@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Nurul Fadilah', 'nurul@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Omar Abdullah', 'omar@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Puspita Sari', 'puspita@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Qori Aulia', 'qori@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Rendi Saputra', 'rendi@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Siti Nurjanah', 'siti@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Taufik Hidayat', 'taufik@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Umi Kalsum', 'umi@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Vera Octavia', 'vera@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Wahyu Setiawan', 'wahyu@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Xena Maharani', 'xena@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Yoga Pratama', 'yoga@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Zahra Amelia', 'zahra@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Angga Permana', 'angga@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Bella Safitri', 'bella@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Candra Kusuma', 'candra@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Diana Putri', 'diana@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Erick Firmansyah', 'erick@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Fani Rahmawati', 'fani@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Gita Savitri', 'gita@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Hendra Gunawan', 'hendra@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Intan Permatasari', 'intan@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Joko Susilo', 'joko@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Kartika Dewi', 'kartika@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Lutfi Rahman', 'lutfi@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Melati Kusuma', 'melati@example.com', '$2y$10$YourHashedPasswordHere', 'tutor'),
('Novan Ardiansyah', 'novan@example.com', '$2y$10$YourHashedPasswordHere', 'tutor');

-- LEARNER USERS (50 Siswa)
INSERT INTO `users` (`nama_lengkap`, `email`, `password`, `role`) VALUES
('Budi Santoso', 'budi@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Citra Dewi', 'citra@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Deni Firmansyah', 'deni@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Eka Putri', 'eka.putri@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Fajar Nugroho', 'fajar@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Gina Rahmawati', 'gina@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Hadi Wijaya', 'hadi@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Indah Permata', 'indah@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Jihan Aulia', 'jihan@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Krisna Mukti', 'krisna@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Laila Sari', 'laila@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Mario Putra', 'mario@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Nina Kurniawati', 'nina@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Oscar Raharjo', 'oscar@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Putri Wulandari', 'putri.w@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Qomar Syarif', 'qomar@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Rina Susanti', 'rina@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Sandi Pratama', 'sandi@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Tina Marlina', 'tina@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Umar Bakri', 'umar@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Vina Octavia', 'vina@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Wawan Setiawan', 'wawan@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Yuli Astuti', 'yuli@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Zaki Abdullah', 'zaki@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Aditya Pratama', 'aditya.p@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Bunga Citra', 'bunga@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Cahya Nugraha', 'cahya@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Dinda Amelia', 'dinda@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Eko Saputra', 'eko.s@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Fira Maharani', 'fira@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Galih Pratama', 'galih@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Hani Fadilah', 'hani@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Irvan Maulana', 'irvan@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Jasmine Putri', 'jasmine@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Karina Salsabila', 'karina@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Luthfi Hakim', 'luthfi@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Mira Rahayu', 'mira@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Nanda Permana', 'nanda@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Olivia Wijaya', 'olivia@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Pandu Wicaksono', 'pandu@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Qonita Rahmawati', 'qonita@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Raka Firmansyah', 'raka@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Salma Azzahra', 'salma@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Tegar Saputra', 'tegar@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Ulfa Rahmawati', 'ulfa@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Vino Mahendra', 'vino@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Wulan Dari', 'wulan@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Yusuf Rahman', 'yusuf@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Zainal Abidin', 'zainal@example.com', '$2y$10$YourHashedPasswordHere', 'learner'),
('Alya Safitri', 'alya@example.com', '$2y$10$YourHashedPasswordHere', 'learner');

-- DATA TUTOR
INSERT INTO `tutor` (`nama_lengkap`, `email`, `telepon`, `keahlian`, `pendidikan`, `pengalaman_mengajar`, `harga_per_sesi`, `deskripsi`, `status`, `rating`) VALUES
('Rizky Ramadhan', 'rizky@example.com', '081234567890', 'Matematika', 'Universitas Indonesia - Teknik Informatika', 3, 100000, 'Lulusan Teknik Informatika UI dengan pengalaman mengajar matematika untuk SMP dan SMA. Metode pengajaran yang mudah dipahami dan menyenangkan.', 'Aktif', 4.9),
('Aulia Putri Santoso', 'aulia@example.com', '081234567891', 'Bahasa Inggris', 'Universitas Gadjah Mada - Sastra Inggris', 4, 90000, 'Passionate English teacher dengan sertifikasi TOEFL. Spesialisasi grammar, conversation, dan TOEFL preparation.', 'Aktif', 5.0),
('Dimas Wahyu Pratama', 'dimas@example.com', '081234567892', 'Fisika', 'Institut Teknologi Bandung - Teknik Fisika', 2, 95000, 'Mahasiswa ITB yang siap membantu kamu menguasai fisika dengan cara yang fun dan mudah dipahami.', 'Aktif', 4.7),
('Nadia Fitri Maharani', 'nadia@example.com', '081234567893', 'Kimia', 'Universitas Padjadjaran - Kimia Murni', 3, 85000, 'Lulusan Kimia Unpad. Berpengalaman mengajar kimia untuk SMP dan SMA. Mari belajar kimia dengan asyik!', 'Aktif', 4.8),
('Farhan Akbar Wijaya', 'farhan@example.com', '081234567894', 'Biologi', 'Universitas Brawijaya - Biologi', 2, 80000, 'Mahasiswa Biologi yang akan membantu kamu memahami makhluk hidup dan ekosistem dengan lebih mendalam.', 'Aktif', 4.6),
('Sinta Dewi Kusuma', 'sinta@example.com', '081234567895', 'Bahasa Indonesia', 'Universitas Negeri Jakarta - Pendidikan Bahasa Indonesia', 4, 75000, 'Guru Bahasa Indonesia berpengalaman. Mari belajar bahasa Indonesia dengan benar dan menyenangkan.', 'Aktif', 4.5),
('Adi Pratama Putra', 'adi@example.com', '081234567896', 'Ekonomi', 'Universitas Airlangga - Ilmu Ekonomi', 3, 85000, 'Lulusan Ekonomi Unair. Siap membantu kamu memahami konsep ekonomi mikro dan makro dengan mudah.', 'Aktif', 4.7),
('Maya Sari Lestari', 'maya@example.com', '081234567897', 'Sejarah', 'Universitas Negeri Yogyakarta - Pendidikan Sejarah', 2, 70000, 'Passionate history teacher. Mari jelajahi sejarah Indonesia dan dunia bersama saya!', 'Aktif', 4.4);

-- DATA SISWA
INSERT INTO `siswa` (`nim`, `nama_lengkap`, `email`, `jenjang`, `sekolah`, `kelas`, `minat`, `status`) VALUES
('2021001', 'Budi Santoso', 'budi@example.com', 'SMA', 'SMA Negeri 1 Bandar Lampung', '12 IPA 1', 'Matematika, Fisika', 'Aktif'),
('2021002', 'Citra Dewi', 'citra@example.com', 'SMP', 'SMP Negeri 5 Bandar Lampung', '9A', 'Bahasa Inggris, IPA', 'Aktif'),
('2021003', 'Deni Firmansyah', 'deni@example.com', 'SMA', 'SMA Negeri 2 Bandar Lampung', '11 IPS 2', 'Ekonomi, Sejarah', 'Aktif');

-- DATA TUTOR_MAPEL (Mata Pelajaran Tanpa Embel-embel Jenjang)
INSERT INTO `tutor_mapel` (`tutor_id`, `nama_mapel`, `jenjang`) VALUES
(1, 'Matematika', 'SMP'),
(1, 'Matematika', 'SMA'),
(1, 'Aljabar', 'SMA'),
(1, 'Kalkulus', 'SMA'),
(2, 'Bahasa Inggris', 'SD'),
(2, 'Bahasa Inggris', 'SMP'),
(2, 'Bahasa Inggris', 'SMA'),
(2, 'TOEFL Preparation', 'Umum'),
(3, 'Fisika', 'SMP'),
(3, 'Fisika', 'SMA'),
(3, 'IPA', 'SMP'),
(4, 'Kimia', 'SMP'),
(4, 'Kimia', 'SMA'),
(4, 'IPA', 'SMP'),
(5, 'Biologi', 'SMP'),
(5, 'Biologi', 'SMA'),
(5, 'IPA', 'SMP'),
(6, 'Bahasa Indonesia', 'SD'),
(6, 'Bahasa Indonesia', 'SMP'),
(6, 'Bahasa Indonesia', 'SMA'),
(7, 'Ekonomi', 'SMP'),
(7, 'Ekonomi', 'SMA'),
(7, 'IPS', 'SMP'),
(8, 'Sejarah', 'SMP'),
(8, 'Sejarah', 'SMA'),
(8, 'IPS', 'SMP');

-- DATA SUBJECTS (Kelas yang Ditawarkan)
INSERT INTO `subjects` (`tutor_id`, `subject_name`, `description`, `price`) VALUES
(1, 'Matematika', 'Matematika untuk SMP dan SMA, termasuk Aljabar, Trigonometri, dan Kalkulus', 100000),
(2, 'Bahasa Inggris', 'General English untuk SD, SMP, SMA, dan TOEFL Preparation', 90000),
(3, 'Fisika', 'Fisika SMP dan SMA, meliputi Mekanika, Termodinamika, dan Elektromagnetisme', 95000),
(4, 'Kimia', 'Kimia SMP dan SMA, konsep dasar hingga reaksi kimia kompleks', 85000),
(5, 'Biologi', 'Biologi SMP dan SMA, mempelajari makhluk hidup dan ekosistem', 80000),
(6, 'Bahasa Indonesia', 'Bahasa Indonesia untuk SD, SMP, dan SMA, grammar dan literature', 75000),
(7, 'Ekonomi', 'Ekonomi Mikro dan Makro untuk SMP dan SMA', 85000),
(8, 'Sejarah', 'Sejarah Indonesia dan Dunia untuk SMP dan SMA', 70000);

-- DATA BOOKINGS (Contoh Booking)
INSERT INTO `bookings` (`learner_id`, `tutor_id`, `subject_id`, `booking_date`, `booking_time`, `duration`, `status`) VALUES
(1, 1, 1, '2026-01-10', '14:00:00', 90, 'confirmed'),
(1, 2, 2, '2026-01-12', '15:00:00', 60, 'confirmed'),
(2, 3, 3, '2026-01-08', '16:00:00', 90, 'completed'),
(3, 4, 4, '2026-01-15', '13:00:00', 60, 'pending');

-- DATA REVIEWS (Contoh Review)
INSERT INTO `reviews` (`tutor_id`, `learner_id`, `booking_id`, `rating`, `review_text`) VALUES
(3, 2, 3, 5, 'Tutor sangat sabar dan metode pengajarannya mudah dipahami! Sangat recommended.'),
(1, 1, 1, 5, 'Penjelasan matematika sangat detail dan mudah dimengerti. Terima kasih!'),
(2, 1, 2, 5, 'Belajar bahasa Inggris jadi menyenangkan. Teacher Aulia sangat profesional!');

COMMIT;
