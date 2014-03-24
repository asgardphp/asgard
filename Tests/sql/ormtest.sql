DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` tinyint NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
);
INSERT INTO `category` (`id`, `title`, `description`) VALUES ('1', 'General', 'General news');
INSERT INTO `category` (`id`, `title`, `description`) VALUES ('2', 'Misc', 'Other news');

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` tinyint NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `score` int(11) NOT NULL
);
INSERT INTO `news` (`id`, `title`, `content`, `category_id`, `author_id`, `score`) VALUES ('1', 'Welcome!', 'blabla', 1, 1, 2);
INSERT INTO `news` (`id`, `title`, `content`, `category_id`, `author_id`, `score`) VALUES ('2', '1000th visitor!', 'blabla', 1, 2, 5);
INSERT INTO `news` (`id`, `title`, `content`, `category_id`, `author_id`, `score`) VALUES ('3', 'Important', 'blabla', 2, 1, 1);

DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `id` tinyint NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL
);
INSERT INTO `author` (`id`, `name`) VALUES ('1', 'Bob');
INSERT INTO `author` (`id`, `name`) VALUES ('2', 'Joe');
INSERT INTO `author` (`id`, `name`) VALUES ('3', 'John');