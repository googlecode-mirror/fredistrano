-- phpMyAdmin SQL Dump
-- version 2.9.1.1-Debian-2ubuntu1
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Vendredi 01 Juin 2007 à 22:57
-- Version du serveur: 5.0.38
-- Version de PHP: 5.2.1
-- 
-- Base de données: `Fredistrano`
-- 

-- --------------------------------------------------------


CREATE TABLE `deployment_logs` (
  `id` int(11) NOT NULL auto_increment,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(25) default NULL,
  `created` datetime NOT NULL,
  `comment` text,
  `archive` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Structure de la table `projects`
-- 

CREATE TABLE `projects` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) collate utf8_unicode_ci NOT NULL,
  `svn_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `prd_url` varchar(255) collate utf8_unicode_ci NOT NULL,
  `prd_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created` timestamp NULL default NULL,
  `modified` timestamp NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;


-- 
-- Contenu de la table `projects`
-- 

INSERT INTO `projects` (`id`, `name`, `svn_url`, `prd_url`, `prd_path`, `created`, `modified`) VALUES 
(1, 'Fredistrano', 'http://svn.fbollon.net/Fredistrano/trunk', 'http://localhost/test/Fredistrano', '/var/www/test1/Fredistrano', '2007-10-02 19:04:09', '2007-10-02 23:03:10');

-- --------------------------------------------------------

-- 
-- Structure de la table `control_objects`
-- 

CREATE TABLE `control_objects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `parent_id` int(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `control_objects`
-- 

INSERT INTO `control_objects` (`id`, `name`, `created`, `modified`, `parent_id`) VALUES (1, 'all', '2007-07-23 19:18:23', '2007-07-23 19:18:23', NULL),
(2, 'public', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 1),
(3, 'administration', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 1),
(4, 'authorizations', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 3),
(5, 'configuration', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 3),
(6, 'gates', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 1),
(7, 'entrance', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 6),
(8, 'buinessData', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 1);


-- --------------------------------------------------------

-- 
-- Structure de la table `groups`
-- 

CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `parent_id` int(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `groups`
-- 

INSERT INTO `groups` (`id`, `name`, `created`, `modified`, `parent_id`) VALUES (1, 'all', '2007-07-23 19:18:23', '2007-07-23 19:18:23', NULL),
(2, 'anonymous', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 1),
(3, 'member', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 1),
(4, 'regular', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 3),
(5, 'premium', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 3),
(6, 'admin', '2007-07-23 19:18:23', '2007-07-23 19:18:23', 3);


-- --------------------------------------------------------

-- 
-- Structure de la table `groups_users`
-- 

CREATE TABLE `groups_users` (
  `user_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`user_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `groups_users`
-- 

INSERT INTO `groups_users` (`user_id`, `group_id`) VALUES (1, 6);


-- --------------------------------------------------------

-- 
-- Structure de la table `users`
-- 

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(255) NOT NULL,
  `password` varchar(32) default NULL,
  `first_name` varchar(255) default NULL,
  `last_name` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `created` datetime default NULL,
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `users`
-- 

INSERT INTO `users` (`id`, `login`, `password`, `first_name`, `last_name`, `email`, `created`, `modified`) VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', NULL, NULL, NULL, '2007-07-23 19:17:34', '2007-07-23 19:17:34');

-- --------------------------------------------------------
-- 
-- Structure de la table `acos`
-- 

CREATE TABLE `acos` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `model` varchar(255) NOT NULL default '',
  `object_id` int(10) unsigned default NULL,
  `alias` varchar(255) NOT NULL default '',
  `lft` int(10) unsigned default NULL,
  `rght` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `acos`
-- 

INSERT INTO `acos` (`id`, `model`, `object_id`, `alias`, `lft`, `rght`) VALUES (1, '', 1, 'all', 1, 16),
(2, '', 2, 'public', 14, 15),
(3, '', 3, 'administration', 8, 13),
(4, '', 4, 'authorizations', 11, 12),
(5, '', 5, 'configuration', 9, 10),
(6, '', 6, 'gates', 4, 7),
(7, '', 7, 'entrance', 5, 6),
(8, '', 8, 'buinessData', 2, 3);

-- --------------------------------------------------------

-- 
-- Structure de la table `aros`
-- 

CREATE TABLE `aros` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `model` varchar(255) NOT NULL default '',
  `foreign_key` int(10) unsigned default NULL,
  `alias` varchar(255) NOT NULL default '',
  `lft` int(10) unsigned default NULL,
  `rght` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `aros`
-- 

INSERT INTO `aros` (`id`, `model`, `foreign_key`, `alias`, `lft`, `rght`) VALUES (1, '', 0, 'group.all', 1, 14),
(2, '', 0, 'group.anonymous', 12, 13),
(3, '', 0, 'group.member', 2, 11),
(4, '', 0, 'group.regular', 9, 10),
(5, '', 0, 'group.premium', 7, 8),
(6, '', 0, 'group.admin', 3, 6),
(7, '', 1, 'admin', 4, 5);

-- --------------------------------------------------------

-- 
-- Structure de la table `aros_acos`
-- 

CREATE TABLE `aros_acos` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `aro_id` int(10) unsigned NOT NULL,
  `aco_id` int(10) unsigned NOT NULL,
  `_create` char(2) NOT NULL default '0',
  `_read` char(2) NOT NULL default '0',
  `_update` char(2) NOT NULL default '0',
  `_delete` char(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `aros_acos`
-- 

INSERT INTO `aros_acos` (`id`, `aro_id`, `aco_id`, `_create`, `_read`, `_update`, `_delete`) VALUES (1, 6, 1, '1', '1', '1', '1'),
(2, 2, 2, '1', '1', '1', '1');
