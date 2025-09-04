
-- Transactions Table (visible in volunteer and admin dashboards)
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    charity_id INT NOT NULL,
    user_email VARCHAR(100),
    amount DECIMAL(10,2),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    screenshot VARCHAR(255),
    FOREIGN KEY (charity_id) REFERENCES charities(id) ON DELETE CASCADE
);