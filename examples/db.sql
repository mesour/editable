-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `reg_num` varchar(255) NOT NULL,
  `verified` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `companies` (`id`, `name`, `reg_num`, `verified`) VALUES
(1,	'Google',	'789123',	1),
(2,	'Google a.s.',	'789123',	1),
(3,	'Allianz',	'456789',	0),
(4,	'Ford',	'987654',	0),
(5,	'Foxconn',	'654321',	0),
(6,	'Verizon',	'357654',	1),
(7,	'Lukoil',	'236846',	1),
(8,	'Honda',	'982154',	0);

DROP TABLE IF EXISTS `empty`;
CREATE TABLE `empty` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `surname` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) COLLATE utf8_estonian_ci NOT NULL,
  `type` enum('first','second') COLLATE utf8_estonian_ci NOT NULL,
  `date` datetime NOT NULL,
  `members` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_estonian_ci;

INSERT INTO `groups` (`id`, `name`, `type`, `date`, `members`) VALUES
(1,	'Group 1',	'first',	'2016-01-01 00:00:00',	7),
(2,	'Group 2',	'second',	'2016-03-05 00:00:00',	6),
(3,	'Group 3',	'second',	'2016-05-09 00:00:00',	7),
(4,	'test',	'first',	'0000-00-00 00:00:00',	654654);

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` int(10) unsigned DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `role` enum('admin','moderator') NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `surname` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `amount` double NOT NULL,
  `avatar` varchar(128) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  `timestamp` int(10) DEFAULT NULL,
  `has_pro` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `action` (`action`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `action`, `group_id`, `role`, `name`, `surname`, `email`, `last_login`, `amount`, `avatar`, `order`, `timestamp`, `has_pro`) VALUES
(1,	1,	2,	'admin',	'John',	'Doe',	'john.doe@test.xx',	'2015-05-14 00:00:00',	150,	'/avatar/01.png',	100,	1418255325,	1),
(2,	1,	2,	'moderator',	'Peter',	'Larson',	'peter.larson@test.xx',	'2014-09-09 13:37:32',	15220.654,	'/avatar/02.png',	160,	1418255330,	0),
(3,	1,	2,	'admin',	'Claude',	'Graves',	'claude.graves@test.xx',	'2014-09-02 14:17:32',	9876.465498,	'/avatar/03.png',	180,	1418255311,	0),
(4,	0,	3,	'moderator',	'Stuart',	'Norman',	'stuart.norman@test.xx',	'2014-09-09 18:39:18',	98766.2131,	'/avatar/04.png',	120,	1418255328,	0),
(5,	1,	1,	'admin',	'Kathy',	'Arnold',	'kathy.arnold@test.xx',	'2014-09-07 10:24:07',	456.987,	'/avatar/05.png',	140,	1418155313,	0),
(6,	0,	3,	'moderator',	'Jan',	'Wilson',	'jan.wilson@test.xx',	'2014-09-03 13:15:22',	123,	'/avatar/06.png',	150,	1418255318,	1),
(7,	0,	1,	'moderator',	'Alberta',	'Erickson',	'alberta.erickson@test.xx',	'2014-08-06 13:37:17',	98753.654,	'/avatar/07.png',	110,	1418255327,	1),
(8,	1,	3,	'admin',	'Ada',	'Wells',	'ada.wells@test.xx',	'2014-08-12 11:25:16',	852.3654,	'/avatar/08.png',	70,	1418255332,	0),
(9,	0,	2,	'admin',	'Ethel',	'Figueroa',	'ethel.figueroa@test.xx',	'2014-09-05 10:23:26',	45695.986,	'/avatar/09.png',	20,	1418255305,	0),
(10,	1,	3,	'moderator',	'Ian',	'Goodwin',	'ian.goodwin@test.xx',	'2014-09-04 12:26:19',	1236.9852,	'/avatar/10.png',	130,	1418255331,	1),
(11,	1,	2,	'moderator',	'Francis',	'Hayes',	'francis.hayes@test.xx',	'2014-09-03 10:16:17',	5498.345,	'/avatar/11.png',	0,	1418255293,	0),
(12,	0,	1,	'moderator',	'Erma',	'Burns',	'erma.burns@test.xx',	'2014-07-02 15:42:15',	63287.9852,	'/avatar/12.png',	60,	1418255316,	1),
(13,	1,	3,	'moderator',	'Kristina',	'Jenkins',	'kristina.jenkins@test.xx',	'2014-08-20 14:39:43',	74523.96549,	'/avatar/13.png',	40,	1418255334,	0),
(14,	0,	3,	'admin',	'Virgil',	'Hunt',	'virgil.hunt@test.xx',	'2014-08-12 16:09:38',	65654.6549,	'/avatar/14.png',	30,	1418255276,	1),
(15,	1,	1,	'moderator',	'Max',	'Martin',	'max.martin@test.xx',	'2014-09-01 12:14:20',	541236.5495,	'/avatar/15.png',	170,	1418255317,	0),
(16,	0,	2,	'admin',	'Melody',	'Manning',	'melody.manning@test.xx',	'2014-09-02 12:26:20',	9871.216,	'/avatar/16.png',	50,	1418255281,	0),
(17,	0,	3,	'moderator',	'Catherine',	'Todd',	'catherine.todd@test.xx',	'2014-06-11 15:14:39',	100.2,	'/avatar/17.png',	10,	1418255313,	0),
(18,	0,	1,	'admin',	'Douglas',	'Stanley',	'douglas.stanley@test.xx',	'2014-04-16 15:22:18',	900,	'/avatar/18.png',	90,	1418255332,	1),
(19,	1,	2,	'admin',	'Patti',	'Diaz',	'patti.diaz@test.xx',	'2014-09-11 12:17:16',	1500,	'/avatar/19.png',	80,	1418255275,	0),
(20,	0,	1,	'moderator',	'John',	'Petterson',	'john.petterson@test.xx',	'2014-10-10 10:10:10',	2500,	'/avatar/20.png',	190,	1418255275,	0);

DROP TABLE IF EXISTS `user_addresses`;
CREATE TABLE `user_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `street` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user_addresses` (`id`, `user_id`, `street`, `city`, `zip`, `country`) VALUES
(10,	1,	'Test 22',	'Hehehov',	'12345',	'CZ');

DROP TABLE IF EXISTS `user_companies`;
CREATE TABLE `user_companies` (
  `user_id` int(10) unsigned NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `user_id_company_id` (`user_id`,`company_id`),
  KEY `user_id` (`user_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `user_companies_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `user_companies_ibfk_4` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `user_companies` (`user_id`, `company_id`) VALUES
(1,	2),
(1,	3),
(1,	5),
(2,	5),
(4,	5),
(5,	5),
(5,	6),
(8,	1),
(8,	5),
(8,	8),
(9,	7),
(10,	8),
(12,	6),
(13,	6),
(15,	2),
(15,	8),
(17,	3),
(19,	2),
(19,	6),
(20,	2);

-- 2016-03-26 17:20:38