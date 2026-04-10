-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Фев 27 2026 г., 13:01
-- Версия сервера: 8.0.19
-- Версия PHP: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `blackwhite_detailing`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `service_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `car_model` varchar(100) NOT NULL,
  `car_year` year DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `notes` text,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_id`, `booking_date`, `booking_time`, `car_model`, `car_year`, `phone`, `notes`, `status`, `created_at`) VALUES
(2, 3, 1, '2026-02-20', '11:30:00', 'BMWx5', 2008, '+7 (952) 620-16-66', 'красный', 'cancelled', '2026-02-03 09:49:58');

-- --------------------------------------------------------

--
-- Структура таблицы `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int NOT NULL,
  `ticket_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `ticket_id`, `user_id`, `message`, `is_admin`, `created_at`) VALUES
(1, 5, 3, 'вы черти испортили мою машину эта моя машина номера мои', 0, '2026-02-27 07:55:22'),
(2, 5, 4, 'за базаром следи это моя машина петушара', 1, '2026-02-27 07:56:35');

-- --------------------------------------------------------

--
-- Структура таблицы `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `services`
--

INSERT INTO `services` (`id`, `title`, `description`, `image`, `created_at`) VALUES
(1, 'Полировка кузова', 'Профессиональная полировка кузова до зеркального блеска с использованием премиальных материалов и оборудования.', 'polishing.jpg', '2026-02-03 09:37:43'),
(2, 'Химчистка салона', 'Глубокая химчистка салона автомобиля с удалением всех видов загрязнений и запахов.', 'cleaning.jpg', '2026-02-03 09:37:43'),
(3, 'Покрытие керамикой', 'Нанесение керамического покрытия для защиты лакокрасочного покрытия и придания эффекта мокрого асфальта.', 'ceramic.jpg', '2026-02-03 09:37:43'),
(4, 'Оклейка плёнкой', 'Бронирование кузова защитной плёнкой PPF, защита от сколов и мелких повреждений.', 'film.jpg', '2026-02-03 09:37:43'),
(5, 'Детейлинг химчистка', 'Комплексная детейлинг химчистка с кондиционированием кожи и пластика.', 'detailing.jpg', '2026-02-03 09:37:43'),
(6, 'Защитное покрытие', 'Нанесение нанокерамики для долговременной защиты от UV-лучей, кислот и агрессивной среды.', 'protection.jpg', '2026-02-03 09:37:43');

-- --------------------------------------------------------

--
-- Структура таблицы `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `subject` varchar(200) NOT NULL,
  `status` enum('open','in_progress','closed') DEFAULT 'open',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `status`, `priority`, `created_at`) VALUES
(5, 3, 'жалоба', 'in_progress', 'medium', '2026-02-27 07:55:22');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_admin`, `created_at`) VALUES
(2, 'user1', 'user1@test.ru', '$2y$10$6YlZ8QqJzQ1WqK9vJjKJUuJ8JkL9M0N1O2P3Q4R5S6T7U8V9W0X1Y2Z3', 0, '2026-02-03 09:37:43'),
(3, 'ruslan', 'ruslanilverov2004@gmail.com', '$2y$10$r5Hns1bK/4SLt2pV3gWePOfDwdMckCv9lmM7FqOVI5nbIpfW6uN1e', 0, '2026-02-03 09:44:14'),
(4, 'admin', 'admin@gmail.com', '$2y$10$lIJinSb1gbG6vMLtQk2n1OaZ.FdXuIaXDUVy4qlovCHIikxrSLI8m', 1, '2026-02-03 09:50:41');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_user` (`user_id`),
  ADD KEY `idx_bookings_service` (`service_id`),
  ADD KEY `idx_bookings_date` (`booking_date`);

--
-- Индексы таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_ticket_id` (`ticket_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Индексы таблицы `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
