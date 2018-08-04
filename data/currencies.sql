PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

INSERT INTO Currency (id, name, symbol, "left") VALUES ('PLN', 'polski złoty', ' zł', 0);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
