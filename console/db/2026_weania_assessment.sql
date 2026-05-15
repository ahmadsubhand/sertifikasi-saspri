SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Assessments
-- --------------------------------------------------------

INSERT INTO assessments (id, title, active_at_level, level, created_at, updated_at, released_at) VALUES
(1, '20260212 Instrument Sertifikasi SASPRI-K 2026', 'weania', 'weania', '2026-05-07 10:00:00', '2026-05-07 10:00:00', '2026-05-07 10:00:00');

-- --------------------------------------------------------
-- Indicator Groups
-- --------------------------------------------------------

INSERT INTO indicator_groups (id, assessment_id, parent_group_id, code, label, `order`, weight) VALUES
(1, 1, NULL, 'A', 'Tata Kelola Manajemen', 1, 45),
(2, NULL, 1, 'A1', 'Sistem dan Budaya Organisasi (legalitas)', 1, 30),
(3, NULL, 1, 'A2', 'Amanat dalam Berorganisasi (aktivitas)', 2, 70),
(4, 1, NULL, 'B', 'Kinerja Bisnis Kolektif', 2, 35),
(5, NULL, 4, 'B1', 'Solidaritas Membangun Usaha Peternakan Kolektif', 1, 60),
(6, NULL, 4, 'B2', 'Pengembangan Usaha Bisnis Kolektif', 2, 40),
(7, 1, NULL, 'C', 'KEPEMILIKAN ASSET & PENERAPAN IPTEK', 3, 20),
(8, NULL, 7, 'C1', 'Realisasi Pemanfaatan IPTEK', 1, 65),
(9, NULL, 7, 'C2', 'Intensifikasi IPTEK pengurus SASPRI-K Untuk Peningkatan Daya Saing', 2, 35);

-- --------------------------------------------------------
-- Indicators
-- --------------------------------------------------------

