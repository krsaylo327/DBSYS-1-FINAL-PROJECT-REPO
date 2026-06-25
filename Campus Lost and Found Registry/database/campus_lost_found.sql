-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 09:57 AM
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
-- Database: `campus_lost_found`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_items_dashboard_summary`
-- (See below for the actual view)
--
CREATE TABLE `active_items_dashboard_summary` (
`category_id` int(11)
,`category_name` varchar(80)
,`total_active_items` bigint(21)
,`total_lost` decimal(22,0)
,`total_found` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(80) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Electronics', 'Mobile phones, laptops, earphones, chargers, and other electronic devices'),
(2, 'School Supplies', 'Notebooks, textbooks, pens, rulers, calculators, and academic materials'),
(3, 'Identification Cards', 'School IDs, government IDs, library cards, and access cards'),
(4, 'Clothing & Apparel', 'Uniforms, jackets, caps, scarves, and wearable items'),
(5, 'Bags & Luggage', 'Backpacks, sling bags, tote bags, and pouches'),
(6, 'Jewelry & Accessories', 'Rings, necklaces, bracelets, watches, and sunglasses'),
(7, 'Keys & Locks', 'House keys, locker keys, padlocks, and key chains'),
(8, 'Wallets & Purses', 'Wallets, coin purses, card holders, and money pouches'),
(9, 'Footwear', 'Shoes, sandals, slippers, and sports footwear'),
(10, 'Sports Equipment', 'Balls, rackets, resistance bands, and gym paraphernalia'),
(11, 'Documents', 'Printed reports, certificates, forms, contracts, and official papers'),
(12, 'Others', 'Miscellaneous items that do not fit any specific category');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `proof_description` text NOT NULL,
  `proof_photo_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`claim_id`, `item_id`, `claimant_id`, `proof_description`, `proof_photo_path`, `status`, `reviewed_by`, `admin_remarks`, `submitted_at`, `reviewed_at`) VALUES
(1, 3, 6, 'This is my Samsung Galaxy A35. My IMEI number is 358271012345678. I can present the original receipt from Lazada dated October 2025.', 'uploads/claims/claim_001_receipt.jpg', 'approved', 1, 'Verified IMEI against the device. Ownership confirmed. Claim approved.', '2026-02-05 01:15:00', '2026-02-06 02:00:00'),
(2, 3, 9, 'I think this might be my phone. It is blue and I also have a Samsung.', NULL, 'rejected', 1, 'Auto-rejected: another claim was approved for this item.', '2026-02-05 03:00:00', '2026-02-06 02:00:01'),
(3, 1, 4, 'These are my Xiaomi AirDots. I bought them from Shopee last November 2025. Order number: SP-20251103-00447. The charging case also has a small dent on the right side.', 'uploads/claims/claim_003_shopee.jpg', 'pending', NULL, NULL, '2026-01-17 06:30:00', NULL),
(4, 2, 10, 'I lost my iPhone 14 on January 20. My phone number is 09181234507 and the contact name \"Mama\" should be listed. I can provide the IMEI from the original box.', 'uploads/claims/claim_004_box.jpg', 'pending', NULL, NULL, '2026-01-21 00:45:00', NULL),
(5, 4, 7, 'The calculator has my name \"A. BAUTISTA\" written on the back in white marker. I am Ana Liza Bautista from BSIT-2A. I can show my class schedule to confirm.', NULL, 'approved', 1, 'Student ID confirmed. Name on calculator matches. Claim approved.', '2026-02-11 05:00:00', '2026-02-12 01:00:00'),
(6, 6, 9, 'The ID belongs to my blockmate. His student number is 2022-04-1187. I can accompany him to the Lost and Found office for verification.', NULL, 'pending', NULL, NULL, '2026-02-19 02:15:00', NULL),
(7, 7, 10, 'This is my PhilSys ID. I can state my PSN number: 0012-3456-7890-1. I have a photocopy of the card for comparison.', 'uploads/claims/claim_007_photocopy.jpg', 'pending', NULL, NULL, '2026-03-02 00:00:00', NULL),
(8, 8, 11, 'The navy jacket is mine. Inside the left pocket there is a folded piece of paper with my locker combination written on it. I can also identify a small burn mark near the right cuff.', NULL, 'pending', NULL, NULL, '2026-03-06 07:00:00', NULL),
(9, 9, 4, 'The jogging pants are mine. The name tag inside says J. DELA CRUZ which is me, Juan Miguel dela Cruz of BSIT-2B. I can present my school ID.', 'uploads/claims/claim_009_id.jpg', 'pending', NULL, NULL, '2026-03-09 03:00:00', NULL),
(10, 10, 5, 'I left a black Jansport bag in the hallway on March 12. Inside there should be a green notebook labeled \"DBMS Notes\" and a white Lenovo charger.', NULL, 'pending', NULL, NULL, '2026-03-13 01:30:00', NULL),
(11, 12, 7, 'The brown wallet is mine. Inside there is my university ID (Ana Liza Bautista), my BDO ATM card, and cash. I lost it on April 2 near the court.', 'uploads/claims/claim_011_id.jpg', 'rejected', 2, 'Claimant could not verify the exact cash amount or provide ATM card details. Claim denied pending additional proof.', '2026-04-03 02:00:00', '2026-04-04 06:30:00'),
(12, 13, 8, 'The silver bracelet belongs to my girlfriend. Her name starts with L — Lourdes. She lost it in the library last week and is too shy to file a claim herself. I am filing on her behalf.', NULL, 'rejected', 1, 'Claims must be filed by the owner directly. Third-party claims are not accepted. Please advise the owner to file personally.', '2026-04-08 00:30:00', '2026-04-08 03:00:00'),
(13, 14, 12, 'The TOR envelope is mine. My full name is Andres Bonifacio Navarro and I requested my TOR from the registrar on April 9. The envelope should have my name printed on the routing slip inside.', 'uploads/claims/claim_013_request.jpg', 'pending', NULL, NULL, '2026-04-10 08:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `reported_by` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `item_type` enum('lost','found') NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('active','claimed','archived') NOT NULL DEFAULT 'active',
  `date_reported` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `reported_by`, `category_id`, `location_id`, `item_type`, `item_name`, `description`, `photo_path`, `status`, `date_reported`, `created_at`) VALUES
