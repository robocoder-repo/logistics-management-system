-- Create the companies table
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Create the stocks table
CREATE TABLE stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    barcode VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    location VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Create the outbound table
CREATE TABLE outbound (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    stock_id INT NOT NULL,
    quantity INT NOT NULL,
    delivery_date DATE NOT NULL,
    delivery_time TIME NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    delivery_address TEXT NOT NULL,
    status ENUM('pending', 'in_transit', 'delivered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (stock_id) REFERENCES stocks(id)
);

-- Create the inbound table
CREATE TABLE inbound (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    stock_id INT NOT NULL,
    quantity INT NOT NULL,
    arrival_date DATE NOT NULL,
    arrival_time TIME NOT NULL,
    supplier VARCHAR(255) NOT NULL,
    status ENUM('pending', 'received') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (stock_id) REFERENCES stocks(id)
);
