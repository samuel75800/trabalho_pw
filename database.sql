CREATE DATABASE IF NOT EXISTS puppyco
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE puppyco;

CREATE TABLE IF NOT EXISTS users (
id         INT          NOT NULL AUTO_INCREMENT,
username   VARCHAR(50)  NOT NULL UNIQUE,
password   VARCHAR(255) NOT NULL,
created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id)
);

INSERT INTO users (username, password)
SELECT
'admin',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE NOT EXISTS (
SELECT 1 FROM users WHERE username = 'admin'
);

CREATE TABLE IF NOT EXISTS owners (
id         INT          NOT NULL AUTO_INCREMENT,
name       VARCHAR(100) NOT NULL,
email      VARCHAR(100) NOT NULL UNIQUE,
phone      VARCHAR(20)  NOT NULL,
address    VARCHAR(255) DEFAULT NULL,
created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS pets (
id         INT          NOT NULL AUTO_INCREMENT,
owner_id   INT          NOT NULL,
name       VARCHAR(100) NOT NULL,
species    VARCHAR(50)  NOT NULL,
breed      VARCHAR(100) DEFAULT NULL,
birthdate  DATE         DEFAULT NULL,
notes      TEXT         DEFAULT NULL,
created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id),
CONSTRAINT fk_pet_owner
FOREIGN KEY (owner_id) REFERENCES owners(id)
ON DELETE CASCADE
ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS appointments (
id                INT NOT NULL AUTO_INCREMENT,
pet_id            INT NOT NULL,
appointment_date  DATE NOT NULL,
appointment_time  TIME NOT NULL,
service           VARCHAR(100) NOT NULL,
status ENUM('scheduled','completed','cancelled')
NOT NULL DEFAULT 'scheduled',
notes             TEXT DEFAULT NULL,
created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (id),
CONSTRAINT fk_appt_pet
FOREIGN KEY (pet_id) REFERENCES pets(id)
ON DELETE CASCADE
ON UPDATE CASCADE
);