(1, 4, 1, 1, 'lost', 'Black Xiaomi Earbuds', 'Xiaomi Redmi AirDots 3 in a black charging case. Left earbud has a small scratch. Last seen during IT Lab class.', 'uploads/items/item_001.jpg', 'active', '2026-01-15', '2026-06-02 06:07:52'),
(2, 5, 1, 3, 'found', 'iPhone 14 — Space Gray', 'Found near the desktop computers in the Engineering Computer Lab. Phone is locked. Has a cracked screen protector.', 'uploads/items/item_002.jpg', 'active', '2026-01-20', '2026-06-02 06:07:52'),
(3, 6, 1, 9, 'lost', 'Samsung Galaxy A35 — Blue', 'Blue Samsung phone with a transparent case and a blue lanyard attached. Possibly left at the cafeteria window seats during lunch.', 'uploads/items/item_003.jpg', 'claimed', '2026-02-03', '2026-06-02 06:07:52'),
(4, 7, 2, 2, 'lost', 'Casio FX-991EX Scientific Calculator', 'Black Casio ClassWiz calculator with the owner\'s name \"A. BAUTISTA\" written in white marker on the back.', NULL, 'active', '2026-02-10', '2026-06-02 06:07:52'),
(5, 8, 2, 5, 'found', 'Bundle of Lecture Notes — Green Folder', 'Green plastic folder containing handwritten lecture notes for what appears to be a DBMS or Programming subject. Found in the library reading area.', 'uploads/items/item_005.jpg', 'active', '2026-02-14', '2026-06-02 06:07:52'),
(6, 9, 3, 11, 'found', 'University Student ID', 'School ID belonging to a student. ID number visible: 2022-04-1187. Found in the Registrar\'s Office lobby on the floor.', 'uploads/items/item_006.jpg', 'active', '2026-02-18', '2026-06-02 06:07:52'),
(7, 10, 3, 10, 'lost', 'PhilSys National ID', 'Philippine National ID. Lost somewhere near the cafeteria exit. Owner is urgently requesting return.', NULL, 'active', '2026-03-01', '2026-06-02 06:07:52'),
(8, 11, 4, 7, 'found', 'Navy Blue Varsity Jacket', 'Dark blue varsity jacket with the university logo on the chest. Size L. Found on the gymnasium bleachers after a PE class.', 'uploads/items/item_008.jpg', 'active', '2026-03-05', '2026-06-02 06:07:52'),
(9, 4, 4, 8, 'lost', 'Gray PE Uniform Jogging Pants', 'Standard university gray jogging pants, Size M. Name tag sewn inside reads \"J. DELA CRUZ\". Missing since the last PE session.', NULL, 'active', '2026-03-08', '2026-06-02 06:07:52'),
(10, 5, 5, 4, 'found', 'Black Jansport Backpack', 'Medium-sized black Jansport backpack found in the Engineering Building 2nd floor hallway. Contains notebooks and a laptop charger (no laptop).', 'uploads/items/item_010.jpg', 'active', '2026-03-12', '2026-06-02 06:07:52'),
(11, 6, 7, 9, 'found', 'Keychain with 3 Keys and a Pikachu Charm', 'A set of three keys on a yellow Pikachu keychain. Found under a cafeteria dining table. Appears to be house/dorm keys.', 'uploads/items/item_011.jpg', 'active', '2026-03-18', '2026-06-02 06:07:52'),
(12, 7, 8, 12, 'lost', 'Brown Leather Bifold Wallet', 'Brown leather wallet containing a university ID, ATM card (BDO), and approximately Php 500.00 in cash. Lost near the basketball court.', NULL, 'active', '2026-04-02', '2026-06-02 06:07:52'),
(13, 8, 6, 6, 'found', 'Silver Bracelet with Initial Pendant', 'Thin silver bracelet with a small pendant showing the letter \"L\". Found on the floor of the library periodicals section.', 'uploads/items/item_013.jpg', 'active', '2026-04-07', '2026-06-02 06:07:52'),
(14, 12, 11, 11, 'found', 'Official Transcript of Records (TOR)', 'Sealed official TOR envelope from the university registrar. Name on document appears to start with \"R. PADI...\". Found in the Administration lobby.', 'uploads/items/item_014.jpg', 'active', '2026-04-10', '2026-06-02 06:07:52');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `location_id` int(11) NOT NULL,
  `building_name` varchar(100) NOT NULL,
  `room_or_area` varchar(80) DEFAULT NULL,
  `campus_zone` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`location_id`, `building_name`, `room_or_area`, `campus_zone`) VALUES
