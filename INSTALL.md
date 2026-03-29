# Installation and Configuration Guide

## System Requirements

- **PHP**: 7.4 or higher
- **MySQL/MariaDB**: 5.7 or higher
- **Apache/Nginx**: Web server with .htaccess support (optional)
- **Web Browser**: Modern browser (Chrome, Firefox, Safari, Edge)
- **Local Development**: XAMPP 7.4+ or equivalent

## Installation Steps

### 1. Prepare Your Environment

#### Using XAMPP:
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP
3. Start Apache and MySQL services

#### Using WAMP:
1. Download WAMP from http://www.wampserver.com/
2. Install WAMP
3. Start the services

#### Using Docker:
Create a docker-compose.yml file for automated setup.

### 2. Extract Project Files

1. Extract the project to your web root:
   ```
   Windows: C:\xampp\htdocs\nmservices\
   Linux: /opt/lampp/htdocs/nmservices/
   macOS: /Applications/XAMPP/htdocs/nmservices/
   ```

2. Verify folder structure matches the repository

### 3. Database Installation

#### Method A: phpMyAdmin GUI (Recommended for beginners)

1. Open phpMyAdmin:
   - URL: http://localhost/phpmyadmin
   - Default credentials: root / (blank password)

2. Create Database:
   - Click "New" button
   - Database name: `nmservices`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. Import SQL File:
   - Click on the `nmservices` database
   - Click "Import" tab
   - Click "Choose File"
   - Select `database.sql` from your project
   - Click "Import"

#### Method B: Command Line

```bash
# Windows (in Command Prompt)
mysql -u root -p < "C:\xampp\htdocs\nmservices\database.sql"

# Linux (in Terminal)
mysql -u root -p < /opt/lampp/htdocs/nmservices/database.sql

# macOS (in Terminal)
mysql -u root -p < /Applications/XAMPP/htdocs/nmservices/database.sql
```

Enter your MySQL password when prompted (default is blank for XAMPP).

#### Method C: MySQL Workbench

1. Open MySQL Workbench
2. Connect to your MySQL server
3. File → Open SQL Script
4. Select `database.sql`
5. Click Execute (⚡) button

### 4. Configuration

#### Default Configuration
No changes needed if using default XAMPP setup with:
- Host: localhost
- User: root
- Password: (blank)
- Database: nmservices

#### Custom Configuration
If your MySQL setup is different, edit `config/db.php`:

```php
<?php
define('DB_HOST', 'your_server_ip');    // e.g., 'localhost' or '192.168.1.100'
define('DB_USER', 'username');           // MySQL username
define('DB_PASS', 'password');           // MySQL password
define('DB_NAME', 'nmservices');         // Database name
```

#### App Configuration (Optional)
Edit `config/db.php` to customize:

```php
define('APP_NAME', 'Your Shop Name');
define('APP_URL', 'http://yourdomain.com/nmservices/');
```

### 5. Verify Installation

1. **Check File Structure**
   ```
   nmservices/
   ├── config/db.php
   ├── database.sql
   ├── index.php
   ├── dashboard.php
   ├── README.md
   └── [other files...]
   ```

2. **Test Database Connection**
   - Save this as test.php in nmservices folder:
   ```php
   <?php
   require 'config/db.php';
   if ($conn->connect_error) {
       die("Database Error: " . $conn->connect_error);
   }
   echo "Database connection successful!";
   ?>
   ```
   - Visit: http://localhost/nmservices/test.php
   - Delete test.php after verification

3. **Access Application**
   - URL: http://localhost/nmservices/
   - You should see the login page

### 6. First Login

1. **Login Credentials**
   - Username: `admin`
   - Password: `admin123`

2. **After Login**
   - Go to "Change Password" immediately
   - Set a strong password
   - Click "Update Password"

## File Permissions

Ensure proper permissions for writable directories:

### Linux/macOS (Terminal):
```bash
# Navigate to project
cd /opt/lampp/htdocs/nmservices/

# Set permissions
chmod 755 public/
chmod 755 public/uploads/
chmod 755 config/
chmod 644 public/css/*
chmod 644 public/js/*
```

### Windows:
1. Right-click folder → Properties
2. Security tab → Edit
3. Select your user
4. Check "Full Control"
5. Click Apply

## Security Setup

### 1. Change Default Password
- Login with admin/admin123
- Go to Profile → Change Password
- Create a strong password (12+ chars, mixed case, numbers, symbols)

