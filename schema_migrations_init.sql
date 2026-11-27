-- One-time start of the migration tracking (run by: php migrate.php --init).
-- migrate.php runs this file only if the table schema_migrations does not exist.
-- It records the 17 migrations that ran by hand before the tracking started.
CREATE TABLE schema_migrations (
  filename   VARCHAR(190) NOT NULL,
  applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (filename)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations (filename) VALUES
 ('migration_base_calculations.sql'), ('migration_calculations.sql'),
 ('migration_class_data_fixes.sql'), ('migration_cleanup_lookup_names.sql'),
 ('migration_content_translation.sql'), ('migration_game_component_library_id.sql'),
 ('migration_item_properties.sql'), ('migration_items.sql'),
 ('migration_lookup_codes.sql'), ('migration_passive_skills.sql'),
 ('migration_pwreset_expires_null.sql'), ('migration_rate_limit.sql'),
 ('migration_session_version.sql'), ('migration_test_items.sql'),
 ('migration_users_language.sql'), ('migration_weapon_rules.sql'),
 ('migration_weapons.sql');
