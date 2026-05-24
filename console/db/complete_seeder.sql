SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Users (Total ~158 Users)
-- Admins: 1-3
-- Independent: 4-13
-- Reps (1-6): 14-133 (20 members each)
-- Additionals (7-11): 134-158 (5 members each)
-- --------------------------------------------------------

INSERT INTO `user` (id, username, auth_key, password_hash, email, status, created_at, updated_at, phone_number, saspri_k_id) VALUES
-- Admins
(1, 'admin.nasional', 'ak1', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'admin1@saspri.id', 10, 1714876800, 1714876800, '08111111111', NULL),
(2, 'admin.kawasan', 'ak2', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'admin2@saspri.id', 10, 1714876800, 1714876800, '08111111112', NULL),
(3, 'admin.pusat', 'ak3', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'admin3@saspri.id', 10, 1714876800, 1714876800, '08111111113', NULL),

-- Independent Users (10: 4-13)
(4, 'bambang.sudjatmiko', 'ak4', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'bambang@gmail.com', 10, 1714876800, 1714876800, '08120000001', NULL),
(5, 'siti.nurhaliza', 'ak5', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'siti.n@gmail.com', 10, 1714876800, 1714876800, '08120000002', NULL),
(6, 'joko.widodo', 'ak6', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'joko.w@gmail.com', 10, 1714876800, 1714876800, '08120000003', NULL),
(7, 'megawati.soekarno', 'ak7', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'mega@gmail.com', 10, 1714876800, 1714876800, '08120000004', NULL),
(8, 'susilo.yudhoyono', 'ak8', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'sby@gmail.com', 10, 1714876800, 1714876800, '08120000005', NULL),
(9, 'prabowo.subianto', 'ak9', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'prabowo@gmail.com', 10, 1714876800, 1714876800, '08120000006', NULL),
(10, 'anies.baswedan', 'ak10', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'anies@gmail.com', 10, 1714876800, 1714876800, '08120000007', NULL),
(11, 'ganjar.pranowo', 'ak11', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'ganjar@gmail.com', 10, 1714876800, 1714876800, '08120000008', NULL),
(12, 'ridwan.kamil', 'ak12', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'rk@gmail.com', 10, 1714876800, 1714876800, '08120000009', NULL),
(13, 'khofifah.parawansa', 'ak13', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'khofifah@gmail.com', 10, 1714876800, 1714876800, '08120000010', NULL);

-- Coords and Members for SASPRI-K units
-- Reps (1-6) - 20 members each
-- 14-33: Rep 1
-- 34-53: Rep 2
-- 54-73: Rep 3
-- 74-93: Rep 4
-- 94-113: Rep 5
-- 114-133: Rep 6
-- Additionals (7-11) - 5 members each
-- 134-138: Add 7
-- 139-143: Add 8
-- 144-148: Add 9
-- 149-153: Add 10
-- 154-158: Add 11

-- Realistic names for Coordinators
INSERT INTO `user` (id, username, auth_key, password_hash, email, status, created_at, updated_at, phone_number, saspri_k_id) VALUES
(14, 'budiman.sujatmiko', 'ak14', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'budiman@gmail.com', 10, 1714876800, 1714876800, '08130000001', 1),
(34, 'agus.harimurti', 'ak34', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'agus@gmail.com', 10, 1714876800, 1714876800, '08130000002', 2), -- SASPRI-K 2: Representative for self_review
(54, 'erick.thohir', 'ak54', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'erick@gmail.com', 10, 1714876800, 1714876800, '08130000003', 3), -- SASPRI-K 3: Representative for pending_peer_team_formation
(74, 'sandiaga.uno', 'ak74', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'sandi@gmail.com', 10, 1714876800, 1714876800, '08130000004', 4), -- SASPRI-K 4: Representative for peer_review
(94, 'nadiem.makarim', 'ak94', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'nadiem@gmail.com', 10, 1714876800, 1714876800, '08130000005', 5), -- SASPRI-K 5: Representative for external_review
(114, 'luhut.pandjaitan', 'ak114', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'luhut@gmail.com', 10, 1714876800, 1714876800, '08130000006', 6), -- SASPRI-K 6: Representative for completed
(134, 'mahfud.md', 'ak134', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'mahfud@gmail.com', 10, 1714876800, 1714876800, '08130000007', 7),
(139, 'muhaimin.iskandar', 'ak139', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'cakimin@gmail.com', 10, 1714876800, 1714876800, '08130000008', 8),
(144, 'ahmad.syaikhu', 'ak144', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'ahmad@gmail.com', 10, 1714876800, 1714876800, '08130000009', 9),
(149, 'zulkifli.hasan', 'ak149', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'zulkifli@gmail.com', 10, 1714876800, 1714876800, '08130000010', 10),
(154, 'suharso.monoarfa', 'ak154', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'suharso@gmail.com', 10, 1714876800, 1714876800, '08130000011', 11);

