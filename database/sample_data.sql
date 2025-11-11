-- Admin + Sample Users
INSERT INTO users (full_name, email, password_hash, user_role, is_verified)
VALUES
('Admin Maktaba', 'admin@maktaba.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
('Alice Kimani', 'alice@example.com', '$2y$10$abcd1234generatedhash...', 'customer', 1),
('Brian Otieno', 'brian@example.com', '$2y$10$efgh5678generatedhash...', 'customer', 1),
('Cynthia Wanjiru', 'cynthia@example.com', '$2y$10$ijkl9012generatedhash...', 'customer', 1),
('David Njoroge', 'david@example.com', '$2y$10$mnop3456generatedhash...', 'customer', 1),
('Eva Mwangi', 'eva@example.com', '$2y$10$qrst7890generatedhash...', 'customer', 1);

-- Publishers
INSERT INTO publishers (name, address, contact_number, email)
VALUES
('Nairobi Books Ltd','Nairobi','0712345678','info@nairobi-books.com'),
('Maktaba Press','Mombasa','0722456789','contact@maktabapress.com'),
('Classic Reads','Kisumu','0733567890','hello@classicreads.com'),
('Sunset Publishing','Eldoret','0744678901','sales@sunsetpub.com'),
('Knowledge House','Nakuru','0755789012','admin@knowledgehouse.com'),
('East African Prints','Nyeri','0766890123','eastafrica@prints.com'),
('Bookwise Publishers','Kakamega','0777901234','info@bookwise.com'),
('Learning Tree','Thika','0788012345','contact@learningtree.com'),
('Jambo Editions','Machakos','0799123456','support@jamboed.com'),
('Writers Hub','Naivasha','0700234567','info@writershub.com');

-- Books
INSERT INTO books (title, author, publisher_id, price, stock_quantity, genre, year_of_publication, description)
VALUES
('The Silent River', 'Grace Ochieng', 1, 950.00, 15, 'Fiction', 2021, 'A deep dive into family and tradition.'),
('Echoes of the Hills', 'John Mwangi', 2, 750.00, 10, 'Drama', 2022, 'Stories inspired by rural Kenya.'),
('Digital Africa', 'Lucy Nduta', 3, 1200.00, 25, 'Technology', 2023, 'Exploring Africa’s tech revolution.'),
('Cooking the Kenyan Way', 'Chef Maina', 4, 650.00, 8, 'Cooking', 2020, 'Delicious recipes from local cuisines.'),
('Wildlife Adventures', 'Peter Otieno', 5, 800.00, 12, 'Adventure', 2019, 'Safari tales and wildlife photography.'),
('Modern JavaScript', 'Ann Karanja', 6, 1300.00, 20, 'Education', 2023, 'Learn JavaScript through Kenyan examples.'),
('Secrets of Success', 'Brian Mutua', 7, 500.00, 18, 'Motivation', 2018, 'Self-improvement for young entrepreneurs.'),
('Mountains Beyond', 'Sarah Wairimu', 8, 900.00, 14, 'Travel', 2021, 'A guide to East African mountains.'),
('The Startup Dream', 'Kevin Ndungu', 9, 1100.00, 11, 'Business', 2022, 'Building startups in Africa.'),
('Herbal Remedies', 'Dr. Achieng', 10, 700.00, 9, 'Health', 2020, 'Traditional herbs and healing practices.');

-- Orders
INSERT INTO orders (user_id, shipping_address, total_amount, order_status, payment_status)
VALUES
(2, 'Nairobi, Kenya', 1750.00, 'Delivered', 'Paid'),
(3, 'Kisumu, Kenya', 1300.00, 'Pending', 'Pending'),
(4, 'Eldoret, Kenya', 950.00, 'Delivered', 'Paid'),
(5, 'Mombasa, Kenya', 2200.00, 'Shipped', 'Paid'),
(6, 'Nakuru, Kenya', 650.00, 'Cancelled', 'Refunded'),
(2, 'Thika, Kenya', 1500.00, 'Delivered', 'Paid'),
(3, 'Naivasha, Kenya', 900.00, 'Shipped', 'Paid'),
(4, 'Nyeri, Kenya', 1300.00, 'Pending', 'Pending'),
(5, 'Machakos, Kenya', 1100.00, 'Delivered', 'Paid'),
(6, 'Nairobi, Kenya', 750.00, 'Delivered', 'Paid');

-- Payments
INSERT INTO payments (order_id, payment_method, payment_status, amount, mpesa_phone, mpesa_transaction_id)
VALUES
(1, 'Mpesa', 'Paid', 1750.00, '0712345678', 'TX123456'),
(2, 'Mpesa', 'Pending', 1300.00, '0722456789', 'TX234567'),
(3, 'Mpesa', 'Paid', 950.00, '0733567890', 'TX345678'),
(4, 'Mpesa', 'Paid', 2200.00, '0744678901', 'TX456789'),
(5, 'Mpesa', 'Refunded', 650.00, '0755789012', 'TX567890'),
(6, 'Mpesa', 'Paid', 1500.00, '0766890123', 'TX678901'),
(7, 'Mpesa', 'Paid', 900.00, '0777901234', 'TX789012'),
(8, 'Mpesa', 'Pending', 1300.00, '0788012345', 'TX890123'),
(9, 'Mpesa', 'Paid', 1100.00, '0799123456', 'TX901234'),
(10, 'Mpesa', 'Paid', 750.00, '0700234567', 'TX012345');

-- Reviews
INSERT INTO reviews (user_id, book_id, rating, comment, is_approved)
VALUES
(2,1,5,'Excellent story!',1),
(3,2,4,'Nice rural setting',1),
(4,3,5,'Very informative!',1),
(5,4,3,'Good recipes',1),
(6,5,4,'Beautiful photography',1),
(2,6,5,'Very helpful for coding',1),
(3,7,4,'Great motivation tips',1),
(4,8,4,'Good travel guide',1),
(5,9,5,'Perfect business book',1),
(6,10,4,'Useful natural remedies',1);

-- 10 sample reservations
INSERT INTO reservations (user_id, book_id, reservation_date, pickup_date, return_date, payment_status, notes, status)
VALUES
(2, 1, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY), 'Paid', 'First reservation', 'Approved'),
(3, 2, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 'Unpaid', 'Urgent', 'Pending'),
(4, 3, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 'Paid', '', 'Approved'),
(5, 4, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY), 'Paid', 'Reserved for project', 'Approved'),
(6, 5, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 'Unpaid', '', 'Pending'),
(2, 6, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 'Paid', 'For research', 'Approved'),
(3, 1, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY), 'Paid', '', 'Approved'),
(4, 2, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY), 'Unpaid', 'Check availability', 'Pending'),
(5, 3, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY), 'Paid', '', 'Approved'),
(6, 4, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY), 'Unpaid', 'Reserved for class', 'Pending');

