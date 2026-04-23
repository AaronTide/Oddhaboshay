-- ============================================================
--  ODDHABOSHAY LMS - Complete Database Schema
--  University DBMS Project
-- ============================================================

CREATE DATABASE IF NOT EXISTS oddhaboshay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE oddhaboshay;

-- ============================================================
--  TABLE 1: admins
--  Stores administrator login credentials
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,              -- bcrypt hash
    email       VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  TABLE 2: students
--  Stores student information and login credentials
-- ============================================================
CREATE TABLE IF NOT EXISTS students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  VARCHAR(20)  NOT NULL UNIQUE,       -- e.g., STU001
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,              -- bcrypt hash
    phone       VARCHAR(20),
    department  VARCHAR(100),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  TABLE 3: teachers
--  Stores teacher information and login credentials
-- ============================================================
CREATE TABLE IF NOT EXISTS teachers (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id  VARCHAR(20)  NOT NULL UNIQUE,       -- e.g., TCH001
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,              -- bcrypt hash
    phone       VARCHAR(20),
    department  VARCHAR(100),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  TABLE 4: courses
--  Stores course details; each course has one teacher
-- ============================================================
CREATE TABLE IF NOT EXISTS courses (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    course_code  VARCHAR(20)  NOT NULL UNIQUE,      -- e.g., CSE101
    course_name  VARCHAR(150) NOT NULL,
    description  TEXT,
    teacher_id   INT,                               -- FK to teachers.id
    credits      INT DEFAULT 3,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

-- ============================================================
--  TABLE 5: enrollments
--  Links students to courses with approval status
-- ============================================================
CREATE TABLE IF NOT EXISTS enrollments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    student_id   INT NOT NULL,                      -- FK to students.id
    course_id    INT NOT NULL,                      -- FK to courses.id
    status       ENUM('pending','approved','declined') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id)  REFERENCES courses(id)  ON DELETE CASCADE,
    UNIQUE KEY   unique_enroll (student_id, course_id)  -- prevent duplicate requests
);

-- ============================================================
--  TABLE 6: course_materials
--  Stores videos, PDFs, and notices for each course
-- ============================================================
CREATE TABLE IF NOT EXISTS course_materials (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    course_id   INT  NOT NULL,                      -- FK to courses.id
    teacher_id  INT  NOT NULL,                      -- FK to teachers.id
    type        ENUM('video','pdf','notice') NOT NULL,
    title       VARCHAR(200) NOT NULL,
    content     TEXT,                               -- video URL or notice text
    file_path   VARCHAR(500),                       -- PDF file path
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id)  ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);

-- ============================================================
--  TABLE 7: messages
--  Messaging between students and teachers
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    sender_type   ENUM('student','teacher') NOT NULL,
    sender_id     INT NOT NULL,
    receiver_type ENUM('student','teacher') NOT NULL,
    receiver_id   INT NOT NULL,
    subject       VARCHAR(200),
    message       TEXT NOT NULL,
    is_read       TINYINT(1) DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
--  SAMPLE DATA
--  Default password for ALL sample accounts: password
--  Hash below is bcrypt for the string "password"
-- ============================================================

INSERT INTO admins (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@oddhaboshay.edu');

INSERT INTO students (student_id, name, email, password, phone, department) VALUES
('STU001', 'Rahim Ahmed',   'rahim@student.edu',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01711000001', 'Computer Science'),
('STU002', 'Karim Hassan',  'karim@student.edu',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01711000002', 'Mathematics'),
('STU003', 'Fatema Begum',  'fatema@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01711000003', 'Physics');

INSERT INTO teachers (teacher_id, name, email, password, phone, department) VALUES
('TCH001', 'Dr. Anwar Hossain',  'anwar@teacher.edu',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01811000001', 'Computer Science'),
('TCH002', 'Prof. Nasima Islam', 'nasima@teacher.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01811000002', 'Mathematics');

INSERT INTO courses (course_code, course_name, description, teacher_id, credits) VALUES
('CSE101', 'Introduction to Programming', 'Learn basics of programming using C language', 1, 3),
('CSE201', 'Database Management Systems', 'Relational databases, SQL, normalization, transactions', 1, 3),
('MATH101', 'Calculus I',  'Limits, derivatives, and integrals', 2, 3),
('CSE301', 'Data Structures', 'Arrays, linked lists, trees, and graphs', 1, 3);

INSERT INTO enrollments (student_id, course_id, status) VALUES
(1, 1, 'approved'),
(1, 2, 'pending'),
(2, 3, 'approved'),
(3, 1, 'approved'),
(3, 2, 'declined');

INSERT INTO course_materials (course_id, teacher_id, type, title, content) VALUES
(1, 1, 'notice', 'Welcome to CSE101', 'Welcome everyone! Classes start this Monday at 9:00 AM in Room 301.'),
(1, 1, 'video',  'Lecture 1 - Hello World in C', 'https://www.youtube.com/watch?v=KJgsSFOSQv0'),
(2, 1, 'notice', 'DBMS Syllabus Posted', 'The complete syllabus covers ER diagrams, SQL, normalization up to BCNF.'),
(3, 2, 'video',  'Calculus Introduction', 'https://www.youtube.com/watch?v=WUvTyaaNkzM');

INSERT INTO messages (sender_type, sender_id, receiver_type, receiver_id, subject, message) VALUES
('student', 1, 'teacher', 1, 'Question about Assignment 1', 'Dear Sir, I have a doubt in the pointer section. Can you explain at next class?'),
('teacher', 1, 'student', 1, 'Re: Question about Assignment 1', 'Sure Rahim! Come to office hours on Wednesday 2-4 PM. Room 205.');
