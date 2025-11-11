-- Enhanced database schema for complete bookstore system
CREATE DATABASE IF NOT EXISTS maktaba;
USE maktaba;

-- Users table (replaces customers, now includes admins)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    preferences TEXT,
    user_role ENUM('customer','admin') DEFAULT 'customer',
    otp_code VARCHAR(10),
    otp_expiry DATETIME,
    is_verified TINYINT(1) DEFAULT 0,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
    COLUMN avatar VARCHAR(255) DEFAULT NULL;
);

-- Publishers tableI
CREATE TABLE publishers (
    publisher_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20),
    email VARCHAR(100)
);

--  Books table
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100),
    publisher_id INT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    reserved_stock INT DEFAULT 0,
    genre VARCHAR(50),
    year_of_publication YEAR,
    description TEXT,
    book_cover VARCHAR(255),
    moodle_course_id VARCHAR(50), -- For Moodle integration
    is_available TINYINT(1) DEFAULT 1,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publisher_id) REFERENCES publishers(publisher_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- Orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shipping_address TEXT,
    total_amount DECIMAL(10,2),
    order_status ENUM('Pending','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    payment_status ENUM('Pending','Paid','Unpaid','Refunded') DEFAULT 'Pending',
    mpesa_receipt VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    book_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id)
);

-- Enhanced Payments table for M-Pesa
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method ENUM('Mpesa') NOT NULL,
    payment_status ENUM('Pending','Paid','Unpaid','Refunded') DEFAULT 'Pending',
    amount DECIMAL(10,2) NOT NULL,
    mpesa_phone VARCHAR(20),
    mpesa_transaction_id VARCHAR(50),
    mpesa_receipt_number VARCHAR(50),
    result_code INT,
    result_desc VARCHAR(255),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
);

-- Shipping table
CREATE TABLE shipping (
    shipping_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    carrier VARCHAR(50),
    tracking_number VARCHAR(50),
    shipped_date DATE,
    estimated_delivery DATE,
    actual_delivery DATE,
    status ENUM('Pending','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_approved TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    ADD COLUMN comment TEXT
);
-- Moodle integration table
CREATE TABLE moodle_sync (
    sync_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    moodle_course_id VARCHAR(50),
    sync_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sync_status ENUM('Success','Failed','Pending'),
    notes TEXT,
    FOREIGN KEY (book_id) REFERENCES books(book_id)
);

-- System logs table
CREATE TABLE system_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    description TEXT,
    ip_address VARCHAR(45),
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);



CREATE TABLE IF NOT EXISTS reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pickup_date DATE NULL,
    return_date DATE NULL,

    payment_status ENUM('Unpaid','Paid') DEFAULT 'Unpaid',
    notes TEXT NULL,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    COLUMN status ENUM('Pending','Approved','Cancelled') DEFAULT 'Pending'
);

 
 CREATE TABLE logs (
  log_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(100),
  module VARCHAR(100),
  description TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);
<<<<<<< HEAD

CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(50),
    details TEXT
);

ALTER TABLE users 
ADD COLUMN avatar VARCHAR(255) DEFAULT NULL;

ALTER TABLE reviews 
ADD COLUMN comment TEXT;

ALTER TABLE logs 
MODIFY COLUMN timestamp DATETIME DEFAULT CURRENT_TIMESTAMP;

Also add this ALTER TABLE logs 
MODIFY COLUMN user_id INT NULL;
=======
>>>>>>> 01d33dc84fd81d92c468b125c2b84e5e7d1486d0

 CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);
