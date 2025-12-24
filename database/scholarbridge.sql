-- Drop database if exists to avoid conflicts
DROP DATABASE IF EXISTS peerlearn;

CREATE DATABASE peerlearn;
USE peerlearn;

-- Drop tables if exist (in reverse order of dependencies)
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS tutor_mapel;
DROP TABLE IF EXISTS tutor;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'tutor', 'learner') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users ADD COLUMN status ENUM('active', 'pending', 'banned') DEFAULT 'active';

UPDATE users SET status = 'active';

CREATE TABLE IF NOT EXISTS siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    jenjang ENUM('SD', 'SMP', 'SMA') NOT NULL,
    sekolah VARCHAR(100),
    kelas VARCHAR(50),
    minat TEXT,
    status ENUM('Aktif', 'Cuti', 'Non-Aktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO siswa (nim, nama_lengkap, email, jenjang, sekolah, kelas, minat, status) VALUES 
('2025001', 'M. Rizky Pratama', 'rizky.p@gmail.com', 'SMA', 'SMAN 2 Bandar Lampung', '12 IPA 1', 'Matematika, Fisika, Robotik', 'Aktif'),
('2025002', 'Alya Kinanti', 'alya.k@yahoo.com', 'SD', 'SD Al-Kautsar Bandar Lampung', 'Kelas 5B', 'Bahasa Inggris, Menggambar', 'Aktif'),
('2025003', 'Andreas Kurniawan', 'andreas.k@gmail.com', 'SMP', 'SMP Xaverius 1 Bandar Lampung', 'Kelas 9C', 'Biologi, Basket', 'Aktif'),
('2025004', 'Siti Fatimah', 'siti.fatimah@outlook.com', 'SMA', 'SMA YP Unila Bandar Lampung', '11 IPS 2', 'Ekonomi, Geografi, Akuntansi', 'Aktif'),
('2025005', 'Kevin Sanjaya', 'kevin.s@gmail.com', 'SD', 'SDN 2 Rawa Laut', 'Kelas 6A', 'Matematika, Olahraga', 'Non-Aktif'),
('2025006', 'Dinda Puspita', 'dinda.p@gmail.com', 'SMP', 'SMPN 1 Bandar Lampung', 'Kelas 8A', 'Bahasa Indonesia, Musik', 'Aktif'),
('2025007', 'Fajar Nugraha', 'fajar.nugraha@gmail.com', 'SMA', 'SMAN 9 Bandar Lampung', '10 IPA 3', 'Koding, Fisika', 'Cuti'),
('2025008', 'Grace Natalia', 'grace.n@yahoo.com', 'SD', 'SD BPK Penabur Bandar Lampung', 'Kelas 3', 'Menyanyi, Calistung', 'Aktif'),
('2025009', 'Budi Santoso', 'budi.santoso@gmail.com', 'SMA', 'SMAN 1 Bandar Lampung', '11 IPA 1', 'Kimia, Biologi', 'Aktif'),
('2025010', 'Putri Rahmawati', 'putri.r@yahoo.com', 'SMP', 'SMPN 2 Bandar Lampung', 'Kelas 7B', 'Matematika, Seni', 'Aktif'),
('2025011', 'Arif Hidayat', 'arif.h@gmail.com', 'SD', 'SDN 1 Teluk Betung', 'Kelas 4A', 'IPA, Olahraga', 'Aktif'),
('2025012', 'Lina Marlina', 'lina.marlina@outlook.com', 'SMA', 'SMAN 5 Bandar Lampung', '12 IPS 1', 'Sejarah, Sosiologi', 'Aktif'),
('2025013', 'Hendra Wijaya', 'hendra.w@gmail.com', 'SMP', 'SMP Muhammadiyah 1', 'Kelas 9A', 'Fisika, TIK', 'Aktif'),
('2025014', 'Sari Wulandari', 'sari.w@yahoo.com', 'SD', 'SD Islam Al-Azhar', 'Kelas 6C', 'Bahasa Arab, Qur\'an', 'Aktif'),
('2025015', 'Rudi Hartono', 'rudi.h@gmail.com', 'SMA', 'SMAN 3 Bandar Lampung', '10 IPA 2', 'Matematika, Informatika', 'Aktif'),
('2025016', 'Maya Anggraini', 'maya.a@outlook.com', 'SMP', 'SMPN 5 Bandar Lampung', 'Kelas 8C', 'Bahasa Inggris, Drama', 'Aktif'),
('2025017', 'Andi Pratama', 'andi.p@gmail.com', 'SD', 'SDN 3 Kedaton', 'Kelas 5A', 'Matematika, Komputer', 'Aktif'),
('2025018', 'Fitri Handayani', 'fitri.h@yahoo.com', 'SMA', 'SMA Perintis 1', '11 IPA 3', 'Fisika, Kimia', 'Aktif'),
('2025019', 'Doni Saputra', 'doni.s@gmail.com', 'SMP', 'SMP Kartika II-2', 'Kelas 7A', 'IPA, Matematika', 'Aktif'),
('2025020', 'Eka Susanti', 'eka.susanti@gmail.com', 'SD', 'SD Fransiskus', 'Kelas 4B', 'Seni, Musik', 'Aktif'),
('2025021', 'Agus Setiawan', 'agus.setiawan@outlook.com', 'SMA', 'SMAN 7 Bandar Lampung', '12 IPA 2', 'Biologi, Geografi', 'Aktif'),
('2025022', 'Dewi Lestari', 'dewi.l@yahoo.com', 'SMP', 'SMPN 10 Bandar Lampung', 'Kelas 9B', 'Ekonomi, Bahasa Indonesia', 'Aktif'),
('2025023', 'Bambang Purnomo', 'bambang.p@gmail.com', 'SD', 'SDN 5 Tanjung Karang', 'Kelas 6B', 'Olahraga, PKN', 'Aktif'),
('2025024', 'Ratna Sari', 'ratna.sari@gmail.com', 'SMA', 'SMA Tri Sukses', '10 IPS 1', 'Ekonomi, Akuntansi', 'Aktif'),
('2025025', 'Irfan Maulana', 'irfan.m@yahoo.com', 'SMP', 'SMP Al-Kautsar', 'Kelas 8B', 'Agama, Bahasa Arab', 'Aktif'),
('2025026', 'Nina Aprilia', 'nina.a@gmail.com', 'SD', 'SD Xaverius 3', 'Kelas 3A', 'Calistung, Menggambar', 'Aktif'),
('2025027', 'Yoga Pratama', 'yoga.p@outlook.com', 'SMA', 'SMAN 4 Bandar Lampung', '11 IPA 2', 'Matematika, Fisika, Programming', 'Aktif'),
('2025028', 'Tari Ramadani', 'tari.r@yahoo.com', 'SMP', 'SMPN 7 Bandar Lampung', 'Kelas 7C', 'Tari, Seni Budaya', 'Aktif'),
('2025029', 'Fauzan Ahmad', 'fauzan.a@gmail.com', 'SD', 'SDN 8 Sukarame', 'Kelas 5C', 'IPA, IPS', 'Aktif'),
('2025030', 'Ayu Lestari', 'ayu.lestari@gmail.com', 'SMA', 'SMAN 6 Bandar Lampung', '12 IPS 2', 'Geografi, Sosiologi', 'Aktif'),
('2025031', 'Reza Pahlevi', 'reza.p@outlook.com', 'SMP', 'SMP Negeri 12', 'Kelas 9C', 'Kimia, Biologi', 'Aktif'),
('2025032', 'Diana Puspita', 'diana.p@yahoo.com', 'SD', 'SD Kristen Immanuel', 'Kelas 6A', 'Bahasa Inggris, Math', 'Aktif'),
('2025033', 'Fikri Hakim', 'fikri.h@gmail.com', 'SMA', 'SMA Negeri 10', '10 IPA 1', 'Robotika, Elektronika', 'Aktif'),
('2025034', 'Sinta Dewi', 'sinta.d@gmail.com', 'SMP', 'SMP IT Al-Firdaus', 'Kelas 8A', 'Tahfidz, Bahasa Arab', 'Aktif'),
('2025035', 'Wahyu Nugroho', 'wahyu.n@outlook.com', 'SD', 'SDN 10 Kemiling', 'Kelas 4C', 'Komputer, Matematika', 'Aktif'),
('2025036', 'Laila Rahma', 'laila.r@yahoo.com', 'SMA', 'SMA Al-Azhar 3', '11 IPS 1', 'Ekonomi, Bahasa Jepang', 'Aktif'),
('2025037', 'Bagas Permana', 'bagas.p@gmail.com', 'SMP', 'SMPN 15 Bandar Lampung', 'Kelas 7B', 'Basket, Olahraga', 'Aktif'),
('2025038', 'Kartika Sari', 'kartika.s@gmail.com', 'SD', 'SD Advent Bandar Lampung', 'Kelas 5B', 'Piano, Menyanyi', 'Aktif'),
('2025039', 'Dimas Anggara', 'dimas.a@outlook.com', 'SMA', 'SMAN 8 Bandar Lampung', '12 IPA 3', 'Kedokteran, Biologi', 'Aktif'),
('2025040', 'Vita Anggraeni', 'vita.a@yahoo.com', 'SMP', 'SMP Strada Bhakti Utama', 'Kelas 9A', 'Matematika, Komputer', 'Aktif'),
('2025041', 'Haris Maulana', 'haris.m@gmail.com', 'SD', 'SDN 12 Rajabasa', 'Kelas 6C', 'Sepak Bola, IPA', 'Aktif'),
('2025042', 'Nia Ramadhani', 'nia.r@gmail.com', 'SMA', 'SMA Negeri 11', '10 IPA 2', 'Kimia, Matematika', 'Aktif'),
('2025043', 'Farhan Rizki', 'farhan.r@outlook.com', 'SMP', 'SMPN 20 Bandar Lampung', 'Kelas 8B', 'Fisika, TIK', 'Aktif'),
('2025044', 'Anggun Permata', 'anggun.p@yahoo.com', 'SD', 'SD Integral Luqman Al Hakim', 'Kelas 4A', 'Tahfidz, Arabic', 'Aktif'),
('2025045', 'Randy Pratama', 'randy.p@gmail.com', 'SMA', 'SMAN 12 Bandar Lampung', '11 IPS 2', 'Akuntansi, Bisnis', 'Aktif'),
('2025046', 'Intan Permatasari', 'intan.p@gmail.com', 'SMP', 'SMP Fransiskus Bandar Lampung', 'Kelas 7A', 'Drama, Bahasa Inggris', 'Aktif'),
('2025047', 'Galih Prasetyo', 'galih.p@outlook.com', 'SD', 'SDN 15 Way Halim', 'Kelas 5A', 'Voli, Renang', 'Aktif'),
('2025048', 'Mira Aulia', 'mira.a@yahoo.com', 'SMA', 'SMA Negeri 13', '12 IPA 1', 'Astronomi, Fisika', 'Aktif'),
('2025049', 'Dedy Kurniawan', 'dedy.k@gmail.com', 'SMP', 'SMPN 25 Bandar Lampung', 'Kelas 9B', 'Geografi, Sejarah', 'Aktif'),
('2025050', 'Cindy Wijaya', 'cindy.w@gmail.com', 'SD', 'SD Katolik Santo Yusup', 'Kelas 6B', 'Catur, Matematika', 'Aktif');

CREATE TABLE IF NOT EXISTS tutor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    keahlian VARCHAR(50) NOT NULL,
    pendidikan VARCHAR(100),   
    status ENUM('Aktif', 'Cuti', 'Non-Aktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE tutor 
ADD COLUMN harga_per_sesi INT DEFAULT 100000,
ADD COLUMN rating DECIMAL(3,2) DEFAULT 4.5,
ADD COLUMN foto_profil VARCHAR(255),
ADD COLUMN telepon VARCHAR(20),
ADD COLUMN pengalaman_mengajar INT DEFAULT 1,
ADD COLUMN deskripsi TEXT;

INSERT INTO tutor (nama_lengkap, email, keahlian, pendidikan, status, harga_per_sesi, rating, telepon, pengalaman_mengajar, deskripsi) VALUES 
('M. Ilham Saputra', 'ilham.math@gmail.com', 'Matematika', 'S1 Pendidikan Matematika Unila', 'Aktif', 150000, 4.8, '081234567801', 5, 'Tutor Matematika berpengalaman untuk tingkat SD hingga SMA. Spesialisasi persiapan UTBK dan OSN.'),
('Sarah Amelia', 'sarah.amelia@yahoo.com', 'Fisika', 'S1 Fisika Itera (Institut Teknologi Sumatera)', 'Aktif', 175000, 4.9, '081234567802', 4, 'Lulusan Fisika dengan pengalaman mengajar privat dan bimbel. Metode pembelajaran interaktif dan mudah dipahami.'),
('Ahmad Fauzi', 'fauzi.english@gmail.com', 'Bahasa Inggris', 'S1 Sastra Inggris UIN Raden Intan', 'Cuti', 120000, 4.6, '081234567803', 3, 'English tutor fokus pada conversation, grammar, dan TOEFL preparation.'),
('Dinda Pertiwi', 'dinda.code@outlook.com', 'Koding', 'S1 Informatika Univ. Teknokrat Indonesia', 'Aktif', 200000, 4.9, '081234567804', 3, 'Programmer dan tutor coding untuk pemula. Mengajarkan Python, JavaScript, HTML/CSS, dan dasar pemrograman.'),
('Bayu Nugroho', 'bayu.nugroho@gmail.com', 'Biologi', 'S1 Kedokteran Univ. Malahayati', 'Non-Aktif', 180000, 4.7, '081234567805', 6, 'Mahasiswa kedokteran yang berpengalaman mengajar Biologi SMP dan SMA serta persiapan masuk kedokteran.'),
('Citra Lestari', 'citra.l@gmail.com', 'Kimia', 'S1 Kimia Murni Unila', 'Aktif', 160000, 4.8, '081234567806', 4, 'Tutor Kimia untuk SMA dan persiapan ujian masuk PTN. Metode belajar fun dan aplikatif.'),
('Eko Prasetyo', 'eko.music@gmail.com', 'Musik', 'S1 Seni Musik Ibi Darmajaya', 'Aktif', 100000, 4.5, '081234567807', 7, 'Guru musik profesional. Mengajar gitar, piano, dan vokal untuk semua usia.'),
('Rina Aulia', 'rina.aulia@yahoo.com', 'Ekonomi', 'S1 Akuntansi UBL (Univ. Bandar Lampung)', 'Aktif', 140000, 4.7, '081234567808', 5, 'Tutor Ekonomi dan Akuntansi untuk SMA. Berpengalaman membantu siswa lolos SBMPTN jurusan ekonomi.'),
('Anwar Hidayat', 'anwar.h@gmail.com', 'Sejarah', 'S1 Pendidikan Sejarah Unila', 'Aktif', 130000, 4.6, '081234567809', 4, 'Tutor sejarah dengan metode storytelling yang menarik. Fokus pada sejarah Indonesia dan dunia.'),
('Vina Marliana', 'vina.m@yahoo.com', 'Geografi', 'S1 Geografi UGM', 'Aktif', 140000, 4.7, '081234567810', 3, 'Lulusan Geografi UGM. Mengajar dengan metode visualisasi dan peta interaktif.'),
('Tommy Wijaya', 'tommy.w@outlook.com', 'Bahasa Indonesia', 'S1 Sastra Indonesia Unila', 'Aktif', 120000, 4.5, '081234567811', 5, 'Tutor Bahasa Indonesia untuk semua jenjang. Spesialisasi sastra dan penulisan kreatif.'),
('Risma Dewi', 'risma.d@gmail.com', 'Matematika', 'S2 Pendidikan Matematika UI', 'Aktif', 180000, 4.8, '081234567812', 6, 'Magister Matematika UI dengan pengalaman mengajar di universitas dan persiapan olimpiade.'),
('Hafiz Rahman', 'hafiz.r@gmail.com', 'Bahasa Arab', 'S1 Pendidikan Bahasa Arab UIN', 'Aktif', 110000, 4.6, '081234567813', 4, 'Lulusan UIN dengan hafalan 10 juz. Mengajar bahasa Arab dan tahfidz.'),
('Linda Kartika', 'linda.k@yahoo.com', 'Seni Rupa', 'S1 Seni Rupa ISI Yogyakarta', 'Aktif', 125000, 4.7, '081234567814', 5, 'Seniman dan pengajar seni rupa. Spesialisasi melukis, sketsa, dan digital art.'),
('Bima Sakti', 'bima.s@gmail.com', 'Fisika', 'S1 Astronomi ITB', 'Aktif', 180000, 4.9, '081234567815', 3, 'Lulusan Astronomi ITB. Mengajar astronomi dan fisika dengan metode observasi.'),
('Mega Sari', 'mega.s@outlook.com', 'Psikologi', 'S1 Psikologi Unpad', 'Aktif', 160000, 4.8, '081234567816', 4, 'Psikolog dan tutor untuk bimbingan konseling dan persiapan masuk psikologi.'),
('Rifki Ananda', 'rifki.a@gmail.com', 'Olahraga', 'S1 Pendidikan Jasmani Unila', 'Aktif', 100000, 4.5, '081234567817', 6, 'Pelatih olahraga dan tutor pendidikan jasmani. Spesialisasi basket dan futsal.'),
('Nadia Putri', 'nadia.p@yahoo.com', 'Desain Grafis', 'S1 DKV Telkom University', 'Aktif', 170000, 4.8, '081234567818', 3, 'Designer profesional. Mengajar Adobe Photoshop, Illustrator, dan design thinking.'),
('Yudi Prasetyo', 'yudi.p@gmail.com', 'Agama Islam', 'S1 Pendidikan Agama Islam UIN', 'Aktif', 100000, 4.6, '081234567819', 7, 'Ustadz dan guru agama Islam. Mengajar fiqih, akidah, dan akhlak.'),
('Tina Suryani', 'tina.s@gmail.com', 'Bahasa Inggris', 'S1 Sastra China Unair', 'Aktif', 150000, 4.7, '081234567820', 4, 'Bilingual teacher. Mengajar Bahasa Inggris dan Mandarin dengan metode komunikatif.'),
('Fachri Ramadhan', 'fachri.r@outlook.com', 'Bahasa Jepang', 'S1 Sastra Jepang UI', 'Aktif', 155000, 4.8, '081234567821', 4, 'Lulusan UI dengan sertifikasi JLPT N2. Mengajar bahasa Jepang dan budaya.'),
('Wulan Dari', 'wulan.d@yahoo.com', 'Tari Tradisional', 'S1 Seni Tari ISI Surakarta', 'Aktif', 120000, 4.6, '081234567822', 5, 'Penari dan koreografer tari tradisional Indonesia. Mengajar berbagai tari nusantara.'),
('Ardiansyah', 'ardi.syah@gmail.com', 'Koding', 'S1 Teknik Elektro Unila', 'Aktif', 190000, 4.7, '081234567823', 3, 'Engineer dan tutor elektronika. Mengajar Arduino, IoT, Python, dan rangkaian elektronik.'),
('Susi Susanti', 'susi.s@gmail.com', 'Akuntansi', 'S1 Akuntansi Trisakti', 'Aktif', 145000, 4.7, '081234567824', 6, 'Akuntan bersertifikat dan tutor akuntansi untuk SMA dan kuliah.'),
('Teguh Santoso', 'teguh.s@outlook.com', 'Ekonomi', 'S1 Manajemen Prasetiya Mulya', 'Aktif', 175000, 4.8, '081234567825', 5, 'Business professional. Mengajar Ekonomi, Marketing, dan Kewirausahaan.'),
('Rini Handayani', 'rini.h@yahoo.com', 'Matematika', 'S1 Statistika ITS', 'Aktif', 160000, 4.8, '081234567826', 4, 'Data analyst dan tutor matematika. Mengajar Matematika, Statistika, SPSS, dan R.'),
('Agung Nugroho', 'agung.n@gmail.com', 'Bahasa Korea', 'S1 Sastra Korea UGM', 'Aktif', 150000, 4.7, '081234567827', 3, 'Lulusan UGM dengan pengalaman tinggal di Korea. Mengajar TOPIK preparation.'),
('Lilis Suryani', 'lilis.s@gmail.com', 'Memasak', 'Diploma Tata Boga Tristar', 'Aktif', 130000, 4.6, '081234567828', 8, 'Chef profesional dan instruktur memasak. Mengajar berbagai masakan Indonesia dan internasional.'),
('Hendro Basuki', 'hendro.b@outlook.com', 'Fotografi', 'S1 Film & TV IKJ', 'Aktif', 155000, 4.8, '081234567829', 5, 'Fotografer profesional. Mengajar teknik fotografi, editing, dan videografi.'),
('Yuni Astuti', 'yuni.a@yahoo.com', 'Komputer Dasar', 'S1 Sistem Informasi Binus', 'Aktif', 115000, 4.5, '081234567830', 6, 'IT trainer untuk pemula. Mengajar Microsoft Office, internet, dan literasi digital.'),
('Bambang Irawan', 'bambang.i@gmail.com', 'Otomotif', 'D3 Teknik Mesin Astra', 'Aktif', 140000, 4.6, '081234567831', 10, 'Mekanik senior dan instruktur otomotif. Mengajar perawatan dan perbaikan kendaraan.'),
('Dewi Anggraini', 'dewi.ang@gmail.com', 'Bahasa Indonesia', 'S1 Ilmu Komunikasi Unpad', 'Aktif', 165000, 4.9, '081234567832', 5, 'MC profesional dan komunikator. Mengajar Bahasa Indonesia, Public Speaking, dan Presentation Skills.'),
('Irwan Setiawan', 'irwan.s@outlook.com', 'Kewirausahaan', 'S2 MBA IPB', 'Aktif', 185000, 4.8, '081234567833', 7, 'Entrepreneur dan business consultant. Mengajar startup, bisnis plan, dan management.'),
('Ratih Permata', 'ratih.p@yahoo.com', 'Fashion Design', 'S1 Desain Mode Esmod Jakarta', 'Aktif', 170000, 4.7, '081234567834', 4, 'Fashion designer dan pattern maker. Mengajar desain busana dan menjahit.'),
('Hadi Prabowo', 'hadi.p@gmail.com', 'Arsitektur', 'S1 Arsitektur UGM', 'Aktif', 180000, 4.8, '081234567835', 4, 'Arsitek profesional. Mengajar AutoCAD, SketchUp, dan desain arsitektur.'),
('Sinta Maharani', 'sinta.m@gmail.com', 'Olahraga', 'Certified Yoga Instructor', 'Aktif', 120000, 4.7, '081234567836', 5, 'Instruktur yoga dan fitness bersertifikat. Mengajar Yoga, Pilates, dan Meditation.'),
('Ridwan Kamil', 'ridwan.k@outlook.com', 'Teknik Sipil', 'S1 Teknik Sipil ITB', 'Aktif', 175000, 4.8, '081234567837', 5, 'Civil engineer dan dosen. Mengajar struktur, RAB, dan manajemen konstruksi.'),
('Fitria Lestari', 'fitria.l@yahoo.com', 'Makeup Artist', 'Certified MUA LaSalle', 'Aktif', 150000, 4.7, '081234567838', 6, 'Professional makeup artist. Mengajar basic makeup hingga bridal makeup.'),
('Iqbal Ramadhan', 'iqbal.r@gmail.com', 'Koding', 'S1 Computer Science Binus', 'Aktif', 220000, 4.9, '081234567839', 2, 'Blockchain developer dan programmer. Mengajar Web Development, Blockchain, dan Cryptocurrency.'),
('Nisa Amalia', 'nisa.a@gmail.com', 'Biologi', 'S1 Gizi IPB', 'Aktif', 155000, 4.8, '081234567840', 4, 'Nutritionist bersertifikat. Mengajar Biologi, Ilmu Gizi, dan Diet Planning.'),
('Farid Habibi', 'farid.h@outlook.com', 'Desain Grafis', 'S1 Animasi Binus', 'Aktif', 185000, 4.8, '081234567841', 3, '2D/3D animator dan designer. Mengajar Animasi, Adobe Suite, dan Blender.'),
('Diah Purnama', 'diah.p@yahoo.com', 'Biologi', 'S1 Keperawatan UI', 'Aktif', 150000, 4.7, '081234567842', 5, 'Perawat profesional. Mengajar Biologi, First Aid, Anatomi, dan persiapan masuk keperawatan.'),
('Yusuf Hidayat', 'yusuf.h@gmail.com', 'Koding', 'S1 Teknik Informatika ITB', 'Aktif', 210000, 4.9, '081234567843', 4, 'Security analyst dan ethical hacker. Mengajar Cyber Security, Network, dan Programming.'),
('Lia Amelia', 'lia.a@gmail.com', 'Bahasa Inggris', 'S1 Jurnalistik UGM', 'Aktif', 140000, 4.7, '081234567844', 5, 'Content writer dan editor. Mengajar English, Copywriting, dan SEO Writing.'),
('Rafi Ahmad', 'rafi.a@outlook.com', 'Desain Grafis', 'S1 Broadcasting IKJ', 'Aktif', 160000, 4.8, '081234567845', 4, 'Video editor profesional. Mengajar Video Editing, Adobe Premiere, dan After Effects.'),
('Novi Rahmawati', 'novi.r@yahoo.com', 'Kesehatan', 'D3 Akupunktur Traditional', 'Aktif', 145000, 4.6, '081234567846', 8, 'Akupunktur terapis bersertifikat. Mengajar ilmu meridian dan teknik akupuntur.'),
('Cahyo Prabowo', 'cahyo.p@gmail.com', 'Fotografi', 'Certified Drone Operator', 'Aktif', 175000, 4.7, '081234567847', 3, 'Professional drone pilot dan photographer. Mengajar aerial photography dan drone operation.'),
('Erni Kusuma', 'erni.k@gmail.com', 'Kesehatan', 'Certified Spa Therapist', 'Aktif', 135000, 4.6, '081234567848', 7, 'Spa therapist dan wellness coach. Mengajar massage techniques dan aromatherapy.'),
('Gilang Pratama', 'gilang.p@outlook.com', 'Koding', 'S1 Game Technology Binus', 'Aktif', 195000, 4.8, '081234567849', 3, 'Game designer dan developer. Mengajar Unity, Unreal Engine, dan Game Development.'),
('Indah Sari', 'indah.s@yahoo.com', 'Desain Grafis', 'S1 Desain Interior Tarumanagara', 'Aktif', 170000, 4.8, '081234567850', 5, 'Interior designer profesional. Mengajar Interior Design, 3ds Max, dan Space Planning.');

-- Update keahlian beberapa tutor agar distribusi lebih realistis
UPDATE tutor SET keahlian = 'Matematika', deskripsi = 'Magister Matematika UI dengan pengalaman mengajar di universitas dan persiapan olimpiade.' WHERE id = 12;
UPDATE tutor SET keahlian = 'Fisika', deskripsi = 'Lulusan Astronomi ITB. Mengajar astronomi dan fisika dengan metode observasi.' WHERE id = 15;
UPDATE tutor SET keahlian = 'Bahasa Inggris', deskripsi = 'Bilingual teacher. Mengajar Bahasa Inggris dan Mandarin dengan metode komunikatif.' WHERE id = 20;
UPDATE tutor SET keahlian = 'Koding', deskripsi = 'Engineer dan tutor elektronika. Mengajar Arduino, IoT, Python, dan rangkaian elektronik.' WHERE id = 23;
UPDATE tutor SET keahlian = 'Ekonomi', deskripsi = 'Business professional. Mengajar Ekonomi, Marketing, dan Kewirausahaan.' WHERE id = 25;
UPDATE tutor SET keahlian = 'Matematika', deskripsi = 'Data analyst dan tutor matematika. Mengajar Matematika, Statistika, SPSS, dan R.' WHERE id = 26;
UPDATE tutor SET keahlian = 'Bahasa Indonesia', deskripsi = 'MC profesional dan komunikator. Mengajar Bahasa Indonesia, Public Speaking, dan Presentation Skills.' WHERE id = 32;
UPDATE tutor SET keahlian = 'Olahraga', deskripsi = 'Instruktur yoga dan fitness bersertifikat. Mengajar Yoga, Pilates, dan Meditation.' WHERE id = 36;
UPDATE tutor SET keahlian = 'Koding', deskripsi = 'Blockchain developer dan programmer. Mengajar Web Development, Blockchain, dan Cryptocurrency.' WHERE id = 39;
UPDATE tutor SET keahlian = 'Biologi', deskripsi = 'Nutritionist bersertifikat. Mengajar Biologi, Ilmu Gizi, dan Diet Planning.' WHERE id = 40;
UPDATE tutor SET keahlian = 'Desain Grafis', deskripsi = '2D/3D animator dan designer. Mengajar Animasi, Adobe Suite, dan Blender.' WHERE id = 41;
UPDATE tutor SET keahlian = 'Biologi', deskripsi = 'Perawat profesional. Mengajar Biologi, First Aid, Anatomi, dan persiapan masuk keperawatan.' WHERE id = 42;
UPDATE tutor SET keahlian = 'Koding', deskripsi = 'Security analyst dan ethical hacker. Mengajar Cyber Security, Network, dan Programming.' WHERE id = 43;
UPDATE tutor SET keahlian = 'Bahasa Inggris', deskripsi = 'Content writer dan editor. Mengajar English, Copywriting, dan SEO Writing.' WHERE id = 44;
UPDATE tutor SET keahlian = 'Desain Grafis', deskripsi = 'Video editor profesional. Mengajar Video Editing, Adobe Premiere, dan After Effects.' WHERE id = 45;
UPDATE tutor SET keahlian = 'Fotografi', deskripsi = 'Professional drone pilot dan photographer. Mengajar aerial photography dan drone operation.' WHERE id = 47;

CREATE TABLE IF NOT EXISTS tutor_mapel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    nama_mapel VARCHAR(50) NOT NULL,
    jenjang ENUM('SD', 'SMP', 'SMA', 'Umum') DEFAULT 'Umum',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES tutor(id) ON DELETE CASCADE
);

INSERT INTO tutor_mapel (tutor_id, nama_mapel, jenjang) VALUES 
(1, 'Matematika', 'SD'),
(1, 'Matematika', 'SMP'),
(1, 'Matematika', 'SMA'),
(2, 'Fisika', 'SMP'),
(2, 'Fisika', 'SMA'),
(2, 'IPA', 'SD'),
(3, 'Bahasa Inggris', 'SD'),
(3, 'Bahasa Inggris', 'SMP'),
(3, 'Bahasa Inggris', 'SMA'),
(3, 'TOEFL Preparation', 'Umum'),
(4, 'Pemrograman Python', 'Umum'),
(4, 'Web Development', 'Umum'),
(4, 'Informatika', 'SMA'),
(5, 'Biologi', 'SMP'),
(5, 'Biologi', 'SMA'),
(5, 'IPA', 'SD'),
(6, 'Kimia', 'SMP'),
(6, 'Kimia', 'SMA'),
(6, 'IPA', 'SD'),
(7, 'Musik - Gitar', 'Umum'),
(7, 'Musik - Piano', 'Umum'),
(7, 'Musik - Vokal', 'Umum'),
(8, 'Ekonomi', 'SMA'),
(8, 'Akuntansi', 'SMA'),
(8, 'IPS', 'SMP');

CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES tutor(id) ON DELETE CASCADE
);

