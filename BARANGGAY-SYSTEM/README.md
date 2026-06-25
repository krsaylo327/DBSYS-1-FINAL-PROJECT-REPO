============================================================
        BARANGAY RESIDENT INFORMATION SYSTEM
        Database Management Systems - Final Project
============================================================

SYSTEM NAME:
Barangay Resident Information System

GROUP MEMBERS:
============================================================
1. Lynn S. Compahinay - 2024-1623-A
2. Johndel A. Zamora - 2023-8205-A
3. Josh Vincent D. Caspillo - 2024-5254-A
4. Clark S. Dadulla - 2025-S03495
5. Russel R. Delos Santos - 2024-4933-A

============================================================
SYSTEM OVERVIEW
============================================================

The Barangay Resident Information System is a digital platform 
designed to securely store and manage the records of residents 
in a local community. It replaces traditional paper-based 
filing systems with a centralized database that allows barangay 
officials to quickly access accurate resident information, 
track household demographics, manage barangay clearances, and 
monitor health records.

The system reduces waiting times for citizens, minimizes 
clerical errors, and helps local leaders make data-driven 
decisions to better serve the community.

============================================================
TECHNICAL REQUIREMENTS
============================================================

To run this system, you need:

1. XAMPP (Version 7.4 or higher)
   - Apache Web Server
   - MySQL Database
   - PHP 7.4+

2. Web Browser (Chrome, Firefox, Edge, etc.)

3. Code Editor (VS Code, Sublime Text, etc.)

============================================================
INSTALLATION INSTRUCTIONS
============================================================

STEP 1: Install XAMPP
----------------------
1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP in: C:\xampp1\ (or default location)
3. Open XAMPP Control Panel
4. Start Apache and MySQL services (click Start button)

STEP 2: Copy Project Files
---------------------------
1. Copy the entire 'barangay-system' folder to:
   C:\xampp1\htdocs\barangay-system\

STEP 3: Create Database
------------------------
1. Open your browser and go to:
   http://localhost/phpmyadmin

2. Click "New" on the left sidebar

3. Enter database name: barangay_system

4. Click "Create"

5. Click the "SQL" tab

6. Copy and paste the contents of:
   C:\xampp1\htdocs\barangay-system\sql\brgy_system.sql

7. Click "Go" to run the SQL script

STEP 4: Configure Database Connection
--------------------------------------
1. Open file: db_connect.php

2. Verify these settings (default XAMPP):
   $host = 'localhost';
   $dbname = 'barangay_system';
   $username = 'root';
   $password = '';

3. If you changed your MySQL password, update it here.

STEP 5: Access the System
--------------------------
1. Open your web browser

2. Go to: http://localhost/barangay-system/

3. The Dashboard should load with sample data

============================================================
DEFAULT LOGIN CREDENTIALS
============================================================

STAFF ACCOUNTS (for administrative access):

| Username          | Password    | Role              |
|-------------------|-------------|-------------------|
| captain.santos    | admin123    | Barangay Captain  |
| secretary.cruz    | secretary123| Barangay Secretary|
| treasurer.reyes   | treasurer123| Barangay Treasurer|

RESIDENT ACCOUNTS:

| Username          | Password    | Role     |
|-------------------|-------------|----------|
| juan.delacruz     | password123 | Resident |
| maria.delacruz    | password123 | Resident |
| ana.reyes         | password123 | Resident |

============================================================
SYSTEM FEATURES
============================================================

1. DASHBOARD
   - Overview statistics (Total Residents, Households, Requests)
   - Real-time status updates
   - Recent activity feed
   - Resident summary view

2. RESIDENT MANAGEMENT - CRUD
   - Add new residents
   - View all residents with search
   - Edit resident information
   - Delete residents (with confirmation)

3. HOUSEHOLD MANAGEMENT
   - View all households
   - Add/Edit household information
   - Track household resident count
   - Filter by purok/zone

4. CERTIFICATE REQUEST SYSTEM
   - Request certificates (Barangay Clearance, Residency, etc.)
   - Track request status (Pending, Approved, Rejected, Released)
   - Process requests using stored procedure
   - View request history

5. CERTIFICATE TYPES
   - View all available certificates
   - See certificate fees and descriptions

