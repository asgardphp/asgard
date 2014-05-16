DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` tinyint NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(255) NOT NULL
);
INSERT INTO `news` (`id`, `title`) VALUES ('1', 'The first news!');