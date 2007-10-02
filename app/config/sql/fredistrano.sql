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


--
-- Structure de la table `deployment_logs`
--

CREATE TABLE `deployment_logs` (
  `id` int(11) NOT NULL auto_increment,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(25) default NULL,
  `created` datetime NOT NULL,
  `comment` text,
  `archive` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





-- 
-- Structure de la table `groups`
-- 

CREATE TABLE `groups` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- 
-- Contenu de la table `groups`
-- 

INSERT INTO `groups` (`id`, `group_name`) VALUES 
(1, 'admin'),
(2, 'member'),
(3, 'premium');

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_acl`
-- 

CREATE TABLE `phpgacl_acl` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default 'system',
  `allow` int(11) NOT NULL default '0',
  `enabled` int(11) NOT NULL default '0',
  `return_value` text,
  `note` text,
  `updated_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `phpgacl_enabled_acl` (`enabled`),
  KEY `phpgacl_section_value_acl` (`section_value`),
  KEY `phpgacl_updated_date_acl` (`updated_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_acl`
-- 

INSERT INTO `phpgacl_acl` (`id`, `section_value`, `allow`, `enabled`, `return_value`, `note`, `updated_date`) VALUES 
(10, 'system', 1, 1, '', '', 1180208747),
(11, 'system', 1, 1, '', '', 1177619150);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_acl_sections`
-- 

CREATE TABLE `phpgacl_acl_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phpgacl_value_acl_sections` (`value`),
  KEY `phpgacl_hidden_acl_sections` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_acl_sections`
-- 

INSERT INTO `phpgacl_acl_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES 
(1, 'system', 1, 'System', 0),
(2, 'user', 2, 'User', 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_acl_seq`
-- 

CREATE TABLE `phpgacl_acl_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_acl_seq`
-- 

INSERT INTO `phpgacl_acl_seq` (`id`) VALUES 
(11);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aco`
-- 

CREATE TABLE `phpgacl_aco` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phpgacl_section_value_value_aco` (`section_value`,`value`),
  KEY `phpgacl_hidden_aco` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aco`
-- 

INSERT INTO `phpgacl_aco` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES 
(10, 'access', 'execute', 0, 'Execute', 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aco_map`
-- 

CREATE TABLE `phpgacl_aco_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aco_map`
-- 

INSERT INTO `phpgacl_aco_map` (`acl_id`, `section_value`, `value`) VALUES 
(10, 'access', 'execute'),
(11, 'access', 'execute');

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aco_sections`
-- 

CREATE TABLE `phpgacl_aco_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phpgacl_value_aco_sections` (`value`),
  KEY `phpgacl_hidden_aco_sections` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aco_sections`
-- 

INSERT INTO `phpgacl_aco_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES 
(10, 'access', 0, 'Access', 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aco_sections_seq`
-- 

CREATE TABLE `phpgacl_aco_sections_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aco_sections_seq`
-- 

INSERT INTO `phpgacl_aco_sections_seq` (`id`) VALUES 
(10);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aco_seq`
-- 

CREATE TABLE `phpgacl_aco_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aco_seq`
-- 

INSERT INTO `phpgacl_aco_seq` (`id`) VALUES 
(10);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro`
-- 

CREATE TABLE `phpgacl_aro` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phpgacl_section_value_value_aro` (`section_value`,`value`),
  KEY `phpgacl_hidden_aro` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro`
-- 

INSERT INTO `phpgacl_aro` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES 
(10, 'user', '1', 0, 'admin', 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro_groups`
-- 

CREATE TABLE `phpgacl_aro_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`value`),
  UNIQUE KEY `phpgacl_value_aro_groups` (`value`),
  KEY `phpgacl_parent_id_aro_groups` (`parent_id`),
  KEY `phpgacl_lft_rgt_aro_groups` (`lft`,`rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro_groups`
-- 

INSERT INTO `phpgacl_aro_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES 
(10, 0, 1, 8, 'ARO Root', 'root'),
(11, 10, 2, 3, 'admin', '1'),
(12, 10, 4, 5, 'member', '2'),
(13, 10, 6, 7, 'premium', '3');

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro_groups_id_seq`
-- 

CREATE TABLE `phpgacl_aro_groups_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro_groups_id_seq`
-- 

INSERT INTO `phpgacl_aro_groups_id_seq` (`id`) VALUES 
(13);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro_groups_map`
-- 

CREATE TABLE `phpgacl_aro_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro_groups_map`
-- 

INSERT INTO `phpgacl_aro_groups_map` (`acl_id`, `group_id`) VALUES 
(10, 11),
(11, 12);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro_map`
-- 

CREATE TABLE `phpgacl_aro_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro_map`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro_sections`
-- 

CREATE TABLE `phpgacl_aro_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phpgacl_value_aro_sections` (`value`),
  KEY `phpgacl_hidden_aro_sections` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro_sections`
-- 

INSERT INTO `phpgacl_aro_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES 
(10, 'user', 0, 'User', 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro_sections_seq`
-- 

CREATE TABLE `phpgacl_aro_sections_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro_sections_seq`
-- 

INSERT INTO `phpgacl_aro_sections_seq` (`id`) VALUES 
(10);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_aro_seq`
-- 

CREATE TABLE `phpgacl_aro_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_aro_seq`
-- 

INSERT INTO `phpgacl_aro_seq` (`id`) VALUES 
(36);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo`
-- 

CREATE TABLE `phpgacl_axo` (
  `id` int(11) NOT NULL default '0',
  `section_value` varchar(240) NOT NULL default '0',
  `value` varchar(240) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phpgacl_section_value_value_axo` (`section_value`,`value`),
  KEY `phpgacl_hidden_axo` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo`
-- 

INSERT INTO `phpgacl_axo` (`id`, `section_value`, `value`, `order_value`, `name`, `hidden`) VALUES 
(10, 'controller.phpgacl', 'index', 0, 'index()', 0),
(11, 'controller.phpgacl', 'install', 0, 'install()', 0),
(12, 'controller.phpgacl_elements', 'aco_sections', 0, 'aco_sections()', 0),
(13, 'controller.phpgacl_elements', 'axo_sections', 0, 'axo_sections()', 0),
(14, 'controller.phpgacl_elements', 'acos', 0, 'acos()', 0),
(15, 'controller.phpgacl_elements', 'axos', 0, 'axos()', 0),
(16, 'controller.phpgacl_elements', 'delete_aco', 0, 'delete_aco()', 0),
(17, 'controller.phpgacl_elements', 'delete_aco_section', 0, 'delete_aco_section()', 0),
(18, 'controller.phpgacl_elements', 'delete_axo', 0, 'delete_axo()', 0),
(19, 'controller.phpgacl_elements', 'delete_axo_section', 0, 'delete_axo_section()', 0),
(20, 'controller.phpgacl_elements', 'delete_group', 0, 'delete_group()', 0),
(21, 'controller.phpgacl_elements', 'edit_aco', 0, 'edit_aco()', 0),
(22, 'controller.phpgacl_elements', 'edit_aco_section', 0, 'edit_aco_section()', 0),
(23, 'controller.phpgacl_elements', 'edit_axo', 0, 'edit_axo()', 0),
(24, 'controller.phpgacl_elements', 'edit_axo_section', 0, 'edit_axo_section()', 0),
(25, 'controller.phpgacl_elements', 'edit_group', 0, 'edit_group()', 0),
(26, 'controller.phpgacl_elements', 'groups', 0, 'groups()', 0),
(27, 'controller.phpgacl_elements', 'index', 0, 'index()', 0),
(28, 'controller.phpgacl_helps', 'index', 0, 'index()', 0),
(29, 'controller.phpgacl_permissions', 'check', 0, 'check()', 0),
(30, 'controller.phpgacl_permissions', 'edit', 0, 'edit()', 0),
(31, 'controller.phpgacl_permissions', 'delete', 0, 'delete()', 0),
(32, 'controller.phpgacl_permissions', 'index', 0, 'index()', 0),
(33, 'controller.phpgacl_users', 'delete_group', 0, 'delete_group()', 0),
(34, 'controller.phpgacl_users', 'delete_user', 0, 'delete_user()', 0),
(35, 'controller.phpgacl_users', 'edit_group', 0, 'edit_group()', 0),
(36, 'controller.phpgacl_users', 'edit_user', 0, 'edit_user()', 0),
(37, 'controller.phpgacl_users', 'groups', 0, 'groups()', 0),
(38, 'controller.phpgacl_users', 'import', 0, 'import()', 0),
(39, 'controller.phpgacl_users', 'users', 0, 'users()', 0),
(40, 'controller.home', 'model', 0, 'model()', 0),
(41, 'controller.home', 'page1', 0, 'page1()', 0),
(42, 'controller.home', 'page2', 0, 'page2()', 0),
(43, 'controller.home', 'index', 0, 'index()', 0),
(55, 'controller.users', 'add', 0, 'add()', 0),
(57, 'controller.users', 'delete', 0, 'delete()', 0),
(58, 'controller.users', 'edit', 0, 'edit()', 0),
(59, 'controller.users', 'index', 0, 'index()', 0),
(62, 'controller.users', 'view', 0, 'view()', 0),
(63, 'controller.projects', 'add', 0, 'add()', 0),
(64, 'controller.projects', 'delete', 0, 'delete()', 0),
(65, 'controller.projects', 'deploy', 0, 'deploy()', 0),
(66, 'controller.projects', 'deploy_result', 0, 'deploy_result()', 0),
(67, 'controller.projects', 'edit', 0, 'edit()', 0),
(68, 'controller.projects', 'index', 0, 'index()', 0),
(69, 'controller.projects', 'synchro', 0, 'synchro()', 0),
(70, 'controller.projects', 'view', 0, 'view()', 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo_groups`
-- 

CREATE TABLE `phpgacl_axo_groups` (
  `id` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL default '0',
  `lft` int(11) NOT NULL default '0',
  `rgt` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`,`value`),
  UNIQUE KEY `phpgacl_value_axo_groups` (`value`),
  KEY `phpgacl_parent_id_axo_groups` (`parent_id`),
  KEY `phpgacl_lft_rgt_axo_groups` (`lft`,`rgt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo_groups`
-- 

INSERT INTO `phpgacl_axo_groups` (`id`, `parent_id`, `lft`, `rgt`, `name`, `value`) VALUES 
(10, 0, 1, 4, 'AXO Root', 'root'),
(11, 10, 2, 3, 'Application Controllers', 'controller');

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo_groups_id_seq`
-- 

CREATE TABLE `phpgacl_axo_groups_id_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo_groups_id_seq`
-- 

INSERT INTO `phpgacl_axo_groups_id_seq` (`id`) VALUES 
(11);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo_groups_map`
-- 

CREATE TABLE `phpgacl_axo_groups_map` (
  `acl_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`acl_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo_groups_map`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo_map`
-- 

CREATE TABLE `phpgacl_axo_map` (
  `acl_id` int(11) NOT NULL default '0',
  `section_value` varchar(230) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`acl_id`,`section_value`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo_map`
-- 

INSERT INTO `phpgacl_axo_map` (`acl_id`, `section_value`, `value`) VALUES 
(10, 'controller.home', 'model'),
(10, 'controller.home', 'page1'),
(10, 'controller.home', 'page2'),
(10, 'controller.phpgacl', 'index'),
(10, 'controller.phpgacl', 'install'),
(10, 'controller.phpgacl_elements', 'acos'),
(10, 'controller.phpgacl_elements', 'aco_sections'),
(10, 'controller.phpgacl_elements', 'axos'),
(10, 'controller.phpgacl_elements', 'axo_sections'),
(10, 'controller.phpgacl_elements', 'delete_aco'),
(10, 'controller.phpgacl_elements', 'delete_aco_section'),
(10, 'controller.phpgacl_elements', 'delete_axo'),
(10, 'controller.phpgacl_elements', 'delete_axo_section'),
(10, 'controller.phpgacl_elements', 'delete_group'),
(10, 'controller.phpgacl_elements', 'edit_aco'),
(10, 'controller.phpgacl_elements', 'edit_aco_section'),
(10, 'controller.phpgacl_elements', 'edit_axo'),
(10, 'controller.phpgacl_elements', 'edit_axo_section'),
(10, 'controller.phpgacl_elements', 'edit_group'),
(10, 'controller.phpgacl_elements', 'groups'),
(10, 'controller.phpgacl_elements', 'index'),
(10, 'controller.phpgacl_helps', 'index'),
(10, 'controller.phpgacl_permissions', 'check'),
(10, 'controller.phpgacl_permissions', 'delete'),
(10, 'controller.phpgacl_permissions', 'edit'),
(10, 'controller.phpgacl_permissions', 'index'),
(10, 'controller.phpgacl_users', 'delete_group'),
(10, 'controller.phpgacl_users', 'delete_user'),
(10, 'controller.phpgacl_users', 'edit_group'),
(10, 'controller.phpgacl_users', 'edit_user'),
(10, 'controller.phpgacl_users', 'groups'),
(10, 'controller.phpgacl_users', 'import'),
(10, 'controller.phpgacl_users', 'users'),
(10, 'controller.projects', 'add'),
(10, 'controller.projects', 'delete'),
(10, 'controller.projects', 'deploy'),
(10, 'controller.projects', 'deploy_result'),
(10, 'controller.projects', 'edit'),
(10, 'controller.projects', 'index'),
(10, 'controller.projects', 'synchro'),
(10, 'controller.projects', 'view'),
(10, 'controller.users', 'add'),
(10, 'controller.users', 'delete'),
(10, 'controller.users', 'edit'),
(10, 'controller.users', 'index'),
(10, 'controller.users', 'view'),
(11, 'controller.audits', 'index'),
(11, 'controller.audits', 'view'),
(11, 'controller.cards', 'index'),
(11, 'controller.cards', 'view'),
(11, 'controller.home', 'page2');

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo_sections`
-- 

CREATE TABLE `phpgacl_axo_sections` (
  `id` int(11) NOT NULL default '0',
  `value` varchar(230) NOT NULL,
  `order_value` int(11) NOT NULL default '0',
  `name` varchar(230) NOT NULL,
  `hidden` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `phpgacl_value_axo_sections` (`value`),
  KEY `phpgacl_hidden_axo_sections` (`hidden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo_sections`
-- 

INSERT INTO `phpgacl_axo_sections` (`id`, `value`, `order_value`, `name`, `hidden`) VALUES 
(10, 'controller.phpgacl', 0, 'Phpgacl Plugin - Phpgacl', 0),
(11, 'controller.phpgacl_elements', 0, 'Phpgacl Plugin - PhpgaclElements', 0),
(12, 'controller.phpgacl_helps', 0, 'Phpgacl Plugin - PhpgaclHelps', 0),
(13, 'controller.phpgacl_permissions', 0, 'Phpgacl Plugin - PhpgaclPermissions', 0),
(14, 'controller.phpgacl_users', 0, 'Phpgacl Plugin - PhpgaclUsers', 0),
(15, 'controller.home', 0, 'Home', 0),
(16, 'controller.audits', 0, 'Audits', 0),
(17, 'controller.cards', 0, 'Cards', 0),
(18, 'controller.users', 0, 'Users', 0),
(19, 'controller.projects', 0, 'Projects', 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo_sections_seq`
-- 

CREATE TABLE `phpgacl_axo_sections_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo_sections_seq`
-- 

INSERT INTO `phpgacl_axo_sections_seq` (`id`) VALUES 
(19);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_axo_seq`
-- 

CREATE TABLE `phpgacl_axo_seq` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_axo_seq`
-- 

INSERT INTO `phpgacl_axo_seq` (`id`) VALUES 
(70);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_groups_aro_map`
-- 

CREATE TABLE `phpgacl_groups_aro_map` (
  `group_id` int(11) NOT NULL default '0',
  `aro_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`aro_id`),
  KEY `phpgacl_aro_id` (`aro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_groups_aro_map`
-- 

INSERT INTO `phpgacl_groups_aro_map` (`group_id`, `aro_id`) VALUES 
(11, 10);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_groups_axo_map`
-- 

CREATE TABLE `phpgacl_groups_axo_map` (
  `group_id` int(11) NOT NULL default '0',
  `axo_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`axo_id`),
  KEY `phpgacl_axo_id` (`axo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_groups_axo_map`
-- 

INSERT INTO `phpgacl_groups_axo_map` (`group_id`, `axo_id`) VALUES 
(11, 10),
(11, 11),
(11, 12),
(11, 13),
(11, 14),
(11, 15),
(11, 16),
(11, 17),
(11, 18),
(11, 19),
(11, 20),
(11, 21),
(11, 22),
(11, 23),
(11, 24),
(11, 25),
(11, 26),
(11, 27),
(11, 28),
(11, 29),
(11, 30),
(11, 31),
(11, 32),
(11, 33),
(11, 34),
(11, 35),
(11, 36),
(11, 37),
(11, 38),
(11, 39),
(11, 40),
(11, 41),
(11, 42),
(11, 43),
(11, 44),
(11, 45),
(11, 46),
(11, 47),
(11, 48),
(11, 49),
(11, 50),
(11, 51),
(11, 52),
(11, 53),
(11, 54),
(11, 55),
(11, 57),
(11, 58),
(11, 59),
(11, 62),
(11, 63),
(11, 64),
(11, 65),
(11, 66),
(11, 67),
(11, 68),
(11, 69),
(11, 70);

-- --------------------------------------------------------

-- 
-- Structure de la table `phpgacl_phpgacl`
-- 

CREATE TABLE `phpgacl_phpgacl` (
  `name` varchar(230) NOT NULL,
  `value` varchar(230) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Contenu de la table `phpgacl_phpgacl`
-- 

INSERT INTO `phpgacl_phpgacl` (`name`, `value`) VALUES 
('schema_version', '2.1'),
('version', '3.3.7');

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
  `config_path` varchar(255) collate utf8_unicode_ci NOT NULL,
  `created` timestamp NULL default NULL,
  `modified` timestamp NULL default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8 AUTO_INCREMENT=2 ;


-- 
-- Contenu de la table `projects`
-- 

INSERT INTO `projects` (`id`, `name`, `svn_url`, `prd_url`, `prd_path`, `config_path`, `created`, `modified`) VALUES 
(1, 'Fredistrano', 'http://svn.fbollon.net/Fredistrano/trunk', 'http://localhost/test/Fredistrano', '/var/www/test1/Fredistrano', 'app/config', '2007-10-02 19:04:09', '2007-10-02 23:03:10');

-- --------------------------------------------------------

-- 
-- Structure de la table `users`
-- 

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) default NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- 
-- Contenu de la table `users`
-- 

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`, `email`, `created`, `modified`) VALUES 
(1, 'admin', '1a1dc91c907325c69271ddf0c944bc72', 'admin', 'admin', 'admin@gmail.com', '0000-00-00 00:00:00', '2007-06-01 22:46:37');