6. BARANGAY STAFF
   - Manage staff accounts
   - Assign roles and permissions
   - Process certificate requests

7. FRONTEND DESIGN
   - Responsive UI design
   - CSS styling and animations
   - Navigation menu
   - Form designs
   - JavaScript validation

8. TESTING & DEPLOYMENT
   - System testing and QA
   - Bug fixing
   - XAMPP deployment
   - Documentation formatting

============================================================
DATABASE SCHEMA
============================================================

The system uses the following tables:

1. households
   - household_id (PK)
   - household_number
   - purok_zone
   - street_address
   - source

2. barangay_residents
   - resident_id (PK)
   - household_id (FK → households)
   - first_name, last_name
   - birth_date, gender
   - contact_number
   - date_registered

3. certificates
   - certificate_id (PK)
   - certificate_name
   - base_fee
   - description

4. certificate_request
   - request_id (PK)
   - resident_id (FK → barangay_residents)
   - certificate_id (FK → certificates)
   - staff_id (FK → barangay_staff)
   - purpose, status
   - requested_at, resolved_at

5. barangay_staff
   - staff_id (PK)
   - first_name, last_name
   - position
   - username, password_hash
   - is_active

6. resident_accounts
   - account_id (PK)
   - resident_id (FK → barangay_residents)
   - username, password_hash
   - email, role
   - account_status

============================================================
STORED PROCEDURES
============================================================

ProcessCertificateRequest
-------------------------
Purpose: Process a certificate request (approve/reject/release)

Parameters:
- p_request_id: ID of the request to process
- p_staff_id: ID of the staff member processing it
- p_status: New status ('Approved', 'Rejected', 'Released')

Usage Example in SQL:
CALL ProcessCertificateRequest(1, 1, 'Approved');

============================================================
VIEWS
============================================================

1. certificate_request_summary
   Shows all requests with resident and staff details

2. resident_dashboard_summary
   Aggregated data per resident (total requests, pending, etc.)

============================================================
SQL QUERIES IMPLEMENTED
============================================================

INNER JOIN
-----------------
SELECT cr.*, CONCAT(r.first_name, ' ', r.last_name) AS resident_name
FROM certificate_request cr
INNER JOIN barangay_residents r ON cr.resident_id = r.resident_id

LEFT JOIN
----------------
SELECT h.*, COUNT(r.resident_id) AS resident_count
FROM households h
LEFT JOIN barangay_residents r ON h.household_id = r.household_id
GROUP BY h.household_id

GROUP BY
----------------
SELECT status, COUNT(*) AS count 
FROM certificate_request 
GROUP BY status

HAVING
----------------
SELECT h.household_number, COUNT(r.resident_id) AS resident_count
FROM households h
LEFT JOIN barangay_residents r ON h.household_id = r.household_id
GROUP BY h.household_id
HAVING COUNT(r.resident_id) > 2

SUBQUERY
---------------
SELECT CONCAT(first_name, ' ', last_name) AS full_name
FROM barangay_residents 
WHERE resident_id NOT IN (SELECT DISTINCT resident_id FROM certificate_request)

AGGREGATE FUNCTIONS
----------------------------
SELECT COUNT(*) AS total, SUM(amount_paid) AS total_collected
FROM certificate_request

============================================================
SYSTEM NAVIGATION
============================================================

Main Navigation Menu:

| Menu Item      | Pages Available                         |
|----------------|-----------------------------------------|
| Dashboard      | index.php                              |
| Residents      | residents.php, residents_add.php       |
| Certificates   | request.php, request_add.php,          |
|                | certificate.php                         |
| Households     | households.php, households_add.php     |
| Staff          | staff.php, staff_add.php               |

============================================================
FILE STRUCTURE
============================================================

C:\xampp1\htdocs\barangay-system\
│
├── index.php              # Dashboard
├── db_connect.php         # Database connection
├── residents.php          # View residents
├── residents_add.php      # Add resident
├── residents_edit.php     # Edit resident
├── residents_delete.php   # Delete resident
├── request.php            # View requests
├── request_add.php        # New request
├── request_edit.php       # Edit request
├── request_delete.php     # Delete request
├── certificate.php        # Certificate types
├── households.php         # View households
├── households_add.php     # Add/Edit household
├── staff.php              # View staff
├── staff_add.php          # Add/Edit staff
│
├── css/
│   └── style.css          # Custom CSS
│
├── js/
│   └── script.js          # JavaScript validation
│
├── sql/
│   └── brgy_system.sql    # Database SQL
│
└── README.txt             # This file

