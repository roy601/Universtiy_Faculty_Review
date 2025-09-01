-- University Faculty Review System Database
-- Run this script in phpMyAdmin to create the database



-- Users table (students and admins)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    name VARCHAR(100) NOT NULL
);
INSERT INTO users (username, password, role, name)
VALUES ('testuser', '1234', 'student', 'Test User');

-- Create departments table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create faculty table
CREATE TABLE faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    designation VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    bio TEXT,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    description TEXT,
    credits INT DEFAULT 3,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create reviews table (without foreign keys initially)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    rating INT NOT NULL,
    comments TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Add foreign keys to faculty table
ALTER TABLE faculty
ADD CONSTRAINT fk_faculty_department
FOREIGN KEY (department_id) REFERENCES departments(id)
ON DELETE CASCADE;

-- Add foreign keys to courses table
ALTER TABLE courses
ADD CONSTRAINT fk_courses_department
FOREIGN KEY (department_id) REFERENCES departments(id)
ON DELETE CASCADE;

-- Add foreign keys to reviews table
ALTER TABLE reviews
ADD CONSTRAINT fk_reviews_student
FOREIGN KEY (student_id) REFERENCES users(id)
ON DELETE CASCADE,
ADD CONSTRAINT fk_reviews_faculty
FOREIGN KEY (faculty_id) REFERENCES faculty(id)
ON DELETE CASCADE,
ADD CONSTRAINT fk_reviews_course
FOREIGN KEY (course_id) REFERENCES courses(id)
ON DELETE CASCADE;

-- Add check constraint for rating
ALTER TABLE reviews
ADD CONSTRAINT chk_rating_range CHECK (rating >= 1 AND rating <= 5);








-- Add created_at column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Create a table for admin actions log
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add more details to faculty table
ALTER TABLE faculty ADD COLUMN IF NOT EXISTS email VARCHAR(100);
ALTER TABLE faculty ADD COLUMN IF NOT EXISTS phone VARCHAR(20);








-- Add course_reviews table
CREATE TABLE course_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  course_id INT NOT NULL,
  difficulty ENUM('easy','medium','hard') NOT NULL,
  rating TINYINT DEFAULT NULL,
  comments TEXT,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (student_id),
  INDEX (course_id),
  CONSTRAINT fk_cr_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cr_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Add profile_picture column to users table
ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL;

-- Add profile_picture column to faculty table
ALTER TABLE faculty ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL;

-- Create a table for storing uploaded images metadata
CREATE TABLE user_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);