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
  `lastpostuser` int(11) unsigned NOT NULL DEFAULT '0',
  `lastpostid` int(11) NOT NULL DEFAULT '0',
  `forder` smallint(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `catid` (`catid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `groupaffiliations`;
CREATE TABLE `groupaffiliations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `leader` int(11) NOT NULL DEFAULT '0',
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

DROP TABLE IF EXISTS `ignoredforums`;
CREATE TABLE `ignoredforums` (
  `uid` int(11) NOT NULL,
  `fid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `ipbans`;
CREATE TABLE `ipbans` (
  `ip` varchar(15) NOT NULL DEFAULT '',
  `reason` varchar(100) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `lastsearches`;
CREATE TABLE `lastsearches` (
  `ip` varchar(16) NOT NULL,
  `userid` int(11) NOT NULL,
  `time` int(11) NOT NULL
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
  `maxpostshourdate` int(11) NOT NULL DEFAULT '0',
  `milestone` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `misc` (`views`, `hotcount`, `porabox`, `poratitle`, `milestone`, `maxuserstext`) VALUES (0, 30, '&hellipSEMICOLON', 'Points of Required Attention', 'Nothing yet.', 'Nobody yet.');

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
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `pollvotes`;
CREATE TABLE `pollvotes` (
  `poll` int(11) NOT NULL DEFAULT '0',
  `choice` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `poll_choices`;
CREATE TABLE `poll_choices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poll` int(11) NOT NULL DEFAULT '0',
  `choice` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `thread` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
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
  `num` int(10) NOT NULL DEFAULT '0',
  `text` varchar(255) NOT NULL DEFAULT '',
  KEY `count` (`num`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `proxybans`;
CREATE TABLE `proxybans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `smilies`;
CREATE TABLE `smilies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(8) NOT NULL DEFAULT '',
  `image` char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `smilies` (`id`, `code`, `image`) VALUES
(1, ':)', 'smile.png'),
(2, 'SEMICOLON)', 'wink.png'),
(3, ':D', 'biggrin.png'),
(4, 'o_o', 'blank.png'),
(5, ':awsum:', 'awsum.png'),
(6, '-_-', 'annoyed.png'),
(7, 'o_O', 'bigeyes.png'),
(8, ':LOL:', 'lol.png'),
(9, ':O', 'jawdrop.png'),
(10, ':(', 'frown.png'),
(11, 'SEMICOLON_SEMICOLON', 'cry.png'),
(12, '>:(', 'mad.png'),
(13, 'O_O', 'eek.png'),
(14, '8-)', 'glasses.png'),
(15, '^_^', 'cute.png'),
(16, '^^SEMICOLON', 'embarras.png'),
(17, '^^SEMICOLONSEMICOLONSEMICOLON', 'cute2.png'),
(18, '>_<', 'yuck.png'),
(19, '¬_¬', 'eyeshift.png'),
(20, '<_<', 'shiftleft.png'),
(21, '>_>', 'shiftright.png'),
(22, '@_@', 'dizzy.png'),
(23, '^~^', 'angel.png'),
(24, '>:)', 'evil.png'),
(25, 'x_x', 'sick.png'),
(26, ':P', 'tongue.png'),
(27, ':S', 'wobbly.png'),
(28, ':[', 'vamp.png'),
(29, '~:o', 'baby.png'),
(30, ':YES:', 'yes.png'),
(31, ':NO:', 'no.png'),
(32, '<3', 'heart.png'),
(33, ':3', 'colonthree.png'),
(34, ':up:', 'approve.png'),
(35, ':down:', 'deny.png'),
(36, ':durr:', 'durrr.png'),
(37, ':barf:', 'barf.png'),
(38, '._.', 'ashamed.png'),
(39, '''.''', 'umm.png'),
(40, '''_''', 'downcast.png'),
(41, ':big:', 'teeth.png'),
(42, ':lawl:', 'lawl.png'),
(43, ':ninja:', 'ninja.png'),
(44, ':pirate:', 'pirate.png'),
(45, 'D:', 'outrage.png'),
(46, ':sob:', 'sob.png'),
(47, ':XD:', 'xd.png'),
(48, ':yum:', 'yum.png'),
(49, ':wafl:', 'wafl.png');

DROP TABLE IF EXISTS `threads`;
CREATE TABLE `threads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `icon` varchar(200) NOT NULL DEFAULT '',
  `replies` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpostdate` int(10) NOT NULL DEFAULT '0',
  `lastposter` int(10) unsigned NOT NULL DEFAULT '0',
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
  `thread` int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `uploader`;
CREATE TABLE `uploader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(512) NOT NULL,
  `description` varchar(1024) NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
  `name` varchar(20) NOT NULL,
  `displayname` varchar(20) NOT NULL,
  `password` varchar(256) NOT NULL DEFAULT '',
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
  `newcomments` tinyint(1) NOT NULL,
  `tempbantime` int(11) NOT NULL DEFAULT '0',
  `tempbanpl` tinyint(2) NOT NULL DEFAULT '0',
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
