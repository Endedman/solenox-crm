-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Фев 04 2024 г., 12:12
-- Версия сервера: 10.11.4-MariaDB-1~deb12u1
-- Версия PHP: 8.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `snowbear_data`
--
CREATE DATABASE IF NOT EXISTS `test_snow_table` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `test_snow_table`;

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `developer` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `license` text NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `icon_url` varchar(256) NOT NULL DEFAULT '/static/img/png/directory_net_web-2.png',
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `categories`:
--   `parent_id`
--       `categories` -> `id`
--

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `developer`, `website`, `license`, `created_by`, `created_at`, `icon_url`, `parent_id`) VALUES
(1, 'Apps', 'museum-jars/admin', 'https://j2me.xyz', 'different', '12', '2023-08-10 09:59:41', '/static/img/png/executable_gear-0.png', 4),
(2, 'Games', 'museum-jars/admin', 'http://j2me.xyz', 'different', '12', '2023-08-10 10:00:15', '/static/img/png/game_mine_2-1.png', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `chat_messages`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 0,
  `message` text DEFAULT NULL,
  `parent_message_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `chat_messages`:
--   `user_id`
--       `users` -> `id`
--

--
-- Дамп данных таблицы `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `user_id`, `message`, `parent_message_id`, `timestamp`) VALUES
(1, 1, 'Welcome to J2ME.XYZ', NULL, '2023-08-10 12:46:59');

-- --------------------------------------------------------

--
-- Структура таблицы `file_versions`
--
-- Создание: Фев 01 2024 г., 17:52
-- Последнее обновление: Фев 03 2024 г., 15:14
--

DROP TABLE IF EXISTS `file_versions`;
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
  `filename_humanreadable` varchar(256) DEFAULT NULL,
  `icon_url` varchar(256) NOT NULL DEFAULT '/static/img/png/package-1.png',
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `passkey_blocked` varchar(256) DEFAULT NULL,
  `virus_protect` tinyint(1) DEFAULT 0,
  `author` varchar(64) NOT NULL DEFAULT 'j2me.xyz museum',
  `subcategory_id` int(11) DEFAULT NULL,
  `version` varchar(16) NOT NULL DEFAULT '1.0.0',
  `screen_resolution` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `file_versions`:
--   `category_id`
--       `categories` -> `id`
--   `subcategory_id`
--       `subcategories` -> `id`
--

--
-- Дамп данных таблицы `file_versions`
--

INSERT INTO `file_versions` (`id`, `category_id`, `file_hash`, `filename`, `file_size`, `upload_date`, `uploaded_by`, `description`, `quality_mark`, `uniqueness_mark`, `interface_language`, `filename_humanreadable`, `icon_url`, `verified`, `passkey_blocked`, `virus_protect`, `author`, `subcategory_id`, `version`, `screen_resolution`) VALUES
(1, 1, '9cc7ceefd26cf8d716da21d23a92db4b', '9cc7ceefd26cf8d716da21d23a92db4b_latest_1.jar', 321083, '2023-08-10 10:03:55', '12', 'The most used browser in J2ME era', 1, 1, 'English', 'Opera mini', '/static/img/png/PROGM001.ICO', 1, '123', 1, 'j2me.xyz museum', 2, '8.0', NULL);
-- --------------------------------------------------------

--
-- Структура таблицы `languages`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL,
  `language` varchar(64) NOT NULL,
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `languages`:
--

--
-- Дамп данных таблицы `languages`
--

