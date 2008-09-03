# CocoaMySQL dump
# Version 0.7b5
# http://cocoamysql.sourceforge.net
#
# Host: localhost (MySQL 5.1.23-rc)
# Database: fredistrano12
# Generation Time: 2008-08-19 00:20:50 +0200
# ************************************************************

# Dump of table acos
# ------------------------------------------------------------

CREATE TABLE `acos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) NOT NULL DEFAULT '',
  `foreign_key` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `lft` int(10) unsigned DEFAULT NULL,
  `rght` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('1',NULL,'ControlObject','1','all','1','18');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('2','1','ControlObject','2','public','2','3');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('3','1','ControlObject','3','administration','4','9');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('4','3','ControlObject','4','authorizations','5','6');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('5','3','ControlObject','5','configuration','7','8');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('6','1','ControlObject','6','gates','10','15');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('7','6','ControlObject','7','entrance','11','12');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('8','1','ControlObject','8','buinessData','16','17');
INSERT INTO `acos` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('9','6','ControlObject','9','password','13','14');


# Dump of table aros
# ------------------------------------------------------------

CREATE TABLE `aros` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) DEFAULT NULL,
  `model` varchar(255) NOT NULL DEFAULT '',
  `foreign_key` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `lft` int(10) unsigned DEFAULT NULL,
  `rght` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('1',NULL,'Group','1','group.all','1','16');
INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('2','1','Group','2','group.anonymous','2','7');
INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('3','1','Group','3','group.member','8','15');
INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('4','3','Group','4','group.regular','9','10');
INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('5','3','Group','5','group.premium','11','12');
INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('6','3','Group','6','group.admin','13','14');
INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('7','2','Group','7','group.other','3','6');
INSERT INTO `aros` (`id`,`parent_id`,`model`,`foreign_key`,`alias`,`lft`,`rght`) VALUES ('8','7','Group','8','group.currentUser','4','5');


# Dump of table aros_acos
# ------------------------------------------------------------

CREATE TABLE `aros_acos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `aro_id` int(10) unsigned NOT NULL,
  `aco_id` int(10) unsigned NOT NULL,
  `_create` char(2) NOT NULL DEFAULT '0',
  `_read` char(2) NOT NULL DEFAULT '0',
  `_update` char(2) NOT NULL DEFAULT '0',
  `_delete` char(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

INSERT INTO `aros_acos` (`id`,`aro_id`,`aco_id`,`_create`,`_read`,`_update`,`_delete`) VALUES ('3','3','7','1','1','1','1');
INSERT INTO `aros_acos` (`id`,`aro_id`,`aco_id`,`_create`,`_read`,`_update`,`_delete`) VALUES ('4','1','2','1','1','1','1');
INSERT INTO `aros_acos` (`id`,`aro_id`,`aco_id`,`_create`,`_read`,`_update`,`_delete`) VALUES ('23','6','1','1','1','1','1');
INSERT INTO `aros_acos` (`id`,`aro_id`,`aco_id`,`_create`,`_read`,`_update`,`_delete`) VALUES ('24','8','9','1','1','1','1');
INSERT INTO `aros_acos` (`id`,`aro_id`,`aco_id`,`_create`,`_read`,`_update`,`_delete`) VALUES ('25','5','8','1','1','1','1');


# Dump of table control_objects
# ------------------------------------------------------------

CREATE TABLE `control_objects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('1','all','2007-07-23 19:18:23','2007-07-23 19:18:23',NULL);
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('2','public','2007-07-23 19:18:23','2007-07-23 19:18:23','1');
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('3','administration','2007-07-23 19:18:23','2007-07-23 19:18:23','1');
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('4','authorizations','2007-07-23 19:18:23','2007-07-23 19:18:23','3');
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('5','configuration','2007-07-23 19:18:23','2007-07-23 19:18:23','3');
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('6','gates','2007-07-23 19:18:23','2007-07-23 19:18:23','1');
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('7','entrance','2007-07-23 19:18:23','2007-07-23 19:18:23','6');
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('8','buinessData','2007-07-23 19:18:23','2007-07-23 19:18:23','1');
INSERT INTO `control_objects` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('9','password','2008-08-15 16:21:02','2008-08-15 16:21:02','6');


# Dump of table deployment_logs
# ------------------------------------------------------------

CREATE TABLE `deployment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `uuid` varchar(50) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `created` datetime NOT NULL,
  `comment` text,
  `archive` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table groups
# ------------------------------------------------------------

CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `parent_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('1','all','2007-07-23 19:18:23','2007-07-23 19:18:23',NULL);
INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('2','anonymous','2007-07-23 19:18:23','2007-07-23 19:18:23','1');
INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('3','member','2007-07-23 19:18:23','2007-07-23 19:18:23','1');
INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('4','regular','2007-07-23 19:18:23','2007-07-23 19:18:23','3');
INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('5','premium','2007-07-23 19:18:23','2007-07-23 19:18:23','3');
INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('6','admin','2007-07-23 19:18:23','2007-07-23 19:18:23','3');
INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('7','other','2008-08-15 16:09:37','2008-08-15 16:09:37','2');
INSERT INTO `groups` (`id`,`name`,`created`,`modified`,`parent_id`) VALUES ('8','currentUser','2008-08-15 16:09:52','2008-08-15 16:09:52','7');


# Dump of table groups_users
# ------------------------------------------------------------

CREATE TABLE `groups_users` (
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `groups_users` (`user_id`,`group_id`) VALUES ('1','6');


# Dump of table profiles
# ------------------------------------------------------------

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lang` char(2) DEFAULT NULL,
  `rss_token` char(40) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


/*
	FIXME F: insert profiles for admin account 
*/

# Dump of table projects
# ------------------------------------------------------------

CREATE TABLE `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `svn_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prd_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prd_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log_path` text COLLATE utf8_unicode_ci,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `projects` (`id`,`name`,`svn_url`,`prd_url`,`prd_path`,`log_path`,`created`,`modified`) VALUES ('1','Fredistrano_example','http://fredistrano.googlecode.com/svn/trunk','http://localhost/testFredistrano/fredistrano/','/Users/fred/Sites/testFredistrano/fredistrano/','/Users/fred/Sites/testFredistrano/fredistrano/app/tmp/logs/debug.log\r\n/Users/fred/Sites/testFredistrano/fredistrano/app/tmp/logs/error.log','2007-10-02 19:04:09','2008-08-19 00:24:53');


# Dump of table users
# ------------------------------------------------------------

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(32) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`,`login`,`password`,`first_name`,`last_name`,`email`,`created`,`modified`) VALUES ('1','admin','1a1dc91c907325c69271ddf0c944bc72','test',NULL,NULL,'2007-07-23 19:17:34','2008-08-15 15:08:32');