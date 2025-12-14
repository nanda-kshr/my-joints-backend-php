-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Dec 14, 2025 at 02:02 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

CREATE DATABASE IF NOT EXISTS myjoints CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE myjoints;


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `myjoints`
--

-- --------------------------------------------------------

--
-- Table structure for table `comorbidities`
--

CREATE TABLE `comorbidities` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comorbidities`
--

INSERT INTO `comorbidities` (`id`, `patient_id`, `text`, `created_at`) VALUES
(1, 2, 'good', '2025-12-14 13:54:13');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `complaint` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `patient_id`, `doctor_id`, `complaint`, `created_at`) VALUES
(1, 2, 2, 'Good', '2025-12-14 13:54:07');

-- --------------------------------------------------------

--
-- Table structure for table `disease_scores`
--

CREATE TABLE `disease_scores` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `sdai` decimal(5,2) DEFAULT NULL,
  `das_28_crp` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `disease_scores`
--

INSERT INTO `disease_scores` (`id`, `patient_id`, `sdai`, `das_28_crp`, `created_at`) VALUES
(1, 2, 24.00, 3.13, '2025-12-14 13:54:22'),
(2, 2, 12.60, 2.35, '2025-12-14 14:01:04');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `specialization` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `email`, `phone`, `address`, `specialization`, `password`, `otp`, `otp_expiry`, `created_at`) VALUES
(1, 'Dr. Smith', 'doctor@example.com', '9876543210', NULL, 'Orthopedic', '$2y$10$8wksMKTA5zcbnRE78xnIn.NZZI1Lsm5VyIQWZvMt6vB4IQscSALNi', NULL, NULL, '2025-12-14 12:34:27'),
(2, 'Dr Nash', 'nandakishorep212@gmail.com', NULL, NULL, 'Dentist', '$2y$10$XHiC7u3AygTl4l1epk430O8jVYU9uLPx8E4U65iRgRdDREprZCpMi', NULL, NULL, '2025-12-14 13:39:47');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_notifications`
--

CREATE TABLE `doctor_notifications` (
  `id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investigations`
--

CREATE TABLE `investigations` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `hb` decimal(5,2) DEFAULT NULL,
  `total_leukocyte_count` int DEFAULT NULL,
  `differential_count` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platelet_count` int DEFAULT NULL,
  `esr` int DEFAULT NULL,
  `crp` decimal(5,2) DEFAULT NULL,
  `lft_total_bilirubin` decimal(5,2) DEFAULT NULL,
  `lft_direct_bilirubin` decimal(5,2) DEFAULT NULL,
  `ast` int DEFAULT NULL,
  `alt` int DEFAULT NULL,
  `albumin` decimal(5,2) DEFAULT NULL,
  `total_protein` decimal(5,2) DEFAULT NULL,
  `ggt` int DEFAULT NULL,
  `urea` decimal(5,2) DEFAULT NULL,
  `creatinine` decimal(5,2) DEFAULT NULL,
  `uric_acid` decimal(5,2) DEFAULT NULL,
  `urine_routine` text COLLATE utf8mb4_unicode_ci,
  `urine_pcr` decimal(5,2) DEFAULT NULL,
  `ra_factor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anti_ccp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `investigations`
--

INSERT INTO `investigations` (`id`, `patient_id`, `hb`, `total_leukocyte_count`, `differential_count`, `platelet_count`, `esr`, `crp`, `lft_total_bilirubin`, `lft_direct_bilirubin`, `ast`, `alt`, `albumin`, `total_protein`, `ggt`, `urea`, `creatinine`, `uric_acid`, `urine_routine`, `urine_pcr`, `ra_factor`, `anti_ccp`, `created_at`) VALUES
(1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.00, 1.00, NULL, NULL, NULL, NULL, '2025-12-14 13:55:09');

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `medications` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `patient_id`, `medications`, `created_at`) VALUES
(1, 2, '[{\"name\":\"dolo\",\"dose\":\"1\",\"period\":\"3\"}]', '2025-12-14 13:54:36');

-- --------------------------------------------------------

--
-- Table structure for table `pain_assessments`
--