### 2. Create User Accounts
- Login as admin
- Go to Settings → User Management
- Create accounts for team members
- Assign roles appropriately

### 3. Configure Backups
```bash
# Weekly backup script (Linux)
0 3 * * 0 mysqldump -u root -p nmservices > /backup/nmservices_$(date +\%Y\%m\%d).sql
```

### 4. Enable HTTPS (Production)
- Purchase SSL certificate
- Configure in web server
- Update APP_URL in config/db.php

## Optimization

### PHP Configuration (php.ini)

For optimal performance, adjust these settings:

```ini
; Maximum file upload size
upload_max_filesize = 64M
post_max_size = 64M

; Maximum execution time
max_execution_time = 300

; Memory limit
memory_limit = 256M

; Timezone
date.timezone = Asia/Kolkata
```

### MySQL Configuration (my.ini)

```ini
# Maximum connections
max_connections = 100

# Buffer sizes
key_buffer_size = 256M
tmp_table_size = 32M
max_heap_table_size = 32M

# InnoDB settings
innodb_buffer_pool_size = 1G
```

## Troubleshooting

### Common Issues and Solutions

**Issue: "Connection refused on 127.0.0.1:3306"**
```
Solution: 
- Verify MySQL is running
- Check MySQL port (default: 3306)
- Restart MySQL service
```

**Issue: "Access denied for user 'root'@'localhost'"**
```
Solution:
- Verify username and password in config/db.php
- Check MySQL user credentials
- Reset MySQL password if forgotten
```

**Issue: "Database 'nmservices' doesn't exist"**
```
Solution:
- Create database from phpMyAdmin
- Import database.sql file
- Verify database name matches config
```

**Issue: "Fatal error: Class 'mysqli' not found"**
```
Solution:
- Enable MySQLi extension in php.ini
- Uncomment: extension=mysqli
- Restart Apache
```

**Issue: "Uploaded files not saving"**
```
Solution:
- Create public/uploads/ directory
- Set permissions: chmod 755 public/uploads/
- Verify disk space available
```

**Issue: "Session not maintaining login"**
```
Solution:
- Ensure session.save_path is writable
- Check cookies are enabled in browser
- Clear browser cache and cookies
- Verify php.ini session settings
```

## Deployment to Production

### Pre-Deployment Checklist
- [ ] Change all default passwords
- [ ] Disable error display (set display_errors = Off)
- [ ] Enable error logging
- [ ] Backup database
- [ ] Test all features
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up backups schedule
- [ ] Document admin passwords securely
- [ ] Create user manual

### Deployment Steps
1. Upload files to production server
2. Apply stricter file permissions (644 for files, 755 for directories)
3. Configure production database
4. Update APP_URL in config/db.php
5. Test all functionality
6. Monitor logs for issues

## Backup and Restore

### Backup Database
```bash
# Full backup
mysqldump -u root -p nmservices > nmservices_backup_$(date +%Y%m%d).sql

# With compression
mysqldump -u root -p nmservices | gzip > nmservices_backup_$(date +%Y%m%d).sql.gz
```

### Restore Database
```bash
# From backup
mysql -u root -p nmservices < nmservices_backup_20260101.sql

# From compressed backup
gunzip < nmservices_backup_20260101.sql.gz | mysql -u root -p nmservices
```

### Backup Files
```bash
# Create zip archive of entire project
zip -r nmservices_backup_$(date +%Y%m%d).zip ./nmservices/

# Exclude sensitive files
zip -r nmservices_backup.zip ./nmservices/ -x "*/config/db.php" "*/.*" "*uploads/*"
```

## Support and Resources

- **Official MySQL Documentation**: https://dev.mysql.com/doc/
- **PHP Documentation**: https://www.php.net/docs.php
- **Bootstrap Documentation**: https://getbootstrap.com/docs/
- **XAMPP Documentation**: https://www.apachefriends.org/
- **Web Standards**: https://www.w3.org/

## Next Steps After Installation

1. **Add Your Business Data**
   - Create customer profiles
   - Add income and expense records
   - Set up expense categories

2. **Customize the System**
   - Update APP_NAME and branding
   - Configure notifications
   - Add custom reports

3. **Team Setup**
   - Create user accounts for team members
   - Set appropriate access levels
   - Train on system usage

4. **Data Management**
   - Export reports regularly
   - Schedule automated backups
   - Review financial data monthly

---

**Need Help?** Refer to README.md or SETUP.md for additional information.

**Version**: 1.0.0
**Updated**: March 2026
