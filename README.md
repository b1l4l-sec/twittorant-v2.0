# 🐦 Twittorant v2.0

> **Team Up using this platform** - A collaborative team sharing and collaboration platform

---

## 📋 Table of Contents

- [About](#-about)
- [Features](#-features)
- [Prerequisites](#-prerequisites)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [Screenshots](#-screenshots)
- [Project Structure](#-project-structure)
- [Admin Access](#-admin-access)
- [Technology Stack](#-technology-stack)
- [Security](#-security)
- [Troubleshooting](#-troubleshooting)
- [Support](#-support)

---

## 🎯 About

**Twittorant v2.0** is a modern web platform developed with **PHP** that allows users to:
- Register and log in securely
- Share posts with their team
- Collaborate on projects in "Team-Up" mode
- Receive real-time notifications
- Comment and interact with posts
- Manage their user profile
- Access an admin interface for platform management

---

## ✨ Features

### For Users
✅ **Secure Authentication** - Registration and login system  
✅ **News Feed** - View posts from team members  
✅ **Post Creation** - Share content with your team  
✅ **Comments System** - Interact and discuss on posts  
✅ **Notifications** - Stay informed about activities  
✅ **Team Up** - Create and join teams/projects  
✅ **User Profile** - Manage your personal information  
✅ **Profile Editing** - Update your personal data  

### For Administrators
👑 **Admin Dashboard** - Manage the entire platform  
👑 **User Management** - Moderation and administration  
👑 **Overview Dashboard** - Statistics and monitoring  

---

## 📦 Prerequisites

Before you begin, make sure you have the following installed:

| Component | Version | Link |
|-----------|---------|------|
| **XAMPP** | 7.0+ | [Download XAMPP](https://www.apachefriends.org/) |
| **PHP** | 7.4+ | Included in XAMPP |
| **MySQL** | 5.7+ | Included in XAMPP |
| **Apache** | 2.4+ | Included in XAMPP |
| **Web Browser** | Chrome/Firefox/Edge | N/A |

---

## 🚀 Installation

### Step 1: Download and Install XAMPP

1. Download **XAMPP** from [apachefriends.org](https://www.apachefriends.org/)
2. Install it in the default directory (e.g., `C:\xampp` on Windows)
3. Launch the **XAMPP Control Panel**

### Step 2: Start Services

1. Open **XAMPP Control Panel**
2. Click **Start** for both **Apache** and **MySQL** services

### Step 3: Clone or Download the Project

#### Option A: Using Git (Recommended)

```bash
cd D:\XAMPP_Apps\htdocs
git clone https://github.com/b1l4l-sec/twittorant-v2.0.git
cd twittorant-v2.0
```

#### Option B: Manual Download

1. Download the ZIP file of the project
2. Extract it to `D:\XAMPP_Apps\htdocs\`
3. Rename the folder to `twittorant-v2.0`

### Step 4: Place the Project in XAMPP htdocs

```
D:\XAMPP_Apps\htdocs\
└── twittorant-v2.0\
    ├── index.php
    ├── login.php
    ├── register.php
    ├── post.php
    ├── profile.php
    ├── notifications.php
    ├── team-up.php
    ├── edit_profile.php
    ├── logout.php
    ├── admin/
    ├── api/
    ├── css/
    ├── db/
    ├── js/
    ├── img/
    ├── includes/
    └── uploads/
```

---

## ⚙️ Configuration

### Step 1: Create the Database

1. Open **phpMyAdmin**:  `http://localhost/phpmyadmin`
2. Create a new database: 
   - Name: `twittorant_db`
   - Collation: `utf8mb4_unicode_ci`

### Step 2: Import Database Tables

1. Go to the `twittorant_db` database
2. Import the SQL file from the `db/` folder of your project
3. Verify that all tables have been created successfully

### Step 3: Configure Database Connection

Edit the configuration file (typically in `includes/config.php` or `db/config.php`):

```php
<?php
// Database Configuration
$servername = "localhost";
$username = "root";
$password = ""; // Leave empty for XAMPP
$dbname = "twittorant_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>
```

### Step 4: Configure Directory Permissions

```bash
# Make uploads folder writable
chmod 755 uploads/
chmod 755 img/
```

---

## 🎮 Usage

### Access the Application

1. Ensure **Apache** and **MySQL** are running
2. Open your browser and navigate to: 
   ```
   http://localhost/twittorant-v2.0/
   ```

### First-Time Setup

#### 1️⃣ **Create a User Account**
- Click on **"Register"**
- Fill in your information
- Confirm your registration

#### 2️⃣ **Log In**
- Click **"Login"**
- Enter your credentials
- Click **"Sign In"**

#### 3️⃣ **Access Your Feed**
- View posts from other team members
- Create your own posts
- Comment and interact with posts

#### 4️⃣ **Create or Join a Team**
- Go to the **"Team Up"** section
- Create a new team or join an existing one
- Collaborate with your teammates

#### 5️⃣ **Manage Your Profile**
- Click on your avatar/profile
- Edit your personal information
- Update your profile picture

#### 6️⃣ **Check Notifications**
- Go to the **"Notifications"** section
- Stay updated on comments and activities

---

## 📸 Screenshots

### 🔐 Login Screen
![LoginScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/LogInScreen.png)
*Secure login with credential validation*

### 🏠 Home Screen
![HomeScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/HomeScreen.png)
*News feed with user posts*

### 💬 Comments Screen
![CommentsScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/CommentsScreen.png)
*Comments section and interaction interface*

### 🔔 Notifications Screen
![NotificationsScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/NotificationsScreen.png)
*Real-time notification center*

### 👥 Team Up Screen
![TeamUpScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/TeamUpScreen.png)
*Team management and creation interface*

### 👑 Admin Dashboard
![AdminHomeScreen](https://github.com/b1l4l-sec/twittorant-v2.0/raw/main/AdminHomeScreen.png)
*Administrator control panel*

---

## 📁 Project Structure

```
twittorant-v2.0/
├── index.php                 # Home feed page
├── login.php                 # Login page
├── register. php              # Registration page
├── logout.php                # Logout functionality
├── post.php                  # Post creation/display
├── profile.php               # User profile page
├── edit_profile.php          # Profile editing
├── notifications.php         # Notifications page
├── team-up.php               # Team management
│
├── admin/                    # 📁 Admin section
│   ├── index.php            # Admin dashboard
│   └── (other admin pages)
│
├── api/                      # 📁 API endpoints
│   ├── posts.php
│   ├── comments.php
│   ├── users.php
│   └── (other APIs)
│
├── includes/                 # 📁 Included files
│   ├── config.php           # Database config
│   ├── header.php           # Header template
│   ├── footer.php           # Footer template
│   └── functions.php        # Reusable functions
│
├── db/                       # 📁 Database
│   ├── config.php           # DB configuration
│   └── schema.sql           # SQL schema
│
├── css/                      # 📁 Stylesheets
│   ├── style.css
│   └── responsive.css
│
├── js/                       # 📁 JavaScript
│   ├── main.js
│   └── ajax.js
│
├── img/                      # 📁 Site images
│   └── logo.png
│
├── uploads/                  # 📁 User uploads
│   ├── profiles/            # Profile pictures
│   └── posts/               # Post images
│
└── package.json              # NPM configuration
```

---

## 🔑 Admin Access

### Default Admin Credentials

A default admin account is available.  Check the **`admin acc. txt`** file for credentials. 

**File**:  `admin acc.txt`

```
Username: admin
Password: (see file)
```

### Admin Features

- 📊 View global statistics
- 👥 Manage users
- 🗑️ Delete inappropriate posts
- 🔒 Manage permissions
- 📈 Monitor platform activity

---

## 🛠️ Technology Stack

| Technology | Purpose |
|-----------|---------|
| **PHP** | Backend and server logic |
| **MySQL** | Database |
| **HTML5** | Page structure |
| **CSS3** | Styling and responsive design |
| **JavaScript** | Client-side interactivity |
| **AJAX** | Dynamic content loading |
| **Bootstrap** | CSS framework (optional) |

---

## 🔐 Security

This application implements security best practices:

- ✅ **Data Validation** - Server and client-side validation
- ✅ **SQL Injection Protection** - Prepared statements
- ✅ **Password Hashing** - MD5/SHA256 hashing
- ✅ **Secure Sessions** - PHP session management
- ✅ **CSRF Protection** - Token validation
- ✅ **Authentication** - Access control verification

---

## 🚨 Troubleshooting

### Issue: "Database Connection Error"

**Solution**:
```
1. Verify MySQL is running in XAMPP Control Panel
2. Check connection parameters in includes/config.php
3. Ensure 'twittorant_db' database exists
4. Verify MySQL user (default: root, no password)
```

### Issue: "Uploads Folder Not Working"

**Solution**:
```
1. Open CMD as Administrator
2. Navigate to the uploads folder
3. Run: chmod 755 uploads/
4. Restart Apache
```

### Issue: "Files Not Displaying"

**Solution**:
```
1. Verify project is in D:\XAMPP_Apps\htdocs\
2. Check URL:  http://localhost/twittorant-v2.0/
3. Clear browser cache (Ctrl+Shift+Del)
4. Restart Apache
```

### Issue: "Git Ownership Error"

**Solution**: 
```bash
git config --global --add safe.directory "D:/XAMPP_Apps/htdocs/twittorant-v2.0"
```

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| **Primary Language** | CSS (frontend) + PHP (backend) |
| **PHP Files** | 9 |
| **Folders** | 8 |
| **Database** | MySQL |
| **Status** | Active & Maintained |
| **Version** | 2.0 |

---

## 📧 Support & Contribution

For questions or suggestions: 

- 🐛 **Report Bugs**:  [Issues](https://github.com/b1l4l-sec/twittorant-v2.0/issues)
- 💡 **Suggest Features**: [Discussions](https://github.com/b1l4l-sec/twittorant-v2.0/discussions)
- 🔗 **Repository**: [GitHub](https://github.com/b1l4l-sec/twittorant-v2.0)

---

## 📝 License

This project is developed by **@b1l4l-sec**

All rights reserved © 2026

---

## 🤝 Getting Help

### Common Questions

**Q: Can I use this on a live server?**  
A: Yes, but ensure you update security settings and change default credentials.

**Q: How do I add more users?**  
A: Users can register themselves through the registration page.

**Q: Can I customize the UI?**  
A: Yes, modify the CSS files in the `css/` folder.

**Q: How do I backup the database?**  
A:  Use phpMyAdmin's export feature or use mysqldump command.

---

## ✅ Quick Start Checklist

- [ ] XAMPP installed and running
- [ ] Apache and MySQL started
- [ ] Project cloned/downloaded to htdocs
- [ ] Database created (twittorant_db)
- [ ] Database configured in config.php
- [ ] Uploads folder permissions set (755)
- [ ] Application accessible at localhost/twittorant-v2.0/
- [ ] Admin credentials saved securely

---

**Last Updated**: January 8, 2026  
**Version**: 2.0  
**Author**: [@b1l4l-sec](https://github.com/b1l4l-sec)

---

Enjoy collaborating!  🚀