CREATE TABLE `pain_assessments` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `pain_score` int NOT NULL,
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pain_assessments`
--

INSERT INTO `pain_assessments` (`id`, `patient_id`, `pain_score`, `recorded_at`) VALUES
(1, 2, 2, '2025-12-14 13:39:02'),
(2, 2, 5, '2025-12-14 13:39:05'),
(3, 2, 8, '2025-12-14 13:39:06'),
(4, 2, 4, '2025-12-14 13:39:08');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `sex` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `name`, `email`, `phone`, `age`, `weight`, `sex`, `occupation`, `address`, `password`, `otp`, `otp_expiry`, `created_at`) VALUES
(1, 'Test Patient', 'test@example.com', '1234567890', NULL, NULL, NULL, NULL, NULL, '$2y$10$ytqgubiPANexu.NWroahFeqLaj9l5M/GH0LYBCemQJeca7Wo6Wg7i', NULL, NULL, '2025-12-14 12:31:50'),
(2, 'Nandakishore P', 'nandakishorep2121@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2y$10$dvWx1tBOiuzEcTGaJkmrzuuLVGRi5co0WPut1lol2IWTI6krwPG1G', NULL, NULL, '2025-12-14 13:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `patient_doctor`
--

CREATE TABLE `patient_doctor` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_doctor`
--

INSERT INTO `patient_doctor` (`id`, `patient_id`, `doctor_id`, `created_at`) VALUES
(1, 2, 2, '2025-12-14 13:44:04');

-- --------------------------------------------------------

--
-- Table structure for table `patient_files`
--

CREATE TABLE `patient_files` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `patient_id`, `text`, `created_at`) VALUES
(1, 2, 'reffered to an ent', '2025-12-14 13:55:40');

-- --------------------------------------------------------

--
-- Table structure for table `treatments`
--

CREATE TABLE `treatments` (
  `id` int NOT NULL,
  `patient_id` int NOT NULL,
  `treatment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dose` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequency` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequency_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_period` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `treatments`
--

INSERT INTO `treatments` (`id`, `patient_id`, `treatment`, `name`, `dose`, `route`, `frequency`, `frequency_text`, `time_period`, `created_at`) VALUES
(1, 2, 'injection', 'Tt', '1', 'Injection', '0', '', '0', '2025-12-14 13:55:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comorbidities`
--
ALTER TABLE `comorbidities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_doctor` (`doctor_id`);

--
-- Indexes for table `disease_scores`
--
ALTER TABLE `disease_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `doctor_notifications`
--
ALTER TABLE `doctor_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `idx_doctor` (`doctor_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `investigations`
--
ALTER TABLE `investigations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `pain_assessments`
--
ALTER TABLE `pain_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `patient_doctor`
--
ALTER TABLE `patient_doctor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_patient_doctor` (`patient_id`,`doctor_id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_doctor` (`doctor_id`);

--
-- Indexes for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- Indexes for table `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comorbidities`
--
ALTER TABLE `comorbidities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `disease_scores`
--
ALTER TABLE `disease_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `doctor_notifications`
--
ALTER TABLE `doctor_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `investigations`
--
ALTER TABLE `investigations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pain_assessments`
--
ALTER TABLE `pain_assessments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patient_doctor`
--
ALTER TABLE `patient_doctor`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patient_files`
--
ALTER TABLE `patient_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `treatments`
--
ALTER TABLE `treatments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comorbidities`
--
ALTER TABLE `comorbidities`
  ADD CONSTRAINT `comorbidities_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disease_scores`
--
ALTER TABLE `disease_scores`
  ADD CONSTRAINT `disease_scores_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_notifications`
--
ALTER TABLE `doctor_notifications`
  ADD CONSTRAINT `doctor_notifications_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_notifications_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `investigations`
--
ALTER TABLE `investigations`
  ADD CONSTRAINT `investigations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pain_assessments`
--
ALTER TABLE `pain_assessments`
  ADD CONSTRAINT `pain_assessments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_doctor`
--
ALTER TABLE `patient_doctor`
  ADD CONSTRAINT `patient_doctor_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patient_doctor_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_files`
--
ALTER TABLE `patient_files`
  ADD CONSTRAINT `patient_files_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `treatments`
--
ALTER TABLE `treatments`
  ADD CONSTRAINT `treatments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
