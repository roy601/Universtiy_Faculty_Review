-- Stored procedure to update faculty ratings based on approved reviews
USE faculty_review_system;

DELIMITER //

CREATE PROCEDURE UpdateFacultyRatings(IN faculty_init VARCHAR(10))
BEGIN
    DECLARE avg_behavior DECIMAL(3,2);
    DECLARE avg_marking DECIMAL(3,2);
    DECLARE avg_teaching DECIMAL(3,2);
    DECLARE avg_overall DECIMAL(3,2);
    DECLARE review_count INT;
    
    -- Calculate averages from approved reviews
    SELECT 
        AVG(behavior_rating),
        AVG(marking_rating),
        AVG(teaching_rating),
        AVG(overall_rating),
        COUNT(*)
    INTO avg_behavior, avg_marking, avg_teaching, avg_overall, review_count
    FROM reviews 
    WHERE faculty_initial = faculty_init AND is_approved = TRUE;
    
    -- Update faculty table
    UPDATE faculty 
    SET 
        behavior_rating = COALESCE(avg_behavior, 0),
        marking_rating = COALESCE(avg_marking, 0),
        teaching_rating = COALESCE(avg_teaching, 0),
        overall_rating = COALESCE(avg_overall, 0),
        total_reviews = COALESCE(review_count, 0)
    WHERE initial = faculty_init;
END //

DELIMITER ;

-- Create trigger to automatically update ratings when reviews are approved
DELIMITER //

CREATE TRIGGER update_faculty_ratings_after_review_approval
    AFTER UPDATE ON reviews
    FOR EACH ROW
BEGIN
    IF NEW.is_approved = TRUE AND OLD.is_approved = FALSE THEN
        CALL UpdateFacultyRatings(NEW.faculty_initial);
    END IF;
END //

DELIMITER ;
