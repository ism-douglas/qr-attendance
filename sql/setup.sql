
CREATE DATABASE IF NOT EXISTS attendance;
USE attendance;

CREATE TABLE staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_no VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE attendance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id INT NOT NULL,
    method ENUM('qr', 'manual') NOT NULL,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id)
);
