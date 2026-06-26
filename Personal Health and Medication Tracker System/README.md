===========================================
   PERSONAL HEALTH & MEDICATION TRACKER
   Database Management Systems Project
   
   Group Name: [The Triple Care]
   Scenario: Personal Health & Medication Tracker System
   
   Submitted to: Mr. Kenny Saylo
   Date: June 2026
===========================================

===========================================
TABLE OF CONTENTS
===========================================

1. System Overview
2. System Requirements
3. Installation Guide
4. Login Credentials
5. System Navigation
6. How to Use the System
7. Database Structure
8. SQL Features Showcase
9. File Structure
10. Troubleshooting
11. Group Members
12. References

===========================================
1. SYSTEM OVERVIEW
===========================================

The Personal Health and Medication Tracker System is a database-driven 
web application that helps individuals record and monitor daily health 
metrics and manage medication schedules.

Key Features:
- User Authentication (Register, Login, Profile Management)
- Vitals Tracking (Blood Pressure, Heart Rate, Weight, Blood Sugar, Temperature)
- Medication Management (Add, Edit, Delete, Mark Active/Inactive)
- Medication Scheduling with Dose Tracking
- Health Goals Setting and Progress Monitoring
- Weekly Health Report Generation
- Adherence Rate Calculation
- Secure Password Hashing
- Responsive Design for Mobile and Desktop

Purpose:
To improve medication adherence, simplify communication with healthcare 
providers, and give users a reliable place to manage their daily health 
information.

===========================================
2. SYSTEM REQUIREMENTS
===========================================

Hardware:
- Minimum 4GB RAM
- 500MB free disk space
- Processor: Intel Core i3 or equivalent

Software:
- Windows 10/11 / Mac OS / Linux
- XAMPP (Apache + MySQL + PHP)
- Web browser (Chrome, Firefox, Edge)

===========================================
3. INSTALLATION GUIDE
===========================================

STEP 1: Install XAMPP
---------------------
1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP on your computer
3. Default installation path:
   - Windows: C:\xampp
   - Mac: /Applications/XAMPP
   - Linux: /opt/lampp

STEP 2: Copy Project Files
---------------------------
1. Copy the entire "health_tracker" folder to:
   - Windows: C:\xampp\htdocs\
   - Mac: /Applications/XAMPP/htdocs/
   - Linux: /opt/lampp/htdocs/

STEP 3: Start XAMPP Services
----------------------------
1. Open XAMPP Control Panel
2. Start Apache (Click "Start" button)
3. Start MySQL (Click "Start" button)
4. Verify both services show green "Running" status

STEP 4: Import Database
------------------------
1. Open web browser and go to: http://localhost/phpmyadmin
2. Click "New" on the left sidebar
3. Database name: health_tracker_system
4. Click "Create"
5. Click "Import" tab
6. Click "Choose File" and select: health_tracker_system.sql
7. Click "Go" at the bottom
8. Wait for import to complete

STEP 5: Run the System
----------------------
1. Open web browser
2. Go to: http://localhost/health_tracker/
3. The system homepage will load
4. You can now login or register a new account

===========================================
4. LOGIN CREDENTIALS
===========================================

TEST ACCOUNTS (Password: Password123!):
----------------------------------------
| Username          | Email                    | Full Name        |
|-------------------|--------------------------|------------------|
| juan_delacruz     | juan@email.com           | Juan Delacruz    |
| maria_santos      | maria@email.com          | Maria Santos     |
| ana_reyes         | ana@email.com            | Ana Reyes        |

REGISTER NEW ACCOUNT:
---------------------
1. Click "Register" on the login page
2. Fill out the registration form:
   - Full Name: [Your Full Name]
   - Username: [Choose a username]
   - Email: [Your Email]
   - Password: [Choose a password]
   - Birthdate: [Your Birthdate]
   - Gender: [Select Gender]
3. Click "Register"
4. Login with your registered email and password

Note: Passwords are hashed using bcrypt for security.

===========================================
5. SYSTEM NAVIGATION
===========================================

FOR ALL USERS (Logged In):
-----------------------------------------------------------
| Menu Item        | Description                           |
|------------------|---------------------------------------|
| Dashboard        | Overview of health stats and reminders |
| Vitals           | Log and manage health measurements     |
| Medications      | Add and manage medications             |
| Schedule         | View daily medication schedule         |
| Goals            | Set and track health goals             |
| Weekly Report    | View 7-day health summary              |
| Logout           | Exit the system                        |

===========================================
6. HOW TO USE THE SYSTEM
===========================================

6.1 LOGGING VITALS
-------------------
Vitals → Add Vitals → Enter:
  - Systolic BP (mmHg)
  - Diastolic BP (mmHg)
  - Heart Rate (bpm)
  - Weight (kg)
  - Blood Sugar (mg/dL)
  - Temperature (°C)
  - Notes (optional)
→ Save Vitals

