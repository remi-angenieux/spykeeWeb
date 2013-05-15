CREATE TABLE configs (
name TEXT  NOT NULL UNIQUE PRIMARY KEY,
section TEXT NOT NULL,
data TEXT NOT NULL
);

CREATE TABLE members (
id SERIAL NOT NULL PRIMARY KEY UNIQUE,
pseudo TEXT NOT NULL UNIQUE,
password TEXT NOT NULL,
email TEXT NOT NULL
);

CREATE TABLE admin (
RefMember INTEGER NOT NULL UNIQUE PRIMARY KEY REFERENCES members ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE chat (
id SERIAL NOT NULL UNIQUE PRIMARY KEY,
RefMember INTEGER NOT NULL DEFAULT -1 REFERENCES members ON UPDATE CASCADE ON DELETE SET DEFAULT,
message TEXT NOT NULL,
timestamp INTEGER NOT NULL
);

CREATE TABLE queue (
RefMember INTEGER NOT NULL UNIQUE PRIMARY KEY REFERENCES members ON UPDATE CASCADE ON DELETE CASCADE,
timestamp INTEGER NOT NULL
);

CREATE TABLE robots (
id SERIAL UNIQUE NOT NULL PRIMARY KEY,
name TEXT UNIQUE NOT NULL,
ctrIp CIDR NOT NULL,
ctrPort INTEGER NOT NULL,
locked BOOLEAN DEFAULT true
/*ip CIDR UNIQUE NOT NULL
mac MACADDR UNIQUE NOT NULL,*/
);

CREATE TABLE games (
id SERIAL NOT NULL UNIQUE PRIMARY KEY,
RefMember INTEGER NOT NULL DEFAULT -1 REFERENCES members ON UPDATE CASCADE ON DELETE SET DEFAULT,
RefRobot INTEGER NOT NULL REFERENCES robots ON UPDATE CASCADE ON DELETE CASCADE,
startTime INTEGER NOT NULL,
lastInput INTEGER DEFAULT current_timestamp
);

CREATE TABLE gameshistory (
id SERIAL NOT NULL UNIQUE PRIMARY KEY,
RefMember INTEGER NOT NULL DEFAULT -1 REFERENCES members ON UPDATE CASCADE ON DELETE SET DEFAULT,
RefRobot INTEGER NOT NULL REFERENCES robots ON UPDATE CASCADE ON DELETE CASCADE,
duration INTEGER NOT NULL
);