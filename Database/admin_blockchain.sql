-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: 26-Dez-2021 às 11:58
-- Versão do servidor: 5.7.36-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_blockchain`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_address`
--

CREATE TABLE `tbl_address` (
  `_id` int(11) NOT NULL,
  `_address` varchar(64) NOT NULL,
  `_confirmations` tinyint(4) NOT NULL DEFAULT '6',
  `_webhook` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_txid`
--

CREATE TABLE `tbl_txid` (
  `_id` int(11) NOT NULL,
  `_txid` varchar(96) NOT NULL,
  `_address` int(11) NOT NULL,
  `_amount` decimal(24,8) NOT NULL,
  `_height` mediumint(9) NOT NULL,
  `_side` tinyint(1) NOT NULL COMMENT '0 = receive, 1 = send',
  `_executed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_address`
--
ALTER TABLE `tbl_address`
  ADD PRIMARY KEY (`_id`);

--
-- Indexes for table `tbl_txid`
--
ALTER TABLE `tbl_txid`
  ADD PRIMARY KEY (`_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_address`
--
ALTER TABLE `tbl_address`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `tbl_txid`
--
ALTER TABLE `tbl_txid`
  MODIFY `_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
