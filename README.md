# Exam Management System

A PHP-based exam management system for universities, featuring an admin panel to manage students, exams, and results.

## Features

- Admin Authentication
- Student Management
- Exam Management
- Result Management
- Dashboard with Statistics
- Secure Password Handling

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP (or similar local development environment)

## Installation

1. Clone this repository to your XAMPP's htdocs folder:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   git clone [repository-url] exam_management
   ```

2. Create the database and tables:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials if needed (default values should work with XAMPP)

4. Access the system:
   - Open your browser and navigate to `http://localhost/exam_management`
   - Login with default credentials:
     - Username: admin
     - Password: password

## Security Notes

- Change the default admin password after first login
- Keep your PHP and MySQL installations up to date
- Regularly backup your database

## Directory Structure

```
exam_management/
├── config/
│   └── database.php
├── index.php
├── dashboard.php
├── students.php
├── exams.php
├── results.php
├── logout.php
├── database.sql
└── README.md
``` 