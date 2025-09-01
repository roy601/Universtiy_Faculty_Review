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
















