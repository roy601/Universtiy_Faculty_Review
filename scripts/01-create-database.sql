-- Creating MySQL database schema based on ER diagram
CREATE DATABASE IF NOT EXISTS faculty_review_system;
USE faculty_review_system;

-- Create Department table first (referenced by other tables)
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dept_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Admin table
CREATE TABLE admins (
    a_id VARCHAR(20) PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    ap_id VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Students table
CREATE TABLE students (
    id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    a_id VARCHAR(20),
    dept_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Create Faculty table
CREATE TABLE faculty (
    initial VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    room_no VARCHAR(20),
    specific_history TEXT,
    behavior_rating DECIMAL(3,2) DEFAULT 0.00,
    marking_rating DECIMAL(3,2) DEFAULT 0.00,
    teaching_rating DECIMAL(3,2) DEFAULT 0.00,
    overall_rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    a_id VARCHAR(20),
    dept_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Create Courses table
CREATE TABLE courses (
    course_code VARCHAR(20) PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    materials TEXT,
    review_count INT DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0.00,
    a_id VARCHAR(20),
    faculty_initial VARCHAR(10),
    dept_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_initial) REFERENCES faculty(initial) ON DELETE SET NULL,
    FOREIGN KEY (dept_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Create Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) NOT NULL,
    student_id VARCHAR(20) NOT NULL,
    faculty_initial VARCHAR(10) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    leaderboard_score DECIMAL(3,2) DEFAULT 0.00,
    comment TEXT,
    behavior_rating DECIMAL(3,2) NOT NULL CHECK (behavior_rating >= 1 AND behavior_rating <= 5),
    marking_rating DECIMAL(3,2) NOT NULL CHECK (marking_rating >= 1 AND marking_rating <= 5),
    teaching_rating DECIMAL(3,2) NOT NULL CHECK (teaching_rating >= 1 AND teaching_rating <= 5),
    overall_rating DECIMAL(3,2) NOT NULL CHECK (overall_rating >= 1 AND overall_rating <= 5),
    is_approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    FOREIGN KEY (course_code) REFERENCES courses(course_code) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_initial) REFERENCES faculty(initial) ON DELETE CASCADE,
    UNIQUE KEY unique_student_course_semester (student_id, course_code, semester)
);

-- Create indexes for better performance
CREATE INDEX idx_reviews_faculty ON reviews(faculty_initial);
CREATE INDEX idx_reviews_course ON reviews(course_code);
CREATE INDEX idx_reviews_student ON reviews(student_id);
CREATE INDEX idx_reviews_approved ON reviews(is_approved);
CREATE INDEX idx_faculty_dept ON faculty(dept_id);
CREATE INDEX idx_students_dept ON students(dept_id);
CREATE INDEX idx_courses_faculty ON courses(faculty_initial);
