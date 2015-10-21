DROP DATABASE IF EXISTS FoundryDB;

CREATE DATABASE IF NOT EXISTS FoundryDB;

USE 'FoundryDB';

CREATE TABLE IF NOT EXISTS users(
id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
email VARCHAR(255) NOT NULL UNIQUE,
pass VARCHAR(255) NOT NULL,
first VARCHAR(45) NOT NULL,
last VARCHAR(45) NOT NULL,
area VARCHAR(5) NOT NULL,
number VARCHAR(10) NOT NULL,
tutor INT NOT NULL
);