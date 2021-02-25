-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.6.10 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table lordsmobile.access_logs
CREATE TABLE IF NOT EXISTS `access_logs` (
  `Script` varchar(250) DEFAULT NULL,
  `URI` varchar(250) DEFAULT NULL,
  `IP` varchar(250) DEFAULT NULL,
  `Stamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.guilds_hidden
CREATE TABLE IF NOT EXISTS `guilds_hidden` (
  `Name` varchar(50) DEFAULT NULL,
  `EndStamp` int(11) DEFAULT NULL,
  `RequestCount` int(11) DEFAULT NULL,
  `IPs` longtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.guild_hives
CREATE TABLE IF NOT EXISTS `guild_hives` (
  `k` int(11) DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `guild` varchar(250) DEFAULT NULL,
  `radius` int(11) DEFAULT NULL,
  `HiveCastles` int(11) DEFAULT NULL,
  `TotalCastles` int(11) DEFAULT NULL,
  `HiveMight` int(11) DEFAULT NULL,
  `TotalMight` int(11) DEFAULT NULL,
  `MaxPLevel` int(11) DEFAULT NULL,
  `AvgPLevel` int(11) DEFAULT NULL,
  `AvgCastleLevel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.guild_hives_multi
CREATE TABLE IF NOT EXISTS `guild_hives_multi` (
  `Gname` varchar(50) DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `might` int(11) DEFAULT NULL,
  `kills` int(11) DEFAULT NULL,
  `plevel` int(11) DEFAULT NULL,
  `clevel` int(11) DEFAULT NULL,
  `pcount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.monsters
CREATE TABLE IF NOT EXISTS `monsters` (
  `rowid` int(11) DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `mtype` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `lastupdated` int(11) DEFAULT NULL,
  UNIQUE KEY `Index 1` (`rowid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.players
CREATE TABLE IF NOT EXISTS `players` (
  `RowId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `k` int(11) DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Guild` varchar(50) DEFAULT NULL,
  `Kills` int(11) DEFAULT NULL,
  `Might` int(11) DEFAULT NULL,
  `LastUpdated` int(11) DEFAULT NULL,
  `innactive` int(11) DEFAULT NULL,
  `HasPrisoners` int(11) DEFAULT NULL,
  `VIP` int(11) DEFAULT NULL,
  `GuildRank` int(11) DEFAULT NULL,
  `PLevel` int(11) DEFAULT NULL,
  `CastleLevel` int(11) DEFAULT NULL,
  UNIQUE KEY `rowid` (`RowId`),
  KEY `k` (`k`),
  KEY `x` (`x`),
  KEY `y` (`y`)
) ENGINE=InnoDB AUTO_INCREMENT=1018274 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.players_archive
CREATE TABLE IF NOT EXISTS `players_archive` (
  `rowid` int(11) DEFAULT NULL,
  `k` int(11) DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Guild` varchar(50) DEFAULT NULL,
  `Kills` int(11) DEFAULT NULL,
  `Might` int(11) DEFAULT NULL,
  `LastUpdated` int(11) DEFAULT NULL,
  `innactive` int(11) DEFAULT NULL,
  `HasPrisoners` int(11) DEFAULT NULL,
  `VIP` int(11) DEFAULT NULL,
  `GuildRank` int(11) DEFAULT NULL,
  `PLevel` int(11) DEFAULT NULL,
  `CastleLevel` int(11) DEFAULT NULL,
  UNIQUE KEY `Index 1` (`rowid`),
  KEY `Index 2` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.players_hidden
CREATE TABLE IF NOT EXISTS `players_hidden` (
  `Name` varchar(50) DEFAULT NULL,
  `EndStamp` int(11) DEFAULT NULL,
  `RequestCount` int(11) DEFAULT NULL,
  `IPs` longtext,
  KEY `Index 1` (`Name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.player_renames
CREATE TABLE IF NOT EXISTS `player_renames` (
  `Name1` varchar(50) DEFAULT NULL,
  `Name2` varchar(50) DEFAULT NULL,
  `NewNameSeenAt` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lordsmobile.resource_nodes
CREATE TABLE IF NOT EXISTS `resource_nodes` (
  `rowid` int(11) DEFAULT NULL,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `rtype` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `max_now` int(11) DEFAULT NULL,
  `playername` varchar(50) DEFAULT NULL,
  `lastupdated` int(11) DEFAULT NULL,
  UNIQUE KEY `Index 1` (`rowid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
