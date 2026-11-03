-- sample_data.sql - test data (run AFTER database.sql)
USE online_voting;

INSERT INTO admins (username, password) VALUES ('admin', 'temp');

INSERT INTO elections (title, start_time, end_time, admin_id)
VALUES ('Class Representative Election', NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 7 DAY, 1);

INSERT INTO candidates (election_id, name, description)
VALUES (1, 'Candidate A', 'Party X'), (1, 'Candidate B', 'Party Y'), (1, 'Candidate C', 'Party Z');