6.2 ADDING A MEDICATION
------------------------
Medications → Add Medication → Enter:
  - Medication Name
  - Dosage (e.g., 5mg, 500mg)
  - Frequency (e.g., Once daily, Twice daily)
  - Start Date
  - End Date (optional)
  - Prescribed By (optional)
  - Instructions (optional)
  - Schedule Times (e.g., 08:00, 20:00)
  - Active Status
→ Add Medication

6.3 MARKING MEDICATION AS TAKEN
--------------------------------
Schedule → Find the medication dose → Click "Mark Taken"
OR
Medications → Find medication → Click "Mark Taken"

6.4 VIEWING MEDICATION SCHEDULE
--------------------------------
Schedule → View all scheduled medications with:
  - Medication Name
  - Dosage
  - Scheduled Time
  - Status (Taken/Pending)

6.5 SETTING HEALTH GOALS
-------------------------
Goals → Add Goal → Enter:
  - Goal Type (Weight, Blood Pressure, Blood Sugar, Exercise, Other)
  - Target Value
  - Current Value (optional)
  - Start Date
  - Target Date (optional)
  - Status (Not Started, In Progress, Achieved, Abandoned)
  - Notes (optional)
→ Save Goal

6.6 VIEWING WEEKLY REPORT
--------------------------
Weekly Report → View:
  - Overall Weekly Averages
  - Daily Breakdown Table
  - Above Average Readings (Subquery Example)
  - Trends over the past 7 days

6.7 EDITING RECORDS
--------------------
- Vitals: Click "Edit" next to the entry
- Medications: Click "Edit" next to the medication
- Goals: Click "Edit" next to the goal

6.8 DELETING RECORDS
---------------------
- Vitals: Click "Delete" next to the entry
- Medications: Click "Delete" next to the medication
- Goals: Click "Delete" next to the goal

===========================================
7. DATABASE STRUCTURE
===========================================

7.1 TABLES
-----------
| Table Name              | Description                           |
|-------------------------|---------------------------------------|
| USERS                   | User accounts and profiles            |
| VITALS_LOG              | Health measurements records           |
| MEDICATIONS             | Medication information                |
| MEDICATIONS_SCHEDULE    | Dose schedule and adherence tracking  |
| HEALTH_GOALS            | User health goals and progress        |

7.2 RELATIONSHIPS
-----------------
- USERS 1────< VITALS_LOG (One user has many vitals entries)
- USERS 1────< MEDICATIONS (One user has many medications)
- USERS 1────< HEALTH_GOALS (One user has many health goals)
- MEDICATIONS 1────< MEDICATIONS_SCHEDULE (One medication has many schedules)

7.3 STORED PROCEDURES
---------------------
Name: LogVital()
Purpose: Inserts a vital log entry and automatically updates matching 
         health goals with current values
Parameters: p_user_id, p_systolic, p_diastolic, p_heart_rate, 
            p_weight, p_blood_sugar, p_temperature, p_notes

Name: GetUserHealthSummary()
Purpose: Retrieves comprehensive health summary for a user including:
         - Average vitals for last 7 days
         - Active medications count
         - Health goals progress
         - Adherence rate
Parameters: p_user_id

7.4 VIEW
--------
Name: health_summary
Purpose: Provides aggregated health data across all users
Used in: dashboard.php and weekly_report.php

===========================================
8. SQL FEATURES SHOWCASE
===========================================

8.1 INNER JOIN (Used in medication_schedule.php)
-------------------------------------------------
SELECT ms.*, m.name, m.dosage, m.frequency 
FROM MEDICATIONS_SCHEDULE ms
INNER JOIN MEDICATIONS m ON ms.medication_id = m.medication_id
WHERE m.user_id = ? AND m.is_active = TRUE
ORDER BY ms.scheduled_time

8.2 LEFT JOIN (Used in health_summary view)
--------------------------------------------
SELECT u.user_id, u.full_name, COUNT(DISTINCT v.log_id) AS total_vitals
FROM USERS u
LEFT JOIN VITALS_LOG v ON u.user_id = v.user_id
GROUP BY u.user_id

8.3 GROUP BY with Aggregate Functions (Used in health_goals.php)
-----------------------------------------------------------------
SELECT goal_type, COUNT(*) AS total, 
       SUM(CASE WHEN status = 'Achieved' THEN 1 ELSE 0 END) AS achieved
FROM HEALTH_GOALS 
WHERE user_id = ?
GROUP BY goal_type

8.4 Correlated Subquery (Used in weekly_report.php)
---------------------------------------------------
SELECT v.*, u.full_name
FROM VITALS_LOG v
JOIN USERS u ON v.user_id = u.user_id
WHERE v.user_id = ? 
AND v.blood_pressure_systolic > (
    SELECT AVG(blood_pressure_systolic) 
    FROM VITALS_LOG 
    WHERE user_id = ?
)
AND v.logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

8.5 Stored Procedure Call (Used in add_vital.php)
--------------------------------------------------
CALL LogVital(?, ?, ?, ?, ?, ?, ?, ?)

