CREATE DATABASE IF NOT EXISTS Products;
USE Products;

DROP TABLE IF EXISTS courses;

CREATE TABLE courses (
    VID BIGINT PRIMARY KEY,
    ID BIGINT,
    Title VARCHAR(128),
    Price VARCHAR(128),
    Option1 VARCHAR(128),
    Option2 VARCHAR(128),
    Option3 VARCHAR(128),
    Barcode VARCHAR(32),
    Last_Update VARCHAR(64)
);

INSERT INTO courses (VID, ID, Title, Price, Option1, Option2, Option3, Barcode, Last_Update)

VALUES
    (39773377265797, 4988608315525, "Globemaster", "16.99", "Globemaster", null, null, "0900267001", "2023-10-27T11:22:18-04:00")