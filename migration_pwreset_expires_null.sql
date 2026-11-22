-- Run this ONCE in phpMyAdmin BEFORE you merge.
-- 0) Use the lenient mode for this session, so that the existing '0000-00-00' values do not stop the change.
SET SESSION sql_mode = '';
-- 1) Allow NULL in users.PWreset_expires_at (reset.php sets it to NULL after a reset).
ALTER TABLE users MODIFY PWreset_expires_at DATETIME NULL DEFAULT NULL;
-- 2) Replace the stored "0000-00-00 00:00:00" values: no open reset -> NULL.
UPDATE users SET PWreset_expires_at = NULL WHERE PWreset_token_hash IS NULL;
-- 3) Check: the result must be 0.
SELECT COUNT(*) AS zero_dates FROM users WHERE PWreset_expires_at = '0000-00-00 00:00:00';