8.6 View Usage (Used in dashboard.php)
---------------------------------------
SELECT * FROM health_summary WHERE user_id = ?

8.7 Aggregate Functions (Used in weekly_report.php)
----------------------------------------------------
SELECT 
    COUNT(*) AS total_entries,
    AVG(blood_pressure_systolic) AS overall_avg_sys,
    AVG(blood_pressure_diastolic) AS overall_avg_dia,
    AVG(weight) AS overall_avg_weight,
    AVG(blood_sugar) AS overall_avg_sugar
FROM VITALS_LOG 
WHERE user_id = ? 
AND logged_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

===========================================
9. FILE STRUCTURE
===========================================

health_tracker/
│
├── README.md                    # This file
├── index.php                    # Login Page
├── register.php                 # User Registration
├── dashboard.php                # Main Dashboard
├── logout.php                   # Logout Handler
├── vitals.php                   # Vitals Log List
├── add_vital.php                # Add Vitals Form
├── edit_vital.php               # Edit Vitals Form
├── delete_vital.php             # Delete Vitals Handler
├── medications.php              # Medications List
├── add_medication.php           # Add Medication Form
├── edit_medication.php          # Edit Medication Form
├── delete_medication.php        # Delete Medication Handler
├── medication_schedule.php      # Daily Schedule View
├── mark_taken.php               # Mark Dose as Taken
├── reset_schedule.php           # Reset Schedule Status
├── health_goals.php             # Health Goals List
├── add_goal.php                 # Add Health Goal Form
├── edit_goal.php                # Edit Health Goal Form
├── delete_goal.php              # Delete Health Goal Handler
├── weekly_report.php            # Weekly Health Report
├── profile.php                  # User Profile
├── settings.php                 # User Settings
│
├── /assets/
│   ├── /css/
│   │   └── style.css            # Main Stylesheet
│   ├── /js/
│   │   └── script.js            # JavaScript Validation
│   └── /images/
│       └── (screenshots)
│
├── /includes/
│   ├── config.php               # Database Configuration
│   ├── db_connection.php        # Database Functions
│   ├── functions.php            # Helper Functions
│   ├── session.php              # Session Management
│   ├── header.php               # Page Header
│   └── footer.php               # Page Footer
│
└── /sql/
    └── health_tracker_system.sql # Complete Database Script

===========================================
10. TROUBLESHOOTING
===========================================

PROBLEM: "Connection failed" error
SOLUTION: Make sure MySQL is running in XAMPP Control Panel

PROBLEM: "404 Not Found" error
SOLUTION: Make sure files are in C:\xampp\htdocs\health_tracker\

PROBLEM: "Access denied for user" error
SOLUTION: Check config.php has username = "root", password = ""

PROBLEM: Blank white page
SOLUTION: Enable error reporting or check PHP error logs

PROBLEM: Cannot login
SOLUTION: Use john.doe@email.com / Password123! or register a new account

PROBLEM: "Unknown database" error
SOLUTION: Database name must be exactly: health_tracker_system

PROBLEM: Password does not match
SOLUTION: 
  - Check that you're using the correct password
  - For seeded users, password is: Password123!
  - For new users, password must be at least 8 characters with uppercase, lowercase, and numbers

PROBLEM: Stored procedure not found
SOLUTION: Make sure the SQL script was imported completely

PROBLEM: Schedule not showing
SOLUTION: Make sure medications are marked as "Active" and have schedule times set

PROBLEM: Session lost when navigating
SOLUTION: Clear browser cache and cookies, then login again

PROBLEM: Port 80 already in use
SOLUTION: Change Apache port in XAMPP or use localhost:8080

===========================================
11. GROUP MEMBERS & CONTRIBUTIONS
===========================================

1. Faith Wenchie C. Flor (2024-1810-A)
   - Lead Backend Developer
   - PHP Backend Scripts
   - CRUD Operations
   - Weekly Report Generation
   - Database Connection
   - Session Management
   - System Optimization

2. Jirah Joy E. Pardos (2024-2718-A)
   - Database Designer
   - ERD Creation
   - SQL Implementation
   - Stored Procedures (LogVital, GetUserHealthSummary)
   - Database View (health_summary)
   - Seed Data Generation
   - Database Testing & Validation

3. Dan Gero T. Villasor (2024-9088-A)
   - Frontend Developer
   - UI/UX Design
   - HTML5, CSS3, JavaScript
   - Responsive Design
   - Form Validation
   - Testing & Quality Assurance
   - Bug Fixes

===========================================
12. REFERENCES
===========================================

- XAMPP: https://www.apachefriends.org/
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- W3Schools PHP Tutorial: https://www.w3schools.com/php/
- PHP Password Hashing: https://www.php.net/manual/en/function.password-hash.php
- Bootstrap: https://getbootstrap.com/
- Font Awesome Icons: https://fontawesome.com/

===========================================
END OF README
===========================================

For questions or support, contact the group leader.

Date Created: June 2026
Last Updated: June 2026
