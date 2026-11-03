-- database.sql - Online Voting System (import this in phpMyAdmin)
CREATE DATABASE IF NOT EXISTS online_voting;
USE online_voting;

CREATE TABLE admins (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE voters (
  voter_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE elections (
  election_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NOT NULL,
  admin_id INT NOT NULL,
  FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
);

CREATE TABLE candidates (
  candidate_id INT AUTO_INCREMENT PRIMARY KEY,
  election_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255),
  FOREIGN KEY (election_id) REFERENCES elections(election_id) ON DELETE CASCADE
);

CREATE TABLE votes (
  vote_id INT AUTO_INCREMENT PRIMARY KEY,
  voter_id INT NOT NULL,
  election_id INT NOT NULL,
  candidate_id INT NOT NULL,
  voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (voter_id, election_id),
  FOREIGN KEY (voter_id) REFERENCES voters(voter_id),
  FOREIGN KEY (election_id) REFERENCES elections(election_id),
  FOREIGN KEY (candidate_id) REFERENCES candidates(candidate_id) ON DELETE CASCADE
);
