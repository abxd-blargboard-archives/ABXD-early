DROP TABLE IF EXISTS `blockedlayouts`;
CREATE TABLE `blockedlayouts` (
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `blockee` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `user` (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `minpower` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `forummods`;
CREATE TABLE `forummods` (
  `forum` int(10) NOT NULL DEFAULT '0',
  `user` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `forums`;
CREATE TABLE `forums` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) DEFAULT NULL,
  `description` text,
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  `minpower` tinyint(2) NOT NULL DEFAULT '0',
  `minpowerthread` tinyint(2) NOT NULL DEFAULT '0',
  `minpowerreply` tinyint(2) NOT NULL DEFAULT '0',
  `numthreads` int(10) unsigned NOT NULL DEFAULT '0',
  `numposts` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpostdate` int(11) NOT NULL DEFAULT '0',
  `lastpostid` int(11) NOT NULL DEFAULT '0',
  `lastpostuser` int(11) unsigned NOT NULL DEFAULT '0',
  `forder` int(10) NOT NULL DEFAULT '0',
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  `lasturl` varchar(256) NOT NULL DEFAULT '',
  `lastforum` int(11) unsigned NOT NULL DEFAULT '0',
  `useragent` varchar(256) NOT NULL DEFAULT '',
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
  `hotcount` int(10) unsigned DEFAULT '30',
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userto` int(10) unsigned NOT NULL DEFAULT '0',
  `userfrom` int(10) unsigned NOT NULL DEFAULT '0',
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
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
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
  `user` int(11) NOT NULL DEFAULT '0'
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
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `thread` int(10) unsigned NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '0.0.0.0',
  `num` int(10) NOT NULL DEFAULT '0',
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
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
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
  `image` char(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `smilies` (`code`, `image`) VALUES
(':)', 'smile.png'),
('SEMICOLON)', 'wink.png'),
(':D', 'biggrin.png'),
('o_o', 'blank.png'),
(':awsum:', 'awsum.png'),
('-_-', 'annoyed.png'),
('o_O', 'bigeyes.png'),
(':LOL:', 'lol.png'),
(':O', 'jawdrop.png'),
(':(', 'frown.png'),
('SEMICOLON_SEMICOLON', 'cry.png'),
('>:', 'mad.png'),
('O_O', 'eek.png'),
('8-)', 'glasses.png'),
('^_^', 'cute.png'),
('^^SEMICOLONSEMICOLONSEMICOLON', 'cute2.png'),
('>_<', 'yuck.png'),
('¬_¬', 'eyeshift.png'),
('<_<', 'shiftleft.png'),
('>_>', 'shiftright.png'),
('@_@', 'dizzy.png'),
('^~^', 'angel.png'),
('>:)', 'evil.png'),
('x_x', 'sick.png'),
(':P', 'tongue.png'),
(':S', 'wobbly.png'),
(':[', 'vamp.png'),
('~:o', 'baby.png'),
(':YES:', 'yes.png'),
(':NO:', 'no.png'),
('<3', 'heart.png'),
(':3', 'colonthree.png'),
(':up:', 'approve.png'),
(':down:', 'deny.png'),
(':durr:', 'durrr.png'),
('^^SEMICOLON', 'embarras.png'),
(':barf:', 'barf.png'),
('._.', 'ashamed.png'),
('''.''', 'umm.png'),
('''_''', 'downcast.png'),
(':big:', 'teeth.png'),
(':lawl:', 'lawl.png'),
(':ninja:', 'ninja.png'),
(':pirate:', 'pirate.png'),
('D:', 'outrage.png'),
(':sob:', 'sob.png'),
(':XD:', 'xd.png'),
(':yum:', 'yum.png');

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
  `poll` int(10) unsigned NOT NULL DEFAULT '0',
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
  `id` int(10) NOT NULL DEFAULT '0',
  `thread` int(10) NOT NULL DEFAULT '0',
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
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `cid` int(11) NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `posts` mediumint(9) NOT NULL DEFAULT '0',
  `regdate` int(11) NOT NULL DEFAULT '0',
  `name` varchar(25) NOT NULL DEFAULT '',
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
  `lastforum` int(11) unsigned NOT NULL DEFAULT '0',
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
  `newcomments` tinyint(1) NOT NULL DEFAULT '0',
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
