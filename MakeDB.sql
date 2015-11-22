DROP DATABASE IF EXISTS FoundryDB;

CREATE DATABASE IF NOT EXISTS FoundryDB;

USE 'FoundryDB';

CREATE TABLE IF NOT EXISTS users(
id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
email VARCHAR(255) NOT NULL UNIQUE,
pass VARCHAR(255) NOT NULL,
first VARCHAR(45) NOT NULL,
last VARCHAR(45) NOT NULL,
url VARCHAR(100) DEFAULT 'https://acrobatusers.com/assets/images/template/author_generic.jpg',
area VARCHAR(5) NOT NULL,
num VARCHAR(10) NOT NULL,
salt VARCHAR(50) NOT NULL,
location VARCHAR(100) DEFAULT "",
rating FLOAT(2,1) DEFAULT 0.0,
status ENUM("active", "busy", "offline") DEFAULT "offline",
updateTime DATETIME NOT NULL DEFAULT NOW(),
num_ratings INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS topics(
id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(100) NOT NULL UNIQUE,
url VARCHAR(100) NOT NULL);

CREATE TABLE IF NOT EXISTS subs(
id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
tid INT NOT NULL,
name VARCHAR(100) NOT NULL,
url VARCHAR(100) NOT NULL,
FOREIGN KEY (tid) REFERENCES topics(id));

-- Max price per hour is $90.90
CREATE TABLE IF NOT EXISTS tutors(
uid INT NOT NULL,
tid INT NOT NULL,
sid INT NOT NULL,
price FLOAT(4,2) DEFAULT 00.00,
travel ENUM("driving", "walking", "bicycling") DEFAULT "driving",
PRIMARY KEY (uid,tid,sid),
FOREIGN KEY (uid) REFERENCES users(id),
FOREIGN KEY (tid) REFERENCES topics(id),
FOREIGN KEY (sid) REFERENCES subs(id)
);

-- Max hours for one session is 11
-- pay per quarter of an hour (round up)
CREATE TABLE IF NOT EXISTS session(
id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
sid INT NOT NULL,
tid INT NOT NULL,
tut_id INT NOT NULL,
stud_id INT NOT NULL,
start TIMESTAMP,
end TIMESTAMP,
sesh_date DATE DEFAULT CURDATE(),
tot_time float (4,2) DEFAULT 00.00,
price FLOAT(4,2) DEFAULT 00.00,
tot_price FLOAT(5,2) DEFAULT 000.00,
FOREIGN KEY (tut_id) REFERENCES users(id),
FOREIGN KEY (stud_id) REFERENCES users(id),
FOREIGN KEY (tid) REFERENCES topics(id),
FOREIGN KEY (sid) REFERENCES subs(id),
FOREIGN KEY (price) REFERENCES tutors(price)
);

CREATE TABLE IF NOT EXISTS ratings(
	uid INT NOT NULL,
	sesh_id INT NOT NULL,
	stud_id INT NOt NULL,
	rate INT NOT NULL,
	comment VARCHAR(150) DEFAULT "",
	time TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY (uid, sesh_id),
	FOREIGN KEY (uid) REFERENCES users(id),
	FOREIGN KEY (stud_id) REFERENCES users(id),
	FOREIGN KEY (sesh_id) REFERENCES session(id)
);