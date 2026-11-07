-- OPTIONAL test data so you can try the admin pages (especially results.php).
-- Run ONCE on a fresh database, AFTER you have created the first admin (admin_id = 1).
USE online_voting;

INSERT INTO voters (name, email, password) VALUES
('Test Voter 1', 'v1@test.com', 'not-a-real-hash'),
('Test Voter 2', 'v2@test.com', 'not-a-real-hash'),
('Test Voter 3', 'v3@test.com', 'not-a-real-hash'),
('Test Voter 4', 'v4@test.com', 'not-a-real-hash');

INSERT INTO elections (title, start_time, end_time, admin_id) VALUES
('Sample Class Representative Election', NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 7 DAY, 1);

INSERT INTO candidates (election_id, name, description) VALUES
(1, 'Candidate A', 'Party X'),
(1, 'Candidate B', 'Party Y'),
(1, 'Candidate C', 'Party Z');

INSERT INTO votes (voter_id, election_id, candidate_id) VALUES
(1, 1, 1), (2, 1, 1), (3, 1, 2), (4, 1, 1);
