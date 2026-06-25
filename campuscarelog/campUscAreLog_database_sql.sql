-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2026 at 10:11 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_clinic`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `RegisterClinicVisit` (IN `p_patient_id` INT, IN `p_nurse_id` INT, IN `p_visit_date` DATE, IN `p_visit_time` TIME, IN `p_symptoms` VARCHAR(255), IN `p_diagnosis` VARCHAR(255), IN `p_treatment` VARCHAR(255), OUT `p_visit_id` INT)   BEGIN
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
        SET p_visit_id = NULL;
        SELECT 'Error: Failed to register clinic visit' AS message;
    END;
    
    START TRANSACTION;
    
    INSERT INTO clinic_visits (
        patient_id, 
        nurse_id, 
        visit_date, 
        visit_time, 
        symptoms, 
        diagnosis, 
        treatment
    ) VALUES (
        p_patient_id,
        p_nurse_id,
        p_visit_date,
        p_visit_time,
        p_symptoms,
        p_diagnosis,
        p_treatment
    );
    
    SET p_visit_id = LAST_INSERT_ID();
    
    COMMIT;
    
    SELECT CONCAT('Clinic visit registered successfully with ID: ', p_visit_id) AS message;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `clinic_visits`
--

CREATE TABLE `clinic_visits` (
  `visit_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `nurse_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `symptoms` varchar(255) NOT NULL,
  `diagnosis` varchar(255) NOT NULL,
  `treatment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clinic_visits`
--

INSERT INTO `clinic_visits` (`visit_id`, `patient_id`, `nurse_id`, `visit_date`, `visit_time`, `symptoms`, `diagnosis`, `treatment`) VALUES
(1, 1, 1, '2026-05-20', '08:15:00', 'Fever and headache', 'Viral infection', 'Paracetamol and rest'),
(2, 2, 2, '2026-05-20', '09:00:00', 'Cough and sore throat', 'Upper respiratory infection', 'Amoxicillin and fluids'),
(3, 3, 3, '2026-05-21', '10:30:00', 'Stomach pain', 'Hyperacidity', 'Antacid suspension'),
(4, 4, 4, '2026-05-21', '11:10:00', 'Body pain after sports', 'Muscle strain', 'Ibuprofen and rest'),
(5, 5, 5, '2026-05-22', '13:20:00', 'Sneezing and itchy eyes', 'Allergic rhinitis', 'Cetirizine'),
(6, 6, 6, '2026-05-22', '14:00:00', 'Loose bowel movement', 'Mild dehydration', 'Oral rehydration salts'),
(7, 7, 7, '2026-05-23', '08:40:00', 'Toothache', 'Dental pain', 'Mefenamic acid'),
(8, 8, 8, '2026-05-23', '09:25:00', 'Mild cough', 'Common cold', 'Cough syrup'),
(9, 9, 9, '2026-05-24', '10:05:00', 'Sore throat and fever', 'Bacterial pharyngitis', 'Amoxicillin and paracetamol'),
(10, 10, 10, '2026-05-24', '15:15:00', 'Weakness and dizziness', 'Possible dehydration', 'Fluids and vitamin C');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `expiration_date` date NOT NULL,
  `dosage` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `medicine_name`, `stock_quantity`, `expiration_date`, `dosage`) VALUES
(1, 'Paracetamol 500mg', 120, '2027-01-31', '1 tablet every 6 hours'),
(2, 'Amoxicillin 500mg', 80, '2026-12-15', '1 capsule every 8 hours'),
(3, 'Ibuprofen 200mg', 60, '2027-03-20', '1 tablet every 8 hours'),
(4, 'Cetirizine 10mg', 75, '2026-11-10', '1 tablet once daily'),
(5, 'Antacid Suspension', 40, '2026-10-05', '10 mL after meals'),
(6, 'Oral Rehydration Salts', 150, '2027-05-18', '1 sachet mixed with water'),
(7, 'Mefenamic Acid 500mg', 50, '2026-09-30', '1 capsule every 8 hours'),
(8, 'Vitamin C 500mg', 200, '2027-02-14', '1 tablet once daily'),
(9, 'Cough Syrup', 55, '2026-12-01', '10 mL every 6 hours'),
(10, 'Metronidazole 500mg', 35, '2026-08-25', '1 tablet every 8 hours');

-- --------------------------------------------------------

--
-- Table structure for table `nurses`
--

CREATE TABLE `nurses` (
  `nurse_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `shift_schedule` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nurses`
--

INSERT INTO `nurses` (`nurse_id`, `first_name`, `last_name`, `contact_number`, `shift_schedule`) VALUES
(1, 'Aileen', 'Gillado', '09051230001', 'Morning Shift'),
(2, 'Marjorie', 'Ganacias', '09051230002', 'Afternoon Shift'),
(3, 'Jenelyn', 'Fernandez', '09051230003', 'Night Shift'),
(4, 'Rose', 'Ysulat', '09051230004', 'Morning Shift'),
(5, 'Karen', 'Seniel', '09051230005', 'Afternoon Shift'),
(6, 'Sheryl', 'Azar', '09051230006', 'Night Shift'),
(7, 'Liza', 'Erispe', '09051230007', 'Morning Shift'),
(8, 'Myrna', 'Vargas', '09051230008', 'Afternoon Shift'),
(9, 'Catherine', 'Sagucom', '09051230009', 'Night Shift'),
(10, 'Elena', 'Napagao', '09051230010', 'Morning Shift');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `birthdate` date NOT NULL,
  `course` varchar(50) NOT NULL,
  `year_level` int(11) NOT NULL,
  `contact_number` varchar(15) DEFAULT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `first_name`, `last_name`, `gender`, `birthdate`, `course`, `year_level`, `contact_number`, `address`) VALUES
(1, 'Melanie', 'Habulin', 'Female', '2004-05-12', 'BS Nursing', 2, '09171234567', 'Valderrama, Antique'),
(2, 'Juan', 'Dela Cruz', 'Male', '2003-11-28', 'BSIT', 3, '09181234567', 'Pandan, Antique'),
(3, 'Andrea', 'Brillantes', 'Female', '2005-02-14', 'BSBA', 1, '09192345678', 'Libertad, Antique'),
(4, 'Carlo', 'Aquino', 'Male', '2004-08-09', 'BSED', 2, '09981234567', 'Culasi, Antique'),
(5, 'Angelica', 'Panganiban', 'Female', '2002-12-03', 'BS Psychology', 4, '09123456789', 'Bugasong, Antique'),
(6, 'Joshua', 'Garcia', 'Male', '2005-06-21', 'BSIT', 1, '09992345678', 'Anini-y, Antique'),
(7, 'Princess', 'Napagao', 'Female', '2003-09-17', 'BSHM', 3, '09174561234', 'San Joaquin, Iloilo'),
(8, 'Mark', 'Azar', 'Male', '2004-01-30', 'BS Criminology', 2, '09185678901', 'San Jose, Antique'),
(9, 'Nicole', 'Solomon', 'Female', '2002-07-25', 'BS Accountancy', 4, '09166778899', 'San Jose, Antique'),
(10, 'Paolo', 'Javier', 'Male', '2005-10-11', 'BSA', 1, '09177889900', 'Tobias, Fornier');

-- --------------------------------------------------------

--
-- Table structure for table `visit_medicines`
--

CREATE TABLE `visit_medicines` (
  `visit_medicines_id` int(11) NOT NULL,
  `visit_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visit_medicines`
--

INSERT INTO `visit_medicines` (`visit_medicines_id`, `visit_id`, `medicine_id`, `quantity`) VALUES
(1, 1, 1, 2),
(2, 2, 2, 3),
(3, 3, 5, 1),
(4, 4, 3, 2),
(5, 5, 4, 1),
(6, 6, 6, 2),
(7, 7, 7, 2),
(8, 8, 9, 1),
(9, 9, 2, 2),
(10, 10, 8, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_clinic_visit_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_clinic_visit_summary` (
`visit_id` int(11)
,`patient_id` int(11)
,`patient_name` varchar(101)
,`gender` varchar(10)
,`birthdate` date
,`course` varchar(50)
,`year_level` int(11)
,`patient_contact` varchar(15)
,`nurse_name` varchar(101)
,`shift_schedule` varchar(50)
,`visit_date` date
,`visit_time` time
,`symptoms` varchar(255)
,`diagnosis` varchar(255)
,`treatment` varchar(255)
);

