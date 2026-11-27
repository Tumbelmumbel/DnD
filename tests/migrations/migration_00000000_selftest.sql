-- Self-test for migrate.php (GitHub workflow only, never on the live database).
-- The workflow copies this file next to migrate.php, runs migrate.php and checks the result.
-- It tests: several statements, a ; and -- inside a string, and a comment with a ;
CREATE TABLE ci_selftest (
  id   int unsigned NOT NULL AUTO_INCREMENT,
  note varchar(100) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* a comment; with a delimiter */
INSERT INTO ci_selftest (note) VALUES ('first; with -- signs'), ("second's row");
INSERT INTO ci_selftest (note) VALUES ('third'); -- end; of line
