CREATE DATABASE IF NOT EXISTS smartstudyhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartstudyhub;

-- USERS
CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(100) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	role ENUM('student','teacher') DEFAULT 'student',
	created_at DATETIME NOT NULL
);

-- SUBJECTS
CREATE TABLE IF NOT EXISTS subjects (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	name VARCHAR(100) NOT NULL,
	description TEXT,
	target_hours_per_week INT DEFAULT 0,
	created_at DATETIME NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- TASKS
CREATE TABLE IF NOT EXISTS tasks (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	subject_id INT NOT NULL,
	title VARCHAR(150) NOT NULL,
	description TEXT,
	due_date DATE,
	priority ENUM('Low','Medium','High') DEFAULT 'Medium',
	status ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
	created_at DATETIME NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- STUDY SESSIONS
CREATE TABLE IF NOT EXISTS study_sessions (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	subject_id INT NOT NULL,
	date DATE NOT NULL,
	duration_minutes INT NOT NULL,
	created_at DATETIME NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- BADGES
CREATE TABLE IF NOT EXISTS badges (
	id INT AUTO_INCREMENT PRIMARY KEY,
	slug VARCHAR(50) NOT NULL UNIQUE,
	name VARCHAR(100) NOT NULL,
	description TEXT
);

-- USER_BADGES
CREATE TABLE IF NOT EXISTS user_badges (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	badge_id INT NOT NULL,
	earned_at DATETIME NOT NULL,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- SEED BADGES
INSERT IGNORE INTO badges (slug, name, description) VALUES
('first_session', 'First Session', 'Completed the first study session.'),
('5_sessions', 'Getting Warm', 'Completed 5 study sessions.'),
('10_sessions', 'Serious Learner', 'Completed 10 study sessions.'),
('1_hour_total', '1 Hour Club', 'Studied for at least 60 total minutes.'),
('7_day_streak', 'Streak Master', 'Studied for 7 days in a row.');