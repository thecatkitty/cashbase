PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

INSERT INTO Language (id, name, local_name, decimal) VALUES ('pl-PL', 'Polish (Poland)', 'polski (Polska)', ',');

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