(1, 'Main Academic Building', 'Room 201 — Information Technology Lab', 'North Wing'),
(2, 'Main Academic Building', 'Room 105 — Lecture Hall', 'North Wing'),
(3, 'Engineering Building', 'Room 310 — Computer Laboratory', 'East Wing'),
(4, 'Engineering Building', 'Hallway — 2nd Floor', 'East Wing'),
(5, 'Library Building', 'Reading Area — Ground Floor', 'Central'),
(6, 'Library Building', 'Periodicals Section — 2nd Floor', 'Central'),
(7, 'Gymnasium', 'Bleachers — Court Side A', 'South Wing'),
(8, 'Gymnasium', 'Locker Room — Men', 'South Wing'),
(9, 'Cafeteria', 'Dining Area — Window Seats', 'West Wing'),
(10, 'Cafeteria', 'Near the Exit Gate', 'West Wing'),
(11, 'Administration Building', 'Registrar\'s Office Lobby', 'Central'),
(12, 'Open Grounds', 'Basketball Court — Covered Court', 'South Wing');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','student','staff') NOT NULL DEFAULT 'student',
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `contact_number`, `created_at`) VALUES
(1, 'Eren Bautista', 'admin.bautista@university.edu.ph', '$2y$10$RO.VcvdTEm0P8.bz7x4eTOiH546SXjLwXkGkb0Jq.wfQG8EyJCMay', 'admin', '09171000001', '2026-06-02 06:07:25'),
(2, 'Ann B. Reyes', 'staff.reyes@university.edu.ph', '$2y$10$xkprXoTiqL1CNZsh9f4E4Onuyx9AZ4Jlwxq/uDFsR1N3gWPKFkP7O', 'staff', '09171000002', '2026-06-02 06:07:25'),
(3, 'Mae F. Napagao', 'staff.mendoza@university.edu.ph', '$2y$10$aPhxLqJLSf1f.FDelzXEzuYhEbSG.wn/MKHiBF4tHH1yxf25X.Uom', 'staff', '09171000003', '2026-06-02 06:07:25'),
(4, 'Kim Juan Secret', 'kimjuan.secret@student.edu.ph', '$2y$10$WTtDBbNGnwkowZ08oqDFruJqJjaNHCxVSfTwvZwAhqFCplhAjxmv6', 'student', '09181234501', '2026-06-02 06:07:25'),
(5, 'Maria Jo Seniel', 'mariajo.seniel@student.edu.ph', '$2y$10$zi80Ropry7ccSWJ7jrfStusExGIQ025yeg3WUtcLsvcCEGUDMbr4G', 'student', '09181234502', '2026-06-02 06:07:25'),
(6, 'Pedro Jose Ysulat', 'pedrojose.ysulat@student.edu.ph', '$2y$10$53sRNBz.Eh.yhdY4eJkRVeRRkB6R0kiBxFxr4Bx35vlGc9wo3OQKi', 'student', '09181234503', '2026-06-02 06:07:25'),
(7, 'Ana Vhonn Bautista', 'anavhonn.bautista@student.edu.ph', '$2y$10$PEMCsdFzn1pKHciiVw4uYe032SPiLQmd4WBWwTA2/BsdAnj0IWqI.', 'student', '09181234504', '2026-06-02 06:07:25'),
(8, 'Ramon Lorenz Vargaz', 'ramonlorenz.vargaz@student.edu.ph', '$2y$10$i/UoXgqFPisRuYJMeUEwpOy/8auZZNVjaVDbnedRgMeeDzmu9iV8e', 'student', '09181234505', '2026-06-02 06:07:25'),
(9, 'Cartherine Joy Pascual', 'catherinejoy.pascual@student.edu.ph', '$2y$10$sDAQHs6pEMoScJk3omyLMuGCGEESNzdNOhIgqUgsajM7C0GPtb/ka', 'student', '09181234506', '2026-06-02 06:07:25'),
(10, 'Chacha Andrei Torres', 'chachaandrei.torres@student.edu.ph', '$2y$10$CwDNIkuOZRwkFFTsNJJp3ea4ZXJc.UVied.iGHWNynR.M5hn7noIS', 'student', '09181234507', '2026-06-02 06:07:25'),
(11, 'Cam Mae Gonzales', 'cammae.gonzales@student.edu.ph', '$2y$10$0LhElvyzWuhWARZB3/rgcOo16eUhFy4qtl0h0AtUr7qCVPn2iv7ra', 'student', '09181234508', '2026-06-02 06:07:25'),
(12, 'Andres Ramel Navarro Jr', 'andresramel.navarrojr@student.edu.ph', '$2y$10$oVUHFwIOOYalmP4Xpi8tjOidyIh/of7vLVHZA19xjTiJlrN7PpWva', 'student', '09181234509', '2026-06-02 06:07:25');

