-- Friends App - Database Initialization
-- Run once to create the schema and seed data.
-- This file is auto-executed by the MySQL Docker image on first start.

CREATE TABLE IF NOT EXISTS MyGuests (
    id        INT          AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname  VARCHAR(255) NOT NULL
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO MyGuests (firstname, lastname) VALUES
    ('Chandler', 'Bing'),
    ('Rachel',   'Green'),
    ('Monica',   'Geller'),
    ('Dr. Ross', 'Geller'),
    ('Joey',     'Tribbiani Jr.'),
    ('Phoebe',   'Buffay');
