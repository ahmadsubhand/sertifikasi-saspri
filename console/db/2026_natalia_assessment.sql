SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Assessments
-- --------------------------------------------------------

INSERT INTO assessment (id, title, active_at_level, level, created_at, updated_at, released_at) VALUES
(2, '2026 Instrument Sertifikasi SASPRI-K Natalia', 'natalia', 'natalia', 1778148000, 1778148000, '2026-05-07 10:00:00');

-- --------------------------------------------------------
-- Indicator Groups
-- --------------------------------------------------------

INSERT INTO indicator_group (id, assessment_id, parent_group_id, code, label, `order`, weight) VALUES
(100, 2, NULL, 'A', 'Tata Kelola Manajemen', 1, 45),
(101, 2, 100, 'A1', 'Sistem dan Budaya Organisasi', 1, 100),
(102, 2, NULL, 'B', 'Kinerja Bisnis Kolektif', 2, 35),
(103, 2, 102, 'B1', 'Solidaritas Membangun Usaha Peternakan Kolektif', 1, 100),
(104, 2, NULL, 'C', 'KEPEMILIKAN ASSET & PENERAPAN IPTEK', 3, 20),
(105, 2, 104, 'C1', 'Realisasi Pemanfaatan IPTEK', 1, 100);

-- --------------------------------------------------------
-- Indicators
-- --------------------------------------------------------

INSERT INTO indicator (id, indicator_group_id, code, label, `order`) VALUES
(100, 101, '1', 'Indikator Natalia A1', 1),
(101, 103, '1', 'Indikator Natalia B1', 1),
(102, 105, '1', 'Indikator Natalia C1', 1);

-- --------------------------------------------------------
-- Indicator Options
-- --------------------------------------------------------

INSERT INTO indicator_option (indicator_id, code, label, `order`, weight) VALUES
-- Options for Indicator 100 (A1)
(100, 'A', 'Kurang Baik', 1, 25),
(100, 'B', 'Cukup Baik', 2, 50),
(100, 'C', 'Baik', 3, 75),
(100, 'D', 'Sangat Baik', 4, 100),
-- Options for Indicator 101 (B1)
(101, 'A', 'Kurang Baik', 1, 25),
(101, 'B', 'Cukup Baik', 2, 50),
(101, 'C', 'Baik', 3, 75),
(101, 'D', 'Sangat Baik', 4, 100),
-- Options for Indicator 102 (C1)
(102, 'A', 'Kurang Baik', 1, 25),
(102, 'B', 'Cukup Baik', 2, 50),
(102, 'C', 'Baik', 3, 75),
(102, 'D', 'Sangat Baik', 4, 100);

SET FOREIGN_KEY_CHECKS = 1;