-- 10 sample shipping records
INSERT INTO shipping (order_id, carrier, tracking_number, shipped_date, estimated_delivery, actual_delivery, status)
VALUES
(1, 'DHL', 'DHL001', NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY), 'Delivered'),
(2, 'FedEx', 'FDX002', NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), NULL, 'Shipped'),
(3, 'UPS', 'UPS003', NOW(), DATE_ADD(NOW(), INTERVAL 4 DAY), NULL, 'Pending'),
(4, 'DHL', 'DHL004', NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY), 'Delivered'),
(5, 'FedEx', 'FDX005', NOW(), DATE_ADD(NOW(), INTERVAL 6 DAY), NULL, 'Shipped'),
(6, 'UPS', 'UPS006', NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), NULL, 'Pending'),
(7, 'DHL', 'DHL007', NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), NULL, 'Shipped'),
(8, 'FedEx', 'FDX008', NOW(), DATE_ADD(NOW(), INTERVAL 4 DAY), NULL, 'Pending'),
(9, 'UPS', 'UPS009', NOW(), DATE_ADD(NOW(), INTERVAL 6 DAY), NULL, 'Pending'),
(10, 'DHL', 'DHL010', NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), NULL, 'Shipped');

-- 10 sample system logs with module
INSERT INTO logs (user_id, action, module, description, timestamp)
VALUES
(2, 'LOGIN', 'Auth', 'User logged in', NOW()),
(3, 'RESERVE_BOOK', 'Reservations', 'Reserved book ID 2', NOW()),
(4, 'PURCHASE', 'Orders', 'Completed order ID 3', NOW()),
(5, 'UPDATE_PROFILE', 'Users', 'Updated phone number', NOW()),
(6, 'CANCEL_ORDER', 'Orders', 'Cancelled order ID 6', NOW()),
(2, 'LOGOUT', 'Auth', 'User logged out', NOW()),
(3, 'LOGIN', 'Auth', 'User logged in', NOW()),
(4, 'ADD_TO_CART', 'Cart', 'Added book ID 3 to cart', NOW()),
(5, 'RESERVE_BOOK', 'Reservations', 'Reserved book ID 4', NOW()),
(6, 'PURCHASE', 'Orders', 'Completed order ID 5', NOW());

