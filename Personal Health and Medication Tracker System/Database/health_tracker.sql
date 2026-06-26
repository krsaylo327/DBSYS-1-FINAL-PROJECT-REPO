-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 25, 2026 at 05:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `health_tracker`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `LogVital` (IN `p_user_id` INT, IN `p_systolic` INT, IN `p_diastolic` INT, IN `p_heart_rate` INT, IN `p_weight` DECIMAL(5,2), IN `p_blood_sugar` DECIMAL(5,2), IN `p_temperature` DECIMAL(4,1), IN `p_notes` TEXT)   BEGIN
    INSERT INTO vitals_log (user_id, blood_pressure_systolic, blood_pressure_diastolic, 
                           heart_rate, weight, blood_sugar, temperature, notes)
    VALUES (p_user_id, p_systolic, p_diastolic, p_heart_rate, p_weight, p_blood_sugar, p_temperature, p_notes);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `health_goals`
--

CREATE TABLE `health_goals` (
  `goal_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `goal_type` enum('Weight','Blood Pressure','Blood Sugar','Exercise','Other') NOT NULL,
  `target_value` varchar(50) NOT NULL,
  `current_value` varchar(50) DEFAULT NULL,
  `start_date` date NOT NULL,
  `target_date` date DEFAULT NULL,
  `status` enum('Not Started','In Progress','Achieved','Abandoned') DEFAULT 'Not Started',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_goals`
--

INSERT INTO `health_goals` (`goal_id`, `user_id`, `goal_type`, `target_value`, `current_value`, `start_date`, `target_date`, `status`, `notes`, `created_at`) VALUES
(1, 1, 'Weight', '65kg', '70.5kg', '2026-01-01', '2026-03-31', 'In Progress', 'Lose 5.5kg through diet and exercise', '2026-06-21 04:50:31'),
(2, 1, 'Blood Pressure', '120/80', '120/80', '2026-01-01', '2026-02-28', 'In Progress', 'Maintain healthy blood pressure', '2026-06-21 04:50:31'),
(3, 2, 'Weight', '55kg', '58.5kg', '2026-01-01', '2026-03-31', 'In Progress', 'Reach target weight', '2026-06-21 04:50:31'),
(4, 2, 'Blood Pressure', '110/70', '110/70', '2026-01-01', '2026-02-28', 'Achieved', 'Excellent blood pressure control', '2026-06-21 04:50:31'),
(5, 3, 'Blood Pressure', '130/85', '135/90', '2026-01-01', '2026-04-30', 'In Progress', 'Lower blood pressure to target', '2026-06-21 04:50:31'),
(6, 3, 'Weight', '75kg', '82.5kg', '2026-01-01', '2026-06-30', 'In Progress', 'Weight loss goal', '2026-06-21 04:50:31'),
(7, 4, 'Weight', '60kg', '62.0kg', '2026-01-01', '2026-04-30', 'In Progress', 'Maintain healthy weight', '2026-06-21 04:50:31'),
(8, 5, 'Blood Sugar', '100mg/dL', '100mg/dL', '2026-01-01', '2026-03-31', 'In Progress', 'Control blood sugar levels', '2026-06-21 04:50:31'),
(9, 6, 'Blood Pressure', '120/80', '112/72', '2026-01-01', '2026-02-28', 'Achieved', 'Blood pressure well controlled', '2026-06-21 04:50:31'),
(10, 7, 'Blood Pressure', '130/85', '135/88', '2026-01-15', '2026-04-15', 'In Progress', 'New blood pressure goal', '2026-06-21 04:50:31'),
(11, 2, 'Weight', '50', '70', '2026-06-21', '2026-06-30', 'In Progress', '', '2026-06-21 13:05:26'),
(12, 6, 'Blood Sugar', '120/70', '130/50', '2026-06-23', '2026-07-31', 'In Progress', '', '2026-06-22 06:22:31'),
(13, 1, 'Weight', '45', '50', '2026-06-25', '2026-07-24', 'In Progress', '', '2026-06-25 08:13:13'),
(14, 1, 'Blood Pressure', '120/70', '130/50', '2026-06-23', '2026-07-31', 'Not Started', '', '2026-06-25 08:14:04');

-- --------------------------------------------------------

--
-- Stand-in structure for view `health_summary`
-- (See below for the actual view)
--
CREATE TABLE `health_summary` (
`user_id` int(11)
,`full_name` varchar(100)
,`total_vitals` bigint(21)
,`avg_systolic` decimal(14,4)
,`avg_diastolic` decimal(14,4)
,`avg_heart_rate` decimal(14,4)
,`avg_weight` decimal(9,6)
,`avg_blood_sugar` decimal(9,6)
,`total_medications` bigint(21)
,`doses_taken` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `medication_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `prescribed_by` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`medication_id`, `user_id`, `name`, `dosage`, `frequency`, `start_date`, `end_date`, `prescribed_by`, `instructions`, `is_active`, `created_at`) VALUES
(1, 1, 'Amlodipine', '5mg', 'Once daily', '2026-01-01', '2026-03-31', 'Dr. Santos', 'Take with breakfast', 1, '2026-06-21 04:48:45'),
(2, 1, 'Metformin', '500mg', 'Twice daily', '2026-01-01', '2026-06-30', 'Dr. Santos', 'Take with meals', 1, '2026-06-21 04:48:45'),
(3, 2, 'Losartan', '50mg', 'Once daily', '2026-01-01', '2026-04-30', 'Dr. Reyes', 'Take in the morning', 1, '2026-06-21 04:48:45'),
(4, 3, 'Amlodipine', '10mg', 'Once daily', '2026-01-01', '2026-03-31', 'Dr. Gomez', 'Take before bedtime', 1, '2026-06-21 04:48:45'),
(5, 3, 'HCTZ', '25mg', 'Once daily', '2026-01-01', '2026-03-31', 'Dr. Gomez', 'Take in the morning', 1, '2026-06-21 04:48:45'),
(6, 4, 'Levothyroxine', '50mcg', 'Once daily', '2026-01-01', '2026-06-30', 'Dr. Tan', 'Take on empty stomach', 1, '2026-06-21 04:48:45'),
(7, 5, 'Metformin', '850mg', 'Twice daily', '2026-01-01', '2026-05-31', 'Dr. Santos', 'Take with meals', 1, '2026-06-21 04:48:45'),
(8, 6, 'Losartan', '100mg', 'Once daily', '2026-01-01', '2026-04-30', 'Dr. Reyes', 'Take in the morning', 1, '2026-06-21 04:48:45'),
(9, 7, 'Amlodipine', '5mg', 'Once daily', '2026-01-15', '2026-04-15', 'Dr. Gomez', 'Take with dinner', 1, '2026-06-21 04:48:45'),
(10, 8, 'Levothyroxine', '75mcg', 'Once daily', '2026-01-01', '2026-06-30', 'Dr. Tan', 'Take on empty stomach', 1, '2026-06-21 04:48:45'),
(12, 1, 'biogesic', '5 mg', 'once daily', '2026-06-21', '2026-06-27', 'Dr.Pardos', '', 1, '2026-06-25 08:11:53');

-- --------------------------------------------------------

--
-- Table structure for table `medication_schedule`
--

CREATE TABLE `medication_schedule` (
  `schedule_id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `scheduled_time` time NOT NULL,
  `days_of_week` varchar(20) DEFAULT 'Daily',
  `is_taken` tinyint(1) DEFAULT 0,
  `taken_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medication_schedule`
--

INSERT INTO `medication_schedule` (`schedule_id`, `medication_id`, `scheduled_time`, `days_of_week`, `is_taken`, `taken_at`) VALUES
(1, 1, '08:00:00', 'Daily', 0, NULL),
(2, 2, '08:00:00', 'Daily', 0, NULL),
(3, 2, '20:00:00', 'Daily', 0, NULL),
(4, 3, '07:00:00', 'Daily', 0, NULL),
(5, 4, '21:00:00', 'Daily', 0, NULL),
(6, 5, '07:00:00', 'Daily', 0, NULL),
(7, 6, '06:00:00', 'Daily', 0, NULL),
(8, 7, '08:00:00', 'Daily', 0, NULL),
(9, 7, '20:00:00', 'Daily', 0, NULL),
(10, 8, '06:00:00', 'Daily', 0, NULL),
(13, 12, '05:00:00', 'Daily', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `full_name`, `birthdate`, `gender`, `created_at`, `updated_at`) VALUES
(1, 'juan_delacruz', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Dela Cruz', '1990-05-15', 'Male', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(2, 'maria_santos', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Santos', '1988-08-22', 'Female', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(3, 'pedro_reyes', 'pedro@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pedro Reyes', '1995-12-01', 'Male', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(4, 'ana_martinez', 'ana@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ana Martinez', '1992-03-10', 'Female', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(5, 'carlos_garcia', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Garcia', '1985-07-25', 'Male', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(6, 'lisa_tan', 'lisa@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lisa Tan', '1993-11-18', 'Female', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(7, 'mark_cruz', 'mark@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mark Cruz', '1991-04-07', 'Male', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(8, 'jennifer_lim', 'jennifer@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jennifer Lim', '1989-09-30', 'Female', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(9, 'robert_fernandez', 'robert@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert Fernandez', '1987-02-14', 'Male', '2026-06-21 04:47:36', '2026-06-21 04:47:36'),
(10, 'sarah_ortiz', 'sarah@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah Ortiz', '1994-06-28', 'Female', '2026-06-21 04:47:36', '2026-06-21 04:47:36');

-- --------------------------------------------------------

--
-- Table structure for table `vitals_log`
--

CREATE TABLE `vitals_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `blood_pressure_systolic` int(11) DEFAULT NULL,
  `blood_pressure_diastolic` int(11) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `blood_sugar` decimal(5,2) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `logged_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vitals_log`
--

INSERT INTO `vitals_log` (`log_id`, `user_id`, `blood_pressure_systolic`, `blood_pressure_diastolic`, `heart_rate`, `weight`, `blood_sugar`, `temperature`, `notes`, `logged_at`) VALUES
(1, 1, 120, 80, 72, 70.50, 95.00, 36.5, 'Morning check - feeling good', '2026-01-01 07:30:00'),
(2, 1, 125, 82, 75, 70.80, 98.00, 36.6, 'Afternoon check', '2026-01-01 14:00:00'),
(3, 1, 118, 78, 70, 70.20, 92.00, 36.4, 'Evening check', '2026-01-01 19:00:00'),
(4, 2, 110, 70, 65, 58.50, 88.00, 36.2, 'Morning check', '2026-01-01 08:00:00'),
(5, 2, 115, 72, 68, 58.80, 90.00, 36.3, 'Evening check', '2026-01-01 20:00:00'),
(6, 3, 135, 90, 85, 82.00, 105.00, 36.8, 'Morning check', '2026-01-01 06:00:00'),
(7, 3, 140, 92, 88, 82.50, 108.00, 36.9, 'Afternoon check', '2026-01-01 13:00:00'),
(8, 4, 115, 75, 70, 62.00, 85.00, 36.5, 'Morning check', '2026-01-01 07:00:00'),
(9, 5, 130, 85, 78, 75.00, 100.00, 36.7, 'Morning check', '2026-01-01 09:00:00'),
(10, 6, 112, 72, 68, 55.00, 82.00, 36.1, 'Morning check', '2026-01-01 08:30:00'),
(11, 1, 122, 82, 73, 71.00, 96.00, 36.5, 'Morning check', '2026-01-02 07:30:00'),
(12, 2, 112, 72, 66, 58.20, 89.00, 36.2, 'Morning check', '2026-01-02 08:00:00'),
(13, 3, 138, 92, 86, 82.80, 106.00, 36.8, 'Morning check', '2026-01-02 06:00:00'),
(14, 4, 116, 76, 71, 62.20, 86.00, 36.5, 'Morning check', '2026-01-02 07:00:00'),
(15, 1, 70, 40, 43, 54.00, 40.00, 35.0, 'Afternoon Check', '2026-06-25 11:17:30'),
(18, 1, 70, 40, 30, 50.00, 40.00, 35.0, '', '2026-06-25 15:43:11'),
(19, 1, 80, 50, 30, 45.00, 45.00, 35.0, 'Morning Check', '2026-06-25 15:49:19');

-- --------------------------------------------------------

--
-- Structure for view `health_summary`
--
DROP TABLE IF EXISTS `health_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `health_summary`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`, count(distinct `v`.`log_id`) AS `total_vitals`, avg(`v`.`blood_pressure_systolic`) AS `avg_systolic`, avg(`v`.`blood_pressure_diastolic`) AS `avg_diastolic`, avg(`v`.`heart_rate`) AS `avg_heart_rate`, avg(`v`.`weight`) AS `avg_weight`, avg(`v`.`blood_sugar`) AS `avg_blood_sugar`, count(distinct `m`.`medication_id`) AS `total_medications`, sum(case when `ms`.`is_taken` = 1 then 1 else 0 end) AS `doses_taken` FROM (((`users` `u` left join `vitals_log` `v` on(`u`.`user_id` = `v`.`user_id`)) left join `medications` `m` on(`u`.`user_id` = `m`.`user_id` and `m`.`is_active` = 1)) left join `medication_schedule` `ms` on(`m`.`medication_id` = `ms`.`medication_id`)) GROUP BY `u`.`user_id`, `u`.`full_name` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `health_goals`
--
ALTER TABLE `health_goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medication_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medication_schedule`
--
ALTER TABLE `medication_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `idx_med_time` (`medication_id`,`scheduled_time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vitals_log`
--
ALTER TABLE `vitals_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_date` (`user_id`,`logged_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `health_goals`
--
ALTER TABLE `health_goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `medication_schedule`
--
ALTER TABLE `medication_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vitals_log`
--
ALTER TABLE `vitals_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `health_goals`
--
ALTER TABLE `health_goals`
  ADD CONSTRAINT `health_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `medication_schedule`
--
ALTER TABLE `medication_schedule`
  ADD CONSTRAINT `medication_schedule_ibfk_1` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`medication_id`) ON DELETE CASCADE;

--
-- Constraints for table `vitals_log`
--
ALTER TABLE `vitals_log`
  ADD CONSTRAINT `vitals_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
