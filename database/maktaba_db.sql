CREATE DATABASE IF NOT EXISTS maktaba_db;
USE maktaba_db;

CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profession VARCHAR(50),
    preferences TEXT,
    otp_code VARCHAR(10),
    otp_expiry DATETIME,
    is_verified TINYINT(1) DEFAULT 0,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE publishers (
    publisher_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT,
    contact_number VARCHAR(20)
);

CREATE TABLE books (
    isbn VARCHAR(20) PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100),
    publisher_id INT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    subject_area VARCHAR(50),
    year_of_publication YEAR,
    description TEXT,
    FOREIGN KEY (publisher_id) REFERENCES publishers(publisher_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    isbn VARCHAR(20),
    reserved_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending','Collected','Cancelled') DEFAULT 'Pending',
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES books(isbn)
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shipping_address TEXT,
    total_amount DECIMAL(10,2),
    order_status ENUM('Pending','Confirmed','Shipped','Delivered','Cancelled','Returned') DEFAULT 'Pending',
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
);

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


CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method ENUM('Card','PayPal','Mpesa','StoreCredit') NOT NULL,
    payment_status ENUM('Pending','Completed','Failed','Refunded') DEFAULT 'Pending',
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
);

CREATE TABLE shipping (
    shipping_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    carrier VARCHAR(50),
    tracking_number VARCHAR(50),
    shipped_date DATE,
    delivery_date DATE,
    status ENUM('Pending','In Transit','Delivered','Returned') DEFAULT 'Pending',
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
);

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    isbn VARCHAR(20),
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE,
    FOREIGN KEY (isbn) REFERENCES books(isbn)
);

CREATE TABLE discounts (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    discount_percent DECIMAL(5,2),
    start_date DATE,
    end_date DATE
);