============================================================
INDIVIDUAL CONTRIBUTIONS SUMMARY
============================================================

1. Lynn S. Compahinay - 2024-1623-A
   ------------------------------------------------------------
   CONTRIBUTIONS:
   - Designed and implemented the entire frontend user interface
   - Created custom CSS styles (style.css) with responsive design 
     for all screen sizes
   - Developed the navigation menu and ensured consistent layout 
     across all pages
   - Designed the dashboard cards and statistics display
   - Created form designs for all CRUD operations
   - Implemented JavaScript validation (script.js) for client-side 
     form validation
   - Designed UI/UX to make the system visually appealing and 
     user-friendly
   - Ensured responsive design works on desktop, tablet, and mobile
   - Added Bootstrap Icons for visual elements
   - Implemented auto-dismiss alerts and confirmation dialogs
   
   FILES CREATED/MODIFIED:
   - css/style.css
   - js/script.js
   - Navigation menu in all PHP files

2. Johndel A. Zamora - 2023-8205-A
   ------------------------------------------------------------
   CONTRIBUTIONS:
   - Built the complete certificate request system including 
     creating, viewing, editing, and deleting requests
   - Implemented certificate types management (certificate.php)
   - Developed staff management system (staff.php, staff_add.php)
   - Created SQL queries using GROUP BY for status statistics
   - Implemented HAVING clause for filtering large households
   - Added aggregate functions (COUNT, SUM, AVG) for dashboard 
     statistics
   - Implemented server-side validation for all forms to prevent 
     SQL injection
   - Created the certificate request form with validation
   - Processed certificate requests with status updates
   - Added staff assignment functionality for request processing
   
   FILES CREATED/MODIFIED:
   - request.php
   - request_add.php
   - request_edit.php
   - request_delete.php
   - certificate.php
   - staff.php
   - staff_add.php

3. Josh Vincent D. Caspillo - 2024-5254-A
   ------------------------------------------------------------
   CONTRIBUTIONS:
   - Implemented complete CRUD operations for residents
   - Created residents list with search functionality
   - Developed resident add, edit, and delete pages
   - Created the households management system
   - Implemented SQL JOIN queries for resident-household relationships
   - Created subqueries for finding residents without certificate 
     requests
   - Added client-side validation for all resident forms
   - Implemented search feature with LIKE queries
   - Created household resident count display
   - Added resident request count in the residents list
   
   FILES CREATED/MODIFIED:
   - residents.php
   - residents_add.php
   - residents_edit.php
   - residents_delete.php
   - households.php
   - households_add.php

4. Clark S. Dadulla - 2025-S03495
   ------------------------------------------------------------
   CONTRIBUTIONS:
   - Designed the entire database schema including all 6 tables, 
     relationships, and constraints
   - Created the Entity-Relationship Diagram (ERD)
   - Developed the Data Dictionary
   - Created the Stored Procedure (ProcessCertificateRequest)
   - Developed all database views (certificate_request_summary, 
     resident_dashboard_summary)
   - Built the Dashboard (index.php) with real-time statistics
   - Created the database connection file (db_connect.php)
   - Wrote the complete SQL script with seed data
   - Implemented aggregate queries for dashboard statistics
   - Created SQL indexes for performance optimization
   
   FILES CREATED/MODIFIED:
   - index.php
   - db_connect.php
   - sql/brgy_system.sql
   - ERD Diagram
   - Data Dictionary