-- Rest of members bulk insert
INSERT INTO `user` (id, username, auth_key, password_hash, email, status, created_at, updated_at, phone_number, saspri_k_id)
SELECT i, CONCAT('user.', i), 'auth_key', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', CONCAT('u', i, '@saspri.id'), 10, 1714876800, 1714876800, CONCAT('0813', LPAD(i, 7, '0')),
CASE 
  WHEN i BETWEEN 14 AND 33 THEN 1
  WHEN i BETWEEN 34 AND 53 THEN 2
  WHEN i BETWEEN 54 AND 73 THEN 3
  WHEN i BETWEEN 74 AND 93 THEN 4
  WHEN i BETWEEN 94 AND 113 THEN 5
  WHEN i BETWEEN 114 AND 133 THEN 6
  WHEN i BETWEEN 134 AND 138 THEN 7
  WHEN i BETWEEN 139 AND 143 THEN 8
  WHEN i BETWEEN 144 AND 148 THEN 9
  WHEN i BETWEEN 149 AND 153 THEN 10
  WHEN i BETWEEN 154 AND 158 THEN 11
END
FROM (
  SELECT a.N + b.N * 10 + c.N * 100 AS i
  FROM (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a
  CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b
  CROSS JOIN (SELECT 0 AS N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) c
) AS numbers
WHERE i BETWEEN 14 AND 158 AND i NOT IN (14,34,54,74,94,114,134,139,144,149,154);

-- --------------------------------------------------------
-- Roles Assignment
-- --------------------------------------------------------

INSERT INTO `auth_assignment` (item_name, user_id, created_at) VALUES
('admin', 1, 1714876800), ('admin', 2, 1714876800), ('admin', 3, 1714876800),
('coordinator', 14, 1714876800), ('coordinator', 34, 1714876800), ('coordinator', 54, 1714876800),
('coordinator', 74, 1714876800), ('coordinator', 94, 1714876800), ('coordinator', 114, 1714876800),
('coordinator', 134, 1714876800), ('coordinator', 139, 1714876800), ('coordinator', 144, 1714876800),
('coordinator', 149, 1714876800), ('coordinator', 154, 1714876800);

-- Dynamic role assignment for everyone else
INSERT INTO `auth_assignment` (item_name, user_id, created_at)
SELECT 'user', id, 1714876800 FROM `user` 
WHERE id NOT IN (SELECT CAST(user_id AS UNSIGNED) FROM auth_assignment);

-- --------------------------------------------------------
-- SASPRI-K
-- --------------------------------------------------------

INSERT INTO saspri_k (id, coordinator_id, district_id, region_name, address, cooperative_name, number_of_groups, number_of_active_members, livestock_type, total_livestock_count, breeding_livestock_count, productive_heifer_count, request_status, created_at, updated_at) VALUES
(1, 14, 265, 'Baiturrahman', 'Jl. Merdeka No. 1', 'Koperasi Baiturrahman', 5, 50, 'Sapi', 200, 100, 50, 'approved', 1714876800, 1714876800),
(2, 34, 1, 'Bakongan', 'Jl. Merdeka No. 2', 'Koperasi Bakongan', 3, 30, 'Sapi', 120, 60, 30, 'approved', 1714876800, 1714876800),
(3, 54, 685, 'Medan Kota', 'Jl. Merdeka No. 3', 'Koperasi Medan Kota', 4, 45, 'Sapi', 180, 90, 45, 'approved', 1714876800, 1714876800),
(4, 74, 1445, 'Ilir Barat Dua', 'Jl. Merdeka No. 4', 'Koperasi Ilir Barat', 2, 25, 'Sapi', 100, 50, 25, 'approved', 1714876800, 1714876800),
(5, 94, 2563, 'Sukasari', 'Jl. Merdeka No. 5', 'Koperasi Sukasari', 3, 35, 'Sapi', 140, 70, 35, 'approved', 1714876800, 1714876800),
(6, 114, 3190, 'Semarang Tengah', 'Jl. Merdeka No. 6', 'Koperasi Semarang Tengah', 4, 40, 'Sapi', 160, 80, 40, 'approved', 1714876800, 1714876800),
(7, 134, 3924, 'Karang Pilang', 'Jl. Merdeka No. 7', 'Koperasi Karang Pilang', 2, 20, 'Sapi', 80, 40, 20, 'approved', 1714876800, 1714876800),
(8, 139, 686, 'Medan Sunggal', 'Jl. Merdeka No. 8', 'Koperasi Medan Sunggal', 2, 18, 'Sapi', 72, 36, 18, 'approved', 1714876800, 1714876800),
(9, 144, 2564, 'Coblong', 'Jl. Merdeka No. 9', 'Koperasi Coblong', 2, 22, 'Sapi', 88, 44, 22, 'approved', 1714876800, 1714876800),
(10, 149, 3191, 'Semarang Utara', 'Jl. Merdeka No. 10', 'Koperasi Semarang Utara', 2, 21, 'Sapi', 84, 42, 21, 'approved', 1714876800, 1714876800),
(11, 154, 3925, 'Wonocolo', 'Jl. Merdeka No. 11', 'Koperasi Wonocolo', 3, 28, 'Sapi', 112, 56, 28, 'approved', 1714876800, 1714876800);

-- --------------------------------------------------------
-- Certifications (Historical)
-- 5 Natalia A, 1 Natalia AB, 1 Natalia B, 2 Weania BC/C
-- --------------------------------------------------------

INSERT INTO certification (id, saspri_k_id, assessment_id, purpose, status, level, total_score, grade, created_at, updated_at, issued_at, next_certification_due_date) VALUES
(1, 1, 1, 'level_up', 'completed', 'natalia', 95, 'a', 1673308800, 1673308800, '2023-01-10', '2025-01-10'),
(2, 2, 1, 'level_up', 'completed', 'natalia', 82, 'b', 1673308800, 1673308800, '2023-01-10', '2025-01-10'),
(3, 3, 1, 'level_up', 'completed', 'natalia', 86, 'ab', 1673308800, 1673308800, '2023-01-10', '2025-01-10'),
(4, 4, 1, 'level_up', 'completed', 'weania', 55, 'bc', 1673308800, 1673308800, '2023-01-10', '2024-01-10'),
(5, 5, 1, 'level_up', 'completed', 'weania', 45, 'c', 1673308800, 1673308800, '2023-01-10', '2024-01-10'),
(6, 6, 1, 'level_up', 'completed', 'natalia', 93, 'a', 1673308800, 1673308800, '2023-01-10', '2025-01-10'),
(18, 4, 1, 'level_up', 'completed', 'natalia', 92, 'a', 1610236800, 1610236800, '2021-01-10', '2023-01-10'),
(19, 5, 1, 'level_up', 'completed', 'natalia', 94, 'a', 1610236800, 1610236800, '2021-01-10', '2023-01-10');

-- --------------------------------------------------------
-- Ongoing Certifications
-- --------------------------------------------------------

-- SaspriK 1: Natalia A -> Level Up -> Weania (pending_self_team_formation)
INSERT INTO certification (id, saspri_k_id, assessment_id, purpose, status, level, created_at, updated_at, self_team_due_date) VALUES
(7, 1, 1, 'level_up', 'pending_self_team_formation', 'weania', 1777639200, 1777639200, '2026-05-17 10:00:00');

-- SaspriK 2: Natalia B -> Level Up -> Weania (self_review)
INSERT INTO certification (id, saspri_k_id, assessment_id, purpose, status, level, created_at, updated_at, self_team_due_date, self_review_due_date) VALUES
(8, 2, 1, 'level_up', 'self_review', 'weania', 1777034400, 1777034400, '2026-05-10 10:00:00', '2026-05-24 10:00:00');

-- SaspriK 3: Natalia AB -> Level Up -> Weania (pending_peer_team_formation)
INSERT INTO certification (id, saspri_k_id, assessment_id, purpose, status, level, created_at, updated_at, self_team_due_date, self_review_due_date, peer_team_due_date) VALUES
(9, 3, 1, 'level_up', 'pending_peer_team_formation', 'weania', 1775824800, 1775824800, '2026-04-26 10:00:00', '2026-05-10 10:00:00', '2026-05-17 10:00:00');

-- SaspriK 4: Weania BC -> Renewal -> Weania (peer_review)
INSERT INTO certification (id, saspri_k_id, assessment_id, purpose, status, level, created_at, updated_at, self_team_due_date, self_review_due_date, peer_team_due_date, peer_review_due_date) VALUES
(10, 4, 1, 'renewal', 'peer_review', 'weania', 1775220000, 1775220000, '2026-04-19 10:00:00', '2026-05-03 10:00:00', '2026-05-10 10:00:00', '2026-05-24 10:00:00');

-- SaspriK 5: Weania C -> Renewal -> Weania (external_review)
INSERT INTO certification (id, saspri_k_id, assessment_id, purpose, status, level, created_at, updated_at, self_team_due_date, self_review_due_date, peer_team_due_date, peer_review_due_date, external_review_due_date) VALUES
(11, 5, 1, 'renewal', 'external_review', 'weania', 1774010400, 1774010400, '2026-04-05 10:00:00', '2026-04-19 10:00:00', '2026-04-26 10:00:00', '2026-05-10 10:00:00', '2026-05-24 10:00:00');

-- SaspriK 6: Natalia A -> Level Up -> Weania (completed)
INSERT INTO certification ( id, saspri_k_id, assessment_id, purpose, status, level, total_score, grade, created_at, updated_at, issued_at, next_certification_due_date) VALUES
(12, 6, 1, 'level_up', 'completed', 'weania', 88, 'ab', 1777639200, 1778416800, '2026-05-10 10:00:00', '2028-05-10 10:00:00');

-- Initial Valid Certs for Additionals (SASPRI-K 7-11)
INSERT INTO certification ( id, saspri_k_id, assessment_id, purpose, status, level, total_score, grade, created_at, updated_at, issued_at, next_certification_due_date) VALUES
(13, 7, 1, 'level_up', 'completed', 'natalia', 90, 'a', 1704067200, 1704844800, '2024-01-10', '2026-01-10'),
(14, 8, 1, 'level_up', 'completed', 'natalia', 90, 'a', 1704067200, 1704844800, '2024-01-10', '2026-01-10'),
(15, 9, 1, 'level_up', 'completed', 'natalia', 90, 'a', 1704067200, 1704844800, '2024-01-10', '2026-01-10'),
(16, 10, 1, 'level_up', 'completed', 'natalia', 90, 'a', 1704067200, 1704844800, '2024-01-10', '2026-01-10'),
(17, 11, 1, 'level_up', 'completed', 'natalia', 90, 'a', 1704067200, 1704844800, '2024-01-10', '2026-01-10');

-- --------------------------------------------------------
-- Team Members
-- --------------------------------------------------------

-- Self Teams
-- Rep 1 (pending formation): 2 approved, 1 pending
INSERT INTO self_team_member (certification_id, user_id, status, role) VALUES
(7, 15, 'approved', 'leader'), (7, 16, 'approved', 'member'), (7, 17, 'pending', 'member');

-- Rep 2 (self review): 3 approved
INSERT INTO self_team_member (certification_id, user_id, status, role) VALUES
(8, 35, 'approved', 'leader'), (8, 36, 'approved', 'member'), (8, 37, 'approved', 'member');

-- Rep 3 (pending peer): Self approved, Peer mixed
INSERT INTO self_team_member (certification_id, user_id, status, role) VALUES
(9, 55, 'approved', 'leader'), (9, 56, 'approved', 'member'), (9, 57, 'approved', 'member');

INSERT INTO peer_team_member (certification_id, user_id, status, role) VALUES
(9, 1, 'approved', 'facilitator'), (9, 34, 'pending', 'leader'), (9, 14, 'rejected', 'member');

-- Rep 4 (peer review): All approved
INSERT INTO self_team_member (certification_id, user_id, status, role) VALUES
(10, 75, 'approved', 'leader'), (10, 76, 'approved', 'member'), (10, 77, 'approved', 'member');

INSERT INTO peer_team_member (certification_id, user_id, status, role) VALUES
(10, 2, 'approved', 'facilitator'), (10, 14, 'approved', 'leader'), (10, 34, 'approved', 'member');

-- Rep 5 (external review): All approved
INSERT INTO self_team_member (certification_id, user_id, status, role) VALUES
(11, 95, 'approved', 'leader'), (11, 96, 'approved', 'member'), (11, 97, 'approved', 'member');

INSERT INTO peer_team_member (certification_id, user_id, status, role) VALUES
(11, 3, 'approved', 'facilitator'), (11, 14, 'approved', 'leader'), (11, 54, 'approved', 'member');

-- --------------------------------------------------------
-- Indicator Scores
-- --------------------------------------------------------

-- Rep 2 (self_review): partial self
INSERT INTO indicator_score (certification_id, indicator_id, self_team_score) VALUES
(8, 1, ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100)), 
(8, 2, ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100)), 
(8, 3, ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100));

