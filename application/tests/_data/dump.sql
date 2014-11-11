-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Мар 11 2014 г., 16:39
-- Версия сервера: 5.6.14
-- Версия PHP: 5.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `dotplant2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `ApiService`
--

DROP TABLE IF EXISTS `ApiService`;
CREATE TABLE IF NOT EXISTS `ApiService` (
  `service_id` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `token_type` varchar(255) NOT NULL,
  `expires_in` int(11) NOT NULL,
  `create_ts` int(11) NOT NULL,
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `AuthAssignment`
--

DROP TABLE IF EXISTS `AuthAssignment`;
CREATE TABLE IF NOT EXISTS `AuthAssignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `biz_rule` varchar(255) DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `AuthAssignment`
--

INSERT INTO `AuthAssignment` (`item_name`, `user_id`, `biz_rule`, `data`) VALUES
('admin', '1', NULL, 'N;');

-- --------------------------------------------------------

--
-- Структура таблицы `AuthItem`
--

DROP TABLE IF EXISTS `AuthItem`;
CREATE TABLE IF NOT EXISTS `AuthItem` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `biz_rule` varchar(255) DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `AuthItem`
--

INSERT INTO `AuthItem` (`name`, `type`, `description`, `biz_rule`, `data`) VALUES
('admin', 2, 'I can do everything', NULL, 'N;');

-- --------------------------------------------------------

--
-- Структура таблицы `AuthItemChild`
--

DROP TABLE IF EXISTS `AuthItemChild`;
CREATE TABLE IF NOT EXISTS `AuthItemChild` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `Block`
--

DROP TABLE IF EXISTS `Block`;
CREATE TABLE IF NOT EXISTS `Block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `view_class` text,
  `view_config` text,
  `edit_class` text,
  `edit_config` text,
  `editor_icon_class` text,
  `config_view` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Дамп данных таблицы `Block`
--

INSERT INTO `Block` (`id`, `name`, `view_class`, `view_config`, `edit_class`, `edit_config`, `editor_icon_class`, `config_view`) VALUES
(1, 'Simple content', 'app\\blocks\\SimpleContent', '[]', 'app\\backend\\blocks\\SimpleContent', '[]', 'fa fa-align-justify', NULL),
(2, 'Row', 'app\\blocks\\Row', '[]', 'app\\backend\\blocks\\Row', '[]', 'nested-icon-row', NULL),
(3, 'Column', 'app\\blocks\\Column', '[]', 'app\\backend\\blocks\\Column', '[''css_class''=>''col-md-6'']', 'nested-icon-column', '@app/backend/blocks/views/column-config.php'),
(4, 'Video', 'app\\blocks\\Video', '[]', 'app\\backend\\blocks\\Video', '[]', 'glyphicon glyphicon-film', '@app/backend/blocks/views/video-config.php'),
(5, 'Nav List', 'app\\blocks\\NavList', '[]', 'app\\backend\\blocks\\NavList', '[]', 'fa fa-list', '@app/backend/blocks/views/navlist-config.php'),
(6, 'Nav Link', 'app\\blocks\\NavLink', '[]', 'app\\backend\\blocks\\NavLink', '[]', 'fa fa-list-alt', '@app/backend/blocks/views/navlink-config.php'),
(7, 'Accordion', 'app\\blocks\\Accordion', '[]', 'app\\backend\\blocks\\Accordion', '[]', 'fa fa-list', '@app/backend/blocks/views/accordion-config.php'),
(8, 'Accordion Block', 'app\\blocks\\AccordionBlock', '[]', 'app\\backend\\blocks\\AccordionBlock', '[]', 'fa fa-list-alt', '@app/backend/blocks/views/accordion-block-config.php');

-- --------------------------------------------------------

--
-- Структура таблицы `Layout`
--

DROP TABLE IF EXISTS `Layout`;
CREATE TABLE IF NOT EXISTS `Layout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `view` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `Layout`
--

INSERT INTO `Layout` (`id`, `name`, `view`) VALUES
(1, 'Default', '');

-- --------------------------------------------------------

--
-- Структура таблицы `LinkAnchors`
--

DROP TABLE IF EXISTS `LinkAnchors`;
CREATE TABLE IF NOT EXISTS `LinkAnchors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modelName` varchar(60) NOT NULL,
  `modelId` int(11) DEFAULT NULL,
  `anchor` text,
  `sort_order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `NestedContent`
--

DROP TABLE IF EXISTS `NestedContent`;
CREATE TABLE IF NOT EXISTS `NestedContent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `root` int(11) DEFAULT NULL,
  `block_id` int(11) NOT NULL,
  `config` text,
  `content` longtext,
  `cache` tinyint(1) DEFAULT '0',
  `cache_lifetime` int(11) DEFAULT '0',
  `noindex` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=72 ;

