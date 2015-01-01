CREATE TABLE `phpGridServerUserMap` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `binding` char(36) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

INSERT INTO `AuthenticationTypes` VALUES (6,'phpgridserver','phpGridServer',0,0,0);
