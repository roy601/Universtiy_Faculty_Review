-- Seeding initial data for testing
USE faculty_review_system;

-- Insert sample departments
INSERT INTO departments (dept_name) VALUES 
('Computer Science'),
('Mathematics'),
('Physics'),
('Chemistry'),
('English');

-- Insert sample admin
INSERT INTO admins (a_id, email, name, password_hash, ap_id) VALUES 
('ADMIN001', 'admin@university.edu', 'System Administrator', '$2b$10$example_hash_here', 'AP001');

-- Insert sample faculty
INSERT INTO faculty (initial, name, email, room_no, specific_history, a_id, dept_id) VALUES 
('JD', 'Dr. John Doe', 'john.doe@university.edu', 'CS-101', 'PhD in Computer Science, 10 years experience', 'FAC001', 1),
('JS', 'Dr. Jane Smith', 'jane.smith@university.edu', 'CS-102', 'PhD in Software Engineering, 8 years experience', 'FAC002', 1),
('MB', 'Dr. Mike Brown', 'mike.brown@university.edu', 'MATH-201', 'PhD in Applied Mathematics, 12 years experience', 'FAC003', 2),
('SJ', 'Dr. Sarah Johnson', 'sarah.johnson@university.edu', 'PHY-301', 'PhD in Theoretical Physics, 15 years experience', 'FAC004', 3);

-- Insert sample students
INSERT INTO students (id, name, email, password_hash, a_id, dept_id) VALUES 
('STU001', 'Alice Wilson', 'alice.wilson@student.edu', '$2b$10$example_hash_here', 'STU001', 1),
('STU002', 'Bob Davis', 'bob.davis@student.edu', '$2b$10$example_hash_here', 'STU002', 1),
('STU003', 'Carol Martinez', 'carol.martinez@student.edu', '$2b$10$example_hash_here', 'STU003', 2),
('STU004', 'David Lee', 'david.lee@student.edu', '$2b$10$example_hash_here', 'STU004', 1);

-- Insert sample courses
INSERT INTO courses (course_code, name, materials, faculty_initial, dept_id) VALUES 
('CS101', 'Introduction to Programming', 'Textbook: Programming Fundamentals, Online resources', 'JD', 1),
('CS201', 'Data Structures and Algorithms', 'Textbook: CLRS, Programming assignments', 'JS', 1),
('MATH101', 'Calculus I', 'Textbook: Stewart Calculus, Problem sets', 'MB', 2),
('PHY101', 'General Physics', 'Textbook: Halliday & Resnick, Lab manual', 'SJ', 3);

-- Insert sample reviews
INSERT INTO reviews (course_code, student_id, faculty_initial, semester, comment, behavior_rating, marking_rating, teaching_rating, overall_rating, is_approved) VALUES 
('CS101', 'STU001', 'JD', 'Fall 2024', 'Excellent teaching style, very clear explanations', 4.5, 4.0, 4.8, 4.4, TRUE),
('CS101', 'STU002', 'JD', 'Fall 2024', 'Good course content, helpful professor', 4.2, 4.3, 4.5, 4.3, TRUE),
('CS201', 'STU001', 'JS', 'Spring 2024', 'Challenging but rewarding course', 4.0, 3.8, 4.2, 4.0, TRUE),
('MATH101', 'STU003', 'MB', 'Fall 2024', 'Clear mathematical explanations', 4.3, 4.5, 4.4, 4.4, FALSE);
