# Universtiy Faculty Review

A PHP-based web application designed to manage and review university faculty data, complete with dashboards for both admin and regular users, signup/login features, and support for file uploads.

## Table of Contents

- [Features](#features)  
- [Repository Structure](#repository-structure)  

## Features

- User registration and login (signup.php, index.php, logout.php)
- Admin and user dashboards (dashboard_admin.php, dashboard_user.php)
- Faculty info management (info.php)
- File upload functionality (upload.php)
- PHP-based backend with MySQL integration (DBconnect.php)
- Basic styling (dashboardstyle.css, styles.css)
- Set up or reset process (setup.php)
- Redirect/access checking (check.php)

## Repository Structure

```text
Universtiy_Faculty_Review/
├── components/              # Reusable UI components (if any)
├── database/                # Database schema or related assets
├── img/uploads/             # Uploaded files repository
├── php/                     # PHP helper scripts or APIs (if any)
├── .gitignore
├── INSTALLATION_GUIDE.md    # Installation instructions
├── DBconnect.php            # Database connection script
├── check.php                # Access check / authorization logic
├── dashboard.php            # Main dashboard router
├── dashboard_admin.php      # Admin dashboard
├── dashboard_user.php       # User dashboard
├── index.php                # Landing or login page
├── info.php                 # Faculty information page
├── logout.php               # Logout functionality
├── navbar.php               # Navigation bar
├── setup.php                # Setup routines (e.g., database or config)
├── signup.php               # User signup page
├── styles.css               # Global styles
├── dashboardstyle.css       # Dashboard-specific styles
└── upload.php               # File upload handling
