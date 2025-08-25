-- Fix passwords for immediate login testing
-- Replace bcrypt hashes with plain text passwords for development
USE faculty_review_system;

-- Update admin password to plain text for testing
UPDATE admins SET password_hash = 'admin123' WHERE a_id = 'ADMIN001';

-- Update student passwords to plain text for testing  
UPDATE students SET password_hash = 'student123' WHERE id IN ('STU001', 'STU002', 'STU003', 'STU004');
