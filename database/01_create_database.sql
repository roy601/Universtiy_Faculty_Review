-- University Faculty Review System Database
-- Run this script in phpMyAdmin to create the database

CREATE DATABASE IF NOT EXISTS faculty_review_system 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE faculty_review_system;

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


































-- Departments table
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(10) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Faculty table
CREATE TABLE faculty (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    department_id INT NOT NULL,
    designation VARCHAR(50),
    qualification VARCHAR(200),
    experience_years INT DEFAULT 0,
    profile_image VARCHAR(255),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    department_id INT NOT NULL,
    credits INT DEFAULT 3,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Faculty-Course mapping
CREATE TABLE faculty_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    semester VARCHAR(20),
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_faculty_course_semester (faculty_id, course_id, semester, year)
);

-- Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    faculty_id INT NOT NULL,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    rating_teaching INT CHECK (rating_teaching BETWEEN 1 AND 5),
    rating_knowledge INT CHECK (rating_knowledge BETWEEN 1 AND 5),
    rating_communication INT CHECK (rating_communication BETWEEN 1 AND 5),
    rating_availability INT CHECK (rating_availability BETWEEN 1 AND 5),
    rating_overall INT CHECK (rating_overall BETWEEN 1 AND 5),
    comment TEXT,
    is_anonymous BOOLEAN DEFAULT TRUE,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_faculty_course (student_id, faculty_id, course_id)
);

-- Create indexes for better performance
CREATE INDEX idx_faculty_department ON faculty(department_id);
CREATE INDEX idx_reviews_faculty ON reviews(faculty_id);
CREATE INDEX idx_reviews_status ON reviews(status);
CREATE INDEX idx_faculty_courses_faculty ON faculty_courses(faculty_id);
CREATE INDEX idx_faculty_courses_course ON faculty_courses(course_id);
