-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 08:38 AM
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
-- Database: `sli_clm`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_rep` varchar(255) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL,
  `client_phone_num` varchar(20) DEFAULT NULL,
  `client_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contract_statuses`
--

CREATE TABLE `contract_statuses` (
  `contract_status_id` int(11) NOT NULL,
  `contract_status_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contract_statuses`
--

INSERT INTO `contract_statuses` (`contract_status_id`, `contract_status_name`) VALUES
(1, 'Active'),
(2, 'Expired'),
(3, 'None');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_minutes`
--

CREATE TABLE `meeting_minutes` (
  `meet_id` int(11) NOT NULL,
  `meet_date` date NOT NULL,
  `client_id` int(11) NOT NULL,
  `meet_source` varchar(255) NOT NULL,
  `meet_issue` text DEFAULT NULL,
  `meet_action` text DEFAULT NULL,
  `meet_timeline` date DEFAULT NULL,
  `meet_status_id` int(11) DEFAULT 1,
  `meet_prio_id` int(11) NOT NULL DEFAULT 4,
  `meet_remarks` text DEFAULT NULL,
  `meet_respo` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meet_prio`
--

CREATE TABLE `meet_prio` (
  `prio_id` int(11) NOT NULL,
  `prio_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meet_prio`
--

INSERT INTO `meet_prio` (`prio_id`, `prio_name`) VALUES
(4, ''),
(1, 'High'),
(3, 'Low'),
(2, 'Medium');

-- --------------------------------------------------------

--
-- Table structure for table `meet_status`
--

CREATE TABLE `meet_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meet_status`
--

INSERT INTO `meet_status` (`status_id`, `status_name`) VALUES
(2, 'Done'),
(1, 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `proposal_monitor`
--

CREATE TABLE `proposal_monitor` (
  `prop_id` int(11) NOT NULL,
  `prospect_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `prop_series_num` varchar(255) NOT NULL DEFAULT '',
  `prop_date_sent` date DEFAULT NULL,
  `prop_sent_by` varchar(255) NOT NULL,
  `prop_signed_date` date DEFAULT NULL,
  `prop_remarks` text DEFAULT NULL,
  `proposal_status_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposal_statuses`
--

CREATE TABLE `proposal_statuses` (
  `proposal_status_id` int(11) NOT NULL,
  `proposal_status_name` varchar(50) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proposal_statuses`
--

INSERT INTO `proposal_statuses` (`proposal_status_id`, `proposal_status_name`, `is_default`) VALUES
(1, 'Open', 1),
(2, 'Closed', 0);

-- --------------------------------------------------------

--
-- Table structure for table `prospect_monitor`
--

CREATE TABLE `prospect_monitor` (
  `prospect_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `prospect_date` date NOT NULL,
  `prospect_status_remarks` text DEFAULT NULL,
  `prospect_reason` text DEFAULT NULL,
  `prospect_notice_date` date NOT NULL,
  `prospect_notice_to` date NOT NULL,
  `prospect_month_est` varchar(255) DEFAULT NULL,
  `prospect_contract_sign` date DEFAULT NULL,
  `prospect_contract_period` varchar(255) DEFAULT 'TBD',
  `prospect_contract_start` date NOT NULL,
  `prospect_contract_end` date NOT NULL,
  `prospect_contract_remarks` text DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `prospect_service_remarks` varchar(255) DEFAULT NULL,
  `contract_status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prospect_stages`
--

CREATE TABLE `prospect_stages` (
  `stage_id` int(11) NOT NULL,
  `stage_name` varchar(255) NOT NULL,
  `allowed_from_stages` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prospect_stages`
--

INSERT INTO `prospect_stages` (`stage_id`, `stage_name`, `allowed_from_stages`) VALUES
(1, 'Stage 1', '1,2'),
(2, 'Stage 2', '1,2,3'),
(3, 'Stage 3', '2,3,4'),
(4, 'Stage 4', '3,4,5'),
(5, 'Stage 5', '5');

-- --------------------------------------------------------

--
-- Table structure for table `prospect_statuses`
--

CREATE TABLE `prospect_statuses` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(255) NOT NULL,
  `stage_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prospect_statuses`
--

INSERT INTO `prospect_statuses` (`status_id`, `status_name`, `stage_id`) VALUES
(1, 'New Lead', 1),
(2, 'Attempting Contact', 1),
(3, 'Contacted', 1),
(4, 'Interested', 2),
(5, 'Not Interested', 2),
(6, 'Unqualified', 2),
(7, 'Follow-Up Required', 2),
(8, 'Needs Assessment Done', 3),
(9, 'Proposal Sent', 3),
(10, 'Negotiation In Progress', 3),
(11, 'Closed - Won', 4),
(12, 'Closed - Lost', 4),
(13, 'On Hold', 4),
(14, 'Client Onboarding', 5),
(15, 'Repeat Business Opportunity', 5);

-- --------------------------------------------------------

--
-- Table structure for table `region`
--

CREATE TABLE `region` (
  `region_id` int(11) NOT NULL,
  `region_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `region`
--

INSERT INTO `region` (`region_id`, `region_name`) VALUES
(1, 'Luzon'),
(2, 'Visayas'),
(3, 'Mindanao');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`) VALUES
(1, 'Warehouse Management'),
(2, 'Transport Services'),
(3, 'Distribution Management'),
(4, 'Built-To-Suit Warehouses'),
(5, 'MHE Sales & Rentals'),
(6, 'Racking System Sales & Rentals'),
(7, 'Reverse Logistics'),
(8, 'Value-Added Services'),
(9, 'Security'),
(10, 'TBD');

-- --------------------------------------------------------

--
-- Table structure for table `trade_fairs`
--

CREATE TABLE `trade_fairs` (
  `fair_id` int(11) NOT NULL,
  `fair_title` varchar(255) NOT NULL,
  `fair_date_start` date NOT NULL,
  `fair_date_end` date NOT NULL,
  `fair_venue` text DEFAULT NULL,
  `fair_desc` text DEFAULT NULL,
  `fair_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `role` enum('Admin','BD','Exec') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `user_email`, `role`) VALUES
(1, 'SLI_ADMIN', '$2y$10$ruRu/2p9AmwYuNLqcmsyZuDRuYdcm2NVvLYKwJ9w4mi.zjFI0jBhm', 'zeroalpha415@gmail.com', 'Admin'),
(2, 'SLI_BD1', '$2y$10$9tlfPQ4xLSQqJUUfUUV1vuUug47p7vtYlJirsQiSM5kYpn/BEZ9c6', 'alexandrajoyce.albina@safexpress.com.ph', 'BD'),
(3, 'SLI_BD2', '$2y$10$MZn/8RRcTo.23zre/EMpvetqWeUdmIp7RIpm8fhQ6KfvyIDWwnhJy', 'ivanna.samera@safexpress.com.ph', 'BD'),
(4, 'SLI_PRES', '$2y$10$xQmg2vdqLSCh/XbYHlHkreYyvN2gtFpxgGntkxV22Z4e.GdLYIYgu', 'sli_pres_placeholder@example.com', 'Exec'),
(5, 'SLI_CEO', '$2y$10$sOEtjWlH9sSFlQE6UvZzUOVBMQiJO3hLTthZGn7TF0s/bYSzLPZra', 'richard.cunanan@safexpress.com.ph', 'Exec');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `region_id` (`region_id`);

--
-- Indexes for table `contract_statuses`
--
ALTER TABLE `contract_statuses`
  ADD PRIMARY KEY (`contract_status_id`),
  ADD UNIQUE KEY `contract_status_name` (`contract_status_name`);

--
-- Indexes for table `meeting_minutes`
--
ALTER TABLE `meeting_minutes`
  ADD PRIMARY KEY (`meet_id`),
  ADD KEY `meet_status_id` (`meet_status_id`),
  ADD KEY `meet_prio_id` (`meet_prio_id`),
  ADD KEY `fk_client_id` (`client_id`);

--
-- Indexes for table `meet_prio`
--
ALTER TABLE `meet_prio`
  ADD PRIMARY KEY (`prio_id`),
  ADD UNIQUE KEY `prio_name` (`prio_name`);

--
-- Indexes for table `meet_status`
--
ALTER TABLE `meet_status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `proposal_monitor`
--
ALTER TABLE `proposal_monitor`
  ADD PRIMARY KEY (`prop_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `prospect_id` (`prospect_id`),
  ADD KEY `proposal_status_id` (`proposal_status_id`);

--
-- Indexes for table `proposal_statuses`
--
ALTER TABLE `proposal_statuses`
  ADD PRIMARY KEY (`proposal_status_id`),
  ADD UNIQUE KEY `proposal_status_name` (`proposal_status_name`);

--
-- Indexes for table `prospect_monitor`
--
ALTER TABLE `prospect_monitor`
  ADD PRIMARY KEY (`prospect_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `contract_status_id` (`contract_status_id`);

--
-- Indexes for table `prospect_stages`
--
ALTER TABLE `prospect_stages`
  ADD PRIMARY KEY (`stage_id`);

--
-- Indexes for table `prospect_statuses`
--
ALTER TABLE `prospect_statuses`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Indexes for table `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`region_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `trade_fairs`
--
ALTER TABLE `trade_fairs`
  ADD PRIMARY KEY (`fair_id`);

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
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contract_statuses`
--
ALTER TABLE `contract_statuses`
  MODIFY `contract_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `meeting_minutes`
--
ALTER TABLE `meeting_minutes`
  MODIFY `meet_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meet_prio`
--
ALTER TABLE `meet_prio`
  MODIFY `prio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `meet_status`
--
ALTER TABLE `meet_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `proposal_monitor`
--
ALTER TABLE `proposal_monitor`
  MODIFY `prop_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposal_statuses`
--
ALTER TABLE `proposal_statuses`
  MODIFY `proposal_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `prospect_monitor`
--
ALTER TABLE `prospect_monitor`
  MODIFY `prospect_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prospect_stages`
--
ALTER TABLE `prospect_stages`
  MODIFY `stage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prospect_statuses`
--
ALTER TABLE `prospect_statuses`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `region`
--
ALTER TABLE `region`
  MODIFY `region_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `trade_fairs`
--
ALTER TABLE `trade_fairs`
  MODIFY `fair_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_2` FOREIGN KEY (`region_id`) REFERENCES `region` (`region_id`);

--
-- Constraints for table `meeting_minutes`
--
ALTER TABLE `meeting_minutes`
  ADD CONSTRAINT `fk_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`),
  ADD CONSTRAINT `meeting_minutes_ibfk_1` FOREIGN KEY (`meet_status_id`) REFERENCES `meet_status` (`status_id`),
  ADD CONSTRAINT `meeting_minutes_ibfk_2` FOREIGN KEY (`meet_prio_id`) REFERENCES `meet_prio` (`prio_id`);

--
-- Constraints for table `proposal_monitor`
--
ALTER TABLE `proposal_monitor`
  ADD CONSTRAINT `proposal_monitor_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposal_monitor_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposal_monitor_ibfk_3` FOREIGN KEY (`prospect_id`) REFERENCES `prospect_monitor` (`prospect_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `proposal_monitor_ibfk_4` FOREIGN KEY (`proposal_status_id`) REFERENCES `proposal_statuses` (`proposal_status_id`);

--
-- Constraints for table `prospect_monitor`
--
ALTER TABLE `prospect_monitor`
  ADD CONSTRAINT `prospect_monitor_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prospect_monitor_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prospect_monitor_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `prospect_statuses` (`status_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prospect_monitor_ibfk_4` FOREIGN KEY (`contract_status_id`) REFERENCES `contract_statuses` (`contract_status_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