INSERT INTO subjects (tutor_id, subject_name, price, description) VALUES 
(1, 'Matematika SD', 120000, 'Matematika untuk siswa SD kelas 1-6, fokus pada pemahaman konsep dasar'),
(1, 'Matematika SMP', 150000, 'Matematika untuk siswa SMP kelas 7-9, persiapan ujian sekolah'),
(1, 'Matematika SMA & UTBK', 180000, 'Matematika SMA kelas 10-12 dan persiapan UTBK, OSN'),
(2, 'Fisika SMP', 150000, 'Fisika dasar untuk siswa SMP dengan metode eksperimen sederhana'),
(2, 'Fisika SMA & UTBK', 200000, 'Fisika SMA dan persiapan UTBK, fokus pada pemecahan soal'),
(2, 'IPA SD', 100000, 'IPA terpadu untuk siswa SD dengan metode fun learning'),
(3, 'English SMP', 120000, 'Grammar, reading, dan conversation untuk siswa SMP'),
(3, 'English SMA', 140000, 'Bahasa Inggris SMA dan persiapan ujian'),
(3, 'TOEFL Preparation', 200000, 'Persiapan TOEFL iBT dan ITP untuk kuliah/beasiswa'),
(4, 'Python untuk Pemula', 180000, 'Belajar Python dari nol hingga bisa membuat project'),
(4, 'Web Development', 220000, 'HTML, CSS, JavaScript untuk membuat website'),
(4, 'Informatika SMA', 160000, 'Mata pelajaran Informatika untuk siswa SMA'),
(5, 'Biologi SMP', 150000, 'Biologi untuk siswa SMP dengan praktikum virtual'),
(5, 'Biologi SMA', 180000, 'Biologi SMA dan persiapan masuk kedokteran'),
(6, 'Kimia SMP', 140000, 'Kimia dasar untuk siswa SMP'),
(6, 'Kimia SMA & UTBK', 180000, 'Kimia SMA dan persiapan UTBK dengan metode aplikatif'),
(7, 'Kursus Gitar', 120000, 'Belajar gitar akustik/elektrik dari dasar hingga mahir'),
(7, 'Kursus Piano', 150000, 'Piano untuk pemula hingga intermediate'),
(7, 'Vocal Training', 100000, 'Latihan vokal dan teknik bernyanyi'),
(8, 'Ekonomi SMA', 150000, 'Ekonomi mikro dan makro untuk siswa SMA'),
(8, 'Akuntansi SMA', 160000, 'Akuntansi dasar dan lanjutan untuk SMA'),
(8, 'IPS SMP', 120000, 'Ilmu Pengetahuan Sosial untuk siswa SMP'),
(9, 'Sejarah Indonesia', 130000, 'Sejarah Indonesia dari masa kerajaan hingga reformasi'),
(9, 'Sejarah Dunia', 135000, 'Sejarah peradaban dunia dan perang dunia'),
(10, 'Geografi SMA', 140000, 'Geografi fisik dan sosial untuk SMA'),
(10, 'Geografi SMP', 120000, 'Pengenalan geografi untuk tingkat SMP'),
(11, 'Bahasa Indonesia SMP', 110000, 'Tata bahasa, sastra, dan menulis untuk SMP'),
(11, 'Bahasa Indonesia SMA', 125000, 'Sastra Indonesia dan menulis esai untuk SMA'),
(12, 'Sosiologi SMA', 145000, 'Pengantar sosiologi dan analisis sosial'),
(12, 'Antropologi', 155000, 'Studi budaya dan masyarakat Indonesia'),
(13, 'Bahasa Arab Pemula', 105000, 'Belajar membaca dan menulis Arab untuk pemula'),
(13, 'Bahasa Arab Lanjut', 125000, 'Conversation dan nahwu shorof'),
(14, 'Melukis Pemula', 120000, 'Teknik dasar melukis dengan cat air dan akrilik'),
(14, 'Digital Art', 140000, 'Menggambar digital dengan tablet dan software'),
(15, 'Astronomi Dasar', 175000, 'Pengenalan sistem tata surya dan bintang'),
(15, 'Astrofisika', 200000, 'Fisika astronomi untuk tingkat lanjut'),
(16, 'Psikologi Umum', 155000, 'Pengenalan psikologi dan perilaku manusia'),
(16, 'Konseling', 170000, 'Teknik konseling dan terapi psikologi'),
(17, 'Basketball Training', 95000, 'Latihan basket untuk pemula dan menengah'),
(17, 'Futsal Training', 90000, 'Teknik dasar dan strategi futsal'),
(18, 'Photoshop', 160000, 'Adobe Photoshop untuk editing foto dan desain'),
(18, 'Illustrator', 165000, 'Vector design dengan Adobe Illustrator'),
(19, 'Fiqih', 95000, 'Ilmu fiqih dan hukum Islam'),
(19, 'Akidah Akhlak', 90000, 'Pelajaran akidah dan pembentukan karakter'),
(20, 'Mandarin HSK 1-2', 145000, 'Persiapan ujian HSK level 1 dan 2'),
(20, 'Mandarin HSK 3-4', 165000, 'Persiapan ujian HSK level 3 dan 4'),
(21, 'Bahasa Jepang N5', 150000, 'Persiapan JLPT N5 untuk pemula'),
(21, 'Bahasa Jepang N4-N3', 170000, 'Persiapan JLPT N4 dan N3'),
(22, 'Tari Saman', 115000, 'Belajar tari Saman dari Aceh'),
(22, 'Tari Pendet', 120000, 'Belajar tari Pendet dari Bali'),
(23, 'Arduino', 160000, 'Belajar Arduino dan IoT dari dasar'),
(23, 'Robotika', 180000, 'Membuat robot sederhana dengan Arduino'),
(24, 'Akuntansi Dasar', 140000, 'Pengantar akuntansi untuk pemula'),
(24, 'Akuntansi Lanjutan', 160000, 'Laporan keuangan dan analisis'),
(25, 'Digital Marketing', 170000, 'Social media marketing dan SEO'),
(25, 'Branding', 180000, 'Membangun brand dan strategi marketing');

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    learner_id INT NOT NULL,
    tutor_id INT NOT NULL,
    subject_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    duration INT DEFAULT 60 COMMENT 'Durasi dalam menit',
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (learner_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tutor(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

INSERT INTO bookings (learner_id, tutor_id, subject_id, booking_date, booking_time, duration, status, notes) VALUES
(1, 1, 3, '2025-12-15', '14:00:00', 90, 'confirmed', 'Persiapan ujian matematika semester, fokus pada integral dan turunan'),
(1, 2, 5, '2025-12-18', '16:00:00', 60, 'pending', 'Belajar fisika gelombang dan bunyi'),
(2, 3, 7, '2025-12-14', '10:00:00', 60, 'completed', 'English conversation practice, belajar vocabulary harian'),
(2, 2, 6, '2025-12-20', '15:00:00', 60, 'confirmed', 'Belajar IPA tentang sistem tata surya'),
(3, 5, 14, '2025-12-16', '13:00:00', 90, 'confirmed', 'Biologi sel dan jaringan, persiapan ulangan'),
(3, 8, 23, '2025-12-22', '11:00:00', 60, 'pending', 'IPS - Geografi Indonesia'),
(4, 8, 21, '2025-12-17', '14:30:00', 60, 'confirmed', 'Ekonomi mikro dan perilaku konsumen'),
(4, 8, 22, '2025-12-19', '16:00:00', 90, 'pending', 'Akuntansi - jurnal umum dan buku besar'),
(6, 3, 8, '2025-12-13', '09:00:00', 60, 'completed', 'Grammar dasar - tenses dan sentence structure'),
(6, 7, 18, '2025-12-21', '10:00:00', 60, 'confirmed', 'Belajar kunci dasar gitar dan lagu sederhana'),
(7, 4, 11, '2025-12-23', '15:00:00', 90, 'pending', 'Belajar Python - variables, loops, dan functions'),
(7, 2, 5, '2025-12-25', '13:00:00', 60, 'pending', 'Fisika - gerak parabola dan hukum Newton'),
(8, 7, 19, '2025-12-24', '11:00:00', 60, 'confirmed', 'Piano pemula - belajar not balok dan lagu anak'),
(9, 6, 16, '2025-12-12', '14:00:00', 60, 'completed', 'Belajar stoikiometri dan perhitungan mol'),
(10, 1, 2, '2025-12-15', '10:00:00', 60, 'confirmed', 'Matematika SMP - aljabar dan persamaan linear'),
(11, 2, 6, '2025-12-16', '13:00:00', 60, 'confirmed', 'IPA SD - siklus air dan cuaca'),
(12, 9, 24, '2025-12-17', '15:00:00', 90, 'pending', 'Sejarah perjuangan kemerdekaan Indonesia'),
(13, 4, 12, '2025-12-18', '16:00:00', 60, 'confirmed', 'Belajar HTML dan CSS untuk website pertama'),
(14, 13, 31, '2025-12-19', '09:00:00', 60, 'completed', 'Belajar huruf hijaiyah dan tajwid'),
(15, 1, 1, '2025-12-20', '14:00:00', 60, 'confirmed', 'Matematika SD - operasi hitung campuran'),
(16, 3, 7, '2025-12-10', '10:00:00', 60, 'completed', 'English conversation - daily activities'),
(17, 4, 10, '2025-12-21', '15:00:00', 90, 'pending', 'Python - membuat calculator sederhana'),
(18, 2, 4, '2025-12-22', '13:00:00', 60, 'confirmed', 'Fisika SMP - gaya dan gerak'),
(19, 5, 13, '2025-12-11', '11:00:00', 60, 'completed', 'Biologi SMP - sistem pencernaan'),
(20, 7, 18, '2025-12-23', '10:00:00', 60, 'pending', 'Piano - belajar chord dasar'),
(21, 5, 14, '2025-12-24', '14:00:00', 90, 'confirmed', 'Biologi SMA - genetika dan hereditas'),
(22, 11, 27, '2025-12-09', '09:00:00', 60, 'completed', 'Bahasa Indonesia - menulis paragraf argumentasi'),
(23, 17, 39, '2025-12-25', '15:00:00', 60, 'confirmed', 'Latihan shooting dan dribbling basket'),
(24, 8, 21, '2025-12-26', '16:00:00', 60, 'pending', 'Ekonomi - permintaan dan penawaran'),
(25, 13, 32, '2025-12-08', '10:00:00', 60, 'completed', 'Bahasa Arab - kosakata sehari-hari'),
(26, 7, 19, '2025-12-27', '11:00:00', 60, 'confirmed', 'Piano - lagu anak-anak Indonesia'),
(27, 1, 3, '2025-12-28', '14:00:00', 90, 'pending', 'Matematika SMA - limit dan turunan fungsi'),
(28, 22, 49, '2025-12-07', '13:00:00', 60, 'completed', 'Tari Pendet - gerakan dasar dan filosofi'),
(29, 2, 6, '2025-12-29', '10:00:00', 60, 'confirmed', 'IPA SD - energi dan perubahannya'),
(30, 10, 25, '2025-12-30', '15:00:00', 60, 'pending', 'Geografi - peta dan skala'),
(31, 6, 15, '2025-12-06', '14:00:00', 60, 'completed', 'Kimia SMP - atom dan molekul'),
(32, 3, 8, '2025-12-31', '09:00:00', 60, 'confirmed', 'English SMA - reading comprehension'),
(33, 23, 47, '2026-01-02', '16:00:00', 90, 'pending', 'Arduino - membuat LED blink project'),
(34, 13, 31, '2026-01-03', '10:00:00', 60, 'confirmed', 'Tahfidz - Surah Al-Mulk'),
(35, 4, 11, '2026-01-04', '14:00:00', 60, 'pending', 'Belajar HTML form dan validasi'),
(36, 20, 45, '2025-12-05', '11:00:00', 60, 'completed', 'Mandarin - perkenalan diri dan angka'),
(37, 17, 40, '2026-01-05', '15:00:00', 60, 'confirmed', 'Futsal - passing dan ball control'),
(38, 7, 17, '2026-01-06', '10:00:00', 60, 'pending', 'Gitar - chord progression dan strumming'),
(39, 5, 14, '2026-01-07', '13:00:00', 90, 'confirmed', 'Biologi - persiapan UTBK biologi sel'),
(40, 1, 2, '2025-12-04', '14:00:00', 60, 'completed', 'Matematika - teorema Pythagoras'),
(41, 18, 41, '2026-01-08', '16:00:00', 60, 'pending', 'Photoshop - color correction dan retouching'),
(42, 6, 16, '2026-01-09', '10:00:00', 60, 'confirmed', 'Kimia SMA - hidrokarbon dan turunannya'),
(43, 4, 12, '2026-01-10', '14:00:00', 90, 'pending', 'JavaScript - DOM manipulation'),
(44, 13, 32, '2025-12-03', '09:00:00', 60, 'completed', 'Bahasa Arab - membaca Al-Quran dengan tajwid'),
(45, 8, 22, '2026-01-11', '15:00:00', 60, 'confirmed', 'Akuntansi - neraca saldo dan jurnal penyesuaian'),
(46, 3, 9, '2026-01-12', '11:00:00', 90, 'pending', 'TOEFL - listening strategies'),
(47, 2, 6, '2025-12-02', '13:00:00', 60, 'completed', 'IPA - fotosintesis dan respirasi'),
(48, 1, 3, '2026-01-13', '14:00:00', 90, 'confirmed', 'Matematika - integral tentu dan tak tentu'),
(49, 11, 28, '2026-01-14', '10:00:00', 60, 'pending', 'Bahasa Indonesia SMA - analisis puisi'),
(50, 21, 47, '2025-12-01', '15:00:00', 60, 'completed', 'Bahasa Jepang - hiragana dan katakana'),
(1, 6, 15, '2026-01-15', '16:00:00', 60, 'confirmed', 'Kimia - elektrokimia dan redoks'),
(2, 14, 33, '2026-01-16', '10:00:00', 60, 'pending', 'Melukis - teknik aquarel landscape'),
(3, 1, 2, '2026-01-17', '14:00:00', 60, 'confirmed', 'Matematika - statistika dan peluang'),
(4, 10, 26, '2026-01-18', '11:00:00', 60, 'pending', 'Geografi SMP - litosfer dan atmosfer'),
(5, 19, 43, '2026-01-19', '09:00:00', 60, 'confirmed', 'Akidah - rukun iman dan rukun Islam'),
(6, 7, 20, '2026-01-20', '10:00:00', 60, 'pending', 'Vocal training - breathing technique'),
(7, 25, 53, '2026-01-21', '15:00:00', 90, 'confirmed', 'Digital marketing - Instagram ads strategy'),
(8, 12, 29, '2026-01-22', '14:00:00', 60, 'pending', 'Sosiologi - stratifikasi sosial'),
(9, 8, 21, '2026-01-23', '16:00:00', 60, 'confirmed', 'Ekonomi - pasar modal dan investasi'),
(10, 24, 51, '2026-01-24', '10:00:00', 90, 'pending', 'Akuntansi - laporan laba rugi');

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    learner_id INT NOT NULL,
    tutor_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (learner_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tutor(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_review (booking_id)
);

INSERT INTO reviews (booking_id, learner_id, tutor_id, rating, review_text) VALUES
(3, 2, 3, 5, 'Pak Ahmad sangat sabar dalam mengajar. Metode conversation practice-nya sangat membantu meningkatkan kepercayaan diri saya berbahasa Inggris. Highly recommended! 👍'),
(9, 6, 3, 5, 'Penjelasan grammar sangat jelas dan mudah dipahami. Kakak Fauzi memberikan banyak contoh praktis yang langsung bisa diaplikasikan. Terima kasih!'),
(14, 9, 6, 5, 'Kak Citra sangat jago menjelaskan konsep kimia yang rumit jadi mudah. Saya jadi lebih paham tentang stoikiometri. Thanks!'),
(16, 11, 2, 4, 'Penjelasan fisika sangat detail, tapi kadang terlalu cepat. Overall bagus!'),
(19, 14, 13, 5, 'Ustadz Hafiz mengajar dengan sangat sabar. Bacaan saya jadi lebih lancar dan benar. Jazakallah khairan'),
(21, 16, 3, 4, 'English conversation - daily activities sangat membantu komunikasi sehari-hari.'),
(22, 17, 4, 5, 'Kak Dinda membuat coding jadi menyenangkan! Penjelasannya step by step dan mudah diikuti. Saya jadi lebih semangat belajar programming.'),
(24, 19, 5, 5, 'Biologi SMP - sistem pencernaan dijelaskan dengan sangat detail dan mudah dipahami!'),
(25, 20, 7, 4, 'Kursus piano menyenangkan, Pak Eko sangat profesional. Semoga bisa terus belajar lagi!'),
(27, 22, 11, 5, 'Bahasa Indonesia - menulis paragraf argumentasi jadi lebih terstruktur. Bu Tommy sangat membantu!'),
(28, 23, 17, 5, 'Pak Rifki melatih dengan baik. Teknik shooting basket saya meningkat pesat dalam 2 minggu. Recommended untuk yang serius latihan!'),
(30, 25, 13, 5, 'Bahasa Arab - kosakata sehari-hari sangat berguna. Ustadz Hafiz mengajar dengan sabar.'),
(31, 6, 15, 5, 'Kimia SMP - atom dan molekul dijelaskan dengan visualisasi yang menarik!'),
(33, 28, 22, 5, 'Tari Pendet - gerakan dasar dan filosofi sangat menarik. Bu Wulan mengajar dengan penuh semangat!'),
(34, 29, 2, 4, 'Penjelasan IPA sangat menarik dengan banyak contoh. Anak saya jadi lebih suka belajar sains.'),
(36, 20, 45, 5, 'Mandarin - perkenalan diri dan angka sangat mudah dipahami. Bu Tina excellent teacher!'),
(37, 32, 3, 5, 'English for SMA sangat membantu persiapan ujian. Reading comprehension jadi lebih mudah dipahami. Thank you!'),
(40, 1, 2, 5, 'Pak Ilham menjelaskan teorema Pythagoras dengan sangat jelas. Saya jadi mengerti aplikasinya dalam kehidupan sehari-hari.'),
(44, 13, 32, 5, 'Bahasa Arab - membaca Al-Quran dengan tajwid jadi lebih lancar. Jazakallah Ustadz!'),
(47, 2, 6, 4, 'IPA - fotosintesis dan respirasi sangat menarik dengan diagram yang jelas.'),
(50, 21, 47, 5, 'Bahasa Jepang - hiragana dan katakana sangat mudah dipelajari dengan Pak Fachri!'),
(1, 1, 3, 5, 'Persiapan ujian matematika sangat membantu. Pak Ilham menjelaskan integral dengan detail!'),
(2, 1, 5, 4, 'Fisika gelombang dan bunyi dijelaskan dengan baik oleh Kak Sarah.'),
(4, 2, 6, 5, 'IPA tentang sistem tata surya sangat menarik! Anak saya jadi lebih suka belajar IPA.'),
(5, 3, 14, 5, 'Biologi sel dan jaringan - persiapan ulangan jadi lebih matang. Thanks Pak Bayu!'),
(6, 3, 23, 4, 'IPS - Geografi Indonesia sangat informatif dengan peta interaktif.'),
(7, 4, 21, 5, 'Ekonomi mikro dan perilaku konsumen explained very well!'),
(8, 4, 22, 4, 'Akuntansi - jurnal umum dan buku besar jadi lebih mudah dipahami.'),
(10, 6, 18, 5, 'Belajar kunci dasar gitar dan lagu sederhana sangat fun!'),
(11, 7, 11, 4, 'Python - variables, loops, dan functions dijelaskan step by step.'),
(12, 7, 5, 5, 'Fisika - gerak parabola dan hukum Newton sangat detail dan aplikatif!'),
(13, 8, 19, 5, 'Piano pemula - belajar not balok dan lagu anak sangat menyenangkan!'),
(15, 10, 2, 4, 'Matematika SMP - aljabar dan persamaan linear jadi lebih mudah.'),
(17, 12, 24, 5, 'Sejarah perjuangan kemerdekaan Indonesia very engaging!'),
(18, 13, 12, 5, 'Belajar HTML dan CSS untuk website pertama sangat helpful!'),
(20, 15, 1, 4, 'Matematika SD - operasi hitung campuran dijelaskan dengan sabar.'),
(23, 18, 4, 5, 'Fisika SMP - gaya dan gerak sangat mudah dipahami!'),
(26, 21, 14, 5, 'Biologi SMA - genetika dan hereditas explained brilliantly!'),
(29, 24, 21, 4, 'Ekonomi - permintaan dan penawaran very informative.'),
(32, 26, 19, 5, 'Piano - lagu anak-anak Indonesia sangat cocok untuk pemula!'),
(35, 27, 3, 4, 'Matematika SMA - limit dan turunan fungsi perlu latihan lebih banyak.'),
(38, 29, 6, 5, 'IPA SD - energi dan perubahannya sangat menarik!'),
(39, 30, 25, 4, 'Geografi - peta dan skala dijelaskan dengan visual yang bagus.'),
(41, 33, 47, 5, 'Arduino - membuat LED blink project sangat fun dan educational!'),
(42, 34, 31, 5, 'Tahfidz - Surah Al-Mulk sangat membantu hafalan saya.'),
(43, 35, 11, 4, 'Belajar HTML form dan validasi sangat praktis dan aplikatif.'),
(45, 37, 40, 5, 'Futsal - passing dan ball control meningkat pesat!'),
(48, 39, 14, 5, 'Biologi - persiapan UTBK biologi sel very comprehensive!'),
(49, 1, 15, 4, 'Kimia - elektrokimia dan redoks explained clearly!'),
(51, 2, 33, 5, 'Melukis - teknik aquarel landscape sangat indah hasilnya!');

UPDATE tutor t 
SET rating = (
    SELECT COALESCE(ROUND(AVG(r.rating), 1), t.rating)
    FROM reviews r 
    WHERE r.tutor_id = t.id
)
WHERE EXISTS (
    SELECT 1 FROM reviews r WHERE r.tutor_id = t.id
);

CREATE INDEX idx_bookings_learner ON bookings(learner_id);
CREATE INDEX idx_bookings_tutor ON bookings(tutor_id);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_date ON bookings(booking_date);
CREATE INDEX idx_reviews_tutor ON reviews(tutor_id);
CREATE INDEX idx_subjects_tutor ON subjects(tutor_id);