--
-- Дамп данных таблицы `NestedContent`
--

INSERT INTO `NestedContent` (`id`, `lft`, `rgt`, `level`, `root`, `block_id`, `config`, `content`, `cache`, `cache_lifetime`, `noindex`) VALUES
(1, 1, 12, 1, 1, 0, '[]', '', 0, 0, 0),
(67, 2, 11, 2, 1, 2, '[]', '', 0, 0, 0),
(68, 3, 6, 3, 1, 3, '{"css_class":"col-md-6"}', '', 0, 0, 0),
(69, 4, 5, 4, 1, 1, '[]', '<p>Welcome to DotPlant CMS v.2d</p>\n\n<p>asdasd</p>\n\n<p>asd</p>\n\n<p>asd</p>\n\n<p>asd</p>\n', 0, 0, 0),
(70, 7, 10, 3, 1, 3, '{"css_class":"col-md-6"}', '', 0, 0, 0),
(71, 8, 9, 4, 1, 1, '[]', '<p>Simple content</p>\n', 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `Notification`
--

DROP TABLE IF EXISTS `Notification`;
CREATE TABLE IF NOT EXISTS `Notification` (
  `task_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`task_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `NotifyMessage`
--

DROP TABLE IF EXISTS `NotifyMessage`;
CREATE TABLE IF NOT EXISTS `NotifyMessage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(11) unsigned NOT NULL,
  `result_status` enum('SUCCESS','FAULT') NOT NULL DEFAULT 'SUCCESS',
  `result` text,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `Page`
--

DROP TABLE IF EXISTS `Page`;
CREATE TABLE IF NOT EXISTS `Page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `slug_compiled` varchar(180) NOT NULL,
  `slug_absolute` tinyint(1) DEFAULT '0',
  `nestedContent_id` int(11) NOT NULL,
  `view_id` int(11) DEFAULT '1',
  `layout_id` int(11) DEFAULT '1',
  `published` tinyint(1) DEFAULT '1',
  `searchable` tinyint(1) DEFAULT '1',
  `robots` tinyint(4) DEFAULT '3',
  `title` text NOT NULL,
  `h1` text,
  `meta_description` text,
  `breadcrumbs_label` text,
  `announce` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expand_in_admin` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `slug_compiled` (`slug_compiled`,`published`),
  KEY `nestedSet` (`lft`,`rgt`,`level`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `Page`
--

INSERT INTO `Page` (`id`, `lft`, `rgt`, `level`, `slug`, `slug_compiled`, `slug_absolute`, `nestedContent_id`, `view_id`, `layout_id`, `published`, `searchable`, `robots`, `title`, `h1`, `meta_description`, `breadcrumbs_label`, `announce`, `date_added`, `date_modified`, `expand_in_admin`) VALUES
(1, 1, 2, 1, ':mainpage:', '', 0, 1, 1, 1, 1, 1, 3, 'Welcom111e to DotPlant CMS!111', 'Welcome to DotPlant CMS!11', 'asdas123123\n\n123123\nadasdsd12111\n\n\n\n', NULL, 'This is an announce!\nasdasd\n', '2014-02-07 13:03:09', '2014-03-03 11:15:27', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `seo_config`
--

DROP TABLE IF EXISTS `seo_config`;
CREATE TABLE IF NOT EXISTS `seo_config` (
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `seo_counter`
--

DROP TABLE IF EXISTS `seo_counter`;
CREATE TABLE IF NOT EXISTS `seo_counter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `code` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `seo_meta`
--

DROP TABLE IF EXISTS `seo_meta`;
CREATE TABLE IF NOT EXISTS `seo_meta` (
  `key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `seo_meta`
--

INSERT INTO `seo_meta` (`key`, `name`, `content`) VALUES
('test', 'test', 'adf');

-- --------------------------------------------------------

--
-- Структура таблицы `seo_redirect`
--

DROP TABLE IF EXISTS `seo_redirect`;
CREATE TABLE IF NOT EXISTS `seo_redirect` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('STATIC','PREG') NOT NULL DEFAULT 'STATIC',
  `from` varchar(255) NOT NULL,
  `to` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `Task`
--

DROP TABLE IF EXISTS `Task`;
CREATE TABLE IF NOT EXISTS `Task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `type` enum('EVENT','REPEAT') NOT NULL DEFAULT 'EVENT',
  `initiator` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `params` text,
  `init_event` varchar(255) DEFAULT NULL,
  `cron_expression` varchar(255) DEFAULT NULL,
  `status` enum('ACTIVE','STOPPED','RUNNING','FAILED','COMPLETED') NOT NULL DEFAULT 'ACTIVE',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `tbl_migration`
--

DROP TABLE IF EXISTS `tbl_migration`;
CREATE TABLE IF NOT EXISTS `tbl_migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tbl_migration`
--

INSERT INTO `tbl_migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1391778186),
('m130524_201442_init', 1391778187),
('m131121_080228_rbac', 1391778188),
('m131212_112648_base_content', 1391778189),
('m140114_091616_users', 1391778191),
('m140203_112755_tasks', 1393492283),
('m140210_103550_notification', 1393492284),
('m140214_045143_add_video_widget', 1393492284),
('m140217_052540_yandexwebmaster', 1393492284),
('m140218_080508_navblocks', 1393492284),
('m140219_095602_accordion', 1393492284),
('m140226_131257_seo_helper', 1393494877),
('m140303_093144_editable_textarea_selectize', 1393839434);

-- --------------------------------------------------------

--
-- Структура таблицы `User`
--

DROP TABLE IF EXISTS `User`;
CREATE TABLE IF NOT EXISTS `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `auth_key` varchar(32) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_reset_token` varchar(32) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '10',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `User`
--

INSERT INTO `User` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `create_time`, `update_time`, `avatar_url`, `first_name`, `last_name`) VALUES
(1, 'admin', 'Nr-xSwxefjy9Umkmc74xO1NNSbQ0UZ9m', '$2y$13$npI7CiPP3k5MsikjbYIAW..TyJ.vXyCIoDsYkFET/4/2aGbQp.FGC', NULL, 'example@example.com', 10, 1391778190, 1391778190, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `UserNotify`
--

DROP TABLE IF EXISTS `UserNotify`;
CREATE TABLE IF NOT EXISTS `UserNotify` (
  `user_id` int(11) unsigned NOT NULL,
  `message_id` int(11) unsigned NOT NULL,
  `isNew` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`,`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `UserService`
--

DROP TABLE IF EXISTS `UserService`;
CREATE TABLE IF NOT EXISTS `UserService` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `service_type` varchar(255) DEFAULT NULL,
  `service_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ix-UserService-service_type-service_id` (`service_type`,`service_id`),
  KEY `ix-UserService-user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `View`
--

DROP TABLE IF EXISTS `View`;
CREATE TABLE IF NOT EXISTS `View` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `view` text,
  `category` text,
  `internal_name` text,
  PRIMARY KEY (`id`),
  KEY `internal_name_category` (`internal_name`(80),`category`(40))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Дамп данных таблицы `View`
--

INSERT INTO `View` (`id`, `name`, `view`, `category`, `internal_name`) VALUES
(1, 'Default', '', '', 'default'),
(2, 'Default editable view', '@app/backend/components/views/default.php', 'Editable.view', 'default'),
(3, 'Default editable edit(text input)', '@app/backend/components/views/editable/default.php', 'Editable.edit', 'default'),
(4, 'Default editable view', '@app/backend/components/views/nested-content.php', 'Editable.view', 'nested-content'),
(5, 'Default editable edit(text input)', '@app/backend/components/views/editable/nested-content.php', 'Editable.edit', 'nested-content'),
(9, 'Textarea editable view', '@app/backend/components/views/nl2br.php', 'Editable.view', 'nl2br'),
(10, 'Textarea editable edit', '@app/backend/components/views/editable/textarea.php', 'Editable.edit', 'textarea'),
(11, 'Comma separated editable view', '@app/backend/components/views/comma_separated.php', 'Editable.view', 'comma_separated'),
(12, 'Selectize editable edit', '@app/backend/components/views/editable/selectize.php', 'Editable.edit', 'selectize');

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `AuthAssignment`
--
ALTER TABLE `AuthAssignment`
  ADD CONSTRAINT `item_name` FOREIGN KEY (`item_name`) REFERENCES `AuthItem` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `AuthItemChild`
--
ALTER TABLE `AuthItemChild`
  ADD CONSTRAINT `child` FOREIGN KEY (`child`) REFERENCES `AuthItem` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `parent` FOREIGN KEY (`parent`) REFERENCES `AuthItem` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;
