-- Sample data for testing
-- Run this after creating the database structure

USE faculty_review_system;

-- Insert departments
INSERT INTO departments (name, code, description) VALUES
('Computer Science', 'CS', 'Department of Computer Science and Engineering'),
('Mathematics', 'MATH', 'Department of Mathematics'),
('Physics', 'PHY', 'Department of Physics'),
('Chemistry', 'CHEM', 'Department of Chemistry'),
('English', 'ENG', 'Department of English Literature');

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Insert sample students (password: student123)
INSERT INTO users (username, email, password, full_name, role, student_id) VALUES
('john_doe', 'john.doe@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'student', 'CS2021001'),
('jane_smith', 'jane.smith@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'student', 'CS2021002'),
('mike_johnson', 'mike.johnson@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike Johnson', 'student', 'MATH2021001');

-- Insert sample faculty
INSERT INTO faculty (name, email, department_id, designation, qualification, experience_years, bio) VALUES
('Dr. Sarah Wilson', 'sarah.wilson@university.edu', 1, 'Professor', 'PhD in Computer Science', 15, 'Expert in Machine Learning and Data Science'),
('Dr. Robert Brown', 'robert.brown@university.edu', 1, 'Associate Professor', 'PhD in Software Engineering', 12, 'Specializes in Web Development and Database Systems'),
('Dr. Emily Davis', 'emily.davis@university.edu', 2, 'Professor', 'PhD in Applied Mathematics', 18, 'Research focus on Mathematical Modeling'),
('Dr. Michael Chen', 'michael.chen@university.edu', 3, 'Assistant Professor', 'PhD in Theoretical Physics', 8, 'Quantum Physics and Computational Physics'),
('Dr. Lisa Anderson', 'lisa.anderson@university.edu', 4, 'Associate Professor', 'PhD in Organic Chemistry', 10, 'Organic Synthesis and Medicinal Chemistry');

-- Insert sample courses
INSERT INTO courses (name, code, department_id, credits, description) VALUES
('Introduction to Programming', 'CS101', 1, 4, 'Basic programming concepts using Python'),
('Data Structures', 'CS201', 1, 4, 'Fundamental data structures and algorithms'),
('Database Systems', 'CS301', 1, 3, 'Database design and management systems'),
('Web Development', 'CS401', 1, 3, 'Modern web development technologies'),
('Calculus I', 'MATH101', 2, 4, 'Differential and integral calculus'),
('Linear Algebra', 'MATH201', 2, 3, 'Vector spaces and linear transformations'),
('Physics I', 'PHY101', 3, 4, 'Classical mechanics and thermodynamics'),
('Organic Chemistry', 'CHEM201', 4, 4, 'Structure and reactions of organic compounds');

-- Map faculty to courses
INSERT INTO faculty_courses (faculty_id, course_id, semester, year) VALUES
(1, 1, 'Fall', 2024),
(1, 2, 'Spring', 2024),
(2, 3, 'Fall', 2024),
(2, 4, 'Spring', 2024),
(3, 5, 'Fall', 2024),
(3, 6, 'Spring', 2024),
(4, 7, 'Fall', 2024),
(5, 8, 'Spring', 2024);

-- Insert sample reviews
INSERT INTO reviews (faculty_id, course_id, student_id, rating_teaching, rating_knowledge, rating_communication, rating_availability, rating_overall, comment, status) VALUES
(1, 1, 2, 5, 5, 4, 4, 5, 'Excellent teacher! Very clear explanations and helpful during office hours.', 'approved'),
(1, 2, 3, 4, 5, 4, 3, 4, 'Great knowledge of the subject, but sometimes hard to reach outside class.', 'approved'),
(2, 3, 2, 4, 4, 5, 5, 4, 'Very approachable and explains complex concepts well.', 'approved'),
(3, 5, 4, 5, 5, 5, 4, 5, 'Outstanding professor! Makes mathematics interesting and understandable.', 'approved');
