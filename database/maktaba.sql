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
    address TEXT,
    profession VARCHAR(50),
    preferences TEXT,
    user_role ENUM('customer','admin','librarian') DEFAULT 'customer',
    otp_code VARCHAR(10),
    otp_expiry DATETIME,
    is_verified TINYINT(1) DEFAULT 0,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Publishers table
CREATE TABLE publishers (
    publisher_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20),
    email VARCHAR(100)
);

-- Enhanced Books table
CREATE TABLE books (
    isbn VARCHAR(20) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100),
    publisher_id INT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    reserved_stock INT DEFAULT 0,
    subject_area VARCHAR(50),
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
    order_status ENUM('Pending','Confirmed','Processing','Shipped','Delivered','Cancelled','Returned') DEFAULT 'Pending',
    payment_status ENUM('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
    mpesa_receipt VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- Order items table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    isbn VARCHAR(20),
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES books(isbn)
);

-- Reservations table
CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    isbn VARCHAR(20),
    reserved_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATETIME,
    status ENUM('Active', 'Fulfilled', 'Cancelled', 'Expired') DEFAULT 'Active',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (isbn) REFERENCES books(isbn)
);

-- Enhanced Payments table for M-Pesa
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method ENUM('Card','PayPal','Mpesa','StoreCredit') NOT NULL,
    payment_status ENUM('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
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
    status ENUM('Pending','In Transit','Delivered','Returned') DEFAULT 'Pending',
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
);

-- Reviews table
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    isbn VARCHAR(20),
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_approved TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES books(isbn)
);

-- Discounts table
CREATE TABLE discounts (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    discount_percent DECIMAL(5,2),
    start_date DATE,
    end_date DATE,
    usage_limit INT DEFAULT 100,
    times_used INT DEFAULT 0
);

-- Moodle integration table
CREATE TABLE moodle_sync (
    sync_id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20),
    moodle_course_id VARCHAR(50),
    sync_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sync_status ENUM('Success','Failed','Pending'),
    notes TEXT,
    FOREIGN KEY (isbn) REFERENCES books(isbn)
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

-- Insert default admin user
INSERT INTO users (full_name, email, password_hash, user_role, is_verified) 
VALUES ('System Admin', 'admin@maktaba.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- Password: password