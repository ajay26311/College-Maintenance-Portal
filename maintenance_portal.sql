-- Create database
CREATE DATABASE maintenance_portal;
USE maintenance_portal;

-- Users table with primary key user_id
CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100),
    role VARCHAR(20),
    email VARCHAR(100),
    password VARCHAR(255),
    roll_no VARCHAR(20)
);

-- Complaints table with foreign key reference to user_id in Users table
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(50),
    type VARCHAR(100),
    department VARCHAR(100),
    description TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


ALTER TABLE complaints 
ADD COLUMN status ENUM('Pending', 'Completed', 'In Working') DEFAULT 'Pending';

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id VARCHAR(50),
    receiver_id VARCHAR(50),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);