-- --------------------------------------------------------

--
-- Structure for view `active_items_dashboard_summary`
--
DROP TABLE IF EXISTS `active_items_dashboard_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_items_dashboard_summary`  AS SELECT `c`.`category_id` AS `category_id`, `c`.`category_name` AS `category_name`, count(`i`.`item_id`) AS `total_active_items`, sum(case when `i`.`item_type` = 'lost' then 1 else 0 end) AS `total_lost`, sum(case when `i`.`item_type` = 'found' then 1 else 0 end) AS `total_found` FROM (`categories` `c` left join `items` `i` on(`c`.`category_id` = `i`.`category_id` and `i`.`status` = 'active')) GROUP BY `c`.`category_id`, `c`.`category_name` ORDER BY count(`i`.`item_id`) DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uq_category_name` (`category_name`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `idx_claims_item_id` (`item_id`),
  ADD KEY `idx_claims_claimant_id` (`claimant_id`),
  ADD KEY `idx_claims_reviewed_by` (`reviewed_by`),
  ADD KEY `idx_claims_status` (`status`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_items_reported_by` (`reported_by`),
  ADD KEY `idx_items_category_id` (`category_id`),
  ADD KEY `idx_items_location_id` (`location_id`),
  ADD KEY `idx_items_status` (`status`),
  ADD KEY `idx_items_type` (`item_type`),
  ADD KEY `idx_items_date_reported` (`date_reported`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`location_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `fk_claims_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_claims_item` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_claims_user` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_loc` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_user` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