-- Rep 3 (pending_peer): full self
INSERT INTO indicator_score (certification_id, indicator_id, self_team_score)
SELECT 9, id, ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100) FROM indicator LIMIT 42;

-- Rep 4 (peer_review): full self, partial peer
INSERT INTO indicator_score (certification_id, indicator_id, self_team_score, peer_team_score, status)
SELECT 10, id, s, p, 
    CASE 
        WHEN p IS NULL THEN NULL 
        WHEN s = p THEN 'identical' 
        ELSE ELT(FLOOR(1 + RAND() * 2), 'different', 'agreed') 
    END
FROM (
    SELECT id, 
           ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100) as s,
           CASE WHEN id <= 5 THEN ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100) ELSE NULL END as p
    FROM indicator LIMIT 42
) as tmp;

-- Rep 5 (external_review): full self, full peer
INSERT INTO indicator_score (certification_id, indicator_id, self_team_score, peer_team_score, status)
SELECT 11, id, s, p, 
    CASE 
        WHEN s = p THEN 'identical' 
        ELSE ELT(FLOOR(1 + RAND() * 2), 'different', 'agreed') 
    END
FROM (
    SELECT id, 
           ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100) as s,
           ELT(FLOOR(1 + RAND() * 4), 25, 50, 75, 100) as p
    FROM indicator LIMIT 42
) as tmp;

