# Maktaba Bookstore Management System

## Overview
Maktaba is a web-based bookstore management system designed to handle day-to-day operations of a bookstore, including sales tracking, inventory management, user management, and reporting. The system provides interfaces for both administrators and customers to manage and access bookstore functionalities.

## Features

### User Management
- Register and manage users
- Assign user roles (Admin, Staff, Customer)

### Book Inventory
- Add, update, and delete books
- Track book stock levels

### Sales & Orders
- Place and manage orders
- Track order status (Pending, Shipped, Delivered)
- Calculate total sales and revenue

### Reports
- Monthly revenue summaries
- Top-selling books
- Recent activity logs

### Database
- Primarily using HeidiSQL (MySQL)
- Tracks users, books, orders, order items, reviews, and activity logs

## Installation

1. Clone the repository:
```bash
git clone https://github.com/linnyika/Maktaba.git
```

2. Import the database using HeidiSQL or any MySQL client:
```sql
CREATE DATABASE maktaba_db;
```
Import all provided tables and sample data.

3. Configure the database connection in `includes/config.php` with your credentials.

4. Run the project on your preferred PHP server (e.g., XAMPP/Apache).

5. Access the system via:
```
http://localhost/Maktaba/
```

## Project Structure / Key Files

- **modules/admin/** – Administrative modules including reports, book management, and user management.
- **modules/user/** – User-facing pages for browsing books and placing orders.
- **includes/summary_helper.php** – Provides summarized data for reports.
- **includes/data_processor.php** – Handles queries and calculations for system summaries.
- **database/config.php** – Database connection setup.
- **assets/css/** – Styles for the project.

## Usage

- **Admin:** Manage books, users, view reports, and oversee system activities.
- **Customer:** Browse books, place orders, and view order history.

## Key Functional Modules

### Final Reports (`modules/admin/final_reports.php`)
- Compiles monthly sales, top books, user roles, and recent logs.
- Uses `summary_helper.php` for summarized queries.

### Book Management (`modules/admin/books.php`)
- Add, edit, and remove books from inventory.
- Update stock levels and pricing.

### User Management (`modules/admin/users.php`)
- Register new users and assign roles.
- View all users and manage accounts.

### Order Management (`modules/user/orders.php`)
- Customers can place new orders.
- Tracks order status and calculates total revenue.

### Helpers (`includes/`)
- `summary_helper.php` – Generates system-wide and user-specific summaries.
- `data_processor.php` – Executes database queries and computations.

### Assets (`assets/`)
- CSS and JS files for front-end styling and interactivity.

## Contributions
Developed as a group project by **Group E-11** for the BBIT program. Contributions include:

- Database design and setup  
- Backend logic and query handling  
- Front-end design and styling  
- Report generation and summaries  

## License
This project is for academic purposes for the BBIT program and is not for commercial use.
