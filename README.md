# Shop Management System

A comprehensive PHP and MySQLi-based shop management system with secure authentication, income/expense tracking, customer management, custom forms, and detailed reports.

## Features

- **Secure Authentication**
  - User login and registration
  - Password hashing using bcrypt
  - Session management
  - Role-based access control (Admin/User)

- **Dashboard**
  - Overview statistics (Total Income, Expense, Customers, Net Profit)
  - Recent income and expense records
  - Quick action buttons

- **Core Modules**
  - **Customers**: Manage customer information with complete details
  - **Income**: Track and record income with customer association
  - **Expense**: Manage expense records with categories
  - **Reports**: Comprehensive reporting with filters and analytics
  - **Custom Forms**: Create custom forms for data collection

- **Additional Features**
  - User profile management
  - Change password functionality
  - User management (Admin only)
  - Export to CSV functionality
  - Print functionality
  - Responsive design with Bootstrap 5

## System Requirements

- PHP 7.4 or higher
- MySQL/MariaDB 5.7 or higher
- Apache/Nginx web server
- XAMPP or similar local development environment

## Installation

### 1. Extract Files
Extract the project files to your web server's root directory:
```
/opt/lampp/htdocs/nmservices/
```

### 2. Create Database

Import the database schema:

**Option A: Via phpMyAdmin**
1. Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
2. Click "Import"
3. Select the `database.sql` file from your project
4. Click "Import"

**Option B: Via MySQL Command Line**
```bash
mysql -u root -p < /path/to/database.sql
```

### 3. Configure Database Connection
Edit `config/db.php` and update the following if needed:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nmservices');
```

### 4. Access the Application

Open your browser and navigate to:
```
http://localhost/nmservices/
```

### 5. Default Login Credentials

- **Username**: `admin`
- **Password**: `admin123`

> **IMPORTANT**: Change the default password immediately after first login!

## Project Structure

```
nmservices/
├── config/
│   └── db.php                 # Database configuration
├── public/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   ├── js/
│   │   └── script.js         # JavaScript utilities
│   ├── images/               # Image storage
│   └── uploads/              # User uploads
├── includes/
│   ├── Auth.php              # Authentication class
│   ├── functions.php         # Helper functions
│   ├── header.php            # Header/Navigation template
│   └── footer.php            # Footer template
├── pages/
│   ├── customers.php         # Customer management
│   ├── income.php            # Income tracking
│   ├── expense.php           # Expense tracking
│   ├── reports.php           # Reports and analytics
│   ├── forms.php             # Custom forms
│   ├── profile.php           # User profile
│   ├── change-password.php   # Password change
│   └── users.php             # User management (Admin)
├── modules/                  # Future expandable modules
├── database.sql              # Database schema
├── index.php                 # Login page
├── dashboard.php             # Dashboard
└── logout.php                # Logout handler
```

## Usage Guide

### Dashboard
The dashboard provides an at-a-glance overview of your business metrics including total income, expenses, customers, and profit.

### Adding Records

#### Add Customer
1. Go to **Modules > Customers**
2. Click **Add New Customer**
3. Fill in customer details
4. Click **Save Customer**

#### Add Income
1. Go to **Modules > Income**
2. Click **Add Income**
3. Select customer (optional)
4. Enter amount, date, and payment details
5. Click **Save Income**

#### Add Expense
1. Go to **Modules > Expense**
2. Click **Add Expense**
3. Select category and enter details
4. Click **Save Expense**

### Generating Reports
1. Go to **Reports**
2. Select start and end dates
3. View statistics and analytics
4. Click **Print** to print the report
5. Click **Export** to download as CSV

### Managing Users (Admin Only)
1. Go to **Settings > User Management**
2. View all users
3. Activate/Deactivate users as needed

## Security Features

- **Password Security**
  - Bcrypt hashing for password storage
  - Minimum password length validation
  - Change password functionality

- **Data Protection**
  - SQL injection prevention with prepared statements
  - XSS protection with input sanitization
  - CSRF protection implementation ready
  - Session-based authentication

- **Access Control**
  - Login requirement for protected pages
  - Role-based access restrictions
  - Admin-only features

## Database Schema

### Tables

1. **users** - User accounts and credentials
2. **customers** - Customer information
3. **income** - Income records with customer association
4. **expense** - Expense records with categories
5. **custom_forms** - Custom form definitions
6. **form_submissions** - Form submission data

## Troubleshooting

### Database Connection Error
- Verify MySQL/MariaDB is running
- Check database credentials in `config/db.php`
- Ensure database `nmservices` exists

### Login Issues
- Check cookies are enabled in browser
- Clear browser cache and try again
- Verify user is active in user management

### File Upload Issues
- Ensure `public/uploads/` directory exists and is writable
- Check file permissions: `chmod 755 public/uploads/`

## Future Enhancements

- Invoice generation and printing
- Payment reminders and notifications
- Multi-currency support
- Advanced analytics and charts
- API integration
- Mobile app support
- Backup and restore functionality
- Audit logging

## Support

For issues or questions:
1. Check the system logs
2. Verify all files are properly uploaded
3. Ensure database connection is working
4. Review your browser console for JavaScript errors

## License

This project is provided as-is for business management purposes.

## Default Demo Credentials

```
Username: admin
Password: admin123
```

Remember to change this password immediately after login!

## Tips for Development

- Keep backups of your database regularly
- Test features in a development environment first
- Monitor system performance with large datasets
- Implement additional validation rules as needed
- Consider adding email notifications for business events

---

**Developed**: 2026
**Version**: 1.0.0
**Technology Stack**: PHP, MySQLi, Bootstrap 5, JavaScript
