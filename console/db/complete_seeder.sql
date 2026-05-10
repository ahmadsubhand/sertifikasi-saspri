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
(11, 'user7', 'test_auth_key_11', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user7@example.com', 10, 1714876800, 1714876800, '08123456783', 3);

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
('user', 11, 1778028133);

-- --------------------------------------------------------
-- SASPRI-K
-- --------------------------------------------------------

INSERT INTO saspri_k (id, coordinator_id, district_id, region_name, address, cooperative_name, number_of_groups, number_of_active_members, livestock_type, total_livestock_count, breeding_livestock_count, productive_heifer_count, request_status, valid_certificate_id) VALUES
(1, 2, 265, 'Baiturrahman', 'Jl. Syiah Kuala No. 10', 'Koperasi Sejahtera', 5, 50, 'Sapi Potong', 200, 100, 50, 'approved', 1),
(2, 6, 1, 'Bakongan', 'Jl. Bakongan No. 1', 'Koperasi Bakongan Jaya', 3, 30, 'Sapi Potong', 120, 60, 30, 'approved', 4),
(3, 8, 2, 'Kluet Utara', 'Jl. Kluet Utara No. 5', 'Koperasi Kluet Mandiri', 4, 45, 'Sapi Potong', 180, 90, 45, 'approved', 5);

INSERT INTO saspri_k_documents (id, url, type, saspri_k_id) VALUES
(1, 'https://example.com/docs/ktp_wali1.pdf', 'ktp', 1),
(2, 'https://example.com/docs/legalitas_koperasi1.pdf', 'legalitas', 1),
(3, 'https://example.com/docs/ktp_wali2.pdf', 'ktp', 2),
(4, 'https://example.com/docs/ktp_wali3.pdf', 'ktp', 3);

-- --------------------------------------------------------
-- Certifications
-- --------------------------------------------------------

INSERT INTO certifications (id, saspri_k_id, assessment_id, purpose, submitted_at, status, level, code, issued_at, total_score, grade, next_certification_due_date, self_team_due_date, self_review_due_date) VALUES
(1, 1, 1, 'level_up', '2024-01-01 10:00:00', 'completed', 'natalia', 'CERT/2024/002', '2024-01-05 10:00:00', 80, 'b', '2026-01-05 10:00:00', NULL, NULL),
(2, 1, 1, 'level_up', '2026-05-01 10:00:00', 'self_review', 'weania', 'CERT/2026/002', NULL, 0, NULL, NULL, '2026-05-23 23:59:59', '2026-05-20 23:59:59'),
(3, 2, 1, 'level_up', '2024-05-01 10:00:00', 'completed', 'natalia', 'CERT/2024/001', '2024-05-05 14:00:00', 85, 'a', '2026-05-05 14:00:00', NULL, NULL),
(4, 3, 1, 'level_up', '2024-01-01 10:00:00', 'completed', 'weania', 'CERT/2024/003', '2024-01-05 10:00:00', 75, 'c', '2025-06-05 10:00:00', NULL, NULL),
(5, 3, 1, 'renewal', '2026-05-05 10:00:00', 'pending_self_team_formation', 'weania', NULL, NULL, 0, NULL, NULL, '2026-05-17 23:59:59', NULL);

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
(9, 5, 11, 'pending', 'member');

INSERT INTO peer_team_members (id, certification_id, user_id, status, role) VALUES
(1, 4, 1, 'approved', 'leader'),
(2, 4, 2, 'approved', 'member'),
(3, 4, 6, 'approved', 'member');

SET FOREIGN_KEY_CHECKS = 1;
