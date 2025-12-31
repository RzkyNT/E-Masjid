-- Update mentor_attendance table to support class-based attendance
-- Add class column to mentor_attendance table

ALTER TABLE mentor_attendance 
ADD COLUMN class VARCHAR(10) NOT NULL DEFAULT 'A' AFTER level;

-- Update existing records to have a default class
UPDATE mentor_attendance SET class = 'A' WHERE class = '';

-- Add composite index for better performance
ALTER TABLE mentor_attendance 
ADD INDEX idx_mentor_date_level_class (mentor_id, attendance_date, level, class);

-- Drop old index if exists
ALTER TABLE mentor_attendance 
DROP INDEX IF EXISTS idx_mentor_date_level;