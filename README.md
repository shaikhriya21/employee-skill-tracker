# SkillTrack Pro - Employee Skill Tracker & Project Assignment System

A comprehensive full-stack web application for managing employee skills, conducting assessments, and intelligently matching candidates to projects based on their expertise.

![SkillTrack Pro](assets/images/screenshot.png)

## Features

### User Roles
- **Admin**: Full system control, user management, skill management, reports
- **HR/Recruiter**: Project creation, employee assignment, assessment management
- **Employee/Candidate**: Profile management, skill tracking, assessments, project assignments

### Core Modules

1. **User Authentication**
   - Secure registration and login
   - Password hashing with bcrypt
   - Session-based authentication
   - Role-based access control

2. **Skill Management**
   - Add/remove skills with proficiency levels
   - Years of experience tracking
   - Skill categories

3. **Online Assessment System**
   - Multiple choice questions (MCQ)
   - Timer-based tests
   - Automatic scoring
   - Pass/fail determination

4. **Project Management**
   - Create projects with required skills
   - Position management
   - Progress tracking

5. **Skill Matching Algorithm**
   - Automatic skill match percentage calculation
   - Assessment score integration
   - Smart candidate recommendations

6. **Employee Assignment**
   - One-click assignment
   - Automatic approval based on criteria
   - Position limit enforcement

7. **Dashboard & Analytics**
   - Real-time statistics
   - Interactive charts
   - Activity logs

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: Core PHP
- **Database**: MySQL
- **UI Framework**: Custom Glassmorphism Design
- **Charts**: Chart.js
- **Icons**: Font Awesome

## Installation Guide

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Web browser

### Step-by-Step Setup

#### 1. Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)

#### 2. Place Project in htdocs
```bash
# Copy the project folder to XAMPP htdocs directory
# Windows: C:\xampp\htdocs\
# Linux/Mac: /opt/lampp/htdocs/

# The folder structure should be:
# htdocs/employee-skill-tracker/
```

#### 3. Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service

#### 4. Import Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click on "Import" tab
3. Choose file: `employee-skill-tracker/database.sql`
4. Click "Go" to import

#### 5. Configure Database Connection
Edit `config/database.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');      // Default XAMPP username
define('DB_PASSWORD', '');           // Default XAMPP password (empty)
define('DB_NAME', 'skill_tracker');
```

#### 6. Run the Application
Open your browser and navigate to:
```
http://localhost/employee-skill-tracker/
```

## Default Login Credentials

### Admin
- **Email**: admin@company.com
- **Password**: admin123

### HR
- **Email**: hr@company.com
- **Password**: hr123

### Employee
Register a new account or use any created employee account.

## Folder Structure

```
employee-skill-tracker/
├── index.php                 # Landing page
├── config/
│   └── database.php         # Database configuration
├── assets/
│   ├── css/
│   │   └── style.css        # Main stylesheet (Glassmorphism)
│   ├── js/
│   │   └── main.js          # JavaScript functions
│   └── images/              # Image assets
├── admin/                   # Admin module
│   ├── dashboard.php
│   ├── manage_users.php
│   ├── manage_skills.php
│   ├── manage_assessments.php
│   ├── manage_questions.php
│   ├── manage_projects.php
│   └── reports.php
├── hr/                      # HR module
│   ├── dashboard.php
│   ├── create_project.php
│   ├── view_projects.php
│   ├── view_project.php
│   ├── assign_employee.php
│   ├── assessment_results.php
│   └── employee_skills.php
├── candidate/               # Employee module
│   ├── dashboard.php
│   ├── profile.php
│   ├── skills.php
│   ├── take_assessment.php
│   ├── my_projects.php
│   └── results.php
├── auth/                    # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
└── database.sql             # Database schema
```

## Database Schema

### Tables
1. **users** - User accounts (admin, hr, candidate)
2. **skills** - Master skill list
3. **candidate_skills** - User-skill relationships
4. **assessments** - Assessment definitions
5. **questions** - MCQ questions
6. **results** - Assessment results
7. **projects** - Project definitions
8. **project_skills** - Project-skill requirements
9. **project_assignments** - Employee assignments
10. **activity_logs** - System activity tracking

## Key Features Explained

### Skill Matching Algorithm
The system automatically calculates skill match percentage:
```
Match % = (Matching Skills / Required Skills) × 100
```

### Auto-Approval Criteria
Employees are automatically approved for projects when:
- Skill Match ≥ 50%
- Assessment Score ≥ 60%

### Assessment Timer
- Real-time countdown timer
- Auto-submit on time expiry
- Visual warning when < 5 minutes remain

## Security Features

- Password hashing using `password_hash()`
- SQL injection protection with prepared statements
- XSS protection with `htmlspecialchars()`
- CSRF protection through session validation
- Input sanitization
- Activity logging

## Customization

### Colors
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #6366f1;
    --secondary-color: #ec4899;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
}
```

### Assessment Passing Score
Default passing score is 60%. Modify in:
- Database: `assessments.passing_score`
- Or when creating assessments

## Troubleshooting

### Database Connection Error
1. Verify MySQL is running in XAMPP
2. Check credentials in `config/database.php`
3. Ensure database is imported correctly

### 404 Errors
1. Check project folder is in correct location
2. Verify Apache is running
3. Check file permissions

### Session Issues
1. Clear browser cookies
2. Check PHP session configuration
3. Verify write permissions for session directory

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## License

This project is open source and available under the MIT License.

## Support

For issues and feature requests, please create an issue in the project repository.

---

**Developed with modern Glassmorphism design principles**
