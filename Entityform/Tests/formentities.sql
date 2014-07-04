DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL
);
DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `content` varchar(255) NOT NULL,
  `user_id` int NOT NULL
);

INSERT INTO `user` (`id`, `name`) VALUES (1, 'bob');
INSERT INTO `user` (`id`, `name`) VALUES (2, 'joe');
INSERT INTO `comment` (`content`, `user_id`) VALUES ('hello', 1);
INSERT INTO `comment` (`content`, `user_id`) VALUES ('world', 2);