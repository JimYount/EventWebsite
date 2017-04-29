-- phpMyAdmin SQL Dump
-- version 4.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 18, 2017 at 03:49 AM
-- Server version: 5.6.32-78.1
-- PHP Version: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `thebark1_AshevilleEvents`
--

-- --------------------------------------------------------

--
-- Table structure for table `Asheville_Events`
--

CREATE TABLE IF NOT EXISTS `Asheville_Events` (
  `id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` int(20) NOT NULL,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `venue` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `eventType` varchar(40) COLLATE utf8_unicode_ci,
  `cost` double NOT NULL,
  `age` int(10) NOT NULL,
  `description` varchar(30) COLLATE utf8_unicode_ci NOT NULL
  `buyLink` varchar(200) COLLATE utf8_unicode_ci,
  `eventLink` varchar(200) COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Asheville_Events`
--
ALTER TABLE `Asheville_Events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Asheville_Events`
--
ALTER TABLE `Asheville_Events`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
