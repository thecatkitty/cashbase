PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

CREATE TABLE `ikt`
(
`id` varchar(6) not null default '00-00',
`opis` mediumtext null default null,
primary key (`id`)
);

CREATE TABLE transakcja (id INTEGER, data DATE DEFAULT NULL, kwota DECIMAL (10, 2) DEFAULT NULL, ikt VARCHAR (6) DEFAULT NULL, opis MEDIUMTEXT DEFAULT NULL, dokument VARCHAR (20) DEFAULT NULL, usun BOOLEAN DEFAULT FALSE, PRIMARY KEY (id));

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
