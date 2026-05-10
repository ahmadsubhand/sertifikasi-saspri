SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Users
-- --------------------------------------------------------

INSERT INTO `user` (id, username, auth_key, password_hash, email, status, created_at, updated_at, phone_number, saspri_k_id) VALUES
(1, 'admin', 'test_auth_key_1', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'admin@example.com', 10, 1714876800, 1714876800, '08123456789', NULL),
(2, 'wali1', 'test_auth_key_2', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'wali1@example.com', 10, 1714876800, 1714876800, '08123456780', 1),
(3, 'user1', 'test_auth_key_3', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user1@example.com', 10, 1714876800, 1714876800, '08123456781', 1),
(4, 'user2', 'test_auth_key_4', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user2@example.com', 10, 1714876800, 1714876800, '08123456782', 1),
(5, 'user3', 'test_auth_key_5', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user3@example.com', 10, 1714876800, 1714876800, '08123456783', 1),
(6, 'wali2', 'test_auth_key_6', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'wali2@example.com', 10, 1714876800, 1714876800, '08123456784', 2),
(7, 'user4', 'test_auth_key_7', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user4@example.com', 10, 1714876800, 1714876800, '08123456783', 2),
(8, 'wali3', 'test_auth_key_8', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'wali3@example.com', 10, 1714876800, 1714876800, '08123456785', 3),
(9, 'user5', 'test_auth_key_9', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user5@example.com', 10, 1714876800, 1714876800, '08123456783', 3),
(10, 'user6', 'test_auth_key_10', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user6@example.com', 10, 1714876800, 1714876800, '08123456783', 3),
(11, 'user7', 'test_auth_key_11', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user7@example.com', 10, 1714876800, 1714876800, '08123456783', 3),
(12, 'wali4', 'test_auth_key_12', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'wali4@example.com', 10, 1714876800, 1714876800, '08123456790', 4),
(13, 'user12', 'test_auth_key_13', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user12@example.com', 10, 1714876800, 1714876800, '08123456791', 4),
(14, 'user13', 'test_auth_key_14', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user13@example.com', 10, 1714876800, 1714876800, '08123456792', 4),
(15, 'user14', 'test_auth_key_15', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user14@example.com', 10, 1714876800, 1714876800, '08123456793', 4),
(16, 'wali5', 'test_auth_key_16', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'wali5@example.com', 10, 1714876800, 1714876800, '08123456794', 5),
(17, 'user15', 'test_auth_key_17', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user15@example.com', 10, 1714876800, 1714876800, '08123456795', 5),
(18, 'user16', 'test_auth_key_18', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user16@example.com', 10, 1714876800, 1714876800, '08123456796', 5),
(19, 'user17', 'test_auth_key_19', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user17@example.com', 10, 1714876800, 1714876800, '08123456797', 5);

-- --------------------------------------------------------
-- Roles
-- --------------------------------------------------------

INSERT INTO `auth_assignment` (item_name, user_id, created_at) values
('admin', 1, 1778028133),
('coordinator', 2, 1778028133),
('user', 3, 1778028133),
('user', 4, 1778028133),
('user', 5, 1778028133),
('coordinator', 6, 1778028133),
('user', 7, 1778028133),
('coordinator', 8, 1778028133),
('user', 9, 1778028133),
('user', 10, 1778028133),
('user', 11, 1778028133),
('coordinator', 12, 1778028133),
('user', 13, 1778028133),
('user', 14, 1778028133),
('user', 15, 1778028133),
('coordinator', 16, 1778028133),
('user', 17, 1778028133),
('user', 18, 1778028133),
('user', 19, 1778028133);

-- --------------------------------------------------------
-- SASPRI-K
-- --------------------------------------------------------

INSERT INTO saspri_k (id, coordinator_id, district_id, region_name, address, cooperative_name, number_of_groups, number_of_active_members, livestock_type, total_livestock_count, breeding_livestock_count, productive_heifer_count, request_status, valid_certificate_id) VALUES
(1, 2, 265, 'Baiturrahman', 'Jl. Syiah Kuala No. 10', 'Koperasi Sejahtera', 5, 50, 'Sapi Potong', 200, 100, 50, 'approved', 1),
(2, 6, 1, 'Bakongan', 'Jl. Bakongan No. 1', 'Koperasi Bakongan Jaya', 3, 30, 'Sapi Potong', 120, 60, 30, 'approved', 4),
(3, 8, 2, 'Kluet Utara', 'Jl. Kluet Utara No. 5', 'Koperasi Kluet Mandiri', 4, 45, 'Sapi Potong', 180, 90, 45, 'approved', 5),
(4, 12, 3, 'Bakongan Timur', 'Jl. Bakongan Timur No. 10', 'Koperasi Bakongan Timur', 2, 25, 'Sapi Potong', 100, 50, 25, 'approved', 6),
(5, 16, 4, 'Bakongan Barat', 'Jl. Bakongan Barat No. 5', 'Koperasi Bakongan Barat', 3, 35, 'Sapi Potong', 140, 70, 35, 'approved', 8);

INSERT INTO saspri_k_documents (id, url, type, saspri_k_id) VALUES
(1, 'https://example.com/docs/ktp_wali1.pdf', 'ktp', 1),
(2, 'https://example.com/docs/legalitas_koperasi1.pdf', 'legalitas', 1),
(3, 'https://example.com/docs/ktp_wali2.pdf', 'ktp', 2),
(4, 'https://example.com/docs/ktp_wali3.pdf', 'ktp', 3),
(5, 'https://example.com/docs/ktp_wali4.pdf', 'ktp', 4),
(6, 'https://example.com/docs/ktp_wali5.pdf', 'ktp', 5);

-- --------------------------------------------------------
-- Certifications
-- --------------------------------------------------------

INSERT INTO certifications (id, saspri_k_id, assessment_id, purpose, submitted_at, status, level, code, issued_at, total_score, grade, next_certification_due_date, self_team_due_date, self_review_due_date, peer_team_due_date, peer_review_due_date) VALUES
(1, 1, 1, 'level_up', '2024-01-01 10:00:00', 'completed', 'natalia', 'CERT/2024/002', '2024-01-05 10:00:00', 80, 'b', '2026-01-05 10:00:00', NULL, NULL, NULL, NULL),
(2, 1, 1, 'level_up', '2026-05-01 10:00:00', 'self_review', 'weania', 'CERT/2026/002', NULL, 0, NULL, NULL, '2026-05-08 10:00:00', '2026-05-22 10:00:00', NULL, NULL),
(3, 2, 1, 'level_up', '2024-05-01 10:00:00', 'completed', 'natalia', 'CERT/2024/001', '2024-05-05 14:00:00', 85, 'a', '2026-05-05 14:00:00', NULL, NULL, NULL, NULL),
(4, 3, 1, 'level_up', '2024-01-01 10:00:00', 'completed', 'weania', 'CERT/2024/003', '2024-01-05 10:00:00', 75, 'c', '2025-06-05 10:00:00', NULL, NULL, NULL, NULL),
(5, 3, 1, 'renewal', '2026-05-05 10:00:00', 'pending_self_team_formation', 'weania', NULL, NULL, 0, NULL, NULL, '2026-05-12 10:00:00', NULL, NULL, NULL),
(6, 4, 1, 'level_up', '2024-01-01 10:00:00', 'completed', 'natalia', 'CERT/2024/004', '2024-01-05 10:00:00', 82, 'b', '2026-01-05 10:00:00', NULL, NULL, NULL, NULL),
(7, 4, 1, 'level_up', '2026-05-01 10:00:00', 'pending_peer_team_formation', 'weania', NULL, NULL, 0, NULL, NULL, '2026-05-08 10:00:00', '2026-05-22 10:00:00', '2026-05-29 10:00:00', NULL),
(8, 5, 1, 'level_up', '2024-01-01 10:00:00', 'completed', 'weania', 'CERT/2024/005', '2024-01-05 10:00:00', 88, 'a', '2026-01-05 10:00:00', NULL, NULL, NULL, NULL),
(9, 5, 1, 'level_up', '2026-05-01 10:00:00', 'peer_review', 'prematura', NULL, NULL, 0, NULL, NULL, '2026-05-08 10:00:00', '2026-05-22 10:00:00', '2026-05-29 10:00:00', '2026-06-12 10:00:00');

-- --------------------------------------------------------
-- Team Members
-- --------------------------------------------------------

INSERT INTO self_team_members (id, certification_id, user_id, status, role) VALUES
(1, 2, 3, 'approved', 'leader'),
(2, 2, 4, 'approved', 'member'),
(3, 2, 5, 'approved', 'member'),
(4, 4, 9, 'approved', 'leader'),
(5, 4, 10, 'approved', 'member'),
(6, 4, 11, 'approved', 'member'),
(7, 5, 9, 'pending', 'member'),
(8, 5, 10, 'pending', 'leader'),
(9, 5, 11, 'pending', 'member'),
(10, 7, 13, 'approved', 'leader'),
(11, 7, 14, 'approved', 'member'),
(12, 7, 15, 'approved', 'member'),
(13, 9, 17, 'approved', 'leader'),
(14, 9, 18, 'approved', 'member'),
(15, 9, 19, 'approved', 'member');

INSERT INTO peer_team_members (id, certification_id, user_id, status, role) VALUES
(1, 4, 1, 'approved', 'facilitator'),
(2, 4, 2, 'approved', 'leader'),
(3, 4, 6, 'approved', 'member'),
(4, 7, 1, 'pending', 'facilitator'),
(5, 7, 2, 'pending', 'leader'),
(6, 7, 6, 'pending', 'member'),
(7, 9, 1, 'approved', 'facilitator'),
(8, 9, 8, 'approved', 'leader'),
(9, 9, 12, 'approved', 'member');

-- --------------------------------------------------------
-- Indicator Scores
-- --------------------------------------------------------

INSERT INTO indicator_scores (certification_id, indicator_id, self_team_score, evidence_url) VALUES
-- Certification 2 (Self Review In Progress - Draft)
(2, 1, 100, '/uploads/evidence/2/self_1.pdf'),
(2, 2, 75, '/uploads/evidence/2/self_2.pdf'),
(2, 3, 50, '/uploads/evidence/2/self_3.pdf'),
(2, 4, 100, NULL),
(2, 5, 25, NULL),
-- Certification 7 (Pending Peer Team Formation - Passed Self Review)
(7, 1, 100, '/uploads/evidence/7/self_1.pdf'),
(7, 2, 100, '/uploads/evidence/7/self_2.pdf'),
(7, 3, 75, '/uploads/evidence/7/self_3.pdf'),
(7, 4, 50, '/uploads/evidence/7/self_4.pdf'),
(7, 5, 100, '/uploads/evidence/7/self_5.pdf'),
(7, 6, 75, '/uploads/evidence/7/self_6.pdf'),
(7, 7, 25, '/uploads/evidence/7/self_7.pdf'),
(7, 8, 100, '/uploads/evidence/7/self_8.pdf'),
(7, 9, 50, '/uploads/evidence/7/self_9.pdf'),
(7, 10, 100, '/uploads/evidence/7/self_10.pdf'),
(7, 11, 75, NULL),
(7, 12, 100, NULL),
(7, 13, 25, NULL),
(7, 14, 50, NULL),
(7, 15, 100, NULL),
-- Certification 9 (Peer Review In Progress - Passed Self Review)
(9, 1, 75, '/uploads/evidence/9/self_1.pdf'),
(9, 2, 50, '/uploads/evidence/9/self_2.pdf'),
(9, 3, 100, '/uploads/evidence/9/self_3.pdf'),
(9, 4, 100, '/uploads/evidence/9/self_4.pdf'),
(9, 5, 25, '/uploads/evidence/9/self_5.pdf'),
(9, 6, 75, '/uploads/evidence/9/self_6.pdf'),
(9, 7, 50, '/uploads/evidence/9/self_7.pdf'),
(9, 8, 100, '/uploads/evidence/9/self_8.pdf'),
(9, 9, 100, '/uploads/evidence/9/self_9.pdf'),
(9, 10, 25, '/uploads/evidence/9/self_10.pdf'),
(9, 11, 50, NULL),
(9, 12, 75, NULL),
(9, 13, 100, NULL),
(9, 14, 100, NULL),
(9, 15, 25, NULL);

SET FOREIGN_KEY_CHECKS = 1;