-- --------------------------------------------------------
-- Pendaftaran Wali (Registration Requests)
-- 3 Pending, 2 Rejected
-- --------------------------------------------------------

INSERT INTO saspri_k (id, coordinator_id, district_id, region_name, address, cooperative_name, number_of_groups, number_of_active_members, livestock_type, total_livestock_count, breeding_livestock_count, productive_heifer_count, request_status, request_rejection_reason, created_at, updated_at) VALUES
(12, 4, 1446, 'Seberang Ulu Satu', 'Jl. Merdeka No. 12', 'Koperasi Seberang Ulu', 2, 15, 'Sapi', 60, 30, 15, 'pending', NULL, 1714876800, 1714876800),
(13, 5, 2565, 'Babakan Ciparay', 'Jl. Merdeka No. 13', 'Koperasi Babakan Ciparay', 2, 16, 'Sapi', 64, 32, 16, 'pending', NULL, 1714876800, 1714876800),
(14, 6, 3192, 'Semarang Timur', 'Jl. Merdeka No. 14', 'Koperasi Semarang Timur', 2, 17, 'Sapi', 68, 34, 17, 'pending', NULL, 1714876800, 1714876800),
(15, 7, 3926, 'Rungkut', 'Jl. Merdeka No. 15', 'Koperasi Rungkut', 2, 18, 'Sapi', 72, 36, 18, 'rejected', 'Dokumen tidak lengkap', 1714876800, 1714876800),
(16, 8, 687, 'Medan Helvetia', 'Jl. Merdeka No. 16', 'Koperasi Medan Helvetia', 2, 19, 'Sapi', 76, 38, 19, 'rejected', 'Wilayah tidak sesuai', 1714876800, 1714876800);

