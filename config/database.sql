-- Interactive English Lab - Full Schema & Seed Data
CREATE DATABASE IF NOT EXISTS englishlab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE englishlab;

-- Core tables ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') DEFAULT 'student',
    xp INT DEFAULT 0,
    streak INT DEFAULT 0,
    last_active DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    order_index INT DEFAULT 0,
    duration_minutes INT DEFAULT 10,
    video_url VARCHAR(500) NULL,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NULL,
    course_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer CHAR(1) NOT NULL,
    points INT DEFAULT 10,
    image_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress_percent INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lesson_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    completed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progress (user_id, lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    score INT NOT NULL,
    max_score INT NOT NULL,
    taken_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safe image_url migrations ---------------------------------------------------
SET @col_exists_courses := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'image_url'
);
SET @sql_courses := IF(
    @col_exists_courses = 0,
    'ALTER TABLE courses ADD COLUMN image_url VARCHAR(500) NULL AFTER description;',
    'SELECT "courses.image_url already exists";'
);
PREPARE stmt_courses FROM @sql_courses;
EXECUTE stmt_courses;
DEALLOCATE PREPARE stmt_courses;

SET @col_exists_lessons := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lessons' AND COLUMN_NAME = 'image_url'
);
SET @sql_lessons := IF(
    @col_exists_lessons = 0,
    'ALTER TABLE lessons ADD COLUMN image_url VARCHAR(500) NULL AFTER video_url;',
    'SELECT "lessons.image_url already exists";'
);
PREPARE stmt_lessons FROM @sql_lessons;
EXECUTE stmt_lessons;
DEALLOCATE PREPARE stmt_lessons;

SET @col_exists_quizzes := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quizzes' AND COLUMN_NAME = 'image_url'
);
SET @sql_quizzes := IF(
    @col_exists_quizzes = 0,
    'ALTER TABLE quizzes ADD COLUMN image_url VARCHAR(500) NULL AFTER points;',
    'SELECT "quizzes.image_url already exists";'
);
PREPARE stmt_quizzes FROM @sql_quizzes;
EXECUTE stmt_quizzes;
DEALLOCATE PREPARE stmt_quizzes;

-- Seed data -------------------------------------------------------------------
INSERT INTO users (name, email, password, role, xp, streak)
VALUES ('Admin User', 'admin@ielab.local', 'admin123', 'admin', 0, 0)
ON DUPLICATE KEY UPDATE name = VALUES(name), password = VALUES(password);

INSERT INTO courses (title, slug, description, image_url) VALUES
('Beginner English Basics', 'beginner-english-basics', 'Start with essential phrases, alphabet drills, and confidence boosters.', 'https://source.unsplash.com/featured/?english,learning,basics'),
('Everyday Conversation', 'everyday-conversation', 'Speak naturally in shops, cafes, phone calls, and casual chats.', 'https://source.unsplash.com/featured/?english,conversation'),
('Grammar Starter Pack', 'grammar-starter', 'Master tenses, sentence structure, and must-know grammar fixes.', 'https://source.unsplash.com/featured/?english,grammar'),
('Vocabulary Booster', 'vocabulary-booster', 'Unlock themed word packs and mini games that build your word bank fast.', 'https://source.unsplash.com/featured/?english,vocabulary'),
('Pronunciation Practice', 'pronunciation-practice', 'Fine-tune sounds, stress, and intonation with guided drills.', 'https://source.unsplash.com/featured/?english,pronunciation'),
('Listening Skills 101', 'listening-skills-101', 'Understand native speakers with slow-to-fast clips and note hacks.', 'https://source.unsplash.com/featured/?english,listening'),
('Reading Skills for Beginners', 'reading-skills-beginners', 'Decode short stories, captions, and articles with comprehension prompts.', 'https://source.unsplash.com/featured/?english,reading'),
('English for Daily Life', 'english-for-daily-life', 'Handle errands, appointments, and chit-chat with polite fluency.', 'https://source.unsplash.com/featured/?english,dailylife'),
('Simple English for Travel', 'simple-english-travel', 'Navigate airports, hotels, and restaurants like a pro traveler.', 'https://source.unsplash.com/featured/?english,travel'),
('English Mini Games & Quizzes', 'english-mini-games', 'Stay motivated with streak-friendly quizzes, puzzles, and XP drops.', 'https://source.unsplash.com/featured/?english,games')
ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), image_url = VALUES(image_url);

SET @course_beginner := (SELECT id FROM courses WHERE slug = 'beginner-english-basics' LIMIT 1);
SET @course_convo := (SELECT id FROM courses WHERE slug = 'everyday-conversation' LIMIT 1);
SET @course_grammar := (SELECT id FROM courses WHERE slug = 'grammar-starter' LIMIT 1);

