CREATE TABLE charities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    charity_name VARCHAR(255) NOT NULL,
    charity_address VARCHAR(255) NOT NULL,
    charity_phone_number VARCHAR(20) NOT NULL,
    charity_bank_number VARCHAR(50) NOT NULL,
    registered_by VARCHAR(255) NOT NULL
);