-- 10 sample cart entries
INSERT INTO cart (user_id, book_id, quantity, added_at)
VALUES
(2, 1, 1, NOW()),
(3, 2, 2, NOW()),
(4, 3, 1, NOW()),
(5, 4, 3, NOW()),
(6, 5, 1, NOW()),
(2, 6, 2, NOW()),
(3, 1, 1, NOW()),
(4, 2, 1, NOW()),
(5, 3, 1, NOW()),
(6, 4, 2, NOW());

INSERT INTO system_logs (user_id, action, description, ip_address, log_date)
VALUES
(2, 'LOGIN', 'User logged in', '192.168.1.2', NOW()),
(3, 'LOGOUT', 'User logged out', '192.168.1.3', NOW()),
(4, 'UPDATE_PROFILE', 'Changed email address', '192.168.1.4', NOW()),
(5, 'PLACE_ORDER', 'Placed order ID 1', '192.168.1.5', NOW()),
(6, 'CANCEL_ORDER', 'Cancelled order ID 2', '192.168.1.6', NOW()),
(2, 'ADD_TO_CART', 'Added book ID 3 to cart', '192.168.1.2', NOW()),
(3, 'RESERVE_BOOK', 'Reserved book ID 4', '192.168.1.3', NOW()),
(4, 'PAYMENT', 'Paid for order ID 3', '192.168.1.4', NOW()),
(5, 'REVIEW_BOOK', 'Reviewed book ID 5', '192.168.1.5', NOW()),
(6, 'LOGIN', 'User logged in', '192.168.1.6', NOW());

INSERT INTO moodle_sync (book_id, moodle_course_id, sync_date, sync_status, notes)
VALUES
(1, 'MATH101', NOW(), 'Success', 'Synced successfully'),
(2, 'ENG202', NOW(), 'Success', 'Synced successfully'),
(3, 'SCI303', NOW(), 'Failed', 'Course not found'),
(4, 'HIS404', NOW(), 'Pending', 'Awaiting confirmation'),
(5, 'BIO505', NOW(), 'Success', 'Synced successfully'),
(6, 'CHEM606', NOW(), 'Success', 'Synced successfully'),
(7, 'PHY707', NOW(), 'Failed', 'Error in data format'),
(8, 'CS808', NOW(), 'Pending', 'Waiting for Moodle approval'),
(9, 'ART909', NOW(), 'Success', 'Synced successfully'),
(10, 'MUS1010', NOW(), 'Success', 'Synced successfully');

INSERT INTO order_items (order_id, book_id, quantity, price)
VALUES
(1, 1, 2, 1200.00),
(1, 2, 1, 500.00),
(2, 3, 1, 750.00),
(2, 4, 3, 2100.00),
(3, 5, 2, 1600.00),
(3, 6, 1, 800.00),
(4, 7, 4, 3200.00),
(4, 8, 1, 600.00),
(5, 9, 2, 1500.00),
(5, 10, 1, 700.00);