INSERT INTO `languages` (`id`, `language`, `parent_id`) VALUES
(1, 'Russian', 1),
(2, 'English', 1),
(3, 'Unassigned', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `listings`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `listings`;
CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `title` varchar(256) NOT NULL,
  `description` varchar(4096) NOT NULL,
  `file` varchar(256) NOT NULL,
  `author` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `listings`:
--

--
-- Дамп данных таблицы `listings`
--

INSERT INTO `listings` (`id`, `title`, `description`, `file`, `author`) VALUES
(1, 'Nokia C3-01', 'A Nokia C3-01 with touchscreen', '', 12);

-- --------------------------------------------------------

--
-- Структура таблицы `listings_screenshots`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `listings_screenshots`;
CREATE TABLE `listings_screenshots` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `screenshot_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `listings_screenshots`:
--   `listing_id`
--       `listings` -> `id`
--

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--
-- Создание: Фев 01 2024 г., 17:52
-- Последнее обновление: Фев 04 2024 г., 07:07
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `news`:
--   `author_id`
--       `users` -> `id`
--

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`id`, `author_id`, `title`, `text`, `date`) VALUES
(1, 1, 'We are full in process of creating awesome admin panel', 'This.Is.Very.Hard.Task. But we done many things: news adding, category creation, uploading files, API, user management system =)', '2023-08-09');
-- --------------------------------------------------------

--
-- Структура таблицы `rank_changes`
--
-- Создание: Фев 01 2024 г., 17:52
-- Последнее обновление: Фев 03 2024 г., 11:48
--

DROP TABLE IF EXISTS `rank_changes`;
CREATE TABLE `rank_changes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_type` enum('increase','decrease') NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `rank_changes`:
--   `user_id`
--       `users` -> `id`
--   `changed_by`
--       `users` -> `id`
--

--
-- Дамп данных таблицы `rank_changes`
--

INSERT INTO `rank_changes` (`id`, `user_id`, `changed_by`, `change_type`, `change_date`) VALUES
(1, 1, 1, 'increase', '2023-10-22 11:24:25');

-- --------------------------------------------------------

--
-- Структура таблицы `redir_links`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `redir_links`;
CREATE TABLE `redir_links` (
  `id` int(11) NOT NULL,
  `link` varchar(4096) NOT NULL,
  `name` varchar(4096) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `redir_links`:
--

--
-- Дамп данных таблицы `redir_links`
--

INSERT INTO `redir_links` (`id`, `link`, `name`, `date`) VALUES
(0, 'https://j2me.xyz', 'J2ME Main', '2023-10-21');

-- --------------------------------------------------------

--
-- Структура таблицы `screenshots`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `screenshots`;
CREATE TABLE `screenshots` (
  `id` int(11) NOT NULL,
  `app_id` int(11) DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `screenshots`:
--   `app_id`
--       `file_versions` -> `id`
--

--
-- Дамп данных таблицы `screenshots`
--

INSERT INTO `screenshots` (`id`, `app_id`, `file_url`) VALUES
(1, 1, 'thumb_s1_j2me.xyz276668db37fb1c790cc4775bc898f965_r62xx.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `screen_resolutions`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `screen_resolutions`;
CREATE TABLE `screen_resolutions` (
  `id` int(11) NOT NULL,
  `resolution` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `screen_resolutions`:
--

--
-- Дамп данных таблицы `screen_resolutions`
--

INSERT INTO `screen_resolutions` (`id`, `resolution`) VALUES
(1, '240*320-portrait'),
(2, '480*360-landscape');

-- --------------------------------------------------------

--
-- Структура таблицы `subcategory`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `subcategory`;
CREATE TABLE `subcategory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `subcategory`:
--   `category_id`
--       `categories` -> `id`
--

--
-- Дамп данных таблицы `subcategory`
--

INSERT INTO `subcategory` (`id`, `name`, `category_id`) VALUES
(1, 'Symbian Apps ', 1),
(2, 'J2ME Apps', 1),
(3, 'Symbian Games', 2),
(4, 'J2ME Games', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--
-- Создание: Фев 01 2024 г., 17:52
-- Последнее обновление: Фев 04 2024 г., 11:11
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `new_email` varchar(255) DEFAULT NULL,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `rank` varchar(50) DEFAULT '0',
  `role` int(11) NOT NULL DEFAULT 0,
  `token` varchar(64) DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT 0,
  `block_reason` text DEFAULT NULL,
  `avatar` varchar(1024) NOT NULL DEFAULT '/static/img/png/user_world-1.png',
  `balance` int(32) NOT NULL DEFAULT 0,
  `user_totp_secret` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `users`:
--

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `new_email`, `email_verification_token`, `rank`, `role`, `token`, `blocked`, `block_reason`, `avatar`, `balance`, `user_totp_secret`) VALUES
(0, 'Guest', '', '', NULL, NULL, '37', -1, '8f42daead5eaf29b0e7b0f9020a24ce81c44c882a3bbed7af7ef49f16f16d5dd', 0, NULL, '/static/img/png/user_world-1.png', 0, 'XXXXXXXXXXXXXXXX'),
(1, 'admin', '$2y$10$OV6LFP8a4wvT3hTBTE2ti.tkZxBOeMY5w79HRH9arqChO44pb8PKa', 'admin@example.com', 'admin@example.com', '03da0f526dd52e8f86cb4a8d1175d872', '0', 3, '9ddf45f7a8604d8f15f8d479b08cd92459ce44b14ff1435d550f540f9f5a58c8', 0, NULL, '/static/img/png/user_world-1.png', 669, 'XXXXXXXXXXXXXXXX'),
(2, 'tester', '$2y$10$yMv0TLhwRnyozTFfqUXn1eOIzbmNv4SCRVb1gfd3/Jt5rNu7J7qc6', 'tester@example.com', 'tester@example.com', '5a86d6c938cb46ed57963c91d90460a7', '1', 1, '5f1d7562aaab9439b9d1384bfccb6647152984230f714d3257a01f6f4464d5dd', 0, NULL, '/static/img/png/user_world-1.png', 0, 'XXXXXXXXXXXXXXXX');
-- --------------------------------------------------------

--
-- Структура таблицы `user_activity`
--
-- Создание: Фев 01 2024 г., 17:52
-- Последнее обновление: Фев 04 2024 г., 12:11
--

DROP TABLE IF EXISTS `user_activity`;
CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `action` varchar(256) NOT NULL,
  `timestamp` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `user_activity`:
--

--
-- Дамп данных таблицы `user_activity`
--

INSERT INTO `user_activity` (`id`, `ip`, `action`, `timestamp`) VALUES
(1, '162.158.238.23', 'User loaded applications', '2023-10-22');

-- --------------------------------------------------------

--
-- Структура таблицы `wishlist`
--
-- Создание: Фев 01 2024 г., 17:52
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `itemId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- ССЫЛКИ ТАБЛИЦЫ `wishlist`:
--   `userId`
--       `users` -> `id`
--   `itemId`
--       `listings` -> `id`
--

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Индексы таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `file_versions`
--
ALTER TABLE `file_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `listings_screenshots`
--
ALTER TABLE `listings_screenshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Индексы таблицы `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Индексы таблицы `rank_changes`
--
ALTER TABLE `rank_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Индексы таблицы `redir_links`
--
ALTER TABLE `redir_links`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `screenshots`
--
ALTER TABLE `screenshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `app_id` (`app_id`);

--
-- Индексы таблицы `screen_resolutions`
--
ALTER TABLE `screen_resolutions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `subcategory`
--
ALTER TABLE `subcategory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_verification_token` (`email_verification_token`);

--
-- Индексы таблицы `user_activity`
--
ALTER TABLE `user_activity`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `itemId` (`itemId`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `file_versions`
--
ALTER TABLE `file_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `listings_screenshots`
--
ALTER TABLE `listings_screenshots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `rank_changes`
--
ALTER TABLE `rank_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `redir_links`
--
ALTER TABLE `redir_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `screenshots`
--
ALTER TABLE `screenshots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `screen_resolutions`
--
ALTER TABLE `screen_resolutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `subcategory`
--
ALTER TABLE `subcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `user_activity`
--
ALTER TABLE `user_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`);