5. Russel R. Delos Santos - 2024-4933-A
   ------------------------------------------------------------
   CONTRIBUTIONS:
   - Performed comprehensive system testing and quality assurance
   - Identified and fixed bugs in the system
   - Handled XAMPP setup, deployment, and database configuration
   - Executed SQL scripts and verified data integrity
   - Created and maintained the README.txt file with complete 
     documentation
   - Organized the project folder structure
   - Reviewed and formatted all documentation for final submission
   - Ensured the system runs smoothly on different environments
   - Tested all CRUD operations and queries
   - Verified all system features work as expected
   - Provided error handling and user feedback mechanisms
   - Ensured cross-browser compatibility
   
   FILES CREATED/MODIFIED:
   - README.txt
   - Tested all files and features
   - Verified system integration

============================================================
TASK DIVISION SUMMARY TABLE
============================================================

| Member | Primary Role | Main Files | Key Deliverables |
|--------|--------------|------------|------------------|
| Lynn   | Frontend Developer | style.css, script.js | UI/UX Design, CSS, JavaScript |
| Johndel| Backend Developer | request*.php, staff*.php, certificate.php | Certificates, Staff, Validation |
| Josh   | Backend Developer | residents*.php, households*.php | Residents CRUD, Households, Search |
| Clark  | Database Architect | index.php, db_connect.php, brgy_system.sql | Database, Dashboard, ERD |
| Russel | QA/Tester | README.txt, All files | Testing, Deployment, Documentation |

============================================================
TROUBLESHOOTING
============================================================

Issue: "Unknown database 'barangay_system'"
Solution: 
1. Go to http://localhost/phpmyadmin
2. Create database named 'barangay_system'
3. Import the SQL file from sql/brgy_system.sql

Issue: "Connection failed"
Solution:
1. Make sure XAMPP is running (Apache + MySQL)
2. Check db_connect.php for correct credentials
3. Default: username='root', password=''

Issue: "Table doesn't exist"
Solution:
1. Run the SQL script again
2. Check for typos in table names (barangay_residents not brangay_residents)

Issue: "404 Not Found"
Solution:
1. Check file names match navigation links
2. Make sure all files are in the correct folder
3. Check for case sensitivity (file names are case-sensitive)

Issue: Blank page / PHP errors
Solution:
1. Add to top of PHP files for debugging:
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
2. Check PHP error logs in XAMPP

============================================================
TESTING CHECKLIST
============================================================

☐ All CRUD operations work
☐ Database connection successful
☐ Dashboard displays correct statistics
☐ Resident management works
☐ Household management works
☐ Certificate requests work
☐ Staff management works
☐ Search functionality works
☐ Stored procedure works
☐ Views display correct data
☐ Client-side validation works
☐ Server-side validation works
☐ Responsive design works
☐ All navigation links work
☐ SQL queries work correctly
☐ Documentation complete
☐ README.txt complete
☐ System runs on XAMPP

============================================================
FILES CREATED PER MEMBER
============================================================

LYNN (2 files):
├── css/style.css
└── js/script.js

JOHNDEL (7 files):
├── request.php
├── request_add.php
├── request_edit.php
├── request_delete.php
├── certificate.php
├── staff.php
└── staff_add.php

JOSH (6 files):
├── residents.php
├── residents_add.php
├── residents_edit.php
├── residents_delete.php
├── households.php
└── households_add.php

CLARK (3 files):
├── index.php
├── db_connect.php
└── sql/brgy_system.sql

RUSSEL (1 file):
├── README.txt

TOTAL: 19 files

============================================================
SUPPORT
============================================================

For technical issues, contact:
- Instructor: During lab hours
- Any group member can assist

Group Members Contact:
- Lynn S. Compahinay: [Contact Info]
- Johndel A. Zamora: [Contact Info]
- Josh Vincent D. Caspillo: [Contact Info]
- Clark S. Dadulla: [Contact Info]
- Russel R. Delos Santos: [Contact Info]

============================================================
ACKNOWLEDGMENTS
============================================================

This system was developed as a final project for:
Database Management Systems - 2nd Year Information Technology

Special thanks to our instructor for guidance and support.

============================================================
VERSION HISTORY
============================================================

Version 1.0 - June 2026
- Initial release
- Complete CRUD operations
- Certificate request system with stored procedures
- Dashboard with real-time statistics
- Search functionality
- Input validation (client + server side)
- Responsive design
- Complete documentation

============================================================
LICENSE
============================================================

This system is developed for educational purposes only.
© 2026 - All Rights Reserved

============================================================
END OF README
============================================================
