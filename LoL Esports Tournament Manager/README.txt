========================================================================
LoL ESPORTS TOURNAMENT TRACKER & MANAGER
========================================================================

SYSTEM NAME:
League of Legends Esports Tournament Manager

DESCRIPTION:
A responsive, dark-themed web platform designed to map out match schedules,
compile tournament team standings, and log results dynamically. The hub includes 
an integrated outcome log engine featuring live-filtering search inputs for streamlined 
Match MVP nomination alongside real-time player leaderboards.

========================================================================
HOW TO SET IT UP ON LOCALHOST
========================================================================

1. Install XAMPP (or any local server configuration supporting PHP 8+ and MySQL).

2. Transfer your tournament project folder into your XAMPP server's root web directory:
   C:\xampp\htdocs\Esports_Tournament\

3. Launch the XAMPP Control Panel and start the Apache and MySQL service engines.

4. Navigate to your local database management interface:
   http://localhost/phpmyadmin

5. Initialize a blank relational database container:
   Name: esports_tournament

6. Select the newly created 'esports_tournament' database, click on the "Import" tab,
   and upload the schema script:
   /sql/esports_tournament.sql

7. Open your preferred web browser and navigate to the application terminal:
   http://localhost/Esports_Tournament/index.php

========================================================================
RELATIONAL ENVIRONMENT TROUBLESHOOTING
========================================================================
If you hit relational integrity errors (such as Error #1701 for Foreign Key
Constraints) while attempting to wipe or rewrite fixtures inside phpMyAdmin:

1. Navigate directly to the 'SQL' script query editor panel in phpMyAdmin.
2. Ensure your execution blocks are preceded by foreign key override flags:

   SET FOREIGN_KEY_CHECKS = 0;
   -- Your target TRUNCATE or INSERT operational commands run safely here
   SET FOREIGN_KEY_CHECKS = 1;

========================================================================
DEFAULT SYSTEM LOGIN PRIVILEGES
========================================================================

ADMINISTRATIVE CONSOLE PROFILE:
Username: admin_league
Password: admin123
Privileges: Full Write access (Create matches, log scores, remove profiles).

SCOUT / VIEWER STAND PROFILE:
Username: guest_scout
Password: viewer123
Privileges: Read-Only access (View schedules, leaderboard matrices, and MVP boards).

(Note: All system security passwords are encrypted using secure bcrypt hashing
algorithms inside the 'users' table structure.)

========================================================================
DEVELOPMENT GROUP MEMBERS
========================================================================

1. Theodore O. Ruelo
2. Jade Vincent V. Bernabe
3. Adonis Emmanuel Zerrudo 
4. Mitz Anfernee Urfilla

========================================================================