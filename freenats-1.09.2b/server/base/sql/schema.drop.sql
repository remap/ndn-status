-- With DROP TABLES - will clean database
-- MySQL dump 10.10
--
-- Host: localhost    Database: freenats
-- ------------------------------------------------------
-- Server version	5.0.22

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
-- Table structure for table `fnalert`
--

DROP TABLE IF EXISTS `fnalert`;
CREATE TABLE `fnalert` (
  `alertid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL,
  `alertlevel` int(11) NOT NULL default '0',
  `openedx` bigint(20) unsigned NOT NULL default '0',
  `closedx` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`alertid`),
  KEY `nodeid` (`nodeid`),
  KEY `closedx` (`closedx`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnalertaction`
--

DROP TABLE IF EXISTS `fnalertaction`;
CREATE TABLE `fnalertaction` (
  `aaid` bigint(20) unsigned NOT NULL auto_increment,
  `atype` varchar(32) NOT NULL,
  `efrom` varchar(250) NOT NULL,
  `etolist` text NOT NULL,
  `esubject` int(11) NOT NULL default '0',
  `etype` int(11) NOT NULL default '0',
  `awarnings` tinyint(1) NOT NULL default '0',
  `adecrease` tinyint(1) NOT NULL default '0',
  `mdata` text NOT NULL,
  `aname` varchar(120) NOT NULL,
  `ctrdate` varchar(8) NOT NULL,
  `ctrlimit` int(10) unsigned NOT NULL default '0',
  `ctrtoday` int(10) unsigned NOT NULL default '0',
  `scheduleid` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`aaid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnalertlog`
--

DROP TABLE IF EXISTS `fnalertlog`;
CREATE TABLE `fnalertlog` (
  `alid` bigint(20) unsigned NOT NULL auto_increment,
  `alertid` bigint(20) unsigned NOT NULL default '0',
  `postedx` bigint(20) unsigned NOT NULL default '0',
  `logentry` varchar(250) NOT NULL,
  PRIMARY KEY  (`alid`),
  KEY `alertid` (`alertid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnconfig`
--

DROP TABLE IF EXISTS `fnconfig`;
CREATE TABLE `fnconfig` (
  `fnc_var` varchar(64) NOT NULL,
  `fnc_val` varchar(64) NOT NULL,
  PRIMARY KEY  (`fnc_var`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fneval`
--

DROP TABLE IF EXISTS `fneval`;
CREATE TABLE `fneval` (
  `evalid` bigint(20) unsigned NOT NULL auto_increment,
  `testid` varchar(128) NOT NULL,
  `weight` int(11) NOT NULL default '0',
  `eoperator` varchar(32) NOT NULL,
  `evalue` varchar(128) NOT NULL,
  `eoutcome` int(11) NOT NULL default '0',
  PRIMARY KEY  (`evalid`),
  KEY `testid` (`testid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fngroup`
--

DROP TABLE IF EXISTS `fngroup`;
CREATE TABLE `fngroup` (
  `groupid` bigint(20) unsigned NOT NULL auto_increment,
  `groupname` varchar(128) NOT NULL,
  `groupdesc` varchar(250) NOT NULL,
  `groupicon` varchar(64) NOT NULL,
  `weight` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fngrouplink`
--

DROP TABLE IF EXISTS `fngrouplink`;
CREATE TABLE `fngrouplink` (
  `glid` bigint(20) unsigned NOT NULL auto_increment,
  `groupid` bigint(20) unsigned NOT NULL default '0',
  `nodeid` varchar(64) NOT NULL,
  PRIMARY KEY  (`glid`),
  KEY `groupid` (`groupid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnlocaltest`
--

DROP TABLE IF EXISTS `fnlocaltest`;
CREATE TABLE `fnlocaltest` (
  `localtestid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL,
  `alertlevel` int(11) NOT NULL default '-1',
  `lastrunx` bigint(20) unsigned NOT NULL default '0',
  `testtype` varchar(128) NOT NULL,
  `testparam` varchar(250) default NULL,
  `testrecord` tinyint(1) NOT NULL default '0',
  `simpleeval` tinyint(1) NOT NULL default '1',
  `testname` varchar(64) NOT NULL,
  `attempts` int(11) NOT NULL default '0',
  `timeout` int(11) NOT NULL default '0',
  `testenabled` tinyint(1) NOT NULL default '1',
  `testparam1` varchar(250) NOT NULL,
  `testparam2` varchar(250) NOT NULL,
  `testparam3` varchar(250) NOT NULL,
  `testparam4` varchar(250) NOT NULL,
  `testparam5` varchar(250) NOT NULL,
  `testparam6` varchar(250) NOT NULL,
  `testparam7` varchar(250) NOT NULL,
  `testparam8` varchar(250) NOT NULL,
  `testparam9` varchar(250) NOT NULL,
  `lastvalue` float NOT NULL default '0',
  `testinterval` int(10) unsigned NOT NULL default '0',
  `nextrunx` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`localtestid`),
  KEY `nodeid` (`nodeid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnlog`
--

DROP TABLE IF EXISTS `fnlog`;
CREATE TABLE `fnlog` (
  `logid` bigint(20) unsigned NOT NULL auto_increment,
  `postedx` bigint(20) unsigned NOT NULL default '0',
  `modid` varchar(32) NOT NULL,
  `catid` varchar(32) NOT NULL,
  `username` varchar(64) NOT NULL,
  `loglevel` int(11) NOT NULL default '1',
  `logevent` varchar(250) NOT NULL,
  PRIMARY KEY  (`logid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnnalink`
--

DROP TABLE IF EXISTS `fnnalink`;
CREATE TABLE `fnnalink` (
  `nalid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL,
  `aaid` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`nalid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnnode`
--

DROP TABLE IF EXISTS `fnnode`;
CREATE TABLE `fnnode` (
  `nodeid` varchar(64) NOT NULL,
  `nodename` varchar(128) NOT NULL,
  `nodedesc` varchar(254) NOT NULL,
  `hostname` varchar(254) NOT NULL,
  `nodeenabled` tinyint(1) NOT NULL default '0',
  `pingtest` tinyint(1) NOT NULL default '0',
  `pingfatal` tinyint(1) NOT NULL default '0',
  `alertlevel` int(11) NOT NULL default '-1',
  `nodeicon` varchar(64) NOT NULL,
  `weight` int(10) unsigned NOT NULL default '0',
  `nodealert` tinyint(1) NOT NULL default '1',
  `scheduleid` bigint(20) NOT NULL default '0',
  `lastrunx` bigint(20) unsigned NOT NULL default '0',
  `testinterval` int(10) unsigned NOT NULL default '5',
  `nextrunx` bigint(20) unsigned NOT NULL default '0',
  `nsenabled` tinyint(1) NOT NULL default '0',
  `nsurl` varchar(254) NOT NULL,
  `nskey` varchar(128) NOT NULL,
  `nspullenabled` tinyint(1) NOT NULL default '0',
  `nspushenabled` tinyint(1) NOT NULL default '0',
  `nspuship` varchar(128) NOT NULL,
  `nsinterval` int(10) unsigned NOT NULL default '15',
  `nslastx` bigint(20) unsigned NOT NULL default '0',
  `nsnextx` bigint(20) unsigned NOT NULL default '0',
  `nspullalert` tinyint(1) NOT NULL default '0',
  `nsfreshpush` tinyint(1) NOT NULL default '0',
  `masterid` varchar(64) NOT NULL,
  `masterjustping` tinyint(1) NOT NULL default '1',
  `ulink0` tinyint(1) NOT NULL default '0',
  `ulink0_title` varchar(254) NOT NULL default 'VNC',
  `ulink0_url` varchar(254) NOT NULL default 'http://{HOSTNAME}:5800/',
  `ulink1` tinyint(1) NOT NULL default '0',
  `ulink1_title` varchar(254) NOT NULL default 'SSH',
  `ulink1_url` varchar(254) NOT NULL default 'ssh://{HOSTNAME}',
  `ulink2` tinyint(1) NOT NULL default '0',
  `ulink2_title` varchar(254) NOT NULL default 'Web',
  `ulink2_url` varchar(254) NOT NULL default 'http://{HOSTNAME}',
  PRIMARY KEY  (`nodeid`),
  KEY `masterid` (`masterid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnnstest`
--

DROP TABLE IF EXISTS `fnnstest`;
CREATE TABLE `fnnstest` (
  `nstestid` bigint(20) unsigned NOT NULL auto_increment,
  `nodeid` varchar(64) NOT NULL default '',
  `alertlevel` int(11) NOT NULL default '-1',
  `lastrunx` bigint(20) unsigned NOT NULL default '0',
  `testtype` varchar(128) NOT NULL default '',
  `testdesc` varchar(250) default NULL,
  `testrecord` tinyint(1) NOT NULL default '0',
  `simpleeval` tinyint(1) NOT NULL default '1',
  `testname` varchar(64) NOT NULL default '',
  `testenabled` tinyint(1) NOT NULL default '0',
  `lastvalue` varchar(128) NOT NULL default '',
  `testalerts` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`nstestid`),
  KEY `nodeid` (`nodeid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnrecord`
--

DROP TABLE IF EXISTS `fnrecord`;
CREATE TABLE `fnrecord` (
  `recordid` bigint(20) unsigned NOT NULL auto_increment,
  `testid` varchar(128) NOT NULL,
  `alertlevel` int(11) NOT NULL default '0',
  `testvalue` float NOT NULL default '0',
  `recordx` bigint(20) unsigned NOT NULL default '0',
  `nodeid` varchar(64) NOT NULL,
  PRIMARY KEY  (`recordid`),
  KEY `testid` (`testid`),
  KEY `recordx` (`recordx`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnreport`
--

DROP TABLE IF EXISTS `fnreport`;
CREATE TABLE `fnreport` (
  `reportid` bigint(20) unsigned NOT NULL auto_increment,
  `reportname` varchar(128) NOT NULL default '',
  `reporttests` text NOT NULL,
  PRIMARY KEY  (`reportid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnscheditem`
--

DROP TABLE IF EXISTS `fnscheditem`;
CREATE TABLE `fnscheditem` (
  `scheditemid` bigint(20) NOT NULL auto_increment,
  `scheduleid` bigint(20) NOT NULL default '0',
  `dayofweek` varchar(8) NOT NULL default '',
  `dayofmonth` int(11) NOT NULL default '0',
  `monthofyear` int(11) NOT NULL default '0',
  `year` int(11) NOT NULL default '0',
  `starthour` int(11) NOT NULL default '0',
  `startmin` int(11) NOT NULL default '0',
  `finishhour` int(11) NOT NULL default '23',
  `finishmin` int(11) NOT NULL default '59',
  PRIMARY KEY  (`scheditemid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnschedule`
--

DROP TABLE IF EXISTS `fnschedule`;
CREATE TABLE `fnschedule` (
  `scheduleid` bigint(20) unsigned NOT NULL auto_increment,
  `schedulename` varchar(128) NOT NULL default '',
  `defaultaction` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`scheduleid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnsession`
--

DROP TABLE IF EXISTS `fnsession`;
CREATE TABLE `fnsession` (
  `sessionid` bigint(20) unsigned NOT NULL auto_increment,
  `sessionkey` varchar(128) NOT NULL,
  `ipaddress` varchar(128) NOT NULL,
  `username` varchar(64) NOT NULL,
  `startx` bigint(20) unsigned NOT NULL default '0',
  `updatex` bigint(20) unsigned NOT NULL default '0',
  `userlevel` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sessionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fntestrun`
--

DROP TABLE IF EXISTS `fntestrun`;
CREATE TABLE `fntestrun` (
  `trid` bigint(20) unsigned NOT NULL auto_increment,
  `startx` bigint(20) unsigned NOT NULL default '0',
  `finishx` bigint(20) unsigned NOT NULL default '0',
  `routput` text NOT NULL,
  `fnode` varchar(64) NOT NULL,
  PRIMARY KEY  (`trid`),
  KEY `finishx` (`finishx`),
  KEY `fnode` (`fnode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnuser`
--

DROP TABLE IF EXISTS `fnuser`;
CREATE TABLE `fnuser` (
  `username` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `realname` varchar(128) NOT NULL,
  `userlevel` int(11) NOT NULL default '1',
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnview`
--

DROP TABLE IF EXISTS `fnview`;
CREATE TABLE `fnview` (
  `viewid` bigint(20) unsigned NOT NULL auto_increment,
  `vtitle` varchar(128) NOT NULL default '',
  `vstyle` varchar(32) NOT NULL default '',
  `vpublic` tinyint(1) NOT NULL default '0',
  `vclick` varchar(32) NOT NULL default '',
  `vrefresh` int(11) NOT NULL default '0',
  `vlinkv` bigint(20) unsigned NOT NULL default '0',
  `vcolumns` smallint(6) NOT NULL default '0',
  `vcolon` tinyint(1) NOT NULL default '1',
  `vdashes` tinyint(1) NOT NULL default '1',
  `vtimeago` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`viewid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `fnviewitem`
--

DROP TABLE IF EXISTS `fnviewitem`;
CREATE TABLE `fnviewitem` (
  `viewitemid` bigint(20) unsigned NOT NULL auto_increment,
  `viewid` bigint(20) unsigned NOT NULL default '0',
  `itype` varchar(128) NOT NULL default '',
  `ioption` varchar(250) NOT NULL default '',
  `icolour` tinyint(1) NOT NULL default '1',
  `itextstatus` tinyint(1) NOT NULL default '0',
  `idetail` smallint(5) unsigned NOT NULL default '0',
  `iweight` int(10) unsigned NOT NULL default '0',
  `isize` smallint(6) NOT NULL default '0',
  `igraphic` smallint(6) NOT NULL default '0',
  `iname` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`viewitemid`),
  KEY `viewid` (`viewid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

