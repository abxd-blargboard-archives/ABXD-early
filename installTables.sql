DROP TABLE IF EXISTS `blockedlayouts`;
CREATE TABLE `blockedlayouts` (
  `user` smallint(5) unsigned NOT NULL DEFAULT '0',
  `blockee` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `minpower` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `forummods`;
CREATE TABLE `forummods` (
  `forum` smallint(5) NOT NULL DEFAULT '0',
  `user` mediumint(8) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `forums`;
CREATE TABLE `forums` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) DEFAULT NULL,
  `description` text,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `minpower` tinyint(2) NOT NULL DEFAULT '0',
  `minpowerthread` tinyint(2) NOT NULL DEFAULT '0',
  `minpowerreply` tinyint(2) NOT NULL DEFAULT '0',
  `numthreads` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `numposts` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lastpostdate` int(11) NOT NULL DEFAULT '0',
  `lastpostid` int(11) NOT NULL DEFAULT '0',
  `lastpostuser` int(11) unsigned NOT NULL DEFAULT '0',
  `forder` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `groupaffiliations`;
CREATE TABLE `groupaffiliations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `leader` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `guests`;
CREATE TABLE `guests` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  `lasturl` varchar(100) NOT NULL DEFAULT '',
  `lastforum` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `useragent` varchar(100) NOT NULL DEFAULT '',
  `bot` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ip2c`;
CREATE TABLE `ip2c` (
  `ip_from` bigint(12) NOT NULL DEFAULT '0',
  `ip_to` bigint(12) NOT NULL DEFAULT '0',
  `cc` char(2) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ipbans`;
CREATE TABLE `ipbans` (
  `ip` varchar(15) NOT NULL DEFAULT '',
  `reason` varchar(100) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `misc`;
CREATE TABLE `misc` (
  `views` int(11) unsigned NOT NULL DEFAULT '0',
  `hotcount` smallint(5) unsigned DEFAULT '30',
  `porabox` text NOT NULL,
  `poratitle` text NOT NULL,
  `maxusers` int(11) NOT NULL DEFAULT '0',
  `maxusersdate` int(11) NOT NULL DEFAULT '0',
  `maxuserstext` text NOT NULL,
  `maxpostsday` int(11) NOT NULL DEFAULT '0',
  `maxpostsdaydate` int(11) NOT NULL DEFAULT '0',
  `maxpostshour` int(11) NOT NULL DEFAULT '0',
  `maxpostshourdate` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `misc` (`views`, `hotcount`, `porabox`, `poratitle`) VALUES (0, 30, '&hellipSEMICOLON', 'Points of Required Attention');

DROP TABLE IF EXISTS `moodavatars`;
CREATE TABLE `moodavatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `mid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `pmsgs`;
CREATE TABLE `pmsgs` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `userto` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userfrom` smallint(5) unsigned NOT NULL DEFAULT '0',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '',
  `msgread` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userto` (`userto`),
  KEY `userfrom` (`userfrom`),
  KEY `msgread` (`msgread`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `pmsgs_text`;
CREATE TABLE `pmsgs_text` (
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `poll`;
CREATE TABLE `poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL DEFAULT '',
  `briefing` text NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `doublevote` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `pollvotes`;
CREATE TABLE `pollvotes` (
  `poll` int(11) NOT NULL DEFAULT '0',
  `choice` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `poll_choices`;
CREATE TABLE `poll_choices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll` int(11) NOT NULL DEFAULT '0',
  `choice` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `thread` int(10) unsigned NOT NULL DEFAULT '0',
  `user` smallint(5) unsigned NOT NULL DEFAULT '0',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '0.0.0.0',
  `num` mediumint(8) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `options` tinyint(4) NOT NULL DEFAULT '0',
  `mood` int(11) NOT NULL DEFAULT '0',
  `currentrevision` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `thread` (`thread`),
  KEY `date` (`date`),
  KEY `user` (`user`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `posts_text`;
CREATE TABLE `posts_text` (
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `text` mediumtext,
  `revision` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ranks`;
CREATE TABLE `ranks` (
  `rset` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `num` mediumint(8) NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL DEFAULT '',
  KEY `count` (`num`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ranksets`;
CREATE TABLE `ranksets` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `reporters`;
CREATE TABLE `reporters` (
  `ip` varchar(128) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `smilies`;
CREATE TABLE `smilies` (
  `code` char(8) NOT NULL DEFAULT '',
  `image` char(16) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `smilies` VALUES('-_-', 'annoyed.gif');
INSERT INTO `smilies` VALUES('~:o', 'baby.gif');
INSERT INTO `smilies` VALUES('o_O', 'bigeyes.gif');
INSERT INTO `smilies` VALUES(':D', 'biggrin.gif');
INSERT INTO `smilies` VALUES('o_o', 'blank.gif');
INSERT INTO `smilies` VALUES('SEMICOLON_SEMICOLON', 'cry.gif');
INSERT INTO `smilies` VALUES('^^SEMICOLONSEMICOLONSEMICOLON', 'cute2.gif');
INSERT INTO `smilies` VALUES('^_^SEMICOLON', 'cute.gif');
INSERT INTO `smilies` VALUES('^_^', 'cute.gif');
INSERT INTO `smilies` VALUES('@_@', 'dizzy.gif');
INSERT INTO `smilies` VALUES('O_O', 'eek.gif');
INSERT INTO `smilies` VALUES('>:)', 'evil.gif');
INSERT INTO `smilies` VALUES('¬_¬', 'eyeshift.gif');
INSERT INTO `smilies` VALUES(':(', 'frown.gif');
INSERT INTO `smilies` VALUES('8-)', 'glasses.gif');
INSERT INTO `smilies` VALUES(':O', 'jawdrop.gif');
INSERT INTO `smilies` VALUES(':LOL:', 'lol.gif');
INSERT INTO `smilies` VALUES('>:', 'mad.gif');
INSERT INTO `smilies` VALUES(':NO:', 'no.png');
INSERT INTO `smilies` VALUES('<_<', 'shiftleft.gif');
INSERT INTO `smilies` VALUES('>_>', 'shiftright.gif');
INSERT INTO `smilies` VALUES('x_x', 'sick.gif');
INSERT INTO `smilies` VALUES(':)', 'smile.gif');
INSERT INTO `smilies` VALUES(':P', 'tongue.gif');
INSERT INTO `smilies` VALUES(':[', 'vamp.gif');
INSERT INTO `smilies` VALUES('SEMICOLON)', 'wink.gif');
INSERT INTO `smilies` VALUES(':S', 'wobbly.gif');
INSERT INTO `smilies` VALUES(':YES:', 'yes.png');
INSERT INTO `smilies` VALUES('>_<', 'yuck.gif');
INSERT INTO `smilies` VALUES(':awsum:', 'awsum.gif');

DROP TABLE IF EXISTS `threads`;
CREATE TABLE `threads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user` smallint(5) unsigned NOT NULL DEFAULT '0',
  `views` smallint(5) unsigned NOT NULL DEFAULT '0',
  `closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `icon` varchar(200) NOT NULL DEFAULT '',
  `replies` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lastpostdate` int(10) NOT NULL DEFAULT '0',
  `lastposter` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lastpostid` int(11) NOT NULL DEFAULT '0',
  `sticky` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `poll` smallint(5) unsigned NOT NULL DEFAULT '0',
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `forum` (`forum`),
  KEY `user` (`user`),
  KEY `sticky` (`sticky`),
  KEY `pollid` (`poll`),
  KEY `lastpostdate` (`lastpostdate`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `threadsread`;
CREATE TABLE `threadsread` (
  `id` smallint(5) NOT NULL DEFAULT '0',
  `thread` tinyint(10) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `usercomments`;
CREATE TABLE `usercomments` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `posts` mediumint(9) NOT NULL DEFAULT '0',
  `regdate` int(11) NOT NULL DEFAULT '0',
  `name` varchar(25) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `minipic` varchar(100) NOT NULL DEFAULT '',
  `picture` varchar(100) NOT NULL DEFAULT '',
  `postheader` text,
  `signature` text,
  `bio` text,
  `powerlevel` tinyint(2) NOT NULL DEFAULT '0',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `title` varchar(255) NOT NULL DEFAULT '',
  `rankset` tinyint(5) unsigned NOT NULL DEFAULT '0',
  `realname` varchar(60) NOT NULL DEFAULT '',
  `location` varchar(200) NOT NULL DEFAULT '',
  `birthday` int(11) NOT NULL DEFAULT '0',
  `email` varchar(60) NOT NULL DEFAULT '',
  `homepageurl` varchar(80) NOT NULL DEFAULT '',
  `homepagename` varchar(100) NOT NULL DEFAULT '',
  `lastposttime` int(10) unsigned NOT NULL DEFAULT '0',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  `lastip` varchar(15) NOT NULL DEFAULT '',
  `lasturl` varchar(100) NOT NULL DEFAULT '',
  `lastforum` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `postsperpage` smallint(4) unsigned NOT NULL DEFAULT '20',
  `threadsperpage` smallint(4) unsigned NOT NULL DEFAULT '50',
  `timezone` float NOT NULL DEFAULT '0',
  `scheme` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `signsep` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `dateformat` varchar(20) NOT NULL DEFAULT 'm-d-y',
  `timeformat` varchar(20) NOT NULL DEFAULT 'h:i a',
  `fontsize` int(11) NOT NULL DEFAULT '80',
  `karma` int(11) NOT NULL DEFAULT '100',
  `blocklayouts` tinyint(1) NOT NULL DEFAULT '0',
  `globalblock` tinyint(1) NOT NULL DEFAULT '0',
  `usebanners` tinyint(1) NOT NULL DEFAULT '1',
  `lastknownbrowser` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `posts` (`posts`),
  KEY `name` (`name`),
  KEY `lastforum` (`lastforum`),
  KEY `lastposttime` (`lastposttime`),
  KEY `lastactivity` (`lastactivity`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `uservotes`;
CREATE TABLE `uservotes` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `voter` int(11) NOT NULL DEFAULT '0',
  `up` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