--
-- Ограничения внешнего ключа таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `file_versions`
--
ALTER TABLE `file_versions`
  ADD CONSTRAINT `file_versions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `file_versions_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`);

--
-- Ограничения внешнего ключа таблицы `listings_screenshots`
--
ALTER TABLE `listings_screenshots`
  ADD CONSTRAINT `listings_screenshots_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`);

--
-- Ограничения внешнего ключа таблицы `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `rank_changes`
--
ALTER TABLE `rank_changes`
  ADD CONSTRAINT `rank_changes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `rank_changes_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `screenshots`
--
ALTER TABLE `screenshots`
  ADD CONSTRAINT `screenshots_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `file_versions` (`id`);

--
-- Ограничения внешнего ключа таблицы `subcategory`
--
ALTER TABLE `subcategory`
  ADD CONSTRAINT `subcategory_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Ограничения внешнего ключа таблицы `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`itemId`) REFERENCES `listings` (`id`);


--
-- Метаданные
--
USE `phpmyadmin`;

--
-- Метаданные для таблицы categories
--

--
-- Метаданные для таблицы chat_messages
--

--
-- Метаданные для таблицы file_versions
--

--
-- Метаданные для таблицы languages
--

--
-- Метаданные для таблицы listings
--

--
-- Метаданные для таблицы listings_screenshots
--

--
-- Метаданные для таблицы news
--

--
-- Метаданные для таблицы rank_changes
--

--
-- Метаданные для таблицы redir_links
--

--
-- Метаданные для таблицы screenshots
--

--
-- Метаданные для таблицы screen_resolutions
--

--
-- Метаданные для таблицы subcategory
--

--
-- Метаданные для таблицы users
--

--
-- Метаданные для таблицы user_activity
--

--
-- Метаданные для таблицы wishlist
--

--
-- Метаданные для базы данных snowbear_data
--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