-- --------------------------------------------------------

--
-- Structure for view `v_clinic_visit_summary`
--
DROP TABLE IF EXISTS `v_clinic_visit_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_clinic_visit_summary`  AS SELECT `cv`.`visit_id` AS `visit_id`, `p`.`patient_id` AS `patient_id`, concat(`p`.`first_name`,' ',`p`.`last_name`) AS `patient_name`, `p`.`gender` AS `gender`, `p`.`birthdate` AS `birthdate`, `p`.`course` AS `course`, `p`.`year_level` AS `year_level`, `p`.`contact_number` AS `patient_contact`, concat(`n`.`first_name`,' ',`n`.`last_name`) AS `nurse_name`, `n`.`shift_schedule` AS `shift_schedule`, `cv`.`visit_date` AS `visit_date`, `cv`.`visit_time` AS `visit_time`, `cv`.`symptoms` AS `symptoms`, `cv`.`diagnosis` AS `diagnosis`, `cv`.`treatment` AS `treatment` FROM ((`clinic_visits` `cv` join `patients` `p` on(`cv`.`patient_id` = `p`.`patient_id`)) join `nurses` `n` on(`cv`.`nurse_id` = `n`.`nurse_id`)) ORDER BY `cv`.`visit_date` DESC, `cv`.`visit_time` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clinic_visits`
--
ALTER TABLE `clinic_visits`
  ADD PRIMARY KEY (`visit_id`),
  ADD KEY `idx_clinic_visits_patient` (`patient_id`),
  ADD KEY `idx_clinic_visits_nurse` (`nurse_id`),
  ADD KEY `idx_clinic_visits_date` (`visit_date`),
  ADD KEY `idx_clinic_visits_datetime` (`visit_date`,`visit_time`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`medicine_id`),
  ADD KEY `idx_medicines_name` (`medicine_name`),
  ADD KEY `idx_medicines_expiration` (`expiration_date`),
  ADD KEY `idx_medicines_stock` (`stock_quantity`);

--
-- Indexes for table `nurses`
--
ALTER TABLE `nurses`
  ADD PRIMARY KEY (`nurse_id`),
  ADD KEY `idx_nurses_last_name` (`last_name`),
  ADD KEY `idx_nurses_contact` (`contact_number`),
  ADD KEY `idx_nurses_shift` (`shift_schedule`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `contact_number` (`contact_number`),
  ADD KEY `idx_patients_last_name` (`last_name`),
  ADD KEY `idx_patients_first_name` (`first_name`),
  ADD KEY `idx_patients_contact` (`contact_number`),
  ADD KEY `idx_patients_course_year` (`course`,`year_level`);

--
-- Indexes for table `visit_medicines`
--
ALTER TABLE `visit_medicines`
  ADD PRIMARY KEY (`visit_medicines_id`),
  ADD KEY `idx_visit_medicines_visit` (`visit_id`),
  ADD KEY `idx_visit_medicines_medicine` (`medicine_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clinic_visits`
--
ALTER TABLE `clinic_visits`
  MODIFY `visit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `nurses`
--
ALTER TABLE `nurses`
  MODIFY `nurse_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `visit_medicines`
--
ALTER TABLE `visit_medicines`
  MODIFY `visit_medicines_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clinic_visits`
--
ALTER TABLE `clinic_visits`
  ADD CONSTRAINT `clinic_visits_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `clinic_visits_ibfk_2` FOREIGN KEY (`nurse_id`) REFERENCES `nurses` (`nurse_id`);

--
-- Constraints for table `visit_medicines`
--
ALTER TABLE `visit_medicines`
  ADD CONSTRAINT `visit_medicines_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `clinic_visits` (`visit_id`),
  ADD CONSTRAINT `visit_medicines_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`medicine_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