INSERT INTO saspri_k_document (saspri_k_id, type, url) VALUES
(12, 'Sertifikat Natalia', '/uploads/document/12/natalia.pdf'),
(13, 'Sertifikat Natalia', '/uploads/document/13/natalia.pdf'),
(14, 'Sertifikat Natalia', '/uploads/document/14/natalia.pdf'),
(15, 'Sertifikat Natalia', '/uploads/document/15/natalia.pdf'),
(16, 'Sertifikat Natalia', '/uploads/document/16/natalia.pdf');

-- Certifications for registration requests (so they can be scored)
INSERT INTO certification (id, saspri_k_id, assessment_id, purpose, status, level, created_at, updated_at) VALUES
(20, 12, 2, 'level_up', 'completed', 'natalia', 1714876800, 1714876800),
(21, 13, 2, 'level_up', 'completed', 'natalia', 1714876800, 1714876800),
(22, 14, 2, 'level_up', 'completed', 'natalia', 1714876800, 1714876800),
(23, 15, 2, 'level_up', 'completed', 'natalia', 1714876800, 1714876800),
(24, 16, 2, 'level_up', 'completed', 'natalia', 1714876800, 1714876800);

-- --------------------------------------------------------
-- Pergantian Wali (Change of Wali Requests)
-- 3 Pending, 2 Rejected
-- --------------------------------------------------------

UPDATE saspri_k SET 
    change_status = 'pending', 
    new_coordinator_id = 9, 
    change_request_reason = 'Wali lama pindah tugas' 
WHERE id = 7;

UPDATE saspri_k SET 
    change_status = 'pending', 
    new_coordinator_id = 10, 
    change_request_reason = 'Wali lama mengundurkan diri' 
WHERE id = 8;

UPDATE saspri_k SET 
    change_status = 'pending', 
    new_coordinator_id = 11, 
    change_request_reason = 'Wali lama sakit' 
WHERE id = 9;

UPDATE saspri_k SET 
    change_status = 'rejected', 
    new_coordinator_id = 12, 
    change_request_reason = 'Wali lama sibuk',
    change_rejection_reason = 'User belum memenuhi kualifikasi' 
WHERE id = 10;

UPDATE saspri_k SET 
    change_status = 'rejected', 
    new_coordinator_id = 13, 
    change_request_reason = 'Alasan kesehatan',
    change_rejection_reason = 'Alasan pergantian tidak valid' 
WHERE id = 11;

SET FOREIGN_KEY_CHECKS = 1;
