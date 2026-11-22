-- ALREADY RUN on the live database on 2026-11-22 (with approval of the owner).
-- This file is the record of the change.
-- 1) Add a primary key.
ALTER TABLE Game_component_library ADD COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
-- 2) Delete the second copy of the identical row "Stangenwaffenmeister".
DELETE FROM Game_component_library WHERE id = 2;
