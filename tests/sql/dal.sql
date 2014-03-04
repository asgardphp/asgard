-- Adminer 3.7.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+08:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `author` (`id`, `name`) VALUES
(1,	'Bob'),
(2,	'Joe'),
(3,	'John');

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `category` (`id`, `title`, `description`) VALUES
(1,	'General',	'General news'),
(2,	'Misc',	'Other news');

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `news` (`id`, `title`, `content`, `category_id`, `author_id`, `score`) VALUES
(1,	'Welcome!',	'blabla',	1,	1,	2),
(2,	'1000th visitor!',	'blabla',	1,	2,	5),
(3,	'Important',	'blabla',	2,	1,	1);

DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  PRIMARY KEY (`id`,`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2014-03-01 15:32:22