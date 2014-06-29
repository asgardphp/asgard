DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `news_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `news_translation`;
CREATE TABLE `news_translation` (
  `id` int(11) NOT NULL,
  `locale` varchar(10) NOT NULL,
  `test` text NOT NULL,
  PRIMARY KEY (`id`,`locale`)
);

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `news_comment`;
CREATE TABLE `news_comment` (
  `news_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL
);

INSERT INTO `news` (`id`, `title`) VALUES
(2, 'news!');

INSERT INTO `comment` (`id`, `title`, `created_at`, `updated_at`, `news_id`) VALUES
(2, 'comment', '2012-01-00 00:00:00', '2012-07-09 20:24:09', 2);

INSERT INTO `news_comment` (`news_id`, `comment_id`) VALUES
(2, 2);

INSERT INTO `news_translation` (`id`, `locale`, `test`) VALUES
(2, 'en', 'Hello'),
(2, 'fr', 'Bonjour');