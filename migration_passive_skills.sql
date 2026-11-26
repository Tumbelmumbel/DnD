-- migration_passive_skills.sql
-- Adds the base rules for the passive skills Perception, Insight and Investigation (10 + skill).
-- Run this migration BEFORE you merge the pull request.

INSERT INTO calculation_templates (source_type, sort_order, target, operator, value_num, value_ref, condition_json) VALUES
  ('base', 500, 'passive.perception',    'set', 10,   NULL,                  NULL),
  ('base', 501, 'passive.perception',    'add', NULL, 'skill.perception',    NULL),
  ('base', 500, 'passive.insight',       'set', 10,   NULL,                  NULL),
  ('base', 501, 'passive.insight',       'add', NULL, 'skill.insight',       NULL),
  ('base', 500, 'passive.investigation', 'set', 10,   NULL,                  NULL),
  ('base', 501, 'passive.investigation', 'add', NULL, 'skill.investigation', NULL);
