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
    FOREIGN KEY (book_id) REFERENCES books(book_id)
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
    status ENUM('Pending','Confirmed','Cancelled','Completed') DEFAULT 'Pending',
    payment_status ENUM('Unpaid','Paid') DEFAULT 'Unpaid',
    notes TEXT NULL,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (full_name, email, password_hash, user_role, is_verified) 
VALUES ('Admin Maktaba', 'admin@maktaba.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- Password: password

-- Insert 6 publishers
INSERT INTO publishers (name, address, contact_number, email) VALUES
('Penguin Random House', '1745 Broadway, New York, NY 10019', '+1-212-782-9000', 'info@penguinrandomhouse.com'),
('HarperCollins', '195 Broadway, New York, NY 10007', '+1-212-207-7000', 'contact@harpercollins.com'),
('Macmillan Publishers', '120 Broadway, New York, NY 10271', '+1-646-307-5151', 'support@macmillan.com'),
('Simon & Schuster', '1230 Avenue of the Americas, New York, NY 10020', '+1-212-698-7000', 'info@simonandschuster.com'),
('Houghton Mifflin Harcourt', '125 High Street, Boston, MA 02110', '+1-617-351-5000', 'info@hmhco.com'),
('Bloomsbury Publishing', '50 Bedford Square, London WC1B 3DP', '+44-20-7631-5600', 'contact@bloomsbury.com');

-- Insert 15 books with publication years from 1901 to 2023
INSERT INTO books (title, author, publisher_id, price, stock_quantity, reserved_stock, genre, year_of_publication, description, book_cover, moodle_course_id, is_available) VALUES
('The Call of the Wild', 'Jack London', 1, 9.99, 20, 2, 'Adventure', 1903, 'A story about a domesticated dog''s transformation to the wild.', 'call_wild.jpg', 'LIT201', 1),
('The Great Gatsby', 'F. Scott Fitzgerald', 1, 12.99, 25, 3, 'Classic', 1925, 'A story of wealth, love, and the American Dream in the Jazz Age.', 'great_gatsby.jpg', 'LIT101', 1),
('To Kill a Mockingbird', 'Harper Lee', 2, 14.50, 18, 2, 'Fiction', 1960, 'A gripping story of racial injustice and childhood innocence.', 'mockingbird.jpg', 'LIT102', 1),
('1984', 'George Orwell', 3, 11.75, 30, 5, 'Dystopian', 1949, 'A dystopian novel about totalitarian control and surveillance.', '1984.jpg', 'POL101', 1),
('Animal Farm', 'George Orwell', 3, 10.25, 22, 1, 'Political Satire', 1945, 'A satirical allegory about Soviet totalitarianism.', 'animal_farm.jpg', 'POL102', 1),
('The Hobbit', 'J.R.R. Tolkien', 4, 16.25, 15, 4, 'Fantasy', 1937, 'A fantasy novel about the adventures of hobbit Bilbo Baggins.', 'hobbit.jpg', 'FAN101', 1),
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', 6, 18.99, 35, 8, 'Fantasy', 1997, 'The first novel in the Harry Potter series about a young wizard.', 'harry_potter1.jpg', 'FAN102', 1),
('The Catcher in the Rye', 'J.D. Salinger', 5, 13.45, 12, 0, 'Fiction', 1951, 'A novel about teenage rebellion and alienation in New York.', 'catcher_rye.jpg', 'LIT104', 1),
('The Alchemist', 'Paulo Coelho', 2, 9.99, 28, 3, 'Adventure', 1988, 'A philosophical book about following your dreams.', 'alchemist.jpg', 'PHI101', 1),
('Brave New World', 'Aldous Huxley', 3, 12.80, 20, 2, 'Dystopian', 1932, 'A dystopian novel about a society controlled by technology.', 'brave_new_world.jpg', 'SOC101', 1),
('The Lord of the Rings: Fellowship', 'J.R.R. Tolkien', 4, 22.50, 10, 6, 'Fantasy', 1954, 'The first volume of the epic fantasy trilogy.', 'lotr_fellowship.jpg', 'FAN103', 1),
('The Da Vinci Code', 'Dan Brown', 1, 15.99, 25, 4, 'Mystery', 2003, 'A mystery thriller about a conspiracy within the Catholic Church.', 'davinci_code.jpg', 'THR101', 1),
('The Hunger Games', 'Suzanne Collins', 2, 14.99, 30, 7, 'Dystopian', 2008, 'A dystopian novel about a televised fight to the death.', 'hunger_games.jpg', 'DYS101', 1),
('The Girl on the Train', 'Paula Hawkins', 5, 13.25, 18, 3, 'Thriller', 2015, 'A psychological thriller about a woman who becomes involved in a mystery.', 'girl_train.jpg', 'THR102', 1),
('Project Hail Mary', 'Andy Weir', 1, 16.99, 22, 5, 'Science Fiction', 2021, 'A sci-fi novel about an astronaut who wakes up with amnesia.', 'hail_mary.jpg', 'SCI101', 1);