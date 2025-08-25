-- Local MySQL Database Setup Instructions
-- Run these commands in your MySQL client (MySQL Workbench, phpMyAdmin, or command line)

-- 1. Create database
CREATE DATABASE IF NOT EXISTS faculty_review_db;

-- 2. Use the database
USE faculty_review_db;

-- 3. Create a user for the application (optional, for security)
-- CREATE USER 'faculty_app'@'localhost' IDENTIFIED BY 'your_password';
-- GRANT ALL PRIVILEGES ON faculty_review_db.* TO 'faculty_app'@'localhost';
-- FLUSH PRIVILEGES;

-- Note: Update your .env.local file with these credentials:
-- DB_HOST=localhost
-- DB_USER=root (or faculty_app if you created a user)
-- DB_PASSWORD=your_mysql_password
-- DB_NAME=faculty_review_db
-- DB_PORT=3306
