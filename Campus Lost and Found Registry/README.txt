============================================
CAMPUS LOST AND FOUND REGISTRY
============================================

SYSTEM NAME:
trU-Access System

DESCRIPTION:
A web-based system that helps students, staff, and admins manage lost and 
found items within the campus. Users can report lost or found items, browse 
existing reports, and file claims for items they believe belong to them. 
Admins review and approve/reject claims to ensure items are returned to the 
right owner.

============================================
HOW TO SET IT UP
============================================

1. Install XAMPP (or any local server with PHP and MySQL).

2. Copy the project folder into the htdocs directory of XAMPP.
   C:\xampp\htdocs\campus_lost_found

3. Start Apache and MySQL from the XAMPP Control Panel.

4. Open phpMyAdmin (http://localhost/phpmyadmin).

5. Create a new database named: campus_lost_found

6. Go to the Import tab and import the SQL file located in:
   /sql/campus_lost_found.sql

7. Open your browser and go to:
   http://localhost/login.php

8. The login page should now load and the system is ready to use.

============================================
DEFAULT LOGIN CREDENTIALS
============================================

ADMIN ACCOUNT:
Email: admin.bautista@university.edu.ph
Password: YourDevPassword!9

STUDENT ACCOUNT:
Email: kimjuan.secret@student.edu.ph
Password: Student01

(Note: Passwords are stored as hashed values in the database for security. 
The credentials above are the plain-text versions used to log in.)

Additional: If error do this instead.

1. Open your browser and go to:
   http://localhost/reset_password.php

2. Choose the admin, and/or any of the students email and change your new desire password.

3. Close it after use.

4. Then go back to step no. 7.

============================================
GROUP MEMBERS
============================================

1. Kimberly Ann
2. Mae Jo Seniel
3. Jam Claudette Napagao
4. Johnric Ysulat

============================================
