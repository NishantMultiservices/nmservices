# Quick Setup Guide

## Step 1: Database Setup

1. **Open phpMyAdmin**
   - Go to `http://localhost/phpmyadmin` in your browser
   - Login with your MySQL credentials (default: username=root, password=blank)

2. **Create Database**
   - Click on "New" or use the sidebar to create a new database
   - Database name: `nmservices`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import SQL Schema**
   - Select the newly created `nmservices` database
   - Click the "Import" tab
   - Choose file: `database.sql` from your project folder
   - Click "Import"

## Step 2: Configure Your Application

No additional configuration needed if:
- MySQL is running on `localhost`
- MySQL username is `root`
- MySQL password is blank (default XAMPP setup)
- Database name is `nmservices`

If you have different settings, edit `config/db.php`:

```php
define('DB_HOST', 'your_host');        // MySQL host
define('DB_USER', 'your_username');    // MySQL username
define('DB_PASS', 'your_password');    // MySQL password
define('DB_NAME', 'nmservices');       // Database name
```

## Step 3: Access the Application

1. **Start XAMPP**
   - Start Apache and MySQL services

2. **Open Browser**
   - Navigate to: `http://localhost/nmservices/`

3. **Login**
   - Username: `admin`
   - Password: `admin123`

## First Time Users

### Immediately After Login:

1. **Change Your Password**
   - Click your profile name (top right)
   - Select "Change Password"
   - Set a strong password

2. **Add Your First Customer**
   - Go to Modules > Customers
   - Click "Add New Customer"
   - Fill in customer details

3. **Record Income/Expense**
   - Go to Modules > Income or Expense
   - Click "Add Income" or "Add Expense"
   - Fill in transaction details

4. **View Reports**
   - Go to Reports
   - Set date range
   - View your business analytics

## Directory Permissions

Ensure these directories are writable:
```bash
chmod 755 public/uploads/
chmod 755 config/
```

## Troubleshooting

### Problem: "Connection failed: Unknown database 'nmservices'"
**Solution**: 
- Import the `database.sql` file from phpMyAdmin
- Verify database name in `config/db.php`

### Problem: "Login page appears after clicking login"
**Solution**:
- Check if cookies are enabled in your browser
- Try a different browser or clear cache
- Verify MySQL is running

### Problem: "Cannot upload files"
**Solution**:
- Check `public/uploads/` directory exists
- Set directory permissions: `chmod 755 public/uploads/`
- Check PHP file upload limits in `php.ini`

### Problem: "Blank pages or 500 errors"
**Solution**:
- Check PHP error logs
- Enable error reporting in `config/db.php`
- Verify all files are uploaded correctly

## Features Checklist

After setup, verify these features are working:

- [ ] Login with admin/admin123
- [ ] Access Dashboard
- [ ] Add a Customer
- [ ] Add Income Record
- [ ] Add Expense Record
- [ ] View Reports
- [ ] Export to CSV
- [ ] Print a page
- [ ] Change Password
- [ ] View User Profile

## Next Steps

1. **Customize Branding**
   - Edit APP_NAME in `config/db.php`
   - Replace logo in `public/images/`

2. **Expand Features**
   - Add more expense categories in `pages/expense.php`
   - Create custom forms in the Forms module
   - Set up email notifications

3. **Backup Data**
   - Regularly backup your MySQL database
   - Use phpMyAdmin export feature or mysqldump command:
     ```bash
     mysqldump -u root nmservices > backup.sql
     ```

4. **Security Best Practices**
   - Change default admin password
   - Create limited user accounts
   - Regularly update records
   - Monitor finance reports

## Support Resources

- Check the main README.md for full documentation
- Review database schema in `database.sql`
- Check PHP error logs for debugging
- Test all forms before using in production

---

**Setup Complete!** You're now ready to start managing your shop. 🎉
