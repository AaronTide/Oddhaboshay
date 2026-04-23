# Oddhaboshay - University LMS
## DBMS Project Setup Guide

### Quick Start (3 Steps)

**Step 1: Database Setup**
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "Import" -> Choose `database/oddhaboshay.sql`
3. Click "Go"

**Step 2: Configure Database**
1. Open `config/db.php`
2. Change DB_USER and DB_PASS to match your MySQL setup

**Step 3: Run the Project**
1. Copy `oddhaboshay/` folder to `htdocs/` (XAMPP) or `www/` (WAMP)
2. Open: http://localhost/oddhaboshay/landpage.php

---

### Demo Login Credentials
> Default password for ALL accounts: **password**

| Role    | ID      | Email                | Password |
|---------|---------|----------------------|----------|
| Admin   | admin   | admin@oddhaboshay.edu| password |
| Student | STU001  | rahim@student.edu    | password |
| Student | STU002  | karim@student.edu    | password |
| Teacher | TCH001  | anwar@teacher.edu    | password |
| Teacher | TCH002  | nasima@teacher.edu   | password |

---

### Project Structure
```
oddhaboshay/
├── landingpage.php              # Homepage - role selection
├── config/db.php          # Database connection config
├── database/              # SQL schema file
├── assets/css/style.css   # Main stylesheet
├── assets/js/main.js      # JavaScript utilities
├── admin/                 # Admin panel (5 pages)
├── student/               # Student panel (5 pages)
├── teacher/               # Teacher panel (4 pages)
└── uploads/pdfs/          # Uploaded PDF storage
```

---



### Technology Stack
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8 (MySQLi, Sessions, password_hash)
- **Database**: MySQL 5.7+
- **Server**: Apache (XAMPP/WAMP)
