SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `developer` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `license` text NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categories` (`id`, `name`, `developer`, `website`, `license`, `created_by`, `created_at`) VALUES
(4, 'Apps', 'museum-jars/admin', 'https://j2me.xyz', 'different', '12', '2023-08-10 09:59:41'),
(5, 'Games', 'museum-jars/admin', 'http://j2me.xyz', 'different', '12', '2023-08-10 10:00:15'),
(6, 'Videos', 'museum-jars/admin', 'http://j2me.xyz', 'different', '12', '2023-08-10 10:00:45'),
(7, 'Manuals', 'museum-jars/admin', 'http://j2me.xyz', 'different', '12', '2023-08-10 10:01:13'),
(13, 'Solenox', 'Solenox', '/', 'GPLv3', '12', '2023-08-11 14:41:09');

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 0,
  `message` text DEFAULT NULL,
  `parent_message_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `parent_message_id`, `timestamp`) VALUES
(54, 12, 'Welcome to J2ME.XYZ', NULL, '2023-08-10 12:46:59'),
(57, 13, 'i am tester!', NULL, '2023-08-11 12:39:29'),
(59, 0, 'Hi', NULL, '2023-08-11 15:04:29'),
(60, 0, '@admin hello', NULL, '2023-08-11 16:57:44'),
(61, 0, 'Duck', NULL, '2023-08-11 16:57:57'),
(62, 12, '@guest hi!', NULL, '2023-08-11 19:35:39');

CREATE TABLE `file_versions` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `file_hash` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploaded_by` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quality_mark` tinyint(1) DEFAULT NULL,
  `uniqueness_mark` tinyint(1) DEFAULT NULL,
  `interface_language` varchar(50) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `filename_humanreadable` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `file_versions` (`id`, `category_id`, `file_hash`, `filename`, `file_size`, `upload_date`, `uploaded_by`, `description`, `quality_mark`, `uniqueness_mark`, `interface_language`, `subcategory_id`, `filename_humanreadable`) VALUES
(6, 4, '9cc7ceefd26cf8d716da21d23a92db4b', '9cc7ceefd26cf8d716da21d23a92db4b_latest_1.jar', 321083, '2023-08-10 10:03:55', '12', 'The most used browser in J2ME era', 1, 1, 'English', NULL, 'Opera mini'),
(7, 4, '18a4d2aec06a30b14dde677cdcb022e1', '18a4d2aec06a30b14dde677cdcb022e1_JTube.jar', 154652, '2023-08-10 10:04:55', '12', 'YouTube client based on Invidious Proxy', 1, 1, 'English', NULL, 'JTube'),
(8, 4, '0baf399590dc49aed9be1f16d1551ad6', '0baf399590dc49aed9be1f16d1551ad6_latest_2.jar', 477294, '2023-08-11 10:54:21', '12', 'Aclient for VK social network', 1, 1, 'English', NULL, 'VK4ME'),
(9, 4, '1d6ba5d63f459a2ad855cba37390c029', '1d6ba5d63f459a2ad855cba37390c029_UCBrowser_V9.4_SPEED_MOD-world18.spcs.bio.jar', 468682, '2023-08-11 10:56:19', '12', 'yet another browser', 1, 0, 'English', NULL, 'UC Browser'),
(10, 13, 'b08863d08efa16cae5489b66174acf7c', 'b08863d08efa16cae5489b66174acf7c_SolenoxLauncher.exe', 9071397, '2023-08-11 14:42:08', '12', '-', 1, 1, 'rus', NULL, 'Solenox Launcher');

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `news` (`id`, `author_id`, `title`, `text`, `date`) VALUES
(1, 12, 'Test News Title', 'This is a test news article.', '2023-08-09'),
(3, 12, 'We are full in process of creating awesome admin panel', 'This.Is.Very.Hard.Task. But we done many things: news adding, category creation, uploading files, API, user management system =)', '2023-08-09'),
(5, 12, 'File system working properly!', 'You can use ierarchy system for searching files. The search engine will be presented soon.', '2023-08-10'),
(6, 12, 'And, rank counting', '... is ready! (should be)', '2023-08-10');

CREATE TABLE `rank_changes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_type` enum('increase','decrease') NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `subcategories` (`id`, `category_id`, `name`) VALUES
(1, 6, 'Manuals');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `new_email` varchar(255) DEFAULT NULL,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `rank` varchar(50) DEFAULT '0',
  `role` int(11) NOT NULL DEFAULT 0,
  `token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `username`, `password`, `email`, `new_email`, `email_verification_token`, `rank`, `role`, `token`) VALUES
(0, 'Guest', '', '', NULL, NULL, '5', -1, 'b30bebecca076b88dc1c173a40b86c904039eda2c3d61b9a8863a9b267f02b61'),
(12, 'admin', '$2y$10$YnUz.dauaUFNHvv4bQn4ZeTdVVQbCaOiyvZ.SrDI0.PRymBugyvuW', 'admin@example.com', 'endedman@gmail.com', 'faddef14c80d9d391abcc195692b1d36', '39', 3, '4fe70349fb91733c39af0bd76bf1a9fde895d069434d81f6d59f76b05a03e8b0'),
(13, 'tester', '$2y$10$yMv0TLhwRnyozTFfqUXn1eOIzbmNv4SCRVb1gfd3/Jt5rNu7J7qc6', 'tester@example.com', 'dd@f', '5a86d6c938cb46ed57963c91d90460a7', '1', 1, 'c1711da21b65776b65775137bef739c981185f7bee3ed20cbca0640e0b051684');

ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `file_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

ALTER TABLE `rank_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `changed_by` (`changed_by`);

ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_verification_token` (`email_verification_token`);


ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

ALTER TABLE `file_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `rank_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;


ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `file_versions`
  ADD CONSTRAINT `file_versions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

ALTER TABLE `rank_changes`
  ADD CONSTRAINT `rank_changes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rank_changes_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
