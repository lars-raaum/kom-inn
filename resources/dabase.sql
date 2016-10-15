-- phpMyAdmin SQL Dump
-- version 4.4.15.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 15, 2016 at 02:13 AM
-- Server version: 5.5.44-MariaDB
-- PHP Version: 5.5.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `kominn`
--
CREATE DATABASE IF NOT EXISTS `kominn` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `kominn`;

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

DROP TABLE IF EXISTS `guests`;
CREATE TABLE IF NOT EXISTS `guests` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `food_concerns` text COLLATE utf8_bin,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

DROP TABLE IF EXISTS `hosts`;
CREATE TABLE IF NOT EXISTS `hosts` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(11) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE IF NOT EXISTS `people` (
  `id` int(10) unsigned NOT NULL,
  `email` varchar(256) COLLATE utf8_bin NOT NULL,
  `name` varchar(128) COLLATE utf8_bin NOT NULL,
  `phone` VARCHAR(16) COLLATE utf8_bin NOT NULL,
  `gender` enum('n/a','male','female') COLLATE utf8_bin NOT NULL DEFAULT 'n/a',
  `age` tinyint(4) NOT NULL,
  `children` tinyint(4) NOT NULL,
  `adults_m` tinyint(4) NOT NULL,
  `adults_f` tinyint(4) NOT NULL,
  `origin` varchar(128) COLLATE utf8_bin NOT NULL,
  `zipcode` varchar(10) COLLATE utf8_bin NOT NULL,
  `address` varchar(256) COLLATE utf8_bin NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `freetext` text COLLATE utf8_bin,
  `loc_long` float NULL DEFAULT NULL,
  `loc_lat` float NULL DEFAULT NULL,
  `visits` tinyint(4) NOT NULL DEFAULT '0',
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hosts`
--
ALTER TABLE `hosts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userId` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`(255));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `people`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;