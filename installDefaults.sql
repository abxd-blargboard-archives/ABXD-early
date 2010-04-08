INSERT INTO `categories` VALUES(1, 'Staff', 1);
INSERT INTO `categories` VALUES(2, 'General', 0);
INSERT INTO `categories` VALUES(3, 'Janitorial Services', 0);

INSERT INTO `forums` (`id`, `title`, `description`, `catid`, `minpower`) VALUES(1, 'Admin room', 'Staff discussion forum', 1, 1);
INSERT INTO `forums` (`id`, `title`, `description`, `catid`) VALUES(2, 'General chat', 'Talk about serious stuff', 2);
INSERT INTO `forums` (`id`, `title`, `description`, `catid`) VALUES(3, 'Off-Topic', 'Talk about other stuff', 2);
INSERT INTO `forums` (`id`, `title`, `description`, `catid`, `minpowerthread`, `minpowerreply`) VALUES(4, 'Trash', '[trash]Where deleted threads go', 3, 3, 3);
