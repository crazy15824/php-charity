-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2021 at 07:01 PM
-- Server version: 10.4.19-MariaDB
-- PHP Version: 8.0.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gold`
--

-- --------------------------------------------------------

--
-- Table structure for table `addr`
--

CREATE TABLE `addr` (
  `AddrID` int(11) NOT NULL,
  `Remote_Addr` varchar(15) NOT NULL,
  `Views` int(11) NOT NULL DEFAULT 1,
  `AgentID_Addr` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `agent`
--

CREATE TABLE `agent` (
  `AgentID` int(11) NOT NULL,
  `Agent` varchar(255) NOT NULL,
  `OS` varchar(30) NOT NULL,
  `Browser` varchar(30) NOT NULL,
  `AddrID_Agent` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cookies`
--

CREATE TABLE `cookies` (
  `CookieID` int(11) NOT NULL,
  `Auth` tinyint(1) NOT NULL,
  `Cookie` varchar(32) NOT NULL,
  `CookieDate` datetime NOT NULL DEFAULT current_timestamp(),
  `UserID_Cookies` int(11) NOT NULL,
  `AddrID_Cookies` int(11) DEFAULT NULL,
  `AgentID_Cookies` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gold_log`
--

CREATE TABLE `gold_log` (
  `TransID` int(11) NOT NULL,
  `GiverID` int(11) DEFAULT NULL,
  `TakerID` int(11) DEFAULT NULL,
  `BankLog` int(11) DEFAULT NULL,
  `GiverGold` int(11) DEFAULT NULL,
  `TakerGold` int(11) DEFAULT NULL,
  `TheGive` int(11) NOT NULL,
  `GiverCredit` int(11) DEFAULT NULL,
  `TakerCredit` int(11) DEFAULT NULL,
  `TransDate` datetime NOT NULL DEFAULT current_timestamp(),
  `Org` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `login_err`
--

CREATE TABLE `login_err` (
  `LoginID` int(11) NOT NULL,
  `Username` varchar(11) NOT NULL,
  `Password` varchar(32) NOT NULL,
  `Counter` tinyint(4) NOT NULL,
  `time` int(11) NOT NULL,
  `AddrID_Err` int(11) DEFAULT NULL,
  `AgentID_Err` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(16) NOT NULL,
  `Father_ID` int(11) DEFAULT NULL,
  `Mother_ID` int(11) DEFAULT NULL,
  `Password` varchar(32) NOT NULL,
  `Phone` varchar(11) DEFAULT NULL,
  `Comment` varchar(32) NOT NULL,
  `Admin` tinyint(1) NOT NULL DEFAULT 0,
  `Bank` int(11) NOT NULL,
  `Gold` int(11) NOT NULL,
  `Share` int(11) NOT NULL,
  `Credit` int(11) NOT NULL,
  `Bonus` int(11) NOT NULL,
  `Last_Take` datetime DEFAULT NULL,
  `Ask_Date` datetime DEFAULT NULL,
  `TakeMyDebit` tinyint(1) NOT NULL,
  `ShowMe` tinyint(1) NOT NULL,
  `Best` tinyint(1) NOT NULL,
  `National_ID` varchar(14) DEFAULT NULL,
  `Full_Name` varchar(70) NOT NULL,
  `First_Name` varchar(20) NOT NULL,
  `Surname` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addr`
--
ALTER TABLE `addr`
  ADD PRIMARY KEY (`AddrID`),
  ADD KEY `AgentID_Addr` (`AgentID_Addr`);

--
-- Indexes for table `agent`
--
ALTER TABLE `agent`
  ADD PRIMARY KEY (`AgentID`),
  ADD KEY `AddrID_Agent` (`AddrID_Agent`);

--
-- Indexes for table `cookies`
--
ALTER TABLE `cookies`
  ADD PRIMARY KEY (`CookieID`),
  ADD KEY `UserID_Cookies` (`UserID_Cookies`),
  ADD KEY `AgentID_Cookies` (`AgentID_Cookies`),
  ADD KEY `AddrID_Cookies` (`AddrID_Cookies`);

--
-- Indexes for table `gold_log`
--
ALTER TABLE `gold_log`
  ADD PRIMARY KEY (`TransID`),
  ADD KEY `GiverID` (`GiverID`,`TakerID`),
  ADD KEY `TakerID` (`TakerID`);

--
-- Indexes for table `login_err`
--
ALTER TABLE `login_err`
  ADD PRIMARY KEY (`LoginID`),
  ADD KEY `AgentID_Err` (`AgentID_Err`),
  ADD KEY `AddrID_Err` (`AddrID_Err`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Phone` (`Phone`),
  ADD UNIQUE KEY `National_ID` (`National_ID`),
  ADD KEY `Father_ID` (`Father_ID`),
  ADD KEY `Mother_ID` (`Mother_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addr`
--
ALTER TABLE `addr`
  MODIFY `AddrID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `agent`
--
ALTER TABLE `agent`
  MODIFY `AgentID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cookies`
--
ALTER TABLE `cookies`
  MODIFY `CookieID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gold_log`
--
ALTER TABLE `gold_log`
  MODIFY `TransID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_err`
--
ALTER TABLE `login_err`
  MODIFY `LoginID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addr`
--
ALTER TABLE `addr`
  ADD CONSTRAINT `addr_ibfk_1` FOREIGN KEY (`AgentID_Addr`) REFERENCES `agent` (`AgentID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `agent`
--
ALTER TABLE `agent`
  ADD CONSTRAINT `agent_ibfk_1` FOREIGN KEY (`AddrID_Agent`) REFERENCES `addr` (`AddrID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `cookies`
--
ALTER TABLE `cookies`
  ADD CONSTRAINT `cookies_ibfk_1` FOREIGN KEY (`UserID_Cookies`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cookies_ibfk_2` FOREIGN KEY (`AgentID_Cookies`) REFERENCES `agent` (`AgentID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `cookies_ibfk_3` FOREIGN KEY (`AddrID_Cookies`) REFERENCES `addr` (`AddrID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `gold_log`
--
ALTER TABLE `gold_log`
  ADD CONSTRAINT `gold_log_ibfk_1` FOREIGN KEY (`GiverID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gold_log_ibfk_2` FOREIGN KEY (`TakerID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `login_err`
--
ALTER TABLE `login_err`
  ADD CONSTRAINT `login_err_ibfk_1` FOREIGN KEY (`AgentID_Err`) REFERENCES `agent` (`AgentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `login_err_ibfk_2` FOREIGN KEY (`AddrID_Err`) REFERENCES `addr` (`AddrID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`Father_ID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`Mother_ID`) REFERENCES `users` (`UserID`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
