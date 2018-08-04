PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

CREATE TABLE AggregationLink (
  aggregation INTEGER  REFERENCES User NOT NULL,
  type        CHAR (1) NOT NULL,
  component   INTEGER  NOT NULL
);

CREATE TABLE Icon (
  class CHAR (1)     NOT NULL,
  id    VARCHAR (20) PRIMARY KEY UNIQUE NOT NULL,
  type  CHAR (1)     NOT NULL,
  value TEXT
);

CREATE TABLE Operation (
  id          INTEGER      PRIMARY KEY AUTOINCREMENT UNIQUE NOT NULL,
  date        DATE         NOT NULL,
  category    VARCHAR (5)  NOT NULL,
  description TEXT         NOT NULL,
  value       NUMERIC      NOT NULL DEFAULT (0),
  bucket      INTEGER      REFERENCES Bucket (id) NOT NULL,
  owner       VARCHAR (32) NOT NULL REFERENCES User (login),
  strikeout   BOOLEAN      NOT NULL DEFAULT (0)
);

CREATE TABLE User (
  login    VARCHAR (32)  PRIMARY KEY UNIQUE NOT NULL,
  passhash VARCHAR (255) NOT NULL,
  name     VARCHAR (80)  NOT NULL,
  language VARCHAR (20)  REFERENCES Language (id) NOT NULL,
  currency VARCHAR (3)   REFERENCES Currency (id) NOT NULL
);

CREATE TABLE Configuration (
  user  VARCHAR (32) REFERENCES User (login),
  "key" TEXT         NOT NULL,
  value TEXT
);

CREATE TABLE Currency (
  id     VARCHAR (3)  PRIMARY KEY UNIQUE,
  name   VARCHAR (30),
  symbol VARCHAR (10),
  "left" BOOLEAN
);

CREATE TABLE Language (
  id         VARCHAR (20) PRIMARY KEY UNIQUE,
  name       VARCHAR (30),
  local_name VARCHAR (30),
  decimal    CHAR (1)
);

CREATE TABLE Aggregation (
  id       INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
  owner    VARCHAR (32) REFERENCES User (login) NOT NULL,
  name     VARCHAR (120),
  currency VARCHAR (10) REFERENCES Currency (id) NOT NULL,
  acl      TEXT
);

CREATE TABLE Bucket (
  id       INTEGER       PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
  name     VARCHAR (120) NOT NULL,
  currency VARCHAR (10)  REFERENCES Currency (id) NOT NULL,
  owner    VARCHAR (32)  NOT NULL REFERENCES User (login),
  acl      TEXT,
  parent   INTEGER       REFERENCES Bucket (id)
);

CREATE TABLE Category (
  id          VARCHAR (5)  NOT NULL,
  language    VARCHAR (20) REFERENCES Language (id) NOT NULL,
  description VARCHAR (120)
);

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
