-- migration_rate_limit.sql
-- Adds the table rate_limit for the limits of sign-in, registration, new verification links and password resets.
-- Run this migration BEFORE you merge the pull request.

CREATE TABLE rate_limit (
  action       varchar(30) NOT NULL,
  subject_hash char(64)    NOT NULL,
  created_at   datetime    NOT NULL,
  KEY idx_rate_limit (action, subject_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Rollback: DROP TABLE rate_limit;
