-- Run this ONCE in phpMyAdmin BEFORE you merge.
-- Adds the language of the interface to each user. English (en) is the base language.
ALTER TABLE users ADD COLUMN language CHAR(2) NOT NULL DEFAULT 'en';
-- Check: the result must show the column "language".
SHOW COLUMNS FROM users LIKE 'language';
