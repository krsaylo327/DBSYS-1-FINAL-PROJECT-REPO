-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: June 25, 2026 at 07:11 PM
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
-- Database: `esports_tournament`
--
CREATE DATABASE IF NOT EXISTS `esports_tournament` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `esports_tournament`;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `wins` int(11) DEFAULT 0,
  `losses` int(11) DEFAULT 0,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`team_id`, `team_name`, `wins`, `losses`, `points`) VALUES
(1, 'T1', 2, 0, 6),
(2, 'Bilibili Gaming', 1, 1, 3),
(3, 'G2 Esports', 2, 0, 6),
(4, 'Team Liquid', 0, 2, 0),
(5, 'Top Esports', 1, 1, 3),
(6, 'Fnatic', 1, 1, 3),
(7, 'Sentinels', 2, 0, 6),
(8, 'Paper Rex', 1, 1, 3),
(9, 'Gen.G Esports', 0, 2, 0),
(10, 'DRX', 0, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `player_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `ign` varchar(100) NOT NULL,
  `mvp_points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`player_id`, `team_id`, `ign`, `mvp_points`) VALUES
(1, 1, 'Faker', 2),
(2, 1, 'Oner', 1),
(3, 2, 'Bin', 1),
(4, 3, 'Caps', 1),
(5, 3, 'BrokenBlade', 1),
(6, 5, '369', 2),
(7, 6, 'Chronicle', 1),
(8, 7, 'TenZ', 3),
(9, 8, 'f0rsakeN', 1),
(10, 4, 'APA', 0),
(11, 2, 'Elk', 0),
(12, 5, 'Creme', 0);

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `game_id` int(11) NOT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `schedule_date` datetime NOT NULL,
  `venue` varchar(100) NOT NULL,
  `is_completed` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`game_id`, `team1_id`, `team2_id`, `schedule_date`, `venue`, `is_completed`) VALUES
(1, 1, 2, '2026-06-10 13:00:00', 'Seoul Dome', 1),
(2, 3, 4, '2026-06-12 15:30:00', 'Berlin Stage', 1),
(3, 5, 6, '2026-06-14 18:00:00', 'London Court', 1),
(4, 7, 8, '2026-06-16 20:00:00', 'Los Angeles Arena', 1),
(5, 9, 10, '2026-06-18 14:00:00', 'Tokyo Arena', 1),
(6, 1, 4, '2026-06-20 16:00:00', 'Seoul Dome', 1),
(7, 3, 9, '2026-06-22 19:00:00', 'Paris Complex', 1),
(8, 7, 6, '2026-06-23 21:00:00', 'Los Angeles Arena', 1),
(9, 2, 5, '2026-06-28 17:00:00', 'Shanghai Hub', 0),
(10, 8, 10, '2026-06-30 19:30:00', 'Singapore Center', 0);

-- --------------------------------------------------------

--
-- Table structure for table `game_results`
--

CREATE TABLE `game_results` (
  `result_id` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `winner_team_id` int(11) NOT NULL,
  `team1_score` int(11) DEFAULT 0,
  `team2_score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_results`
--

INSERT INTO `game_results` (`result_id`, `game_id`, `winner_team_id`, `team1_score`, `team2_score`) VALUES
(1, 1, 1, 13, 7),
(2, 2, 3, 13, 5),
(3, 3, 5, 13, 11),
(4, 4, 7, 13, 10),
(5, 5, 6, 8, 13),
(6, 6, 1, 13, 4),
(7, 7, 3, 16, 14),
(8, 8, 7, 13, 9);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','viewer') DEFAULT 'viewer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'admin_league', '$2y$10$WqXG.75y8G3kBy2T9fOpeexHqIOm1Kymf1hZzIqVdYxKzO39YgA6q', 'admin'),
(2, 'guest_scout', '$2y$10$7R0Zq72Hk8Iq7XgBvA3Sre8W6mPnZ2xFk7yB1wzXmC3qL9fO8hU4.', 'viewer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`),
  ADD UNIQUE KEY `team_name` (`team_name`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`player_id`),
  ADD KEY `team_id` (`team_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`game_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`);

--
-- Indexes for table `game_results`
--
ALTER TABLE `game_results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `game_id` (`game_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `player_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `game_results`
--
ALTER TABLE `game_results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `games`
--
ALTER TABLE `games`
  ADD CONSTRAINT `games_ibfk_1` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `games_ibfk_2` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `game_results`
--
ALTER TABLE `game_results`
  ADD CONSTRAINT `game_results_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;