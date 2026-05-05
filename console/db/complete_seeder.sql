SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Users
-- --------------------------------------------------------

INSERT INTO `user` (id, username, auth_key, password_hash, email, status, created_at, updated_at, role, phone_number, saspri_k_id) VALUES
(1, 'admin', 'test_auth_key_1', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'admin@example.com', 10, 1714876800, 1714876800, 'admin', '08123456789', NULL),
(2, 'wali1', 'test_auth_key_2', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'wali1@example.com', 10, 1714876800, 1714876800, 'coordinator', '08123456780', 1),
(3, 'user1', 'test_auth_key_3', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user1@example.com', 10, 1714876800, 1714876800, 'user', '08123456781', NULL),
(4, 'user2', 'test_auth_key_4', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user2@example.com', 10, 1714876800, 1714876800, 'user', '08123456782', NULL),
(5, 'user3', 'test_auth_key_5', '$2y$13$EjaPFBnZOQsHdGuHI.xvhuDp1fHpo8hKRSk6yshqa9c5EG8s3C3lO', 'user3@example.com', 10, 1714876800, 1714876800, 'user', '08123456783', NULL);

-- --------------------------------------------------------
-- Indicator Groups and Indicators
-- --------------------------------------------------------

INSERT INTO indicator_groups (id, parent_group_id, code, label, `order`, weight) VALUES
(1, 1, 'G1', 'Group 1', 1, 10.0),
(2, 1, 'G1.1', 'Sub Group 1.1', 1, 5.0),
(3, 1, 'G1.2', 'Sub Group 1.2', 2, 5.0);

INSERT INTO indicators (id, indicator_group_id, code, label, `order`) VALUES
(1, 2, 'I1.1.1', 'Indicator 1.1.1', 1),
(2, 2, 'I1.1.2', 'Indicator 1.1.2', 2),
(3, 3, 'I1.2.1', 'Indicator 1.2.1', 1);

-- --------------------------------------------------------
-- Assessments
-- --------------------------------------------------------

INSERT INTO assessments (id, title, is_active, created_at, updated_at, released_at) VALUES
(1, 'Assessment Utama 2024', 1, '2024-01-01 00:00:00', '2024-01-01 00:00:00', '2024-01-01 00:00:00');

INSERT INTO assessment_indicator_relations (assessment_id, indicator_id) VALUES
(1, 1),
(1, 2),
(1, 3);

-- --------------------------------------------------------
-- SASPRI-K
-- --------------------------------------------------------

INSERT INTO saspri_k (id, coordinator_id, district_id, region_name, address, cooperative_name, number_of_groups, number_of_active_members, livestock_type, total_livestock_count, breeding_livestock_count, productive_heifer_count, request_status, valid_certificate_id) VALUES
(1, 2, 265, 'Baiturrahman', 'Jl. Syiah Kuala No. 10', 'Koperasi Sejahtera', 5, 50, 'Sapi Potong', 200, 100, 50, 'approved', 1);

INSERT INTO saspri_k_documents (id, url, type, saspri_k_id) VALUES
(1, 'https://example.com/docs/ktp_wali1.pdf', 'ktp', 1),
(2, 'https://example.com/docs/legalitas_koperasi1.pdf', 'legalitas', 1);

-- --------------------------------------------------------
-- Certifications
-- --------------------------------------------------------

INSERT INTO certifications (id, saspri_k_id, assessment_id, purpose, submitted_at, status, level, code, issued_at, total_score, grade, next_certification_due_date) VALUES
(1, 1, 1, 'renewal', '2024-05-01 10:00:00', 'completed', 'natalia', 'CERT/2024/001', '2024-05-05 14:00:00', 85, 'a', '2026-05-05 14:00:00');

-- --------------------------------------------------------
-- Team Members
-- --------------------------------------------------------

INSERT INTO self_team_members (id, certification_id, user_id, status, role) VALUES
(1, 1, 2, 'approved', 'leader'),
(2, 1, 3, 'approved', 'member');

INSERT INTO peer_team_members (id, certification_id, user_id, status, role) VALUES
(1, 1, 4, 'approved', 'leader'),
(2, 1, 5, 'approved', 'member');

-- --------------------------------------------------------
-- Indicator Scores
-- --------------------------------------------------------

INSERT INTO indicator_scores (id, indicator_id, certification_id, self_team_score, peer_team_score, status, evidence_url) VALUES
(1, 1, 1, 4, 4, 'identical', 'https://example.com/evidence/1_1.jpg'),
(2, 2, 1, 5, 4, 'different', 'https://example.com/evidence/1_2.jpg'),
(3, 3, 1, 3, 3, 'agreed', 'https://example.com/evidence/1_3.jpg');

SET FOREIGN_KEY_CHECKS = 1;
