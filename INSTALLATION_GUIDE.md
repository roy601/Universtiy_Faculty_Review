# Faculty Review System - Installation Guide for XAMPP

## Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser
- Text editor (optional, for customization)

## Step-by-Step Installation

### 1. Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP with Apache, MySQL, and PHP components
3. Start Apache and MySQL services from XAMPP Control Panel

### 2. Setup Project Files
1. Copy the entire project folder to `C:\xampp\htdocs\faculty-review-system\`
2. Ensure all files are in the correct directory structure

### 3. Database Setup
1. Open your web browser and go to `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `database/01_create_database.sql`
4. Click "Go" to create database and tables
5. Import sample data: `database/02_insert_sample_data.sql`
6. Click "Go" to insert sample data

### 4. Configuration
1. Open `config/database.php`
2. Verify database settings:
   - Host: `localhost`
   - Database: `faculty_review_system`
   - Username: `root`
   - Password: `` (empty for default XAMPP)

### 5. File Permissions
1. Create `uploads` folder in project root
2. Set write permissions for uploads folder (if on Linux/Mac)

### 6. Access the System
1. Open browser and go to: `http://localhost/faculty-review-system`
2. Default admin login:
   - Username: `admin`
   - Password: `admin123`
3. Default student login:
   - Username: `john_doe`
   - Password: `student123`

## Default Accounts

### Admin Account
- **Username:** admin
- **Password:** admin123
- **Access:** Full system administration

### Student Accounts
- **Username:** john_doe | **Password:** student123
- **Username:** jane_smith | **Password:** student123
- **Username:** mike_johnson | **Password:** student123

## Troubleshooting

### Common Issues:
1. **Database Connection Error:**
   - Ensure MySQL is running in XAMPP
   - Check database credentials in `config/database.php`

2. **Page Not Found:**
   - Verify project is in `htdocs/faculty-review-system/`
   - Check Apache is running

3. **Permission Denied:**
   - Ensure uploads folder has write permissions
   - Check file ownership (Linux/Mac)

### Security Notes:
- Change default passwords before production use
- Update database credentials for production
- Enable HTTPS for production deployment
- Regular database backups recommended

## Features Included:
- User authentication (students and admins)
- Faculty management
- Course management
- Review submission and approval
- Rating system with multiple criteria
- Responsive design
- Admin dashboard with statistics
- Search and filter functionality
