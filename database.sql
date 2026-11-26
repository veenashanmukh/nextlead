-- NextLead voting system database
-- Run this once in your MySQL database before using the app.

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT GENERATED ALWAYS AS (id) VIRTUAL,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(15) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS constituencies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL
);

CREATE TABLE IF NOT EXISTS election (
  election_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME NOT NULL,
  election_date DATE,
  status VARCHAR(20) DEFAULT 'Upcoming'
);

CREATE TABLE IF NOT EXISTS voters (
  voter_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(15) NOT NULL UNIQUE,
  gender VARCHAR(10),
  constituency_id INT,
  age INT,
  password VARCHAR(255) NOT NULL,
  election_id INT
);

CREATE TABLE IF NOT EXISTS candidates (
  candidate_id INT AUTO_INCREMENT PRIMARY KEY,
  id INT GENERATED ALWAYS AS (candidate_id) VIRTUAL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(15) NOT NULL UNIQUE,
  gender VARCHAR(10),
  party_name VARCHAR(100),
  constituency_id INT,
  age INT,
  password VARCHAR(255) NOT NULL,
  election_id INT
);

CREATE TABLE IF NOT EXISTS votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  voter_id INT NOT NULL,
  candidate_id INT NOT NULL,
  election_id INT NOT NULL,
  voted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_vote_per_election (voter_id, election_id)
);