INSERT INTO indicators (id, indicator_group_id, code, label, `order`) VALUES
-- A1
(1, 2, '1', 'Struktur organisasi SASPRI-K dengan personil pengurusnya', 1),
(2, 2, '2', 'Jumlah personil yang menandatangani Pakta Integritas Kewalian', 2),
(3, 2, '3', 'Jumlah personil yang menandatangani Pakta Kebersamaan Pengurus', 3),
(4, 2, '4', 'Jumlah personil yang membaca dan mengerti AD/ART SASPRI', 4),
(5, 2, '5', 'Jumlah personil yang mengerti dan memahami 8 nilai luhur SPR', 5),
(6, 2, '6', 'Status kepemilikan kantor sekretariat SASPRI-K', 6),
(7, 2, '7', 'Penempatan tanda kelulusan SPR dan atribut keSASPRI-an', 7),
-- A2
(8, 3, '1', 'Persentase fasilitas yang dimiliki SASPRI-K sesuai Perwalut 01/SASPRI/2026', 1),
(9, 3, '2', 'Persentase kehadiran pengurus dalam pertemuan', 2),
(10, 3, '3', 'Pendistribusian notulen pertemuan kepada personil pengurus', 3),
(11, 3, '4', 'Frekuensi penerbitan laporan keuangan organisasi dalam satu tahun', 4),
(12, 3, '5', 'Pembahasan laporan dan masalah keuangan diagendakan di setiap rapat pengurus', 5),
(13, 3, '6', 'Jumlah kelompok yang dibina SASPRI-K', 6),
(14, 3, '7', 'Jumlah pihak (lembaga pemerintah/swasta) yang diundang dan hadir di pertemuan dalam tahun 2025', 7),
-- B1
(15, 5, '1', 'Status unit bisnis kolektif berjamaah di bawah SASPRI-Kawasan', 1),
(16, 5, '2', 'Struktur organisasi unit bisnis kolektif di bawah SASPRI-K', 2),
(17, 5, '3', 'Keikutseraan personil pengurus dalam unit bisnis kolektif', 3),
(18, 5, '4', 'Pembagian peran dalam pengurus unit bisnis', 4),
(19, 5, '5', 'Frekuensi rapat khusus pengurus SASPRI-K dalam unit bisnis kolektif', 5),
(20, 5, '6', 'Aturan main dalam kelompok untuk menjalankan aktivitas bisnis', 6),
(21, 5, '7', 'Penerapan sistem bagi hasil antar anggota unit bisnis', 7),
-- B2
(22, 6, '1', 'Jumlah semua ternak yang dimiliki personil pengurus SASPRI-K [dalam Satuan Ternak]', 1),
(23, 6, '2', 'Jenis komoditas ternak milik personil pengurus yang diusahakan secara kolektif', 2),
(24, 6, '3', 'Nilai kumulatif aset ternak milik personil pengurus yang dapat diusahakan secara kolektif', 3),
(25, 6, '4', 'Jenis aset selain ternak yang dimiliki personil pengurus SASPRI-K', 4),
(26, 6, '5', 'Nilai aset selain ternak yang dimiliki personil pengurus SASPRI-K', 5),
(27, 6, '6', 'Pengalaman usaha bersama dalam kelompok sampai tahun 2025', 6),
(28, 6, '7', 'Nilai bantuan yang diberikan dalam usaha kelompok sampai 2025', 7),
-- C1
(29, 8, '1', 'Pengakuan pemerintah terhadap status KRITIS di bawah SASPRI', 1),
(30, 8, '2', 'Jumlah kandang kelompok yang dapat digunakan untuk kegiatan riset', 2),
(31, 8, '3', 'Personil pengelola KRITIS di bawah pengurus SASPRI-K', 3),
(32, 8, '4', 'Identitas KRITIS dan informasi tentang penggunaan KRITIS', 4),
(33, 8, '5', 'Jumlah SOP penggunaan teknologi yang telah dibuat', 5),
(34, 8, '6', 'Jumlah peralatan mesin yang digunakan untuk kegiatan produktif', 6),
(35, 8, '7', 'Jumlah teknologi yang digunakan dalam kegiatan budidaya', 7),
-- C2
(36, 9, '1', 'Jenis ID ternak indukan yang digunakan', 1),
(37, 9, '2', 'Pencatatan parameter produksi yang dicatat', 2),
(38, 9, '3', 'Cara penyimpanan semua data dalam kegiatan bisnis', 3),
(39, 9, '4', 'Jumlah orang yang melakukan riset di KRITIS sampai 2025', 4),
(40, 9, '5', 'Jumlah mahasiswa yang melakukan pengabdian dan PL di KRITIS sampai 2025', 5),
(41, 9, '6', 'Ketersediaan internet dan perangkat teknologi informasi untuk usaha [CCTV, WIFI, MEDIA SOSIAL, EMAIL]', 6),
(42, 9, '7', 'Personil pengurus yang mampu berkomunikasi secara virtual (Zoom, email, WA, dll.)', 7);

-- --------------------------------------------------------
-- Indicator Options
-- --------------------------------------------------------

