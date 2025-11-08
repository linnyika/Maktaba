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

-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
NEW SAMPLE DATA 

INSERT INTO cart (user_id, book_id, quantity, added_at) VALUES
(1, 3, 2, NOW()),   -- Admin testing book 'To Kill a Mockingbird'
(1, 7, 1, NOW()),   -- 'Harry Potter and the Sorcerer’s Stone'
(1, 10, 1, NOW()),  -- 'Brave New World'
(1, 12, 1, NOW());  -- 'The Da Vinci Code'

2. orders
INSERT INTO orders (user_id, shipping_address, total_amount, order_status, payment_status, mpesa_receipt)
VALUES
(1, '123 Library Lane, Nairobi, Kenya', 35.98, 'Delivered', 'Paid', 'MPESA123ABC'),
(1, '456 River Road, Nakuru, Kenya', 22.50, 'Shipped', 'Paid', 'MPESA456DEF'),
(1, '789 Hilltop Avenue, Mombasa, Kenya', 18.99, 'Pending', 'Pending', NULL),
(1, '101 Garden Estate, Kisumu, Kenya', 27.79, 'Delivered', 'Paid', 'MPESA789GHI');

3. order_items
INSERT INTO order_items (order_id, book_id, quantity, price) VALUES
(1, 3, 1, 14.50),   -- To Kill a Mockingbird
(1, 10, 1, 12.80),  -- Brave New World
(2, 11, 1, 22.50),  -- The Fellowship
(3, 7, 1, 18.99),   -- Harry Potter
(4, 5, 1, 10.25),   -- Animal Farm
(4, 9, 1, 9.99);    -- The Alchemist

4. payments
INSERT INTO payments (order_id, payment_method, payment_status, amount, mpesa_phone, mpesa_transaction_id, mpesa_receipt_number, result_code, result_desc)
VALUES
(1, 'Mpesa', 'Paid', 35.98, '+254712345678', 'TXN001', 'MPESA123ABC', 0, 'Completed successfully'),
(2, 'Mpesa', 'Paid', 22.50, '+254701112233', 'TXN002', 'MPESA456DEF', 0, 'Completed successfully'),
(3, 'Mpesa', 'Pending', 18.99, '+254722334455', 'TXN003', NULL, 1, 'Awaiting confirmation'),
(4, 'Mpesa', 'Paid', 20.24, '+254733445566', 'TXN004', 'MPESA789GHI', 0, 'Completed successfully');

5. shipping
INSERT INTO shipping (order_id, carrier, tracking_number, shipped_date, estimated_delivery, actual_delivery, status)
VALUES
(1, 'DHL', 'DHLKEN12345', '2025-11-01', '2025-11-03', '2025-11-03', 'Delivered'),
(2, 'Posta Kenya', 'PKEN56789', '2025-11-02', '2025-11-05', NULL, 'Shipped'),
(3, NULL, NULL, NULL, NULL, NULL, 'Pending'),
(4, 'Fargo Courier', 'FARGO99887', '2025-11-03', '2025-11-06', '2025-11-05', 'Delivered');

6. reservations
INSERT INTO reservations (user_id, book_id, pickup_date, return_date, status, payment_status, notes)
VALUES
(1, 2, '2025-11-05', '2025-11-10', 'Approved', 'Paid', 'Reserved for literature workshop'),
(1, 4, '2025-11-06', '2025-11-12', 'Pending', 'Unpaid', 'Awaiting approval'),
(1, 6, '2025-11-07', '2025-11-13', 'Cancelled', 'Unpaid', 'User cancelled reservation'),
(1, 13, '2025-11-08', '2025-11-15', 'Approved', 'Paid', 'For book club review');

7. moodle_sync
INSERT INTO moodle_sync (book_id, moodle_course_id, sync_status, notes)
VALUES
(1, 'LIT201', 'Success', 'Book linked successfully with Moodle course LIT201'),
(3, 'LIT102', 'Success', 'Synchronized with Moodle course LIT102'),
(5, 'POL102', 'Pending', 'Awaiting Moodle API response'),
(7, 'FAN102', 'Success', 'Successfully linked with Fantasy module'),
(10, 'SOC101', 'Failed', 'Moodle API timeout – retry scheduled');

8. logs
INSERT INTO logs (user_id, action, module, description)
VALUES
(1, 'LOGIN', 'Auth', 'Admin logged in successfully'),
(1, 'VIEW', 'Books', 'Viewed details of book "1984"'),
(1, 'UPDATE', 'Orders', 'Updated order #2 status to Shipped'),
(1, 'DELETE', 'Reservations', 'Cancelled reservation #3'),
(1, 'EXPORT', 'Reports', 'Generated monthly sales report');

9. system_logs
INSERT INTO system_logs (user_id, action, description, ip_address)
VALUES
(1, 'CREATE_ORDER', 'Created new order #3 for user 1', '192.168.0.2'),
(1, 'UPDATE_BOOK', 'Adjusted stock levels for book_id 7', '192.168.0.2'),
(1, 'PROCESS_PAYMENT', 'Processed M-Pesa payment TXN004 successfully', '192.168.0.2'),
(1, 'LOGIN', 'Admin logged in at 2025-11-07 09:30', '192.168.0.2'),
(1, 'GENERATE_REPORT', 'Generated analytics dashboard summary', '192.168.0.2');