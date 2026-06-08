-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 04:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `registerdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `usernamepass`
--

CREATE TABLE `usernamepass` (
  `ID` int(11) NOT NULL,
  `UserName` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usernamepass`
--

INSERT INTO `usernamepass` (`ID`, `UserName`, `Email`, `Password`) VALUES
(19, 'fiona', 'fiona@gmail.com', '$2y$10$8OgxJBDXwjryJbstmvvhXe..6TLr1ov8yM.97Pib0an1S3f4XqsgW'),
(30, 'fontillas', 'fontillasgreencemark@gmail.com', '$2y$10$9/gvjDw.X.KRZ/nlaMgAnOqcwKLIOuyfRr.tdOuFI.xWEthAIh1nC'),
(31, 'Greence Mark P. Fontillas', 'project@gmail.com', '$2y$10$Dg1yALBWCz4womkOvk33suDvwY3VasTk8hgLOh5nGS7FLIG0M2yVO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `usernamepass`
--
ALTER TABLE `usernamepass`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `usernamepass`
--
ALTER TABLE `usernamepass`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
ALTER TABLE usernamepass
ADD SchoolID VARCHAR(50),
ADD CourseSection VARCHAR(50);