INSERT INTO indicator_options (indicator_id, code, label, `order`, weight) VALUES
-- Options for Indicator 1 (A1-1)
(1, 'A', 'Tidak lengkap dan tidak terpampang', 1, 25),
(1, 'B', 'Lengkap dan tidak terpampang', 2, 50),
(1, 'C', 'Tidak lengkap dan terpampang', 3, 75),
(1, 'D', 'Lengkap dan terpampang', 4, 100),
-- Options for Indicator 2 (A1-2)
(2, 'A', 'Tidak tahu sehingga belum ditandatangani', 1, 25),
(2, 'B', 'Sudah ditandatangani saja dan tidak tahu isinya', 2, 50),
(2, 'C', 'Sudah ditandatangani wali tapi belum memahami dengn baik', 3, 75),
(2, 'D', 'Sudah ditandatangani wali and memahami isinya', 4, 100),
-- Options for Indicator 3 (A1-3)
(3, 'A', 'Tidak ada', 1, 25),
(3, 'B', '< 3 atau 5 orang', 2, 50),
(3, 'C', '4 atau 6 orang', 3, 75),
(3, 'D', '9 atau 18 orang', 4, 100),
-- Options for Indicator 4 (A1-4)
(4, 'A', 'Tidak ada', 1, 25),
(4, 'B', '< 3 atau 5 orang', 2, 50),
(4, 'C', '4 atau 6 orang', 3, 75),
(4, 'D', '9 atau 18 orang', 4, 100),
-- Options for Indicator 5 (A1-5)
(5, 'A', 'Tidak ada', 1, 25),
(5, 'B', '< 3 atau 5 orang', 2, 50),
(5, 'C', '4 atau 6 orang', 3, 75),
(5, 'D', '9 atau 18 orang', 4, 100),
-- Options for Indicator 6 (A1-6)
(6, 'A', 'Belum jelas/belum ada', 1, 25),
(6, 'B', 'Milik Ketua atau personil lainnya', 2, 50),
(6, 'C', 'Hak Sewa dan permanen', 3, 75),
(6, 'D', 'Hak Milik & Permanen', 4, 100),
-- Options for Indicator 7 (A1-7)
(7, 'A', 'Tidak lengkap dan tidak terpampang', 1, 25),
(7, 'B', 'Lengkap dan tidak terpampang', 2, 50),
(7, 'C', 'Tidak lengkap dan terpampang', 3, 75),
(7, 'D', 'Lengkap dan terpampang', 4, 100),
-- Options for Indicator 8 (A2-1)
(8, 'A', '0-25', 1, 25),
(8, 'B', '26-50', 2, 50),
(8, 'C', '51-75', 3, 75),
(8, 'D', '76-100', 4, 100),
-- Options for Indicator 9 (A2-2)
(9, 'A', '20%-40%', 1, 25),
(9, 'B', '41%-60%', 2, 50),
(9, 'C', '61%-80%', 3, 75),
(9, 'D', '>80%', 4, 100),
-- Options for Indicator 10 (A2-3)
(10, 'A', 'Tidak pernah', 1, 25),
(10, 'B', 'Jarang', 2, 50),
(10, 'C', 'Sering', 3, 75),
(10, 'D', 'Selalu', 4, 100),
-- Options for Indicator 11 (A2-4)
(11, 'A', 'Tahunan', 1, 25),
(11, 'B', 'Semester', 2, 50),
(11, 'C', 'Triwulan', 3, 75),
(11, 'D', 'Bulanan', 4, 100),
-- Options for Indicator 12 (A2-5)
(12, 'A', 'Tidak pernah', 1, 25),
(12, 'B', 'Jarang', 2, 50),
(12, 'C', 'Sering', 3, 75),
(12, 'D', 'Selalu', 4, 100),
-- Options for Indicator 13 (A2-6)
(13, 'A', '1 kelompok', 1, 25),
(13, 'B', '2 kelompok', 2, 50),
(13, 'C', '3 kelompok', 3, 75),
(13, 'D', 'Lebih dari 3 kelompok', 4, 100),
-- Options for Indicator 14 (A2-7)
(14, 'A', '1 pihak', 1, 25),
(14, 'B', '2 pihak', 2, 50),
(14, 'C', '3 pihak', 3, 75),
(14, 'D', 'Lebih dari 3 pihak', 4, 100),
-- Options for Indicator 15 (B1-1)
(15, 'A', 'Belum ada unit bisnis kolektif', 1, 25),
(15, 'B', 'Telah ada koperasi produsen dan melanjutkan saja', 2, 50),
(15, 'C', 'Telah ada koperasi produsen dan mengubah namanya koperasi produsen "Kec" Mulia Sejahter', 3, 75),
(15, 'D', 'Terdaftar sebagai koperasi produsen dan sudah ada akte pendiriannya', 4, 100),
-- Options for Indicator 16 (B1-2)
(16, 'A', 'Belum ada susunan pengurus', 1, 25),
(16, 'B', 'Hanya ada ketua', 2, 50),
(16, 'C', 'Hanya pengurus saja', 3, 75),
(16, 'D', 'Lengkap ada pengurus dan pengawas', 4, 100),
-- Options for Indicator 17 (B1-3)
(17, 'A', '40% atau kurang  personil pengurs SASPRI menjadi anggota', 1, 25),
(17, 'B', '60% personil pengurs SASPRI menjadi anggota', 2, 50),
(17, 'C', '80% personil pengurs SASPRI menjadi anggota', 3, 75),
(17, 'D', 'Seluruh personil pengurs SASPRI menjadi anggota', 4, 100),
-- Options for Indicator 18 (B1-4)
(18, 'A', 'Tidak jelas dan tumpang tindih', 1, 25),
(18, 'B', 'Ada pembagian peran tapi tidak optimal', 2, 50),
(18, 'C', 'Pembagian peran sesuai keahlian, berjalan baik', 3, 75),
(18, 'D', 'Ada uraian pekerjaan tertulis dan ada rotasi untuk pengembangan kapasitas', 4, 100),
-- Options for Indicator 19 (B1-5)
(19, 'A', 'Jarang atau tidak pernah karena tidak ada usaha bersama', 1, 25),
(19, 'B', 'Rapat 2-3 bulan sekali, lebih banyak formalitas', 2, 50),
(19, 'C', 'Rapat minimal 1 bulan sekali, membahas kemajuan usaha', 3, 75),
(19, 'D', 'Rapat rutin 2 minggu sekali, ada aksi nyata gotong royong pasca rapat', 4, 100),
-- Options for Indicator 20 (B1-6)
(20, 'A', 'Tidak ada aturan tertulis', 1, 25),
(20, 'B', 'Ada tertulis tetapi tidak diterapkan', 2, 50),
(20, 'C', 'Ada tertulis dan diterapkan tetapi masih ada pelanggaran atau tidak konistsen', 3, 75),
(20, 'D', 'Aturan dibuat bersama, sanksi jelas, dipatuhi semua anggota', 4, 100),
-- Options for Indicator 21 (B1-7)
(21, 'A', 'Tidak ada sistem bagi hasil yang jelas', 1, 25),
(21, 'B', 'Ada sistem bagi hasil tapi tidak transparan perhitungannya', 2, 50),
(21, 'C', 'Sistem bagi hasil adil dan proporsional, mudah dipahami tetapi tidak disiplin waktu penerapan', 3, 75),
(21, 'D', 'Sistem bagi hasil adil dan proporsional, mudah dipahami serta diterapkan secara konsisten', 4, 100),
-- Options for Indicator 22 (B2-1)
(22, 'A', 'Kurang dari 9 ST', 1, 25),
(22, 'B', '9 ST', 2, 50),
(22, 'C', '18 ST', 3, 75),
(22, 'D', '27 ST atau lebih', 4, 100),
-- Options for Indicator 23 (B2-2)
(23, 'A', '4 komoditas', 1, 25),
(23, 'B', '3 komoditas', 2, 50),
(23, 'C', '2 komoditas', 3, 75),
(23, 'D', '1 komoditas', 4, 100),
-- Options for Indicator 24 (B2-3)
(24, 'A', 'Kurang dari Rp. 100 juta', 1, 25),
(24, 'B', 'Sampai 500 juta', 2, 50),
(24, 'C', 'sampai 750 juta', 3, 75),
(24, 'D', 'Lebih dari 1 Rp. M', 4, 100),
-- Options for Indicator 25 (B2-4)
(25, 'A', '1 komoditas', 1, 25),
(25, 'B', '2 komoditas', 2, 50),
(25, 'C', '3 komoditas', 3, 75),
(25, 'D', '4  komoditas', 4, 100),
-- Options for Indicator 26 (B2-5)
(26, 'A', 'Kurang dari Rp. 100 juta', 1, 25),
(26, 'B', 'Sampai 500 juta', 2, 50),
(26, 'C', 'sampai 750 juta', 3, 75),
(26, 'D', 'Lebih dari 1 Rp. M', 4, 100),
-- Options for Indicator 27 (B2-6)
(27, 'A', 'Tidak pernah', 1, 25),
(27, 'B', 'Sekali saja', 2, 50),
(27, 'C', 'Beberapa kali', 3, 75),
(27, 'D', 'Sering', 4, 100),
-- Options for Indicator 28 (B2-7)
(28, 'A', 'Kurang dari Rp. 100 juta', 1, 25),
(28, 'B', 'Sampai 500 juta', 2, 50),
(28, 'C', 'sampai 750 juta', 3, 75),
(28, 'D', 'Lebih dari 1 Rp. M', 4, 100),
-- Options for Indicator 29 (C1-1)
(29, 'A', 'Belum dapat legalitas dari Pemkab', 1, 25),
(29, 'B', 'Sedang dalam proses pengajuan', 2, 50),
(29, 'C', 'Meenunggu terbitnya legalitas Pemkab', 3, 75),
(29, 'D', 'Dapat legalitas resmi dari Pemkab', 4, 100),
-- Options for Indicator 30 (C1-2)
(30, 'A', '1 atau belum ada', 1, 25),
(30, 'B', '2 kandang', 2, 50),
(30, 'C', '3 kandang', 3, 75),
(30, 'D', 'Lebih dari 3 kandang', 4, 100),
-- Options for Indicator 31 (C1-3)
(31, 'A', 'Hanya ada manajer KRITIS dalam struktur organisasi SASPRI', 1, 25),
(31, 'B', 'Masih ada pengurus inti [Ketua, sekretaris, dan bendahara]', 2, 50),
(31, 'C', 'Lengkap [ketua, sekretaris, bendahara, bidang internal, dan bidang eksternal] tapi belum ada aturan main', 3, 75),
(31, 'D', 'Susunan lengkap dan telah terbit aturan main penggunaan KRITIS', 4, 100),
-- Options for Indicator 32 (C1-4)
(32, 'A', 'Tidak/belum ada KRITIS', 1, 25),
(32, 'B', 'Tidak lengkap dan tidak ada informasi tentang penggunaan kandang kelompok untuk riset', 2, 50),
(32, 'C', 'Lengkap tetapi tidak ada informasi penggunaan riset di kandang kelompok', 3, 75),
(32, 'D', 'Lengkap (papan nama di setiap kandang kelompok yang layak untuk digunakan riset) and ada informasi jelas', 4, 100),
-- Options for Indicator 33 (C1-5)
(33, 'A', '1', 1, 25),
(33, 'B', '2', 2, 50),
(33, 'C', '3', 3, 75),
(33, 'D', '4', 4, 100),
-- Options for Indicator 34 (C1-6)
(34, 'A', '1', 1, 25),
(34, 'B', '2', 2, 50),
(34, 'C', '3', 3, 75),
(34, 'D', '4', 4, 100),
-- Options for Indicator 35 (C1-7)
(35, 'A', '1', 1, 25),
(35, 'B', '2', 2, 50),
(35, 'C', '3', 3, 75),
(35, 'D', '4', 4, 100),
-- Options for Indicator 36 (C2-1)
(36, 'A', 'Tulis dengan cat', 1, 25),
(36, 'B', 'Tempel', 2, 50),
(36, 'C', 'Tattoo', 3, 75),
(36, 'D', 'Chip', 4, 100),
-- Options for Indicator 37 (C2-2)
(37, 'A', '1', 1, 25),
(37, 'B', '2', 2, 50),
(37, 'C', '3', 3, 75),
(37, 'D', '4 atau lebih', 4, 100),
-- Options for Indicator 38 (C2-3)
(38, 'A', 'Manual', 1, 25),
(38, 'B', 'Campuran', 2, 50),
(38, 'C', 'Computerised', 3, 75),
(38, 'D', 'Cloud', 4, 100),
-- Options for Indicator 39 (C2-4)
(39, 'A', '2 orang atau kurang', 1, 25),
(39, 'B', '3 orang', 2, 50),
(39, 'C', '4 orang', 3, 75),
(39, 'D', '5 orang atau lebih', 4, 100),
-- Options for Indicator 40 (C2-5)
(40, 'A', '4 orang atau kurang', 1, 25),
(40, 'B', '5-7 orang', 2, 50),
(40, 'C', '8 - 9 orang', 3, 75),
(40, 'D', '10 orang atau lebih', 4, 100),
-- Options for Indicator 41 (C2-6)
(41, 'A', '1 item atau tidak ada', 1, 25),
(41, 'B', '2 item', 2, 50),
(41, 'C', '3 item', 3, 75),
(41, 'D', 'lengkap [4 item] atau lebih', 4, 100),
-- Options for Indicator 42 (C2-7)
(42, 'A', '0-40%', 1, 25),
(42, 'B', '41-60%', 2, 50),
(42, 'C', '61-80%', 3, 75),
(42, 'D', '>80%', 4, 100);

SET FOREIGN_KEY_CHECKS = 1;
