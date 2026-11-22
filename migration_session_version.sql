-- Run this ONCE in your database (for example in phpMyAdmin) BEFORE you deploy this change.
-- It adds a counter that increases on each password reset.
-- When it changes, all existing sessions of that user end.
ALTER TABLE users ADD COLUMN session_version INT NOT NULL DEFAULT 0;
