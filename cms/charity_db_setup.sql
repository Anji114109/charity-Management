-- User Registration Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Volunteer Registration Table (updated columns)
CREATE TABLE volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Charities Table (updated columns)
CREATE TABLE charities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    charity_name VARCHAR(100) NOT NULL,
    charity_address TEXT,
    charity_phone_number VARCHAR(20),
    charity_bank_number VARCHAR(30)
);

-- Image Upload Table (for users uploading photo per charity)
CREATE TABLE charity_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    charity_id INT NOT NULL,
    image_path VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (charity_id) REFERENCES charities(id) ON DELETE CASCADE
);


