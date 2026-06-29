-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 18, 2026 at 10:27 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gym_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE IF NOT EXISTS `attendances` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `check_in` timestamp NOT NULL,
  `check_out` timestamp NULL DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Supplements', 'Protein powders, pre-workout, vitamins, and nutritional supplements', 'supplements', '2026-05-04 11:51:42', '2026-05-04 11:51:42'),
(2, 'Gym Apparel', 'Workout clothes, shoes, and athletic wear', 'gym-apparel', '2026-05-04 11:51:42', '2026-05-04 11:51:42'),
(3, 'Equipment', 'Gym accessories, resistance bands, yoga mats, and training equipment', 'equipment', '2026-05-04 11:51:42', '2026-05-04 11:51:42'),
(4, 'Drinks', 'Sports drinks, protein shakes, and energy drinks', 'drinks', '2026-05-04 11:51:42', '2026-05-04 11:51:42'),
(5, 'Accessories', 'Gym bags, water bottles, towels, and other accessories', 'accessories', '2026-05-04 11:51:42', '2026-05-04 11:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `coaches`
--

DROP TABLE IF EXISTS `coaches`;
CREATE TABLE IF NOT EXISTS `coaches` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `specialization` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `certifications` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_years` int NOT NULL DEFAULT '0',
  `clients_count` int NOT NULL DEFAULT '0',
  `rating` decimal(3,2) NOT NULL DEFAULT '5.00',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expertise_areas` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `coaches_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coaches`
--

INSERT INTO `coaches` (`id`, `name`, `user_id`, `specialization`, `bio`, `certifications`, `experience_years`, `clients_count`, `rating`, `is_available`, `hourly_rate`, `avatar`, `expertise_areas`, `created_at`, `updated_at`) VALUES
(1, 'Marcus Thorne', NULL, 'Hypertrophy & Strength', 'Former Olympian specializing in biological recalibration and muscle density optimization.', 'CSCS, NASM-PES, Precision Nutrition L2', 12, 1, 4.90, 1, NULL, 'https://media1.giphy.com/media/v1.Y2lkPTZjMDliOTUybGtjMDZrMnVlbWo4ZWR6dm85MjRsd3JqenVmMXhkdW4xeWc2bTNkbCZlcD12MV9naWZzX3NlYXJjaCZjdD1n/KH3BP7P9JlGttsyuZD/200w.gif', '[\"Powerlifting\", \"Muscle Growth\", \"Metabolic Conditioning\"]', '2026-05-18 12:58:08', '2026-05-18 12:58:49'),
(2, 'Sienna Vance', NULL, 'Mobility & Athleticism', 'Expert in biomechanics and flow-state training. Dedicated to erasing physical limitations.', 'NSCA-CPT, Yoga Alliance RYT-500', 8, 0, 4.80, 1, NULL, 'https://media0.giphy.com/media/v1.Y2lkPTZjMDliOTUyOXRmaHZhcXh3ZGNkYWc4NTBmanBxNGhldXlybGhvNXZxendmZHVoayZlcD12MV9naWZzX3NlYXJjaCZjdD1n/vUBCZJdPyaw8r1bEKW/giphy-downsized.gif', '[\"Flexibility\", \"Agility\", \"Post-Injury Recovery\"]', '2026-05-18 12:58:08', '2026-05-18 12:58:08'),
(3, 'Kaelen Drax', NULL, 'Endurance & Bio-Hacking', 'Specialist in aerobic capacity and nutrient timing. Optimization is the only path.', 'ACSM-CEP, Precision Nutrition L1', 10, 0, 4.70, 1, NULL, 'https://i.makeagif.com/media/4-02-2018/Si0jK-.gif', '[\"Marathon Training\", \"VO2 Max Optimization\", \"Ketogenic Coaching\"]', '2026-05-18 12:58:08', '2026-05-18 12:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

DROP TABLE IF EXISTS `exercises`;
CREATE TABLE IF NOT EXISTS `exercises` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `muscle_group` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `difficulty` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'beginner',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `description`, `image`, `video_url`, `muscle_group`, `equipment`, `difficulty`, `created_at`, `updated_at`) VALUES
(1, 'Bench Press', NULL, 'https://i0.wp.com/www.strengthlog.com/wp-content/uploads/2021/09/bench-press.gif?fit=600%2C600&ssl=1', NULL, 'Chest', 'Barbell', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(2, 'Dumbbell Press', NULL, 'https://images.squarespace-cdn.com/content/v1/54f9e84de4b0d13f30bba4cb/1526590877144-BYR9X8ZX5FROTGOZ2VHL/DSC_6330.mov.gif', NULL, 'Chest', 'Dumbbells', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(3, 'Chest Flys', NULL, 'https://training.fit/wp-content/uploads/2020/02/butterflys-800x448.png', NULL, 'Chest', 'Cables', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(4, 'Pushups', NULL, 'https://www.sixstarpro.com/cdn/shop/articles/how-to-get-better-at-push-ups_c96cd61d-63d8-4bd0-a93e-e3a8dee52408.jpg?v=1726000547', NULL, 'Chest', 'Bodyweight', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(5, 'Dips', NULL, 'https://training.fit/wp-content/uploads/2020/03/arnold-dips-800x448.png', NULL, 'Chest', 'Parallel Bars', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(6, 'Deadlift', NULL, 'https://www.journalmenu.com/wp-content/uploads/2018/03/deadlift-gif-side.gif', NULL, 'Back', 'Barbell', 'advanced', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(7, 'Pull Ups', NULL, 'https://media2.giphy.com/media/v1.Y2lkPTZjMDliOTUydG03bHVobndlMnBxbWMyd2c1ZmIyb3plN3c5bWtyMDlnandocnNkMiZlcD12MV9naWZzX3NlYXJjaCZjdD1n/EzlBTmRv3uLcRMsLvU/giphy.gif', NULL, 'Back', 'Pull-up Bar', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(8, 'Bent Over Row', NULL, 'https://liftmanual.com/wp-content/uploads/2023/04/barbell-bent-over-row.jpg', NULL, 'Back', 'Barbell', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(9, 'Lat Pulldown', NULL, 'https://training.fit/wp-content/uploads/2020/02/latzug.png', NULL, 'Back', 'Machine', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(10, 'Single Arm Row', NULL, 'https://training.fit/wp-content/uploads/2020/02/rudern-kurzhantel.png', NULL, 'Back', 'Dumbbells', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(11, 'Squats', NULL, 'https://www.strengthlog.com/wp-content/uploads/2021/11/squat.gif', NULL, 'Legs', 'Barbell', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(12, 'Leg Press', NULL, 'https://www.verywellfit.com/thmb/0_4BPwSszzrmzmuVkQjwvYPYHXs=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/50-3498610-Leg-Press-GIF-7e720a89577d456db0bcb5dab2bd5d5f.gif', NULL, 'Legs', 'Machine', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(13, 'Lunges', NULL, 'https://trainingstation.co.uk/cdn/shop/articles/Lunges-movment_d958998d-2a9f-430e-bdea-06f1e2bcc835_900x.webp?v=1741687877', NULL, 'Legs', 'Dumbbells', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(14, 'Leg Extensions', NULL, 'https://training.fit/wp-content/uploads/2020/03/beinstrecken-geraet-1.png', NULL, 'Legs', 'Machine', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(15, 'Hamstring Curls', NULL, 'https://cdn.shopify.com/s/files/1/0449/8453/3153/files/leg_curl.jpg?v=1739000697', NULL, 'Legs', 'Machine', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(16, 'Overhead Press', NULL, 'https://i0.wp.com/www.strengthlog.com/wp-content/uploads/2020/12/Overhead-press-exercise.gif?fit=600%2C600&ssl=1', NULL, 'Shoulders', 'Barbell', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(17, 'Lateral Raises', NULL, 'https://i0.wp.com/www.strengthlog.com/wp-content/uploads/2020/12/Dumbbell-Lateral-Raise.gif?fit=600%2C600&ssl=1', NULL, 'Shoulders', 'Dumbbells', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(18, 'Front Raises', NULL, 'https://weighttraining.guide/wp-content/uploads/2016/10/Dumbbell-Standing-Alternate-Front-Raise-resized.png', NULL, 'Shoulders', 'Dumbbells', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(19, 'Face Pulls', NULL, 'https://liftmanual.com/wp-content/uploads/2023/04/cable-standing-face-pull.jpg', NULL, 'Shoulders', 'Cables', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(20, 'Arnold Press', NULL, 'https://hips.hearstapps.com/hmg-prod/images/db-seated-shoulder-pressat1-25x-64d387fe154b1.jpg?resize=980:*', NULL, 'Shoulders', 'Dumbbells', 'advanced', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(21, 'Bicep Curls', NULL, 'https://training.fit/wp-content/uploads/2020/02/bizepscurls-stehend-langhantel.png', NULL, 'Arms', 'Dumbbells', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(22, 'Hammer Curls', NULL, 'https://training.fit/wp-content/uploads/2020/02/bizeps-hammercurls.png', NULL, 'Arms', 'Dumbbells', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(23, 'Tricep Pushdowns', NULL, 'https://trainingstation.co.uk/cdn/shop/articles/Tricep-pushdown-movement_ddb8dbd8-566d-4f55-99e0-36c35790234a_1224x.png?v=1739005533', NULL, 'Arms', 'Cables', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(24, 'Skull Crushers', NULL, 'https://imagely.mirafit.co.uk/wp/wp-content/uploads/2023/12/skull-crusher-using-Mirafit-EZ-Curl-Bar-1024x683.jpg', NULL, 'Arms', 'Barbell', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(25, 'Preacher Curls', NULL, 'https://training.fit/wp-content/uploads/2020/02/scottcurls-schraegbank.png', NULL, 'Arms', 'EZ Bar', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(26, 'Plank', NULL, 'https://gymnation.com/media/jpbjzofv/plank2.webp?width=956&height=675&v=1dc68400a14c040', NULL, 'Core', 'Bodyweight', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(27, 'Hanging Leg Raises', NULL, 'https://training.fit/wp-content/uploads/2020/01/haengendes-knieheben.png', NULL, 'Core', 'Pull-up Bar', 'advanced', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(28, 'Russian Twists', NULL, 'https://trainingstation.co.uk/cdn/shop/articles/russian-twist-kettlebell_1_2504x.png?v=1758384047', NULL, 'Core', 'Dumbbells', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(29, 'Crunches', NULL, 'https://training.fit/wp-content/uploads/2019/08/crunches-liegend.png', NULL, 'Core', 'Bodyweight', 'beginner', '2026-05-18 11:54:15', '2026-05-18 11:54:15'),
(30, 'Mountain Climbers', NULL, 'https://s3.amazonaws.com/prod.skimble/assets/1334408/image_iphone.jpg', NULL, 'Core', 'Bodyweight', 'intermediate', '2026-05-18 11:54:15', '2026-05-18 11:54:15');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

DROP TABLE IF EXISTS `meals`;
CREATE TABLE IF NOT EXISTS `meals` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `nutrition_log_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meal_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `calories` int NOT NULL DEFAULT '0',
  `protein_g` int NOT NULL DEFAULT '0',
  `carbs_g` int NOT NULL DEFAULT '0',
  `fats_g` int NOT NULL DEFAULT '0',
  `eaten_at` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meals_user_id_foreign` (`user_id`),
  KEY `meals_nutrition_log_id_foreign` (`nutrition_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`id`, `user_id`, `nutrition_log_id`, `name`, `description`, `image`, `meal_type`, `calories`, `protein_g`, `carbs_g`, `fats_g`, `eaten_at`, `created_at`, `updated_at`) VALUES
(1, 36, 1, 'pariatur', 'Sapiente veritatis rerum et aut aut molestiae repellat id.', 'https://via.placeholder.com/400x300.png/0033aa?text=food+occaecati', 'dinner', 762, 33, 15, 32, '05:34:31', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(2, 38, 2, 'aut', 'Ducimus eum praesentium dolorum dolores nostrum quia ipsam animi.', 'https://via.placeholder.com/400x300.png/00cc55?text=food+aperiam', 'dinner', 145, 14, 76, 13, '15:56:38', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(3, 40, 3, 'velit', 'Perspiciatis modi ad laborum est maxime autem.', 'https://via.placeholder.com/400x300.png/005522?text=food+et', 'lunch', 570, 33, 67, 7, '08:10:10', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(4, 42, 4, 'illum', 'Laborum numquam quia rem omnis optio.', 'https://via.placeholder.com/400x300.png/00aa99?text=food+ab', 'lunch', 728, 44, 96, 25, '02:45:39', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(5, 44, 5, 'ut', 'Qui ut sint ipsa laudantium sunt.', 'https://via.placeholder.com/400x300.png/0088bb?text=food+eveniet', 'breakfast', 464, 5, 54, 6, '01:39:20', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(6, 46, 6, 'quia', 'Enim ipsum cumque eveniet.', 'https://via.placeholder.com/400x300.png/000044?text=food+hic', 'dinner', 309, 47, 29, 31, '19:16:57', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(7, 48, 7, 'earum', 'Vero iure adipisci nam.', 'https://via.placeholder.com/400x300.png/0055aa?text=food+saepe', 'lunch', 105, 38, 55, 10, '02:14:10', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(8, 50, 8, 'impedit', 'Eos beatae omnis et saepe et molestias sit.', 'https://via.placeholder.com/400x300.png/006688?text=food+voluptatem', 'dinner', 323, 24, 92, 15, '12:41:52', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(9, 52, 9, 'tempora', 'Eos officiis est tenetur eaque.', 'https://via.placeholder.com/400x300.png/0088bb?text=food+in', 'lunch', 241, 27, 67, 25, '12:20:26', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(10, 54, 10, 'non', 'Minus velit quo iste et.', 'https://via.placeholder.com/400x300.png/00eedd?text=food+velit', 'lunch', 209, 25, 47, 40, '16:43:42', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(11, 56, 11, 'id', 'Tempore nulla assumenda ad non libero.', 'https://via.placeholder.com/400x300.png/006699?text=food+dolorem', 'lunch', 540, 16, 35, 34, '07:33:45', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(12, 58, 12, 'repellendus', 'Debitis dolores sunt animi sed consectetur corporis at.', 'https://via.placeholder.com/400x300.png/00ffbb?text=food+consequatur', 'breakfast', 116, 29, 36, 6, '17:43:41', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(13, 60, 13, 'molestiae', 'Doloribus voluptas et quo quis.', 'https://via.placeholder.com/400x300.png/00cc66?text=food+explicabo', 'breakfast', 630, 50, 41, 38, '15:37:50', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(14, 62, 14, 'incidunt', 'Eligendi ipsa vero rem officiis veniam.', 'https://via.placeholder.com/400x300.png/006677?text=food+unde', 'snack', 760, 10, 36, 19, '15:30:29', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(15, 64, 15, 'autem', 'Porro repellat repellat et ratione quaerat unde nemo.', 'https://via.placeholder.com/400x300.png/00bb99?text=food+dicta', 'lunch', 197, 42, 22, 39, '05:53:17', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(16, 66, 16, 'aperiam', 'Dicta beatae dolores eum sed qui.', 'https://via.placeholder.com/400x300.png/00ccaa?text=food+non', 'snack', 407, 27, 44, 23, '11:48:22', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(17, 68, 17, 'voluptates', 'Est maiores vel nulla consequatur quis rerum.', 'https://via.placeholder.com/400x300.png/005599?text=food+et', 'lunch', 389, 5, 59, 37, '05:20:26', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(18, 70, 18, 'est', 'Dignissimos est harum quo incidunt ea accusamus.', 'https://via.placeholder.com/400x300.png/008800?text=food+corrupti', 'dinner', 540, 6, 70, 36, '04:18:18', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(19, 72, 19, 'voluptas', 'Nisi natus suscipit quidem consequatur possimus et aliquam vitae.', 'https://via.placeholder.com/400x300.png/00eedd?text=food+eligendi', 'snack', 282, 43, 66, 8, '14:40:45', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(20, 74, 20, 'neque', 'Harum numquam tempore odio.', 'https://via.placeholder.com/400x300.png/0055cc?text=food+cupiditate', 'lunch', 482, 39, 99, 28, '09:21:08', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(21, 76, 21, 'veritatis', 'Iste sit minus ea consequuntur ipsum impedit.', 'https://via.placeholder.com/400x300.png/00eeee?text=food+dicta', 'breakfast', 619, 30, 64, 6, '07:51:10', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(22, 78, 22, 'ipsum', 'Quia nihil fuga voluptas earum autem qui.', 'https://via.placeholder.com/400x300.png/00bb00?text=food+ratione', 'snack', 585, 27, 25, 7, '19:45:04', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(23, 80, 23, 'soluta', 'Tempore accusantium ut corporis velit dolores.', 'https://via.placeholder.com/400x300.png/00ff11?text=food+et', 'breakfast', 399, 49, 20, 27, '23:15:22', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(24, 82, 24, 'quis', 'Ad a ab laboriosam dolores eos reiciendis temporibus aut.', 'https://via.placeholder.com/400x300.png/0011cc?text=food+quidem', 'breakfast', 762, 14, 87, 28, '03:16:06', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(25, 84, 25, 'veniam', 'Cupiditate et nemo repellat ut.', 'https://via.placeholder.com/400x300.png/00aacc?text=food+nihil', 'snack', 686, 22, 18, 33, '02:30:02', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(26, 86, 26, 'nisi', 'In atque dicta et ea ad voluptas.', 'https://via.placeholder.com/400x300.png/000000?text=food+voluptatem', 'lunch', 253, 37, 68, 33, '01:57:05', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(27, 88, 27, 'tempora', 'Qui quibusdam mollitia odit similique laboriosam voluptas.', 'https://via.placeholder.com/400x300.png/00ffaa?text=food+blanditiis', 'snack', 612, 20, 18, 15, '19:36:02', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(28, 90, 28, 'ratione', 'Occaecati incidunt inventore sed incidunt et.', 'https://via.placeholder.com/400x300.png/005566?text=food+aliquid', 'dinner', 675, 26, 85, 10, '10:29:31', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(29, 92, 29, 'minus', 'Eum suscipit commodi illum occaecati occaecati vel.', 'https://via.placeholder.com/400x300.png/00ff22?text=food+sint', 'dinner', 361, 24, 56, 13, '04:56:22', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(30, 94, 30, 'doloremque', 'Assumenda ut et inventore magnam voluptate et ut debitis.', 'https://via.placeholder.com/400x300.png/0044cc?text=food+sed', 'lunch', 237, 40, 72, 6, '06:46:44', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(31, 96, 31, 'animi', 'Sit rerum consectetur autem.', 'https://via.placeholder.com/400x300.png/0033ff?text=food+ex', 'dinner', 760, 37, 68, 5, '22:09:52', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(32, 98, 32, 'doloremque', 'Tenetur omnis nulla qui qui doloribus eius voluptatem.', 'https://via.placeholder.com/400x300.png/0044dd?text=food+doloremque', 'snack', 154, 44, 23, 23, '22:06:14', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(33, 100, 33, 'corrupti', 'Fugit rerum magni dolor est.', 'https://via.placeholder.com/400x300.png/0055bb?text=food+beatae', 'lunch', 143, 34, 25, 7, '01:28:38', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(34, 102, 34, 'tempora', 'Exercitationem aspernatur suscipit iste quo omnis quidem consequatur voluptatibus.', 'https://via.placeholder.com/400x300.png/008866?text=food+qui', 'dinner', 710, 40, 17, 21, '19:51:14', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(35, 104, 35, 'repellat', 'Repellat sit et et adipisci odit ut enim.', 'https://via.placeholder.com/400x300.png/00ee99?text=food+et', 'dinner', 212, 25, 32, 23, '13:10:29', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(36, 106, 36, 'et', 'Occaecati dolorem dolores qui ea provident tenetur.', 'https://via.placeholder.com/400x300.png/00bb99?text=food+adipisci', 'snack', 175, 37, 64, 6, '13:40:09', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(37, 108, 37, 'deserunt', 'Quis in fuga quod qui sapiente.', 'https://via.placeholder.com/400x300.png/00dd66?text=food+corrupti', 'snack', 221, 49, 21, 25, '12:32:43', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(38, 110, 38, 'odio', 'Et vel qui quidem voluptatibus unde omnis.', 'https://via.placeholder.com/400x300.png/001177?text=food+et', 'lunch', 798, 41, 24, 40, '10:04:38', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(39, 112, 39, 'laudantium', 'Ullam facere at sit et provident accusamus minus.', 'https://via.placeholder.com/400x300.png/00ddcc?text=food+blanditiis', 'snack', 608, 46, 70, 33, '22:39:46', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(40, 114, 40, 'vel', 'Iste velit ullam quod excepturi.', 'https://via.placeholder.com/400x300.png/00ee66?text=food+optio', 'breakfast', 685, 49, 96, 37, '00:06:53', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(41, 116, 41, 'laudantium', 'Repellendus inventore rerum qui quos sed praesentium exercitationem.', 'https://via.placeholder.com/400x300.png/00aa99?text=food+mollitia', 'dinner', 770, 28, 97, 24, '19:30:26', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(42, 118, 42, 'occaecati', 'Illo nam aliquid ut consequatur cum corrupti nam.', 'https://via.placeholder.com/400x300.png/00eeff?text=food+error', 'breakfast', 713, 26, 36, 34, '13:48:08', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(43, 120, 43, 'eum', 'Laudantium molestiae quasi est soluta expedita.', 'https://via.placeholder.com/400x300.png/0011ee?text=food+ullam', 'dinner', 367, 24, 61, 36, '04:56:24', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(44, 122, 44, 'beatae', 'Cum consequatur nisi voluptates aut commodi animi.', 'https://via.placeholder.com/400x300.png/0000bb?text=food+asperiores', 'breakfast', 749, 27, 62, 25, '09:18:56', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(45, 124, 45, 'est', 'Nam cumque exercitationem itaque commodi nesciunt.', 'https://via.placeholder.com/400x300.png/0055ee?text=food+repellat', 'snack', 557, 32, 66, 40, '19:56:07', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(46, 126, 46, 'optio', 'Ut deserunt saepe molestiae atque nobis qui unde.', 'https://via.placeholder.com/400x300.png/00ff33?text=food+molestias', 'dinner', 792, 39, 90, 39, '00:40:02', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(47, 128, 47, 'veritatis', 'Sint id molestias enim dolorum.', 'https://via.placeholder.com/400x300.png/000000?text=food+qui', 'dinner', 510, 43, 21, 19, '08:24:26', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(48, 130, 48, 'expedita', 'Debitis deleniti modi sunt earum id quia.', 'https://via.placeholder.com/400x300.png/008899?text=food+aut', 'dinner', 248, 38, 99, 35, '18:59:24', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(49, 132, 49, 'amet', 'Nesciunt est nam explicabo architecto accusamus.', 'https://via.placeholder.com/400x300.png/004422?text=food+consequatur', 'breakfast', 247, 32, 55, 18, '20:13:32', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(50, 134, 50, 'totam', 'Autem iste quia omnis fugiat facere aliquid.', 'https://via.placeholder.com/400x300.png/0033bb?text=food+facere', 'snack', 287, 50, 47, 27, '00:17:22', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(51, 2, 83, 'roz chicken', NULL, NULL, 'snack', 300, 34, 234, 23, NULL, '2026-05-14 10:19:54', '2026-05-14 10:19:54'),
(52, 2, 83, 'spaghetti', NULL, NULL, 'dinner', 500, 49, 43, 34, NULL, '2026-05-14 10:30:20', '2026-05-14 10:30:20'),
(53, 2, 83, 'sardine', NULL, NULL, 'lunch', 500, 34, 13, 22, NULL, '2026-05-14 11:04:02', '2026-05-14 11:04:02'),
(54, 2, 85, 'sdfadsf', NULL, NULL, 'lunch', 3243, 234, 123, 321, NULL, '2026-05-17 11:47:31', '2026-05-17 11:47:31'),
(55, 171, 86, 'djaj', NULL, NULL, 'snack', 344, 30, 160, 40, NULL, '2026-05-18 17:03:19', '2026-05-18 17:03:19');

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

DROP TABLE IF EXISTS `memberships`;
CREATE TABLE IF NOT EXISTS `memberships` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `plan_id` bigint UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `memberships_user_id_foreign` (`user_id`),
  KEY `memberships_plan_id_foreign` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id`, `user_id`, `plan_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2026-05-18', '2026-06-17', 'active', '2026-05-18 15:37:05', '2026-05-18 15:50:55'),
(2, 2, 2, '2026-05-18', '2026-06-17', 'active', '2026-05-18 16:23:07', '2026-05-18 16:23:35'),
(3, 2, 3, '2026-05-18', '2027-05-18', 'active', '2026-05-18 16:34:01', '2026-05-18 16:34:27'),
(4, 171, 2, '2026-05-18', '2026-06-17', 'active', '2026-05-18 16:53:47', '2026-05-18 16:54:34');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_03_04_000001_create_attendances_table', 1),
(5, '2024_03_25_000001_create_categories_table', 1),
(6, '2024_03_25_000002_create_products_table', 1),
(7, '2024_03_25_000003_create_orders_table', 1),
(8, '2024_03_25_000004_create_order_items_table', 1),
(9, '2026_03_04_122243_create_personal_access_tokens_table', 1),
(10, '2026_03_04_122318_create_plans_table', 1),
(11, '2026_03_07_230018_create_memberships_table', 1),
(12, '2026_04_28_091520_create_workouts_table', 1),
(13, '2026_04_28_091521_create_exercises_table', 1),
(14, '2026_04_28_091521_create_programs_table', 1),
(15, '2026_04_28_091522_create_program_exercises_table', 1),
(16, '2026_04_28_091522_create_user_programs_table', 1),
(17, '2026_04_28_091523_create_coaches_table', 1),
(18, '2026_04_28_091523_create_meals_table', 1),
(19, '2026_04_28_091523_create_nutrition_logs_table', 1),
(20, '2026_04_28_091524_add_image_to_products_table', 1),
(21, '2026_04_28_091524_create_user_coaches_table', 1),
(22, '2026_04_28_091525_add_fields_to_users_table', 1),
(23, '2026_04_28_091903_create_workout_exercises_table', 1),
(24, '2026_04_28_092536_create_program_workouts_table', 1),
(25, '2026_04_28_092636_create_user_workouts_table', 1),
(26, '2026_05_16_111954_create_schedules_table', 2),
(27, '2026_05_16_140405_ensure_coach_id_exists_in_schedules_table', 3),
(28, '2026_05_16_143145_create_schedules_table_fresh', 4),
(29, '2026_05_17_111625_create_notifications_table', 5),
(30, '2026_05_18_134655_add_name_to_coaches_table', 6),
(31, '2026_05_18_135724_make_user_id_nullable_in_coaches_table', 7);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nutrition_logs`
--

DROP TABLE IF EXISTS `nutrition_logs`;
CREATE TABLE IF NOT EXISTS `nutrition_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `calories` int NOT NULL DEFAULT '0',
  `protein_g` int NOT NULL DEFAULT '0',
  `carbs_g` int NOT NULL DEFAULT '0',
  `fats_g` int NOT NULL DEFAULT '0',
  `water_ml` int NOT NULL DEFAULT '0',
  `target_calories` int NOT NULL DEFAULT '2500',
  `target_protein_g` int NOT NULL DEFAULT '180',
  `target_carbs_g` int NOT NULL DEFAULT '300',
  `target_fats_g` int NOT NULL DEFAULT '80',
  `target_water_ml` int NOT NULL DEFAULT '3000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nutrition_logs_user_id_log_date_unique` (`user_id`,`log_date`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `nutrition_logs`
--

INSERT INTO `nutrition_logs` (`id`, `user_id`, `log_date`, `calories`, `protein_g`, `carbs_g`, `fats_g`, `water_ml`, `target_calories`, `target_protein_g`, `target_carbs_g`, `target_fats_g`, `target_water_ml`, `created_at`, `updated_at`) VALUES
(1, 37, '1979-11-19', 2953, 154, 183, 94, 3399, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(2, 39, '1991-04-13', 1665, 185, 368, 75, 3497, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(3, 41, '1996-03-24', 2050, 200, 185, 40, 2008, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(4, 43, '2006-05-20', 2601, 110, 196, 50, 3295, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(5, 45, '2005-01-31', 2035, 199, 399, 59, 2007, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(6, 47, '2017-08-10', 1965, 77, 200, 79, 3376, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(7, 49, '1982-03-19', 2044, 67, 391, 40, 2060, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(8, 51, '2004-08-30', 2215, 177, 299, 48, 2640, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(9, 53, '2005-08-19', 2585, 188, 320, 40, 3288, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(10, 55, '1971-05-15', 1991, 187, 378, 72, 1721, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(11, 57, '2021-10-19', 2243, 151, 174, 50, 2040, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(12, 59, '1970-11-21', 1565, 143, 204, 63, 2232, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(13, 61, '1986-07-17', 1771, 197, 184, 94, 2859, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(14, 63, '2007-12-15', 2140, 110, 272, 51, 3213, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(15, 65, '1986-08-29', 1779, 120, 230, 65, 1814, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(16, 67, '2021-07-14', 2691, 63, 393, 91, 2301, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(17, 69, '2005-10-06', 2059, 59, 372, 89, 2508, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(18, 71, '2013-11-17', 2178, 130, 181, 91, 2791, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(19, 73, '1972-06-14', 1570, 91, 393, 61, 2480, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(20, 75, '1975-06-28', 1602, 174, 153, 87, 2396, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(21, 77, '1993-07-29', 1967, 104, 238, 61, 3481, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(22, 79, '1972-08-22', 2649, 74, 162, 95, 1790, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(23, 81, '1987-08-18', 1901, 118, 346, 97, 3476, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(24, 83, '2022-03-31', 2572, 135, 268, 72, 2515, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(25, 85, '2016-06-19', 2078, 155, 205, 93, 3287, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(26, 87, '1972-08-07', 2226, 200, 219, 77, 3406, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(27, 89, '1983-04-04', 2968, 116, 151, 74, 2761, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(28, 91, '1989-08-07', 2775, 152, 352, 83, 2923, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(29, 93, '2019-09-14', 2761, 62, 314, 88, 2460, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(30, 95, '1972-09-29', 2996, 154, 319, 94, 2771, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(31, 97, '1981-11-04', 2655, 65, 386, 89, 1683, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(32, 99, '2017-07-11', 2827, 87, 390, 53, 1544, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(33, 101, '2018-06-08', 1939, 194, 378, 43, 3437, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(34, 103, '2018-07-24', 1776, 164, 319, 99, 3004, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(35, 105, '1978-11-10', 1958, 158, 152, 50, 1597, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(36, 107, '2008-07-08', 2983, 184, 328, 62, 1882, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(37, 109, '2006-06-09', 1699, 83, 394, 72, 3285, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(38, 111, '2021-09-10', 2146, 70, 309, 94, 1607, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(39, 113, '1996-12-26', 2076, 166, 230, 66, 1863, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(40, 115, '2008-06-20', 2462, 120, 190, 80, 2698, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(41, 117, '1982-07-12', 2194, 192, 203, 87, 2873, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(42, 119, '1973-12-27', 1897, 181, 185, 61, 2407, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(43, 121, '2002-06-02', 2782, 123, 226, 41, 1852, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(44, 123, '2019-12-29', 2242, 133, 306, 64, 2807, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(45, 125, '2012-09-19', 2423, 174, 250, 47, 2529, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(46, 127, '2006-11-10', 1848, 125, 313, 72, 1677, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(47, 129, '1985-03-27', 2248, 154, 316, 90, 3013, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(48, 131, '1980-05-06', 2498, 79, 253, 60, 3319, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(49, 133, '2020-03-16', 2774, 112, 189, 93, 2205, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(50, 135, '1996-01-12', 2675, 176, 170, 95, 2640, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(51, 136, '1970-07-24', 2188, 145, 227, 85, 3396, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(52, 137, '1985-03-11', 2327, 90, 314, 74, 1596, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(53, 138, '2004-03-06', 2520, 64, 322, 94, 1756, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(54, 139, '1989-02-27', 2884, 198, 170, 99, 2767, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(55, 140, '1972-04-03', 2717, 129, 298, 51, 2973, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(56, 141, '2020-07-18', 2128, 194, 346, 77, 2133, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(57, 142, '1994-10-03', 2050, 145, 313, 88, 2306, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(58, 143, '2002-01-21', 2697, 51, 370, 63, 2183, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(59, 144, '2006-11-02', 1584, 76, 292, 76, 3078, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(60, 145, '1972-12-31', 2365, 91, 153, 91, 2503, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(61, 146, '2013-09-10', 2385, 146, 191, 91, 3298, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(62, 147, '2015-05-07', 2062, 185, 206, 78, 1871, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(63, 148, '2026-01-15', 1818, 177, 303, 41, 2004, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(64, 149, '2009-11-28', 2811, 157, 227, 66, 3441, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(65, 150, '1985-10-26', 2149, 158, 183, 64, 3492, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(66, 151, '1978-04-09', 2603, 101, 285, 85, 2816, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(67, 152, '1992-04-16', 2613, 55, 347, 98, 2169, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(68, 153, '1991-07-02', 2459, 120, 217, 97, 3093, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(69, 154, '1987-12-20', 2723, 131, 318, 84, 1857, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(70, 155, '2025-01-28', 1607, 173, 203, 93, 2748, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(71, 156, '1999-09-20', 2175, 164, 259, 62, 1972, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(72, 157, '1991-10-29', 2388, 166, 271, 54, 2725, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(73, 158, '1997-12-30', 2401, 68, 271, 47, 2315, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(74, 159, '1978-03-03', 1750, 186, 158, 63, 2659, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(75, 160, '1984-01-06', 2045, 71, 352, 95, 1611, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(76, 161, '1976-01-30', 1977, 197, 242, 92, 1549, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(77, 162, '2000-04-14', 2490, 51, 384, 61, 1714, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(78, 163, '1978-10-31', 1730, 131, 395, 91, 2945, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(79, 164, '1998-05-25', 2985, 163, 325, 63, 2243, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(80, 165, '1980-07-01', 2815, 165, 367, 40, 2435, 2500, 150, 250, 70, 3000, '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(81, 2, '2026-05-10', 0, 0, 0, 0, 2000, 2500, 180, 300, 80, 3000, '2026-05-10 01:32:18', '2026-05-10 01:32:39'),
(82, 2, '2026-05-11', 0, 0, 0, 0, 11500, 2500, 180, 300, 80, 3000, '2026-05-11 09:58:48', '2026-05-11 10:06:34'),
(83, 2, '2026-05-14', 1300, 117, 290, 79, 10000, 2500, 180, 300, 80, 3000, '2026-05-14 09:46:59', '2026-05-14 11:04:02'),
(84, 2, '2026-05-16', 0, 0, 0, 0, 500, 2500, 180, 300, 80, 3000, '2026-05-16 14:13:47', '2026-05-16 14:13:47'),
(85, 2, '2026-05-17', 3243, 234, 123, 321, 1500, 2500, 180, 300, 80, 3000, '2026-05-17 11:47:31', '2026-05-17 11:47:52'),
(86, 171, '2026-05-18', 344, 30, 160, 40, 1500, 2500, 180, 300, 80, 3000, '2026-05-18 16:53:10', '2026-05-18 17:03:19');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD202605112470', 29.99, 'pending', NULL, '2026-05-11 11:31:06', '2026-05-11 11:31:06'),
(2, 2, 'ORD202605148865', 99.98, 'pending', NULL, '2026-05-14 10:05:05', '2026-05-14 10:05:05'),
(3, 2, 'ORD202605140405', 49.99, 'pending', NULL, '2026-05-14 10:21:29', '2026-05-14 10:21:29'),
(4, 2, 'ORD202605148804', 24.99, 'pending', NULL, '2026-05-14 10:41:03', '2026-05-14 10:41:03'),
(5, 2, 'ORD202605147074', 49.99, 'pending', NULL, '2026-05-14 11:04:49', '2026-05-14 11:04:49'),
(6, 1, 'ORD202605169174', 99.98, 'pending', NULL, '2026-05-16 10:44:20', '2026-05-16 10:44:20'),
(7, 2, 'ORD202605160398', 29.99, 'pending', NULL, '2026-05-16 14:51:06', '2026-05-16 14:51:06'),
(8, 2, 'ORD202605188666', 9.99, 'pending', NULL, '2026-05-18 14:25:13', '2026-05-18 14:25:13');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 29.99, 29.99, '2026-05-11 11:31:06', '2026-05-11 11:31:06'),
(2, 2, 1, 2, 49.99, 99.98, '2026-05-14 10:05:05', '2026-05-14 10:05:05'),
(3, 3, 1, 1, 49.99, 49.99, '2026-05-14 10:21:29', '2026-05-14 10:21:29'),
(4, 4, 3, 1, 24.99, 24.99, '2026-05-14 10:41:03', '2026-05-14 10:41:03'),
(5, 5, 1, 1, 49.99, 49.99, '2026-05-14 11:04:49', '2026-05-14 11:04:49'),
(6, 6, 1, 2, 49.99, 99.98, '2026-05-16 10:44:20', '2026-05-16 10:44:20'),
(7, 7, 2, 1, 29.99, 29.99, '2026-05-16 14:51:06', '2026-05-16 14:51:06'),
(8, 8, 11, 1, 9.99, 9.99, '2026-05-18 14:25:13', '2026-05-18 14:25:13');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'App\\Models\\User', 2, 'auth_token', '4502a695da77a97979b02ffd820d21c4b4adc771a3e2f6e021cc12058d24c53e', '[\"*\"]', '2026-05-04 12:09:43', NULL, '2026-05-04 11:56:25', '2026-05-04 12:09:43'),
(11, 'App\\Models\\User', 2, 'auth_token', '81f9a8ebb211983a783abf1f5781fcff28c1b74b18ed33d4dc5ea04cd4b823b2', '[\"*\"]', '2026-05-06 19:20:36', NULL, '2026-05-06 19:20:19', '2026-05-06 19:20:36'),
(7, 'App\\Models\\User', 2, 'auth_token', 'b5412933b14280df0fb8738bc7eabe64efb1beec8d8d3582bee111ffa6c0f59c', '[\"*\"]', '2026-05-06 16:11:32', NULL, '2026-05-06 15:18:47', '2026-05-06 16:11:32'),
(12, 'App\\Models\\User', 2, 'auth_token', 'a8ede317ff4f942ec4c5a37d8a60f2c79d59bbbfbf304781c1a0873e210f06ec', '[\"*\"]', '2026-05-09 10:51:28', NULL, '2026-05-09 10:51:17', '2026-05-09 10:51:28'),
(13, 'App\\Models\\User', 1, 'auth_token', 'a5e94c8f6cddb170420b9438b457665dd6d528c3f8f5e3b917a0d3b25660191a', '[\"*\"]', '2026-05-09 12:14:33', NULL, '2026-05-09 10:53:23', '2026-05-09 12:14:33'),
(15, 'App\\Models\\User', 2, 'auth_token', 'd8dbd45975410ed6eae0fc502c3d5c81e3cc1762c2c94fb7f2f3d6b05be8402a', '[\"*\"]', '2026-05-09 12:16:59', NULL, '2026-05-09 12:16:49', '2026-05-09 12:16:59'),
(21, 'App\\Models\\User', 2, 'auth_token', '5f0d9cd9c5f9a1d418c22eefbb36de1d63eb845cdb32ad4cb54f1ecb5a23a80b', '[\"*\"]', '2026-05-09 18:56:13', NULL, '2026-05-09 18:53:28', '2026-05-09 18:56:13'),
(24, 'App\\Models\\User', 2, 'auth_token', 'fd792f9f8f6511f70f738cc42c3c885cded5c28ebb6489959392f2f93fa6e482', '[\"*\"]', '2026-05-10 01:36:23', NULL, '2026-05-10 01:31:07', '2026-05-10 01:36:23'),
(25, 'App\\Models\\User', 2, 'auth_token', 'a69ba269d9de4a569d9a55619c8288179a823bd35f60efbed95e5f55fa04574c', '[\"*\"]', '2026-05-10 11:29:36', NULL, '2026-05-10 11:20:01', '2026-05-10 11:29:36'),
(31, 'App\\Models\\User', 2, 'auth_token', 'b6eb1270f5d6a07e14819f9237655797b8d1455331b398769364441e33a1ac4b', '[\"*\"]', '2026-05-11 20:23:30', NULL, '2026-05-11 19:55:28', '2026-05-11 20:23:30'),
(34, 'App\\Models\\User', 2, 'auth_token', '2dda639009903b325db9250e14253527ed389174081ab6c6b0adf5b8b7cb8adb', '[\"*\"]', NULL, NULL, '2026-05-12 23:53:33', '2026-05-12 23:53:33'),
(44, 'App\\Models\\User', 166, 'auth_token', '237f8d70e1717a3c4dc73d9ee9a6de55b544319a03405254f56d1fc93366b6a6', '[\"*\"]', '2026-05-16 10:54:31', NULL, '2026-05-16 10:47:21', '2026-05-16 10:54:31'),
(51, 'App\\Models\\User', 1, 'auth_token', 'c51b75ce2ef3162c8e317a82667628694a77768e096ebd6621c72602893ef180', '[\"*\"]', '2026-05-17 00:57:30', NULL, '2026-05-17 00:52:44', '2026-05-17 00:57:30'),
(52, 'App\\Models\\User', 2, 'auth_token', 'f170cb568a2205a93971ff771fc56220a06f30895ca429ef748622ab0e4d4b7a', '[\"*\"]', NULL, NULL, '2026-05-17 10:52:25', '2026-05-17 10:52:25'),
(53, 'App\\Models\\User', 2, 'auth_token', 'ad02719a576dac22c6c5b0c67801ddbe8ba3fa5ffd9ace2bba64b3bf7eec9c87', '[\"*\"]', NULL, NULL, '2026-05-17 10:52:42', '2026-05-17 10:52:42'),
(66, 'App\\Models\\User', 2, 'auth_token', '7bbc77b35d9362876ea9b41a8edf11763e6b2a3858a320f2bacbc3cdd556b4e4', '[\"*\"]', '2026-05-17 12:28:46', NULL, '2026-05-17 12:10:43', '2026-05-17 12:28:46'),
(75, 'App\\Models\\User', 2, 'auth_token', 'a16e670db25447d8989f1d3272b95dab99c4ba88475ac8a234811d29b522ddbd', '[\"*\"]', '2026-05-18 15:43:31', NULL, '2026-05-18 15:43:26', '2026-05-18 15:43:31'),
(68, 'App\\Models\\User', 2, 'auth_token', '9934288bb2bd86f87467dceb1f4dfb5ef31aba4f485ed3ab594e57fabc6bc371', '[\"*\"]', '2026-05-18 14:38:46', NULL, '2026-05-18 14:36:44', '2026-05-18 14:38:46');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
CREATE TABLE IF NOT EXISTS `plans` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `duration` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `price`, `duration`, `created_at`, `updated_at`) VALUES
(1, 'Basic Monthly', 169.99, 30, '2026-05-04 11:51:42', '2026-05-04 11:51:42'),
(2, 'Premium Monthly', 249.99, 30, '2026-05-04 11:51:42', '2026-05-04 11:51:42'),
(3, 'Annual VIP', 1499.99, 365, '2026-05-04 11:51:42', '2026-05-04 11:51:42'),
(4, 'Weekly Pass', 49.99, 7, '2026-05-04 11:51:42', '2026-05-04 11:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_foreign` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `slug`, `price`, `stock`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Whey Protein Powder 2kg', 'Premium whey protein isolate for muscle growth and recovery', 'whey-protein-powder-2kg', 49.99, 44, 'https://www.optimumnutrition.co.in/cdn/shop/files/748927071337_1.jpg?v=1756973390', 'active', '2026-05-04 11:51:42', '2026-05-16 10:44:20'),
(2, 1, 'Pre-Workout Energy Boost', 'High-energy pre-workout formula with caffeine and beta-alanine', 'pre-workout-energy-boost', 29.99, 28, 'https://arscornutrition.com/cdn/shop/files/PRE_GAINZ_BLACK_ICY_BLUEBERRY.jpg?v=1765883641&width=1946', 'active', '2026-05-04 11:51:42', '2026-05-16 15:47:08'),
(3, 1, 'BCAA Recovery Formula', 'Branched-chain amino acids for muscle recovery', 'bcaa-recovery-formula', 24.99, 39, 'https://supplementsmaroc.com/cdn/shop/files/Untitleddesign_62.png?v=1749919882', 'active', '2026-05-04 11:51:42', '2026-05-16 10:27:01'),
(4, 1, 'Creatine Monohydrate 500g', 'Pure creatine for strength and power', 'creatine-monohydrate-500g', 19.99, 60, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT6YMbofMGkjDHshb2UfT5jvd8alQLZ4jfyoQ&s', 'active', '2026-05-04 11:51:42', '2026-05-16 10:09:24'),
(5, 2, 'Performance Gym T-Shirt', 'Moisture-wicking athletic t-shirt', 'performance-gym-t-shirt', 19.99, 100, 'https://www.newtheoryclothing.com/cdn/shop/files/EPRT-ShirtGrey-17.jpg?v=1695295571', 'active', '2026-05-04 11:51:42', '2026-05-16 10:27:31'),
(6, 2, 'Compression Training Shorts', 'Flexible compression shorts for intense workouts', 'compression-training-shorts', 29.99, 80, 'https://2xu.com/cdn/shop/files/MA5331b_BLK-BRF_BLK-BRF_1_kqu5vm.jpg?v=1722841351', 'active', '2026-05-04 11:51:42', '2026-05-16 10:28:37'),
(7, 2, 'Gym Hoodie', 'Comfortable cotton-blend hoodie', 'gym-hoodie', 39.99, 50, 'https://thegymking.com/cdn/shop/files/pro-graph-hood.jpg?v=1727271621&width=3000', 'active', '2026-05-04 11:51:42', '2026-05-16 10:29:15'),
(8, 3, 'Resistance Bands Set', '5-piece resistance band set with different strengths', 'resistance-bands-set', 24.99, 40, 'https://www.gallantsports.co.uk/cdn/shop/files/Main_Pull-Up_Assistance_Resistance_Exercise_Bands_Set_-_Pull_Up_Bands.jpg?v=1741078319&width=1280', 'active', '2026-05-04 11:51:42', '2026-05-16 10:29:56'),
(9, 3, 'Yoga Mat Premium', 'Non-slip premium yoga mat with carrying strap', 'yoga-mat-premium', 34.99, 30, 'https://m.media-amazon.com/images/I/81JA2pahtiL.jpg', 'active', '2026-05-04 11:51:42', '2026-05-16 10:30:31'),
(10, 3, 'Lifting Gloves', 'Padded weight-lifting gloves with wrist support', 'lifting-gloves', 14.99, 60, 'https://production.atgwasl.com/dw/image/v2/BDSP_PRD/on/demandware.static/-/Sites-GS-master-catalog/default/dw4f75b555/sfcc-gsk-production/2/1/7/1/7/217177677_E2.jpg', 'active', '2026-05-04 11:51:42', '2026-05-16 10:31:50'),
(11, 3, 'Jump Rope Speed', 'Adjustable speed jump rope for cardio', 'jump-rope-speed', 9.99, 69, 'https://www.venum.com/cdn/shop/files/09b08ae7425a7e321877eda7da967a2cb75df290_EU_VENUM_0973___VNM___1.jpg?v=1751035002', 'active', '2026-05-04 11:51:42', '2026-05-18 14:25:13'),
(12, 4, 'Sports Drink 500ml', 'Electrolyte-enhanced sports drink', 'sports-drink-500ml', 2.99, 200, 'https://med365.co.za/cdn/shop/files/Powerade_Orange_Sports_Drink_6_x_500ml_ce397292-fa94-4ae8-baad-023b2796dea3_1024x1024.jpg?v=1757488782', 'active', '2026-05-04 11:51:42', '2026-05-16 10:26:13'),
(13, 4, 'Energy Drink', 'Sugar-free energy drink', 'energy-drink', 3.49, 150, 'https://groceries.morrisons.com/images-v3/4b85987b-1398-4173-a0c1-3546047c9d74/c643e192-47de-495b-84b8-67002b72022a/300x300.jpg', 'active', '2026-05-04 11:51:42', '2026-05-16 10:34:31'),
(14, 4, 'Protein Shake Ready-to-Drink', '25g protein ready-to-drink shake', 'protein-shake-ready-to-drink', 4.99, 100, 'https://www.optimumnutrition.com/cdn/shop/files/ON_ProteinShake_RTD_Choc_6077194.png?v=1769454515&width=5000', 'active', '2026-05-04 11:51:42', '2026-05-16 10:37:27'),
(15, 5, 'Gym Bag Large', 'Spacious gym bag with multiple compartments', 'gym-bag-large', 44.99, 25, 'https://image.made-in-china.com/202f0j00sbAoOaHdQTqJ/40L-Water-Resistant-Sports-Bag-Gym-Duffle-Bag-with-Wet-Pocket-Large-Travel-Duffel-Weekender-Overnight-Sports-Bag.webp', 'active', '2026-05-04 11:51:42', '2026-05-16 10:35:11'),
(16, 5, 'Water Bottle 1L', 'BPA-free sports water bottle', 'water-bottle-1l', 12.99, 80, 'https://m.media-amazon.com/images/I/71HOfeG+rdL._AC_UF894,1000_QL80_.jpg', 'active', '2026-05-04 11:51:42', '2026-05-16 10:35:39'),
(17, 5, 'Microfiber Gym Towel', 'Quick-dry microfiber towel', 'microfiber-gym-towel', 9.99, 100, 'https://www.trueprotein.com.au/cdn/shop/files/GymTowel04_2000x2000_9e146203-9dc8-4c06-97de-4a1337d92b94.jpg?v=1747639022&width=1500', 'active', '2026-05-04 11:51:42', '2026-05-16 10:36:13'),
(18, 5, 'Wireless Earbuds', 'Sweat-proof wireless earbuds for workouts', 'wireless-earbuds', 59.99, 20, 'https://media.wired.com/photos/68d6f5d9a10e9fa625d46eae/4:3/w_640%2Cc_limit/Apple%2520AirPods%2520Pro%25203%2520%25203source%2520parker%2520hall.png', 'active', '2026-05-04 11:51:42', '2026-05-16 10:36:49'),
(19, 1, 'cellulor preworkout 500gr', 'the best preworkout out there to insure a better training energized session', 'cellulor-preworkout-500gr', 40.00, 20, 'https://vitamins.lv/cdn/shop/products/cellucor-preworkout-cellucor-c4-pre-workout-360-g-2035056476188.jpg?v=1623084170', 'active', '2026-05-16 10:06:41', '2026-05-16 10:06:41');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
CREATE TABLE IF NOT EXISTS `programs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `thumbnail` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_weeks` int NOT NULL DEFAULT '6',
  `days_per_week` int NOT NULL DEFAULT '4',
  `difficulty` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'intermediate',
  `goal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `coach_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `programs_coach_id_foreign` (`coach_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `name`, `description`, `thumbnail`, `duration_weeks`, `days_per_week`, `difficulty`, `goal`, `is_active`, `coach_id`, `created_at`, `updated_at`) VALUES
(1, 'FULL BODY FOUNDATION', 'A comprehensive entry-level program designed to build a balanced base of strength and stability.', 'https://images.unsplash.com/photo-1517836357463-d251f1904C-S-S-S', 8, 3, 'beginner', 'General Fitness', 1, 1, '2026-05-18 12:20:02', '2026-05-18 12:20:02'),
(2, 'LOWER BODY SPECIALIST', 'Advanced protocols focusing on quad development, hamstring power, and glute hypertrophy.', 'https://images.unsplash.com/photo-1534258524067-87637bb7578d?q=80&w=800', 10, 3, 'intermediate', 'Leg Growth', 1, 1, '2026-05-18 12:20:02', '2026-05-18 12:20:02'),
(3, 'UPPER BODY POWER', 'Focused on maximizing pushing and pulling strength for a powerful V-taper physique.', 'https://images.unsplash.com/photo-158100914S-S-S', 12, 4, 'advanced', 'Upper Body Strength', 1, 1, '2026-05-18 12:20:02', '2026-05-18 12:20:02'),
(4, 'CORE & CONDITIONING', 'High-intensity metabolic conditioning paired with deep core stabilization techniques.', 'https://images.unsplash.com/photo-1544005613-13f17aee242d?q=80&w=800', 6, 5, 'intermediate', 'Athleticism', 1, 1, '2026-05-18 12:20:02', '2026-05-18 12:20:02'),
(5, 'ELITE ATHLETE PERFORMANCE', 'The pinnacle of training. Combining heavy compound lifts with high-volume accessory work.', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', 16, 6, 'advanced', 'Peak Performance', 1, 1, '2026-05-18 12:20:02', '2026-05-18 12:20:02');

-- --------------------------------------------------------

--
-- Table structure for table `program_exercises`
--

DROP TABLE IF EXISTS `program_exercises`;
CREATE TABLE IF NOT EXISTS `program_exercises` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_workouts`
--

DROP TABLE IF EXISTS `program_workouts`;
CREATE TABLE IF NOT EXISTS `program_workouts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_id` bigint UNSIGNED NOT NULL,
  `workout_id` bigint UNSIGNED NOT NULL,
  `week` int NOT NULL DEFAULT '1',
  `day` int NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `program_workouts_program_id_foreign` (`program_id`),
  KEY `program_workouts_workout_id_foreign` (`workout_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `program_workouts`
--

INSERT INTO `program_workouts` (`id`, `program_id`, `workout_id`, `week`, `day`, `order`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 1, 1, NULL, NULL),
(2, 1, 7, 1, 2, 2, NULL, NULL),
(3, 1, 9, 1, 3, 3, NULL, NULL),
(4, 2, 3, 1, 1, 1, NULL, NULL),
(5, 2, 8, 1, 2, 2, NULL, NULL),
(6, 2, 2, 1, 3, 3, NULL, NULL),
(7, 3, 1, 1, 1, 1, NULL, NULL),
(8, 3, 5, 1, 2, 2, NULL, NULL),
(9, 3, 6, 1, 3, 3, NULL, NULL),
(10, 4, 4, 1, 1, 1, NULL, NULL),
(11, 4, 7, 1, 2, 2, NULL, NULL),
(12, 4, 9, 1, 3, 3, NULL, NULL),
(13, 5, 1, 1, 1, 1, NULL, NULL),
(14, 5, 2, 1, 2, 2, NULL, NULL),
(15, 5, 3, 1, 3, 3, NULL, NULL),
(16, 5, 6, 2, 4, 4, NULL, NULL),
(17, 5, 8, 2, 5, 5, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `day_of_week` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `capacity` int NOT NULL,
  `room` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `class_name`, `day_of_week`, `start_time`, `end_time`, `capacity`, `room`, `created_at`, `updated_at`) VALUES
(1, 'Elite HIIT', 'Monday', '13:00:00', '14:00:00', 20, 'Studio A', '2026-05-16 13:33:38', '2026-05-16 13:33:38'),
(2, 'Power Lifting', 'Wednesday', '16:00:00', '18:00:00', 10, 'Heavy Zone', '2026-05-16 13:33:38', '2026-05-16 13:33:38'),
(3, 'Weight Loss', 'Saturday', '11:00:00', '12:00:00', 34, 'Studio B', '2026-05-16 13:33:38', '2026-05-16 13:33:38'),
(4, 'Girls class', 'Thursday', '11:00:00', '00:00:00', 29, 'Studio c', '2026-05-16 14:07:37', '2026-05-16 14:07:37');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `fitness_goal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `measurement_unit` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'metric',
  `height_cm` int DEFAULT NULL,
  `weight_kg` int DEFAULT NULL,
  `workout_reminders` tinyint(1) NOT NULL DEFAULT '1',
  `nutrition_alerts` tinyint(1) NOT NULL DEFAULT '1',
  `system_updates` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','client') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'client',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `avatar`, `phone`, `birth_date`, `fitness_goal`, `measurement_unit`, `height_cm`, `weight_kg`, `workout_reminders`, `nutrition_alerts`, `system_updates`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@gym.com', NULL, '0621010978', NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$aA0DZyuv1TWixYD/2ZiMae/XskqyMItZdjPQOVWxTvRtnHTp02paO', 'admin', NULL, '2026-05-04 11:51:42', '2026-05-18 17:22:50'),
(2, 'Oualid Pro', 'oualid@gym.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$5IzfT4lViVLx8n1R6sdue.zvuywd340sS7y6GGNnnL8rN9wf.WDzK', 'client', NULL, '2026-05-04 11:51:42', '2026-05-18 14:54:16'),
(3, '7madddiiii', 'maurice90@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '28D5hWZ3oU', '2026-05-04 11:51:44', '2026-05-18 17:14:20'),
(4, 'Caden Stamm I', 'houston.kuhn@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'onQku5cwcY', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(5, 'Elyse', 'toy.kaya@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'FdCHh0aRoU', '2026-05-04 11:51:44', '2026-05-18 17:14:06'),
(6, 'Mrs. Ofelia Price', 'jamal09@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'aTpFxdWIUq', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(7, 'Ms. Emma Adams', 'giovanni86@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'PyuzEgM9vL', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(8, 'Mckenzie Kohler', 'fschmitt@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'qqjMPRaAfI', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(9, 'Lexi Koepp DVM', 'hauck.foster@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'DjKC3QgoA8', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(11, 'Jarvis Heaney', 'harmon83@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'd4Qgf1Wfpo', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(12, 'Jonas Schuster', 'rigoberto18@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'TkJK7APQxE', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(13, 'Idell Jaskolski', 'obrakus@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'EwtM4125qQ', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(14, 'Zelma Runte', 'cristal04@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '0SSeqvaW0F', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(16, 'Dane Roob', 'annabel83@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'x98vDM71o2', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(17, 'Ezekiel Sanford', 'ometz@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'nAbZqRK1rZ', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(18, 'Dr. Benny Ortiz', 'gnicolas@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'zos4D4Dh54', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(19, 'Noah Collins V', 'bberge@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'w8jBDEpHNk', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(20, 'Finn Prohaska', 'emard.darlene@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'IWEzi6rRi7', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(21, 'Oswald Koepp', 'jerrell97@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'Gp01mhMCJ6', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(22, 'Declan Cole I', 'mcdermott.dayana@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'TJxjsDs9WQ', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(23, 'Darion Moen', 'fgusikowski@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'O07S1wN1MX', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(24, 'Gilbert Gleichner', 'xframi@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'TEvzmUOSNW', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(25, 'Berenice West', 'newton.berge@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'yMqRLBhzIY', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(26, 'Llewellyn Grimes', 'jeichmann@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'MIDFAHldzw', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(27, 'Dr. Trent Welch', 'geovanny.roob@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '8JgYS0e9S7', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(28, 'Eldon McKenzie', 'irenner@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'BCLTgbkgCO', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(29, 'Dr. Everardo Feeney V', 'pagac.zena@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'HIZhFqDaDh', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(30, 'Dr. Javonte Robel', 'rjenkins@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'qcn0VCE7gs', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(31, 'Roselyn Zemlak', 'telly32@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'YX3IK2CZrd', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(32, 'Crawford Wolf', 'qlockman@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '0xlX9BRxjJ', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(33, 'Cullen Pfannerstill', 'christine.kutch@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'AcSnqWwEZj', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(34, 'Cory Rosenbaum', 'durgan.furman@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'BcoYuvkCCX', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(35, 'Mr. Timmothy Bauch', 'mhuel@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'z5PwnNGcPT', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(36, 'Carroll Lebsack', 'bbrakus@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'iKKaHj9uYc', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(37, 'Veda Rippin', 'bertram.mclaughlin@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'eoUc3sI8IF', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(38, 'Dr. Dejuan Beier', 'clement61@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '0hXlxu6TWW', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(39, 'Cayla Shanahan', 'jtromp@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'rsppDI63qy', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(40, 'Janie Muller V', 'julius36@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'VzaM0jbHUq', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(41, 'Urban Osinski', 'bella63@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'RPwTUG6iTd', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(42, 'Mrs. Amely Koelpin I', 'bechtelar.darwin@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'wpwTiD2GPP', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(43, 'Ned Hermann', 'nnikolaus@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'M6iOI0J99a', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(44, 'Edgardo Marvin', 'jedidiah24@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '6bRBgac3Dl', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(45, 'Montana Stamm', 'anika64@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'Vo1VJ9pCEj', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(46, 'Vivianne Leuschke I', 'oauer@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'PVU2eeYzNI', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(47, 'Riley Rippin', 'vmueller@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'DYpD71hr3U', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(48, 'Arnoldo O\'Connell', 'smith.arnoldo@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'AdZ3iepxVV', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(49, 'Melba Brekke', 'schamberger.missouri@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'JAC8ZJBIaL', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(50, 'Jakayla Gutkowski', 'dariana85@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'wNJLtaqcAz', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(51, 'Mr. Emmanuel Spinka', 'runte.kaden@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'gfhoG8MSPJ', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(52, 'Marina Von Sr.', 'beatrice36@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'PUb6x2Cuah', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(53, 'Michael Grant III', 'wdaniel@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'jatzIGHCeG', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(54, 'Rex Goldner', 'violet43@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'LPLRuvt4QW', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(55, 'Dr. Art Windler', 'oyundt@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'F7M53i0BCo', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(56, 'Ms. Elaina Stoltenberg', 'leuschke.edwin@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'Ukd1DQ6lvU', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(57, 'Mr. Jayson Graham', 'cierra12@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '6X14eTGITo', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(58, 'Eleanore Barrows', 'breana.wolff@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '6UX4ZHW2Ag', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(59, 'Noemy Wolff I', 'padberg.alyson@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'WG3YqOY5DH', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(60, 'Miss Luella Thiel', 'margie.ryan@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '00WYxboIuo', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(61, 'Adriel Boehm', 'sanford.shanel@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '5QSlCeKjVN', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(62, 'Mrs. Vivienne Sipes', 'frami.neoma@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '19I7tufIXz', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(63, 'Prof. Eddie Hane', 'adrienne46@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '1jMmTRPHNc', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(64, 'Niko Hegmann', 'brekke.yadira@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'pQ4dgbk6e2', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(65, 'Golden Gislason', 'xfranecki@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '7rqlBKSwxm', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(66, 'Mr. Laverne Stanton PhD', 'laurianne06@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'De3JaDYpah', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(67, 'Brando Kovacek PhD', 'bradley91@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'B3oK62G0Vv', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(68, 'Gladys Heathcote', 'caterina.bauch@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'q1d3iD7ADj', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(69, 'Dr. Tabitha Lemke IV', 'claudie08@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'ST1iB9fB3g', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(70, 'Orie Vandervort', 'callie02@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'H9RIaIhk7g', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(71, 'Miss Matilda McCullough', 'nestor24@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'wIdZIg29yD', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(72, 'Dulce Heidenreich', 'collier.dora@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'pCyo8rfPMm', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(73, 'Conner O\'Conner', 'dare.augustine@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'yR3ska1XIp', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(74, 'Chauncey Ritchie', 'marquardt.amir@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'UjHr9AbqpA', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(75, 'Opal VonRueden', 'zachery.farrell@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'izZIQ1zK0g', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(76, 'Camille Yundt', 'zherzog@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'Rfq3512BFh', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(77, 'Ms. Jazlyn McClure', 'rutherford.katheryn@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'CcAKpQmI7H', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(78, 'Dr. Lily Dibbert DDS', 'dibbert.jonathan@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'TwL58wNipZ', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(79, 'Hal Okuneva', 'hand.karolann@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'GjAz3YmCvx', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(80, 'Kaya Stokes', 'ulehner@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '6DqVmvMmvb', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(81, 'Dr. Jaquan Prosacco', 'medhurst.fredy@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '6Zp34q3tJM', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(82, 'Shany Balistreri', 'padberg.tremayne@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'zziJOJemzW', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(83, 'Ulices Reynolds', 'martina.ratke@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '6LwAuQzK3r', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(84, 'Eunice Homenick', 'bogisich.beatrice@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'pkWo02DDlk', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(85, 'Riley Ward', 'fisher.ciara@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'qJQ2gjIQCH', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(86, 'Ephraim Becker', 'ursula98@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'him6qEbW68', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(87, 'Ignatius Tillman', 'gaylord.lakin@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'DxH8E7STDU', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(88, 'Miss Dena Hand DDS', 'jeanette84@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'EBLT2IHg5J', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(89, 'Gwendolyn Huels', 'reichert.raven@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'BMo22vSj8T', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(90, 'Araceli Wolf', 'sammie.volkman@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'eCVd2ATSH1', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(91, 'Harry Larson', 'kayla71@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '44pTlPoU5L', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(92, 'Mr. Arjun Medhurst', 'aparisian@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'nTVm8w7cXT', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(93, 'Nicolas Heller', 'margot40@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'f4ckjnnOqx', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(94, 'Chelsea Bailey', 'yfeeney@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'XXqD9aCW2C', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(95, 'Ms. Cleta Harris', 'rylee89@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'ofKtVT6P1A', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(96, 'Raymundo Konopelski', 'kelly.torphy@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'Sxcy38zqih', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(97, 'Austin Skiles', 'annamarie.harber@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'pwpOTpE5LM', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(98, 'Rigoberto Runte', 'kbecker@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'g3TcZjhStH', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(99, 'Lolita Beier', 'moriah07@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '4QkEhu86qx', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(100, 'Dr. Emanuel Haag', 'herzog.sanford@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'T3uqRfJdQB', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(101, 'Dina Kihn', 'zkutch@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'HawYQJfFE0', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(102, 'Rowland Haley', 'macy.brakus@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '1zQejNsr8N', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(103, 'Loyal Smitham', 'chadd72@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'DEDhws8pvl', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(104, 'Alek Glover', 'ncrist@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'v3a1DTlGuv', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(105, 'Ned Lowe IV', 'abe19@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'zGOAqjNbML', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(106, 'Jamarcus Feil', 'domenica50@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '4qQ10KLezv', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(107, 'Miss Lurline Kozey', 'ufeest@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'RdbPVkocf8', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(108, 'Rory Marvin MD', 'ziemann.flossie@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'Sow9f41mXD', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(109, 'Kristina Cruickshank', 'kautzer.modesto@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'mvVMbJDqFo', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(110, 'Ari Mills', 'pouros.bret@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:44', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'OgqJRem6WG', '2026-05-04 11:51:44', '2026-05-04 11:51:44'),
(111, 'Colten Kertzmann', 'stokes.addison@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'H91SI8kNTY', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(112, 'Paolo Koch', 'brock15@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'qe7cqtv1Fg', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(113, 'Mrs. Letitia Aufderhar', 'phuels@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'oPnB7ofzNK', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(114, 'Prof. Rodrick Braun', 'merritt32@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'ZlBIdK8UmR', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(115, 'Adolfo Homenick', 'edison.bartoletti@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '9A8hOkIyZx', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(116, 'Oma Wolff', 'kkshlerin@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'WPLHn7feWm', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(117, 'Dr. Jesus Daniel PhD', 'marta56@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'TLhsOsxLQU', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(118, 'Mayra Botsford II', 'ebert.ethel@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'ZyKGooIaTz', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(119, 'Miss Karli Goodwin MD', 'rebeka28@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'hAY0kjMEYu', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(120, 'Mrs. Adaline Miller III', 'allie.auer@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'JLn4xJ39H3', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(121, 'Mr. Ernesto Funk DDS', 'damore.grayce@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'AwsrcpidLU', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(122, 'Jerome Bruen', 'mokuneva@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'nTI22WEtou', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(123, 'Prof. Wendy Reichert', 'dejuan04@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'f2SCyAhMjM', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(124, 'Virgil Sauer', 'mayert.josephine@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'awFND35Xu2', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(125, 'Janiya Mertz', 'august06@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '9rxISmHqJf', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(126, 'Mrs. Elody Kessler DVM', 'eduardo.kuhic@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'rs5hbKgbCF', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(127, 'Josiane Effertz', 'kelton.tromp@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'inPjf5JC7s', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(128, 'Lacey VonRueden', 'nolan43@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'NxgG5oDtQZ', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(129, 'Shyanne Mohr', 'goodwin.maddison@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'DAaXgIcyzP', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(130, 'Dr. Ted Abernathy', 'hettinger.kira@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '48eXLxhD85', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(131, 'Miss Camila Farrell', 'verla28@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'QtFfzSqt2h', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(132, 'Craig Feil', 'smitham.candice@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'qYL60bHW5A', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(133, 'Naomi Koch', 'bill.hickle@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '5A2FnHeUuG', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(134, 'Vickie Herman', 'jeffrey.lebsack@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'XNJOmuZPJB', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(135, 'Amelie Jast', 'skilback@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'kBuBYaYswO', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(136, 'Brown Waelchi', 'gbartell@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'c0izFwkwyq', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(137, 'Martin Cormier', 'bobbie.kertzmann@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'jaebSI0YvU', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(138, 'Davon Johnston PhD', 'rice.wallace@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'CeTYzyo7Gp', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(139, 'Jalen Koch', 'ron.jacobs@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'EYnuDiDtZJ', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(140, 'Dr. Elise Grady', 'hand.baylee@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'zLTT2wZ4oW', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(141, 'Hilma Thompson', 'alyson.osinski@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'uJJ3k861J1', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(142, 'Prof. Dessie Kuphal', 'jake89@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'DMucy2XlyX', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(143, 'Fay Hammes', 'ibins@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'tlPOhFmEG5', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(144, 'Cyrus Kemmer', 'wwisozk@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'sU0B3bvZFm', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(145, 'Maxie Eichmann', 'sebastian96@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'yypI0k8eql', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(146, 'Jena Koss', 'beahan.antonetta@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'VuuEjoiUfP', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(147, 'Baby Altenwerth III', 'lou.okeefe@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '5V7EOKYxbF', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(148, 'Nettie Krajcik', 'austyn.fisher@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'uu5ALLRq13', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(149, 'Miss Maggie Carroll Jr.', 'jlittel@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '9SzSMmtxLB', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(150, 'Jon O\'Keefe', 'jfay@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'g8PRtoUj6m', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(151, 'Thurman Murphy', 'johnnie18@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'BNgXmGoSPz', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(152, 'Mrs. Chelsea Boyer', 'cale.rath@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'Nky4VC9P8j', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(153, 'Jeanie Crona', 'nia96@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '1xiR6bYZxA', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(154, 'Alivia Willms', 'qgislason@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'oSQnYvg4TE', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(155, 'Prof. Sterling Little', 'jheaney@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'yCf7n7LI0G', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(156, 'Elyse Parker', 'alan.morissette@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'afPZUTuWLk', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(157, 'Aurelie Swaniawski', 'anahi25@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'QXoXoA3eOh', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(158, 'Jalen Stoltenberg V', 'dstehr@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'ChaltWvioX', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(159, 'Perry Rutherford III', 'mhuels@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'RAksFWSQmP', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(160, 'Orville Stiedemann', 'buckridge.kirk@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', '2sUIa5zXGy', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(161, 'Keven Rosenbaum', 'agaylord@example.net', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'WYspAk2OOi', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(162, 'Vickie Herzog', 'wintheiser.rosalyn@example.org', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'PsXHXvs2Kp', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(163, 'Fatima Conroy', 'anne72@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'AH7nVIlKRj', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(164, 'Prof. Misty Walker', 'corwin.baylee@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'l33umdBIwh', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(165, 'Rhea Marvin', 'bethel.roob@example.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, '2026-05-04 11:51:45', '$2y$12$raRCeisBLDGP5zv6rr/wQ.VNrEwJDxDYHGicFq0V5jKchwVYunw/a', 'client', 'L8Y8QE9zdK', '2026-05-04 11:51:45', '2026-05-04 11:51:45'),
(166, 'mohammed arbi', 'mohammed@gmail.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$x/Y0PPOAmzsdSQXvTHHdAuCCyIPmn7u7CDPFDMyLe/VenfFmyI.vG', 'client', NULL, '2026-05-16 10:47:21', '2026-05-16 10:47:21'),
(167, 'mozmoz', 'moz@gmail.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$xc75aLANx5xgLqPuQthqh.dqPPJ2KFUuL5o2Y9pHJW7AxDdJFkyF.', 'client', NULL, '2026-05-17 11:15:34', '2026-05-17 11:15:34'),
(168, 'Marcus Thorne', 'marcus.thorne@alien.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$H6lkZZt/IxU5GBq6ym5RquWlOkxBwTLASRieW8oMVSJlpO5bc5.z6', 'admin', NULL, '2026-05-18 11:11:59', '2026-05-18 11:11:59'),
(169, 'Sienna Vance', 'sienna.vance@alien.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$ZvLXpNQ4H9DFHgIteMA1p.gxezPrGzdjK1zrHWnGWgQm.c9Aigkaa', 'admin', NULL, '2026-05-18 11:11:59', '2026-05-18 11:11:59'),
(170, 'Kaelen Drax', 'kaelen.drax@alien.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$YtNTTtebfq7UT7WPovHY5uqtn0mqsL/0s3r05ElmwMIAq4yhi9z6W', 'admin', NULL, '2026-05-18 11:12:00', '2026-05-18 11:12:00'),
(171, 'mouataz', 'mozz@gmail.com', NULL, NULL, NULL, NULL, 'metric', NULL, NULL, 1, 1, 0, NULL, '$2y$12$i.CikjwJ8VWIAfcMzEtAFOj9cxTKc5BP.qXTegcgJcq9TOG5GU44K', 'client', NULL, '2026-05-18 16:52:28', '2026-05-18 16:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `user_coaches`
--

DROP TABLE IF EXISTS `user_coaches`;
CREATE TABLE IF NOT EXISTS `user_coaches` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `coach_id` bigint UNSIGNED NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `started_at` date DEFAULT NULL,
  `ended_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_coaches_user_id_foreign` (`user_id`),
  KEY `user_coaches_coach_id_foreign` (`coach_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_coaches`
--

INSERT INTO `user_coaches` (`id`, `user_id`, `coach_id`, `status`, `started_at`, `ended_at`, `created_at`, `updated_at`) VALUES
(1, 2, 13, 'active', '2026-05-04', NULL, '2026-05-04 11:56:40', '2026-05-04 11:56:40'),
(2, 2, 1, 'active', '2026-05-14', NULL, '2026-05-14 12:20:47', '2026-05-14 12:20:47'),
(3, 2, 1, 'active', '2026-05-18', NULL, '2026-05-18 12:58:49', '2026-05-18 12:58:49');

-- --------------------------------------------------------

--
-- Table structure for table `user_programs`
--

DROP TABLE IF EXISTS `user_programs`;
CREATE TABLE IF NOT EXISTS `user_programs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `program_id` bigint UNSIGNED NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `current_week` int NOT NULL DEFAULT '1',
  `current_day` int NOT NULL DEFAULT '1',
  `completion_percentage` int NOT NULL DEFAULT '0',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_programs_user_id_foreign` (`user_id`),
  KEY `user_programs_program_id_foreign` (`program_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_programs`
--

INSERT INTO `user_programs` (`id`, `user_id`, `program_id`, `start_date`, `end_date`, `current_week`, `current_day`, `completion_percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 3, '2026-05-04', '2026-07-20', 1, 1, 0, 'active', '2026-05-04 11:56:55', '2026-05-04 11:56:55'),
(2, 2, 2, '2026-05-04', '2026-07-27', 1, 1, 0, 'active', '2026-05-04 11:56:56', '2026-05-04 11:56:56'),
(3, 2, 1, '2026-05-04', '2026-07-06', 1, 1, 0, 'active', '2026-05-04 11:56:56', '2026-05-04 11:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `user_workouts`
--

DROP TABLE IF EXISTS `user_workouts`;
CREATE TABLE IF NOT EXISTS `user_workouts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `workout_id` bigint UNSIGNED NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `calories_burned` int DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `rating` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_workouts_user_id_foreign` (`user_id`),
  KEY `user_workouts_workout_id_foreign` (`workout_id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_workouts`
--

INSERT INTO `user_workouts` (`id`, `user_id`, `workout_id`, `started_at`, `completed_at`, `duration_minutes`, `calories_burned`, `status`, `notes`, `rating`, `created_at`, `updated_at`) VALUES
(1, 2, 9, '2026-05-14 11:58:22', '2026-05-14 11:58:54', 0, 279, 'completed', 'Completed via app', NULL, '2026-05-14 10:58:22', '2026-05-14 10:58:54'),
(2, 2, 14, '2026-05-14 11:59:12', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 10:59:12', '2026-05-14 10:59:12'),
(3, 2, 14, '2026-05-14 11:59:12', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 10:59:12', '2026-05-14 10:59:12'),
(4, 2, 14, '2026-05-14 11:59:38', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 10:59:38', '2026-05-14 10:59:38'),
(5, 2, 14, '2026-05-14 11:59:38', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 10:59:38', '2026-05-14 10:59:38'),
(6, 2, 14, '2026-05-14 12:02:04', '2026-05-14 12:02:51', 0, 285, 'completed', 'Completed via app', NULL, '2026-05-14 11:02:04', '2026-05-14 11:02:51'),
(7, 2, 14, '2026-05-14 13:03:52', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:03:52', '2026-05-14 12:03:52'),
(8, 2, 14, '2026-05-14 13:05:03', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:05:03', '2026-05-14 12:05:03'),
(9, 2, 14, '2026-05-14 13:05:03', '2026-05-14 13:05:11', 0, 285, 'completed', 'Completed via app', NULL, '2026-05-14 12:05:03', '2026-05-14 12:05:11'),
(10, 2, 4, '2026-05-14 13:06:22', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:06:22', '2026-05-14 12:06:22'),
(11, 2, 4, '2026-05-14 13:13:35', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:13:35', '2026-05-14 12:13:35'),
(12, 2, 4, '2026-05-14 13:13:35', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:13:35', '2026-05-14 12:13:35'),
(13, 2, 4, '2026-05-14 13:19:16', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:19:16', '2026-05-14 12:19:16'),
(14, 2, 4, '2026-05-14 13:19:32', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:19:32', '2026-05-14 12:19:32'),
(15, 2, 4, '2026-05-14 13:19:48', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:19:48', '2026-05-14 12:19:48'),
(16, 2, 4, '2026-05-14 13:19:48', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:19:48', '2026-05-14 12:19:48'),
(17, 2, 4, '2026-05-14 13:20:07', '2026-05-14 13:20:13', 0, 358, 'completed', 'Completed via app', NULL, '2026-05-14 12:20:07', '2026-05-14 12:20:13'),
(18, 2, 14, '2026-05-14 13:20:22', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:20:22', '2026-05-14 12:20:22'),
(19, 2, 14, '2026-05-14 13:20:22', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-14 12:20:22', '2026-05-14 12:20:22'),
(20, 166, 9, '2026-05-16 11:49:05', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:49:05', '2026-05-16 10:49:05'),
(21, 166, 9, '2026-05-16 11:52:12', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:12', '2026-05-16 10:52:12'),
(22, 166, 9, '2026-05-16 11:52:12', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:12', '2026-05-16 10:52:12'),
(23, 166, 9, '2026-05-16 11:52:14', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:14', '2026-05-16 10:52:14'),
(24, 166, 9, '2026-05-16 11:52:14', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:14', '2026-05-16 10:52:14'),
(25, 166, 9, '2026-05-16 11:52:19', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:19', '2026-05-16 10:52:19'),
(26, 166, 9, '2026-05-16 11:52:19', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:19', '2026-05-16 10:52:19'),
(27, 166, 9, '2026-05-16 11:52:21', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:21', '2026-05-16 10:52:21'),
(28, 166, 9, '2026-05-16 11:52:21', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:21', '2026-05-16 10:52:21'),
(29, 166, 9, '2026-05-16 11:52:22', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:22', '2026-05-16 10:52:22'),
(30, 166, 9, '2026-05-16 11:52:22', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:22', '2026-05-16 10:52:22'),
(31, 166, 9, '2026-05-16 11:52:39', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:39', '2026-05-16 10:52:39'),
(32, 166, 9, '2026-05-16 11:52:39', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:39', '2026-05-16 10:52:39'),
(33, 166, 9, '2026-05-16 11:52:43', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:43', '2026-05-16 10:52:43'),
(34, 166, 9, '2026-05-16 11:52:43', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 10:52:43', '2026-05-16 10:52:43'),
(35, 2, 14, '2026-05-16 15:13:41', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-16 14:13:41', '2026-05-16 14:13:41'),
(36, 2, 4, '2026-05-18 11:42:17', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 1', NULL, '2026-05-18 10:42:17', '2026-05-18 10:43:56'),
(37, 2, 14, '2026-05-18 11:44:07', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 10:44:07', '2026-05-18 10:44:07'),
(38, 2, 4, '2026-05-18 11:56:28', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 10:56:28', '2026-05-18 10:56:28'),
(39, 2, 4, '2026-05-18 12:12:51', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:12:51', '2026-05-18 11:12:51'),
(40, 2, 4, '2026-05-18 12:12:52', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:12:52', '2026-05-18 11:12:52'),
(41, 2, 9, '2026-05-18 12:28:59', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:28:59', '2026-05-18 11:28:59'),
(42, 2, 9, '2026-05-18 12:30:53', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:30:53', '2026-05-18 11:30:53'),
(43, 2, 9, '2026-05-18 12:30:54', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:30:54', '2026-05-18 11:30:54'),
(44, 2, 9, '2026-05-18 12:31:04', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:31:04', '2026-05-18 11:31:04'),
(45, 2, 9, '2026-05-18 12:31:04', '2026-05-18 12:31:18', 0, 798, 'completed', 'Completed via app', NULL, '2026-05-18 11:31:04', '2026-05-18 11:31:18'),
(46, 2, 6, '2026-05-18 12:33:36', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:33:36', '2026-05-18 11:33:36'),
(47, 2, 6, '2026-05-18 12:33:36', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:33:36', '2026-05-18 11:33:36'),
(48, 2, 6, '2026-05-18 12:55:02', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:55:02', '2026-05-18 11:55:02'),
(49, 2, 6, '2026-05-18 12:55:02', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 0', NULL, '2026-05-18 11:55:02', '2026-05-18 11:55:05'),
(50, 2, 1, '2026-05-18 12:55:20', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:55:20', '2026-05-18 11:55:20'),
(51, 2, 1, '2026-05-18 12:56:49', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:56:49', '2026-05-18 11:56:49'),
(52, 2, 1, '2026-05-18 12:56:50', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:56:50', '2026-05-18 11:56:50'),
(53, 2, 1, '2026-05-18 12:56:58', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:56:58', '2026-05-18 11:56:58'),
(54, 2, 1, '2026-05-18 12:56:58', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 11:56:58', '2026-05-18 11:56:58'),
(55, 2, 1, '2026-05-18 13:01:38', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:01:38', '2026-05-18 12:01:38'),
(56, 2, 1, '2026-05-18 13:01:39', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:01:39', '2026-05-18 12:01:39'),
(57, 2, 1, '2026-05-18 13:03:37', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:03:37', '2026-05-18 12:03:37'),
(58, 2, 1, '2026-05-18 13:03:38', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:03:38', '2026-05-18 12:03:38'),
(59, 2, 1, '2026-05-18 13:04:12', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:04:12', '2026-05-18 12:04:12'),
(60, 2, 1, '2026-05-18 13:04:12', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:04:12', '2026-05-18 12:04:12'),
(61, 2, 1, '2026-05-18 13:04:43', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:04:43', '2026-05-18 12:04:43'),
(62, 2, 1, '2026-05-18 13:04:43', '2026-05-18 13:05:31', 0, 507, 'completed', 'Completed via app', NULL, '2026-05-18 12:04:43', '2026-05-18 12:05:31'),
(63, 2, 4, '2026-05-18 13:05:45', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 0', NULL, '2026-05-18 12:05:45', '2026-05-18 12:08:49'),
(64, 2, 9, '2026-05-18 13:09:00', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 0', NULL, '2026-05-18 12:09:00', '2026-05-18 12:09:06'),
(65, 2, 4, '2026-05-18 13:09:10', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:09:10', '2026-05-18 12:09:10'),
(66, 2, 1, '2026-05-18 13:09:18', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:09:18', '2026-05-18 12:09:18'),
(67, 2, 1, '2026-05-18 13:20:13', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:20:13', '2026-05-18 12:20:13'),
(68, 2, 1, '2026-05-18 13:20:14', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:20:14', '2026-05-18 12:20:14'),
(69, 2, 2, '2026-05-18 13:20:23', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:20:23', '2026-05-18 12:20:23'),
(70, 2, 2, '2026-05-18 13:23:52', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:23:52', '2026-05-18 12:23:52'),
(71, 2, 2, '2026-05-18 13:23:52', '2026-05-18 13:24:28', 0, 709, 'completed', 'Completed via app', NULL, '2026-05-18 12:23:52', '2026-05-18 12:24:28'),
(72, 2, 3, '2026-05-18 13:24:50', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:24:50', '2026-05-18 12:24:50'),
(73, 2, 2, '2026-05-18 13:25:55', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:25:55', '2026-05-18 12:25:55'),
(74, 2, 3, '2026-05-18 13:26:26', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:26:26', '2026-05-18 12:26:26'),
(75, 2, 2, '2026-05-18 13:27:11', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:27:11', '2026-05-18 12:27:11'),
(76, 2, 3, '2026-05-18 13:27:17', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:27:17', '2026-05-18 12:27:17'),
(77, 2, 1, '2026-05-18 13:27:26', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:27:26', '2026-05-18 12:27:26'),
(78, 2, 4, '2026-05-18 13:27:31', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:27:31', '2026-05-18 12:27:31'),
(79, 2, 1, '2026-05-18 13:27:52', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:27:52', '2026-05-18 12:27:52'),
(80, 2, 4, '2026-05-18 13:28:25', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 0', NULL, '2026-05-18 12:28:25', '2026-05-18 12:28:44'),
(81, 2, 2, '2026-05-18 13:30:44', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 1', NULL, '2026-05-18 12:30:44', '2026-05-18 12:30:52'),
(82, 2, 3, '2026-05-18 13:31:11', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 0', NULL, '2026-05-18 12:31:11', '2026-05-18 12:31:13'),
(83, 2, 1, '2026-05-18 13:31:24', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 0', NULL, '2026-05-18 12:31:24', '2026-05-18 12:31:27'),
(84, 2, 1, '2026-05-18 13:32:19', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:32:19', '2026-05-18 12:32:19'),
(85, 2, 1, '2026-05-18 13:32:20', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:32:20', '2026-05-18 12:32:20'),
(86, 2, 1, '2026-05-18 13:33:19', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:33:19', '2026-05-18 12:33:19'),
(87, 2, 1, '2026-05-18 13:33:19', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 1', NULL, '2026-05-18 12:33:19', '2026-05-18 12:33:31'),
(88, 2, 1, '2026-05-18 13:34:23', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:34:23', '2026-05-18 12:34:23'),
(89, 2, 1, '2026-05-18 13:34:23', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 2', NULL, '2026-05-18 12:34:23', '2026-05-18 12:34:32'),
(90, 2, 4, '2026-05-18 13:34:53', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:34:53', '2026-05-18 12:34:53'),
(91, 2, 1, '2026-05-18 13:35:04', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:35:04', '2026-05-18 12:35:04'),
(92, 2, 1, '2026-05-18 13:35:41', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 12:35:41', '2026-05-18 12:35:41'),
(93, 2, 2, '2026-05-18 14:13:12', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 13:13:12', '2026-05-18 13:13:12'),
(94, 2, 2, '2026-05-18 15:36:55', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 14:36:55', '2026-05-18 14:36:55'),
(95, 2, 2, '2026-05-18 16:15:09', NULL, NULL, NULL, 'in_progress', 'Last completed exercise index: 1', NULL, '2026-05-18 15:15:09', '2026-05-18 15:15:39'),
(96, 2, 1, '2026-05-18 16:15:56', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 15:15:56', '2026-05-18 15:15:56'),
(97, 171, 3, '2026-05-18 17:52:59', NULL, NULL, NULL, 'in_progress', NULL, NULL, '2026-05-18 16:52:59', '2026-05-18 16:52:59'),
(98, 171, 3, '2026-05-18 18:00:59', '2026-05-18 18:01:06', 0, 759, 'completed', 'Completed via app', NULL, '2026-05-18 17:00:59', '2026-05-18 17:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `workouts`
--

DROP TABLE IF EXISTS `workouts`;
CREATE TABLE IF NOT EXISTS `workouts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `difficulty` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'intermediate',
  `duration_minutes` int NOT NULL DEFAULT '45',
  `calories_burned` int NOT NULL DEFAULT '300',
  `body_focus` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_needed` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `coach_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workouts_coach_id_foreign` (`coach_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workouts`
--

INSERT INTO `workouts` (`id`, `name`, `description`, `difficulty`, `duration_minutes`, `calories_burned`, `body_focus`, `equipment_needed`, `thumbnail`, `video_url`, `is_active`, `coach_id`, `created_at`, `updated_at`) VALUES
(1, 'Apex Strength', 'Elite Chest & Back training protocol.', 'advanced', 69, 507, 'Chest & Back', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(2, 'Hypertrophy Hour', 'Elite Full Body training protocol.', 'intermediate', 65, 709, 'Full Body', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(3, 'Lower Body Burn', 'Elite Legs training protocol.', 'intermediate', 58, 759, 'Legs', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(4, 'Neural Reset', 'Elite Shoulders & Core training protocol.', 'beginner', 81, 665, 'Shoulders & Core', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(5, 'Upper Body Blitz', 'Elite Arms & Chest training protocol.', 'intermediate', 83, 684, 'Arms & Chest', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(6, 'V-Taper Protocol', 'Elite Back & Shoulders training protocol.', 'advanced', 90, 675, 'Back & Shoulders', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(7, 'Core Stability', 'Elite Abs training protocol.', 'beginner', 75, 401, 'Abs', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(8, 'Iron Will', 'Elite Full Body Power training protocol.', 'advanced', 71, 543, 'Full Body Power', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19'),
(9, 'Quick Burn', 'Elite Cardio/Core training protocol.', 'beginner', 50, 627, 'Cardio/Core', NULL, 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=800', NULL, 1, 1, '2026-05-18 11:54:19', '2026-05-18 11:54:19');

-- --------------------------------------------------------

--
-- Table structure for table `workout_exercises`
--

DROP TABLE IF EXISTS `workout_exercises`;
CREATE TABLE IF NOT EXISTS `workout_exercises` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `workout_id` bigint UNSIGNED NOT NULL,
  `exercise_id` bigint UNSIGNED NOT NULL,
  `order` int NOT NULL DEFAULT '1',
  `sets` int NOT NULL DEFAULT '3',
  `reps` int DEFAULT NULL,
  `duration_seconds` int DEFAULT NULL,
  `rest_seconds` int NOT NULL DEFAULT '60',
  `notes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workout_exercises_workout_id_foreign` (`workout_id`),
  KEY `workout_exercises_exercise_id_foreign` (`exercise_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `workout_exercises`
--

INSERT INTO `workout_exercises` (`id`, `workout_id`, `exercise_id`, `order`, `sets`, `reps`, `duration_seconds`, `rest_seconds`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 3, 15, NULL, 60, NULL, NULL, NULL),
(2, 1, 2, 2, 4, 12, NULL, 60, NULL, NULL, NULL),
(3, 1, 6, 3, 5, 8, NULL, 60, NULL, NULL, NULL),
(4, 1, 7, 4, 5, 11, NULL, 60, NULL, NULL, NULL),
(5, 1, 8, 5, 4, 10, NULL, 60, NULL, NULL, NULL),
(6, 1, 11, 6, 5, 10, NULL, 60, NULL, NULL, NULL),
(7, 2, 1, 1, 5, 9, NULL, 60, NULL, NULL, NULL),
(8, 2, 11, 2, 4, 12, NULL, 60, NULL, NULL, NULL),
(9, 2, 16, 3, 5, 15, NULL, 60, NULL, NULL, NULL),
(10, 2, 21, 4, 3, 9, NULL, 60, NULL, NULL, NULL),
(11, 2, 26, 5, 3, 12, NULL, 60, NULL, NULL, NULL),
(12, 2, 10, 6, 5, 15, NULL, 60, NULL, NULL, NULL),
(13, 3, 11, 1, 5, 9, NULL, 60, NULL, NULL, NULL),
(14, 3, 12, 2, 5, 14, NULL, 60, NULL, NULL, NULL),
(15, 3, 13, 3, 5, 13, NULL, 60, NULL, NULL, NULL),
(16, 3, 14, 4, 4, 13, NULL, 60, NULL, NULL, NULL),
(17, 3, 15, 5, 4, 14, NULL, 60, NULL, NULL, NULL),
(18, 4, 16, 1, 5, 15, NULL, 60, NULL, NULL, NULL),
(19, 4, 17, 2, 5, 13, NULL, 60, NULL, NULL, NULL),
(20, 4, 18, 3, 3, 8, NULL, 60, NULL, NULL, NULL),
(21, 4, 26, 4, 3, 8, NULL, 60, NULL, NULL, NULL),
(22, 4, 27, 5, 5, 14, NULL, 60, NULL, NULL, NULL),
(23, 5, 1, 1, 4, 9, NULL, 60, NULL, NULL, NULL),
(24, 5, 3, 2, 4, 10, NULL, 60, NULL, NULL, NULL),
(25, 5, 21, 3, 4, 10, NULL, 60, NULL, NULL, NULL),
(26, 5, 22, 4, 3, 14, NULL, 60, NULL, NULL, NULL),
(27, 5, 23, 5, 4, 8, NULL, 60, NULL, NULL, NULL),
(28, 5, 24, 6, 3, 8, NULL, 60, NULL, NULL, NULL),
(29, 6, 7, 1, 5, 11, NULL, 60, NULL, NULL, NULL),
(30, 6, 8, 2, 3, 10, NULL, 60, NULL, NULL, NULL),
(31, 6, 9, 3, 3, 15, NULL, 60, NULL, NULL, NULL),
(32, 6, 16, 4, 5, 13, NULL, 60, NULL, NULL, NULL),
(33, 6, 19, 5, 3, 14, NULL, 60, NULL, NULL, NULL),
(34, 7, 26, 1, 3, 9, NULL, 60, NULL, NULL, NULL),
(35, 7, 27, 2, 4, 11, NULL, 60, NULL, NULL, NULL),
(36, 7, 28, 3, 5, 12, NULL, 60, NULL, NULL, NULL),
(37, 7, 29, 4, 4, 15, NULL, 60, NULL, NULL, NULL),
(38, 7, 30, 5, 4, 15, NULL, 60, NULL, NULL, NULL),
(39, 8, 6, 1, 3, 15, NULL, 60, NULL, NULL, NULL),
(40, 8, 11, 2, 3, 11, NULL, 60, NULL, NULL, NULL),
(41, 8, 16, 3, 3, 11, NULL, 60, NULL, NULL, NULL),
(42, 8, 21, 4, 5, 15, NULL, 60, NULL, NULL, NULL),
(43, 8, 1, 5, 5, 12, NULL, 60, NULL, NULL, NULL),
(44, 8, 7, 6, 3, 14, NULL, 60, NULL, NULL, NULL),
(45, 9, 30, 1, 5, 12, NULL, 60, NULL, NULL, NULL),
(46, 9, 29, 2, 3, 9, NULL, 60, NULL, NULL, NULL),
(47, 9, 4, 3, 4, 9, NULL, 60, NULL, NULL, NULL),
(48, 9, 14, 4, 5, 10, NULL, 60, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
