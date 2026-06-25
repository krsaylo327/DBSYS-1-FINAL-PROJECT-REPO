-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2026 at 03:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brgy_system`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessCertificateRequest` (IN `p_request_id` INT, IN `p_staff_id` INT, IN `p_status` VARCHAR(20))   BEGIN
    DECLARE v_resident_id INT;
    DECLARE v_certificate_id INT;
    DECLARE v_certificate_name VARCHAR(100);
    DECLARE v_fee DECIMAL(10,2);
   
    -- Get request details
    SELECT resident_id, certificate_id
    INTO v_resident_id, v_certificate_id
    FROM certificate_request
    WHERE request_id = p_request_id;
   
    -- Get certificate fee
    SELECT certificate_name, base_fee
    INTO v_certificate_name, v_fee
    FROM certificates
    WHERE certificate_id = v_certificate_id;
   
    -- Update the request
    UPDATE certificate_request
    SET
        staff_id = p_staff_id,
        status = p_status,
        resolved_at = NOW()
    WHERE request_id = p_request_id;
   
    -- Return info
    SELECT
        'Request processed successfully!' AS message,
        v_certificate_name AS certificate_name,
        v_fee AS fee,
        p_status AS new_status;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `barangay_residents`
--

CREATE TABLE `barangay_residents` (
  `resident_id` int(11) NOT NULL,
  `household_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_residents`
--

INSERT INTO `barangay_residents` (`resident_id`, `household_id`, `first_name`, `last_name`, `birth_date`, `gender`, `contact_number`, `date_registered`, `last_updated_at`) VALUES
(1, 1, 'Juan', 'Dela Cruz', '1990-05-15', 'Male', '09171234567', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(2, 1, 'Maria', 'Dela Cruz', '1992-08-22', 'Female', '09181234568', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(3, 1, 'Jose', 'Dela Cruz', '2015-03-10', 'Male', '09191234569', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(4, 2, 'Pedro', 'Reyes', '1985-11-30', 'Male', '09201234570', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(5, 2, 'Ana', 'Reyes', '1988-07-12', 'Female', '09211234571', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(6, 3, 'Carlos', 'Santos', '1975-01-05', 'Male', '09221234572', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(7, 3, 'Elena', 'Santos', '1978-09-18', 'Female', '09231234573', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(8, 4, 'Ramon', 'Garcia', '1995-04-25', 'Male', '09241234574', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(9, 4, 'Liza', 'Garcia', '1997-06-14', 'Female', '09251234575', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(10, 5, 'Manuel', 'Fernandez', '1980-02-20', 'Male', '09261234576', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(11, 5, 'Teresa', 'Fernandez', '1983-10-05', 'Female', '09271234577', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(12, 6, 'Gregorio', 'Mendoza', '1992-12-01', 'Male', '09281234578', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(13, 6, 'Sofia', 'Mendoza', '1994-03-28', 'Female', '09291234579', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(14, 7, 'Andres', 'Bonifacio', '1970-08-15', 'Male', '09301234580', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(15, 7, 'Julia', 'Bonifacio', '1973-11-22', 'Female', '09311234581', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(16, 8, 'Emilio', 'Aguinaldo', '1988-05-09', 'Male', '09321234582', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(17, 8, 'Hilaria', 'Aguinaldo', '1990-07-19', 'Female', '09331234583', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(18, 9, 'Jose', 'Rizal', '1982-06-19', 'Male', '09341234584', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(19, 9, 'Leonor', 'Rizal', '1985-12-30', 'Female', '09351234585', '2026-06-19 12:19:33', '2026-06-19 12:19:33'),
(20, 10, 'Marcelo', 'Del Pilar', '1977-09-12', 'Male', '09361234586', '2026-06-19 12:19:33', '2026-06-19 12:19:33');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_staff`
--

CREATE TABLE `barangay_staff` (
  `staff_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `position` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_staff`
--

INSERT INTO `barangay_staff` (`staff_id`, `first_name`, `last_name`, `position`, `username`, `password_hash`, `contact_number`, `created_at`, `is_active`) VALUES
(1, 'Juan', 'Santos', 'Barangay Captain', 'captain.santos', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', '09171234567', '2026-06-19 12:19:33', 1),
(2, 'Maria', 'Cruz', 'Barangay Secretary', 'secretary.cruz', 'dece17bb4784e2c98ce3119aee61310465fcc7542780852d2b1ef3b50f3374b9', '09181234568', '2026-06-19 12:19:33', 1),
(3, 'Pedro', 'Reyes', 'Barangay Treasurer', 'treasurer.reyes', 'cfec7d62a2bfdf1f7aa9835a934afe43fa3eeb30125222abf01cfce4680a17bf', '09191234569', '2026-06-19 12:19:33', 1);

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `certificate_name` varchar(100) NOT NULL,
  `base_fee` decimal(10,2) NOT NULL DEFAULT 50.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `certificate_name`, `base_fee`, `description`, `is_active`) VALUES
(1, 'Barangay Clearance', 50.00, 'Official certification of residency and good moral character', 1),
(2, 'Certificate of Residency', 50.00, 'Proof of residence in the barangay', 1),
(3, 'Certificate of Indigency', 30.00, 'Certification for financial assistance programs', 1),
(4, 'Barangay Business Clearance', 100.00, 'Required for business permits and licenses', 1),
(5, 'Certificate of Good Moral Character', 50.00, 'For employment and school applications', 1);

-- --------------------------------------------------------

--
-- Table structure for table `certificate_request`
--

CREATE TABLE `certificate_request` (
  `request_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `certificate_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `purpose` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Released') DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_request`
--

INSERT INTO `certificate_request` (`request_id`, `resident_id`, `certificate_id`, `staff_id`, `purpose`, `status`, `requested_at`, `resolved_at`) VALUES
(1, 1, 1, 1, 'Employment application', 'Approved', '2026-01-15 02:00:00', '2026-01-16 06:30:00'),
(2, 2, 2, 1, 'School enrollment requirement', 'Released', '2026-01-20 01:30:00', '2026-01-21 03:00:00'),
(3, 3, 3, 2, 'Medical financial assistance', 'Pending', '2026-01-25 06:15:00', NULL),
(4, 4, 4, 2, 'Business permit renewal', 'Approved', '2026-02-01 00:45:00', '2026-02-02 08:00:00'),
(5, 5, 1, 1, 'Job interview requirement', 'Rejected', '2026-02-05 03:20:00', '2026-02-06 01:00:00'),
(6, 6, 2, 3, 'Government ID application', 'Pending', '2026-02-10 05:00:00', NULL),
(7, 7, 5, 1, 'College admission requirement', 'Approved', '2026-02-15 02:30:00', '2026-02-16 07:45:00'),
(8, 8, 1, 3, 'Work abroad requirement', 'Pending', '2026-02-20 01:00:00', NULL),
(9, 9, 2, 1, 'Voter registration requirement', 'Released', '2026-03-01 06:00:00', '2026-03-02 02:00:00'),
(10, 10, 3, 2, 'Financial assistance program', 'Approved', '2026-03-05 00:30:00', '2026-03-06 03:30:00'),
(11, 11, 4, 1, 'Small business registration', 'Pending', '2026-03-10 05:45:00', NULL),
(12, 12, 5, 3, 'Employment requirement', 'Released', '2026-03-15 02:00:00', '2026-03-16 01:30:00'),
(13, 13, 1, 2, 'Barangay ID application', 'Approved', '2026-03-20 03:15:00', '2026-03-21 06:00:00'),
(14, 14, 2, 1, 'Senior citizen benefits', 'Pending', '2026-04-01 01:00:00', NULL),
(15, 15, 3, 3, 'Medical assistance', 'Released', '2026-04-05 07:30:00', '2026-04-06 02:00:00');

-- --------------------------------------------------------

--
-- Stand-in structure for view `certificate_request_summary`
-- (See below for the actual view)
--
CREATE TABLE `certificate_request_summary` (
`request_id` int(11)
,`resident_name` varchar(101)
,`certificate_name` varchar(100)
,`fee` decimal(10,2)
,`purpose` varchar(255)
,`status` enum('Pending','Approved','Rejected','Released')
,`requested_at` timestamp
,`resolved_at` timestamp
,`processed_by` varchar(101)
);

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `household_id` int(11) NOT NULL,
  `household_number` varchar(50) NOT NULL,
  `purok_zone` varchar(50) NOT NULL,
  `street_address` varchar(200) NOT NULL,
  `source` varchar(100) DEFAULT 'Barangay Census',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`household_id`, `household_number`, `purok_zone`, `street_address`, `source`, `created_at`) VALUES
(1, '001', 'Purok 1', '123 Mabini Street', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(2, '002', 'Purok 1', '124 Mabini Street', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(3, '003', 'Purok 2', '101 Rizal Avenue', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(4, '004', 'Purok 2', '102 Rizal Avenue', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(5, '005', 'Purok 3', '45 Bonifacio Street', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(6, '006', 'Purok 3', '46 Bonifacio Street', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(7, '007', 'Purok 4', '78 Luna Street', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(8, '008', 'Purok 4', '79 Luna Street', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(9, '009', 'Purok 5', '12 Del Pilar Street', 'Barangay Census 2025', '2026-06-19 12:19:33'),
(10, '010', 'Purok 5', '13 Del Pilar Street', 'Barangay Census 2025', '2026-06-19 12:19:33');

-- --------------------------------------------------------

--
-- Table structure for table `resident_accounts`
--

CREATE TABLE `resident_accounts` (
  `account_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Resident','Staff','Admin') DEFAULT 'Resident',
  `account_status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by_staff_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resident_accounts`
--

INSERT INTO `resident_accounts` (`account_id`, `resident_id`, `username`, `password_hash`, `email`, `role`, `account_status`, `created_at`, `approved_by_staff_id`) VALUES
(1, 1, 'juan.delacruz', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'juan.delacruz@email.com', 'Resident', 'Active', '2026-06-19 12:19:33', NULL),
(2, 2, 'maria.delacruz', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'maria.delacruz@email.com', 'Resident', 'Active', '2026-06-19 12:19:33', NULL),
(3, 5, 'ana.reyes', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'ana.reyes@email.com', 'Resident', 'Active', '2026-06-19 12:19:33', NULL);

-- --------------------------------------------------------

--
-- Structure for view `certificate_request_summary`
--
DROP TABLE IF EXISTS `certificate_request_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `certificate_request_summary`  AS SELECT `cr`.`request_id` AS `request_id`, concat(`r`.`first_name`,' ',`r`.`last_name`) AS `resident_name`, `c`.`certificate_name` AS `certificate_name`, `c`.`base_fee` AS `fee`, `cr`.`purpose` AS `purpose`, `cr`.`status` AS `status`, `cr`.`requested_at` AS `requested_at`, `cr`.`resolved_at` AS `resolved_at`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `processed_by` FROM (((`certificate_request` `cr` join `barangay_residents` `r` on(`cr`.`resident_id` = `r`.`resident_id`)) join `certificates` `c` on(`cr`.`certificate_id` = `c`.`certificate_id`)) left join `barangay_staff` `s` on(`cr`.`staff_id` = `s`.`staff_id`)) ORDER BY `cr`.`requested_at` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barangay_residents`
--
ALTER TABLE `barangay_residents`
  ADD PRIMARY KEY (`resident_id`),
  ADD KEY `idx_residents_household` (`household_id`),
  ADD KEY `idx_residents_name` (`last_name`,`first_name`);

--
-- Indexes for table `barangay_staff`
--
ALTER TABLE `barangay_staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD UNIQUE KEY `certificate_name` (`certificate_name`);

--
-- Indexes for table `certificate_request`
--
ALTER TABLE `certificate_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_requests_resident` (`resident_id`),
  ADD KEY `idx_requests_certificate` (`certificate_id`),
  ADD KEY `idx_requests_status` (`status`),
  ADD KEY `idx_requests_staff` (`staff_id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`household_id`),
  ADD UNIQUE KEY `household_number` (`household_number`);

--
-- Indexes for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `approved_by_staff_id` (`approved_by_staff_id`),
  ADD KEY `idx_accounts_resident` (`resident_id`),
  ADD KEY `idx_accounts_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barangay_residents`
--
ALTER TABLE `barangay_residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `barangay_staff`
--
ALTER TABLE `barangay_staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `certificate_request`
--
ALTER TABLE `certificate_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `household_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barangay_residents`
--
ALTER TABLE `barangay_residents`
  ADD CONSTRAINT `barangay_residents_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `households` (`household_id`) ON DELETE CASCADE;

--
-- Constraints for table `certificate_request`
--
ALTER TABLE `certificate_request`
  ADD CONSTRAINT `certificate_request_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `barangay_residents` (`resident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificate_request_ibfk_2` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`certificate_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificate_request_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `barangay_staff` (`staff_id`) ON DELETE SET NULL;

--
-- Constraints for table `resident_accounts`
--
ALTER TABLE `resident_accounts`
  ADD CONSTRAINT `resident_accounts_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `barangay_residents` (`resident_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resident_accounts_ibfk_2` FOREIGN KEY (`approved_by_staff_id`) REFERENCES `barangay_staff` (`staff_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
