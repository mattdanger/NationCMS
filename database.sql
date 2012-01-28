-- MySQL dump 10.11
--
-- Host: localhost    Database: nation
-- ------------------------------------------------------
-- Server version	5.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_event_log`
--

DROP TABLE IF EXISTS `admin_event_log`;
CREATE TABLE `admin_event_log` (
  `id` int(5) NOT NULL auto_increment,
  `timestamp` int(10) NOT NULL default '0',
  `name` varchar(16) NOT NULL default '',
  `event` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=266 DEFAULT CHARSET=latin1;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int(5) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `body` text NOT NULL,
  `photo` varchar(255) NOT NULL default '',
  `date_added` varchar(128) NOT NULL default '',
  `author` varchar(32) NOT NULL default '',
  `last_revised` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `id` tinyint(3) NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `url_title` varchar(128) NOT NULL default '',
  `description` text NOT NULL,
  `photo` varchar(32) NOT NULL default '',
  `myspace` varchar(16) default NULL,
  `website` varchar(64) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(8) NOT NULL auto_increment,
  `date` int(16) NOT NULL default '0',
  `ip` varchar(16) NOT NULL default '',
  `user_agent` varchar(255) NOT NULL default '',
  `type` varchar(32) NOT NULL default '',
  `message` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(5) NOT NULL auto_increment,
  `title` varchar(64) NOT NULL default '',
  `url_title` varchar(128) NOT NULL default '',
  `body` text NOT NULL,
  `author` varchar(32) NOT NULL default '',
  `timestamp` int(10) NOT NULL default '0',
  `last_edited_by` varchar(32) default NULL,
  `last_edited_timestamp` int(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=latin1;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
CREATE TABLE `photos` (
  `id` int(5) NOT NULL auto_increment,
  `filename` varchar(32) NOT NULL default '',
  `url_title` varchar(128) NOT NULL default '',
  `belongs_to_user_id` int(1) NOT NULL default '0',
  `belongs_to` varchar(32) NOT NULL default '',
  `photographer` varchar(32) NOT NULL default '',
  `location` varchar(32) NOT NULL default '',
  `date_taken` varchar(64) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=275 DEFAULT CHARSET=latin1;

--
-- Table structure for table `profile_questions`
--

DROP TABLE IF EXISTS `profile_questions`;
CREATE TABLE `profile_questions` (
  `id` int(255) NOT NULL auto_increment,
  `belongs_to_id` int(4) NOT NULL default '0',
  `question` varchar(64) NOT NULL default '',
  `answer` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=584 DEFAULT CHARSET=latin1;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `id` int(2) NOT NULL auto_increment,
  `first_name` varchar(32) NOT NULL default '',
  `last_name` varchar(32) NOT NULL default '',
  `user_id` int(4) NOT NULL default '0',
  `description` text NOT NULL,
  `avatar` varchar(128) NOT NULL default '',
  `primary_photo` varchar(128) default NULL,
  `msn_messenger` varchar(32) default NULL,
  `aim` varchar(32) default NULL,
  `yahoo` varchar(32) default NULL,
  `google` varchar(32) default NULL,
  `facebook` varchar(32) default NULL,
  `myspace` varchar(32) default NULL,
  `flickr` varchar(32) default NULL,
  `website` varchar(74) default NULL,
  `delicious` varchar(32) default NULL,
  `skype` varchar(32) default NULL,
  `youtube` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `site_content`
--

DROP TABLE IF EXISTS `site_content`;
CREATE TABLE `site_content` (
  `id` int(2) NOT NULL auto_increment,
  `section` varchar(10) NOT NULL default '',
  `heading` varchar(64) NOT NULL default '',
  `main` text NOT NULL,
  `side_panel` text NOT NULL,
  `photo` varchar(32) default NULL,
  `last_edited` varchar(128) NOT NULL default '',
  `last_edited_by` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(4) NOT NULL auto_increment,
  `username` varchar(32) NOT NULL default '',
  `password` varchar(64) NOT NULL default '',
  `salt` varchar(8) NOT NULL default '',
  `first_name` varchar(32) default NULL,
  `last_name` varchar(32) NOT NULL default '',
  `user_level` int(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Table structure for table `video`
--

DROP TABLE IF EXISTS `video`;
CREATE TABLE `video` (
  `id` tinyint(3) NOT NULL auto_increment,
  `filename` varchar(128) NOT NULL default '',
  `title` varchar(64) NOT NULL default '',
  `url_title` varchar(128) NOT NULL default '',
  `filmer` varchar(64) NOT NULL default '',
  `editor` varchar(32) NOT NULL default '',
  `featuring` varchar(64) NOT NULL default '',
  `date` varchar(32) NOT NULL default '',
  `music` varchar(64) default NULL,
  `description` varchar(255) default NULL,
  `widescreen` tinyint(1) NOT NULL default '0',
  `filesize` int(16) NOT NULL default '0',
  `runtime` varchar(16) NOT NULL default '',
  `software_info` varchar(255) default NULL,
  `camera_info` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Table structure for table `view_counts_photos`
--

DROP TABLE IF EXISTS `view_counts_photos`;
CREATE TABLE `view_counts_photos` (
  `id` int(255) NOT NULL auto_increment,
  `filename` varchar(64) NOT NULL default '',
  `timestamp` int(32) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9323 DEFAULT CHARSET=latin1;

--
-- Table structure for table `view_counts_videos`
--

DROP TABLE IF EXISTS `view_counts_videos`;
CREATE TABLE `view_counts_videos` (
  `id` int(255) NOT NULL auto_increment,
  `filename` varchar(64) NOT NULL default '',
  `timestamp` int(32) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5388 DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-03-04 23:14:19
