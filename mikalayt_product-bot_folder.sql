-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Сен 17 2023 г., 22:01
-- Версия сервера: 5.7.42-cll-lve
-- Версия PHP: 8.1.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `mikalayt_product-bot_folder`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bot_group_adts`
--

CREATE TABLE `bot_group_adts` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `from_user_id` bigint(20) NOT NULL,
  `from_user_name` varchar(55) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `private_sessions`
--

CREATE TABLE `private_sessions` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(55) NOT NULL,
  `screen_name` varchar(55) NOT NULL,
  `screen_messages_id` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `private_sessions`
--

INSERT INTO `private_sessions` (`id`, `user_id`, `user_name`, `screen_name`, `screen_messages_id`, `created_at`) VALUES
(10, 5502265307, 'СК Таэквон-до \"АЛЬЯНС\"', '/start', '[195]', '2023-08-21 19:17:29'),
(16, 755620054, 'Кэт', '/start', '[220]', '2023-08-22 12:40:20'),
(36, 242332872, 'Nastassia', '/start', '[362]', '2023-08-26 21:54:47'),
(75, 1284581873, 'Татьяна', '/start', '[614]', '2023-09-03 12:56:06'),
(123, 871220052, 'Aleksey_1245', '/start', '[1177]', '2023-09-09 12:52:34'),
(149, 1085824711, 'Elins', 'pay-conf-start', '[1396]', '2023-09-14 12:13:59'),
(155, 591031694, 'Nika', '/start', '[1420]', '2023-09-16 11:00:02'),
(160, 439967664, 'Татьяна', '/start', '[1456]', '2023-09-17 06:43:52'),
(161, 610040425, 'Екатерина', '/start', '[1442]', '2023-09-17 14:39:04'),
(162, 497781590, 'Анастасия', '/start', '[1458]', '2023-09-17 18:49:31');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(55) NOT NULL,
  `date_pay` varchar(55) NOT NULL,
  `temp_access` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `user_id`, `user_name`, `date_pay`, `temp_access`, `created_at`) VALUES
(9, 439967664, 'Татьяна', '2023-09-21 12:28:56', 0, '2023-08-21 09:27:45'),
(329, 2023331827, 'Annet', '2023-09-25 22:25:53', 0, '2023-08-25 19:02:13'),
(406, 1118703553, 'Kozlovskaya', '2023-09-27 18:22:04', 0, '2023-08-27 15:09:55'),
(445, 1295636303, 'I', '2023-09-28 16:35:35', 0, '2023-08-28 12:24:31'),
(470, 903642282, 'liberty', '2023-09-29 08:40:08', 0, '2023-08-29 04:26:27'),
(547, 841866863, 'Ксения', '2023-09-30 11:02:16', 0, '2023-08-30 07:45:49'),
(816, 1300906182, 'Дмитрий', '2023-10-04 21:37:05', 0, '2023-09-04 14:25:06'),
(867, 965245602, 'Шпилевская Екатерина', '2023-10-05 14:25:42', 0, '2023-09-05 11:24:39'),
(870, 1964172492, 'Татьяна @nogtiminsk333', '2023-10-05 14:50:20', 0, '2023-09-05 11:45:42'),
(872, 5184918683, '@#₽', '2023-10-05 14:50:41', 0, '2023-09-05 11:48:01'),
(882, 976966274, 'Анастасия', '2023-10-05 19:14:29', 0, '2023-09-05 16:11:24'),
(884, 396363584, 'Алла', '2023-10-05 19:34:32', 0, '2023-09-05 16:28:55'),
(1007, 795196099, 'Наталья', '2023-11-06 15:25:35', 0, '2023-09-07 12:22:36'),
(1130, 5121302209, '', '2023-10-09 09:44:45', 0, '2023-09-09 06:32:44'),
(1151, 716531793, 'Александра', '2023-10-09 11:57:32', 0, '2023-09-09 08:49:26'),
(1259, 530434721, 'Ден', '2023-10-11 11:56:51', 0, '2023-09-11 08:37:05'),
(1663, 480123657, 'Катя', '2023-10-16 18:56:09', 0, '2023-09-16 15:48:11'),
(1673, 868533552, 'Anastasia', '', 0, '2023-09-17 06:41:39'),
(1674, 6621698136, 'Маргарита', '', 0, '2023-09-17 08:19:15'),
(1675, 1942091044, 'Karabas Barabas', '', 0, '2023-09-17 08:25:16'),
(1676, 6093745222, 'Ivanka Semko', '', 0, '2023-09-17 08:32:03'),
(1677, 5178304595, 'Alla', '', 0, '2023-09-17 08:58:06'),
(1678, 6228421483, 'Раиса', '', 0, '2023-09-17 08:59:54'),
(1679, 6319116597, 'Виктор', '', 0, '2023-09-17 09:02:02'),
(1680, 6053554719, 'Джорджия', '', 0, '2023-09-17 09:05:33'),
(1681, 5651420856, 'Ира', '', 0, '2023-09-17 09:08:17'),
(1682, 6563664854, 'Владимир', '', 0, '2023-09-17 09:34:20'),
(1683, 1778047940, 'Дмитрий', '', 0, '2023-09-17 09:44:00'),
(1684, 5692789422, 'Stas', '', 0, '2023-09-17 09:51:58'),
(1685, 5879769419, 'Sheri', '', 0, '2023-09-17 10:09:24'),
(1686, 6173680931, 'Lavazza', '', 0, '2023-09-17 10:56:40'),
(1687, 0, '0', '', 0, '2023-09-17 11:22:43'),
(1688, 6622700148, 'Saveliy', '', 0, '2023-09-17 11:40:42'),
(1689, 6251069135, '', '', 0, '2023-09-17 13:59:37'),
(1690, 6658331829, 'Пристайко Елеонора', '', 0, '2023-09-17 14:13:30'),
(1691, 6610456348, 'Виктор', '', 0, '2023-09-17 14:33:21'),
(1692, 610040425, 'Екатерина', '', 0, '2023-09-17 14:38:49'),
(1693, 6408118548, 'Каролина', '', 0, '2023-09-17 14:39:19'),
(1694, 6438681389, 'Виталий', '', 0, '2023-09-17 17:25:09'),
(1695, 6436305196, 'Виталий', '', 0, '2023-09-17 17:27:24'),
(1696, 497781590, 'Анастасия', '2023-10-17 21:54:45', 0, '2023-09-17 18:49:15');

-- --------------------------------------------------------

--
-- Структура таблицы `users_for_admin`
--

CREATE TABLE `users_for_admin` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(55) NOT NULL,
  `user_link` varchar(55) NOT NULL,
  `pay_message` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users_for_admin`
--

INSERT INTO `users_for_admin` (`id`, `user_id`, `user_name`, `user_link`, `pay_message`) VALUES
(22, 302911626, 'Olga Loskutova', '@olga29086', '[\"sendPhoto\",{\"photo\":\"AgACAgIAAxkBAAIFgGUDfHVVAfUrakkyW5Ivv4UkxvWBAALbzjEb8l8YSG6yJEfbXpmxAQADAgADcwADMAQ\"}]');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `bot_group_adts`
--
ALTER TABLE `bot_group_adts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `private_sessions`
--
ALTER TABLE `private_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users_for_admin`
--
ALTER TABLE `users_for_admin`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `bot_group_adts`
--
ALTER TABLE `bot_group_adts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2996;

--
-- AUTO_INCREMENT для таблицы `private_sessions`
--
ALTER TABLE `private_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1697;

--
-- AUTO_INCREMENT для таблицы `users_for_admin`
--
ALTER TABLE `users_for_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