INSERT INTO lessons (course_id, title, content, order_index, duration_minutes, video_url, image_url) VALUES
(@course_beginner, 'Introduction to English', 'Meet the alphabet sounds, practice spelling your name, and greet confidently.', 1, 10, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://source.unsplash.com/featured/?alphabet,english'),
(@course_beginner, 'Numbers and Counting', 'Count objects, read prices, and play quick math speaking games.', 2, 12, NULL, 'https://source.unsplash.com/featured/?numbers,english'),
(@course_beginner, 'Color Splash Vocabulary', 'Describe outfits, foods, and weather using vibrant color expressions.', 3, 9, NULL, 'https://source.unsplash.com/featured/?colors,english'),
(@course_beginner, 'Polite Everyday Phrases', 'Use greetings, gratitude, apologies, and polite fillers naturally.', 4, 11, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://source.unsplash.com/featured/?conversation,english'),
(@course_beginner, 'Introducing Yourself', 'Craft a confident self-introduction for class, work, or travel buddies.', 5, 15, NULL, 'https://source.unsplash.com/featured/?introduction,english'),

(@course_convo, 'Greetings & Farewells', 'Switch between formal and casual hellos, goodbyes, and small talk starters.', 1, 10, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://source.unsplash.com/featured/?greetings,english'),
(@course_convo, 'Shopping Dialogues', 'Ask for sizes, compare prices, and confirm purchases politely.', 2, 14, NULL, 'https://source.unsplash.com/featured/?shopping,english'),
(@course_convo, 'Ordering Food & Drinks', 'Order confidently, customize meals, and handle bills with ease.', 3, 12, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://source.unsplash.com/featured/?restaurant,english'),
(@course_convo, 'Asking for Directions', 'Follow and give directions using landmarks, turns, and transit verbs.', 4, 9, NULL, 'https://source.unsplash.com/featured/?directions,english'),
(@course_convo, 'Phone & Video Calls', 'Answer, transfer, and close calls professionally and casually.', 5, 13, NULL, 'https://source.unsplash.com/featured/?phonecall,english'),
(@course_convo, 'Invitations & Plans', 'Invite friends, accept gracefully, and reschedule without stress.', 6, 10, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://source.unsplash.com/featured/?friendship,english'),

(@course_grammar, 'Present Tense Mastery', 'Compare present simple vs. continuous with guided drills and story prompts.', 1, 15, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://source.unsplash.com/featured/?grammar,present'),
(@course_grammar, 'Past Tense Stories', 'Retell trips and news updates using regular + irregular verbs correctly.', 2, 15, NULL, 'https://source.unsplash.com/featured/?grammar,past'),
(@course_grammar, 'Future Plans & Predictions', 'Use “will”, “going to”, and present continuous to plan next week.', 3, 12, 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'https://source.unsplash.com/featured/?future,english'),
(@course_grammar, 'Articles Made Simple', 'Decide between a, an, the, or zero article with bite-sized tasks.', 4, 10, NULL, 'https://source.unsplash.com/featured/?articles,english')
ON DUPLICATE KEY UPDATE content = VALUES(content), video_url = VALUES(video_url), image_url = VALUES(image_url);

INSERT INTO quizzes (lesson_id, course_id, question, option_a, option_b, option_c, option_d, correct_answer, points, image_url) VALUES
((SELECT id FROM lessons WHERE course_id = @course_beginner AND order_index = 1 LIMIT 1), @course_beginner, 'What is the natural greeting before noon?', 'Good night', 'Good morning', 'Good evening', 'See you', 'B', 10, 'https://source.unsplash.com/featured/?greeting'),
((SELECT id FROM lessons WHERE course_id = @course_beginner AND order_index = 2 LIMIT 1), @course_beginner, 'Which number follows nine?', 'Eight', 'Ten', 'Seven', 'One', 'B', 10, 'https://source.unsplash.com/featured/?numbers'),
((SELECT id FROM lessons WHERE course_id = @course_beginner AND order_index = 3 LIMIT 1), @course_beginner, 'Red + blue creates which color?', 'Green', 'Purple', 'Yellow', 'Pink', 'B', 10, 'https://source.unsplash.com/featured/?colors'),

((SELECT id FROM lessons WHERE course_id = @course_convo AND order_index = 1 LIMIT 1), @course_convo, 'Pick the most formal farewell.', 'Catch you later', 'Bye', 'See ya', 'Goodbye', 'D', 10, 'https://source.unsplash.com/featured/?farewell'),
((SELECT id FROM lessons WHERE course_id = @course_convo AND order_index = 2 LIMIT 1), @course_convo, 'How do you ask for price politely?', 'Where is it?', 'How much does this cost?', 'Give me discount', 'Is it good?', 'B', 10, 'https://source.unsplash.com/featured/?shopping'),
((SELECT id FROM lessons WHERE course_id = @course_convo AND order_index = 3 LIMIT 1), @course_convo, 'Choose the polite order phrase.', 'Food please', 'I want food', 'I’d like to order...', 'Bring me food', 'C', 10, 'https://source.unsplash.com/featured/?food'),
((SELECT id FROM lessons WHERE course_id = @course_convo AND order_index = 4 LIMIT 1), @course_convo, 'Best question for directions?', 'Where am I?', 'Turn left? ', 'Where is the nearest station?', 'Who are you?', 'C', 10, 'https://source.unsplash.com/featured/?map'),

((SELECT id FROM lessons WHERE course_id = @course_grammar AND order_index = 1 LIMIT 1), @course_grammar, 'Which sentence is present continuous?', 'I cook dinner', 'I am cooking dinner', 'I cooked dinner', 'I will cook dinner', 'B', 10, 'https://source.unsplash.com/featured/?grammar'),
((SELECT id FROM lessons WHERE course_id = @course_grammar AND order_index = 2 LIMIT 1), @course_grammar, 'Past tense of “go” is...?', 'Goed', 'Went', 'Gone', 'Go', 'B', 10, 'https://source.unsplash.com/featured/?study'),
((SELECT id FROM lessons WHERE course_id = @course_grammar AND order_index = 3 LIMIT 1), @course_grammar, 'Pick the correct article: ___ apple a day.', 'A', 'An', 'The', 'No article', 'B', 10, 'https://source.unsplash.com/featured/?apple,english')
ON DUPLICATE KEY UPDATE question = VALUES(question), points = VALUES(points), image_url = VALUES(image_url);

