-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2020 at 12:30 PM
-- Server version: 10.1.34-MariaDB
-- PHP Version: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `findhandyman`
--

DELIMITER $$
--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `BUDGET_SCORE` (`budget` FLOAT, `hstart_price` FLOAT, `hend_price` FLOAT) RETURNS FLOAT NO SQL
BEGIN

DECLARE score FLOAT;

IF budget < hstart_price THEN
	SET score = 0;
ELSE
	SET score = (budget - hstart_price) / budget;
END IF;

return score;

END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `DATE_SCORE` (`job_date` DATE, `hday_name` VARCHAR(10)) RETURNS FLOAT NO SQL
BEGIN

DECLARE score FLOAT;

IF CONCAT(DAYNAME(job_date),'s') = hday_name THEN
	SET score = 1;
ELSE
	SET score = 0;
END IF;
    
RETURN score;
    
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `DISTANCE_SCORE` (`lat1` FLOAT, `lng1` FLOAT, `lat2` FLOAT, `lng2` FLOAT) RETURNS FLOAT NO SQL
BEGIN

DECLARE a FLOAT;
DECLARE distance FLOAT;

SET distance  =  HAVERSINE(lat1, lng1, lat2, lng2 );

IF distance <= 1 THEN
	SET a = 1;
ELSE
	SET a = 1 / distance;
END IF;
   
RETURN a;

END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `HAVERSINE` (`lat1` FLOAT, `lng1` FLOAT, `lat2` FLOAT, `lng2` FLOAT) RETURNS FLOAT NO SQL
BEGIN
    RETURN (6371 * acos(cos(radians(lat1)) * 
                    cos(radians(lat2)) * 
                    cos(radians(lng2) - radians(lng1)) + 
                    sin(radians(lat1)) * sin(radians(lat2))));
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `MATCH_SCORE` (`hday_name` VARCHAR(10), `hstart_time` TIME, `hend_time` TIME, `haddress_lat` FLOAT, `haddress_lng` FLOAT, `hstart_price` DOUBLE, `hend_price` DOUBLE, `jdate` DATE, `jbudget` DOUBLE, `jaddress_lat` FLOAT, `jaddress_lng` FLOAT, `jtime` TIME) RETURNS FLOAT NO SQL
BEGIN

    DECLARE d DECIMAL(10,9);
    DECLARE b DECIMAL(10,9);
    DECLARE a DECIMAL(10,9);
    DECLARE t DECIMAL(10,9);
    DECLARE score DECIMAL(10,9);

    SET d = DATE_SCORE(jdate, hday_name);

    SET b = BUDGET_SCORE(jbudget, hstart_price, hend_price);

	SET a = DISTANCE_SCORE(jaddress_lat, jaddress_lng, haddress_lat, haddress_lng);

    SET t = TIME_SCORE(jtime, hstart_time, hend_time);

    SET score = ( d*(t+4) + (b*3) + (a*2));

    RETURN score;

END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `TIME_SCORE` (`job_time` TIME, `hstart_time` TIME, `hend_time` TIME) RETURNS FLOAT NO SQL
BEGIN

DECLARE score FLOAT;

IF job_time < hstart_time OR job_time > hend_time THEN
	SET score = 0;
ELSE
	SET score = (hend_time - job_time) / job_time;
END IF;

RETURN score;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `handyman_services`
--

CREATE TABLE `handyman_services` (
  `handyman_id` varchar(250) NOT NULL,
  `service_id` int(11) NOT NULL,
  `start_price` double NOT NULL,
  `end_price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `handyman_services`
--

INSERT INTO `handyman_services` (`handyman_id`, `service_id`, `start_price`, `end_price`) VALUES
('ByKHLbjNFtclcJuZDSRgWg9NOu22', 4, 600, 1000),
('ByKHLbjNFtclcJuZDSRgWg9NOu22', 13, 200, 500),
('cGgV5SV8VhabXe4d6AhxqKcYsG02', 9, 250, 800),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 2, 500, 1000),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 7, 400, 800),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 14, 300, 600),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 14, 333, 999),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 15, 222, 888),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 8, 300, 650),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 9, 500, 1000),
('rHXUVW5zi5QuZnL5LZ18Kdb3gXc2', 1, 100, 1000),
('TrJerfYmH0VjE6XspnJg8Eue7Ig1', 4, 150, 500),
('u8e2IgloVXNXAxsqwgv7tRjAWbC3', 1, 333, 999),
('Wjfhkdsjdhfskdjhsdl', 14, 333, 999),
('Wjfhkdsjdhfskdjhsdl', 15, 222, 888),
('WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 2, 222, 555),
('WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 3, 100, 250),
('zsI09y7W6NXDdidtj39speLEBrQ2', 7, 600, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `handyman_working_days_time`
--

CREATE TABLE `handyman_working_days_time` (
  `handyman_id` varchar(250) NOT NULL,
  `day_name` varchar(10) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `handyman_working_days_time`
--

INSERT INTO `handyman_working_days_time` (`handyman_id`, `day_name`, `start_time`, `end_time`) VALUES
('ByKHLbjNFtclcJuZDSRgWg9NOu22', 'Sundays', '10:36:00', '14:36:00'),
('ByKHLbjNFtclcJuZDSRgWg9NOu22', 'Thursdays', '14:46:00', '09:46:00'),
('cGgV5SV8VhabXe4d6AhxqKcYsG02', 'Mondays', '08:24:33', '17:24:33'),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 'Thursdays', '09:15:13', '13:15:13'),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 'Tuesdays', '10:15:13', '18:15:13'),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 'Wednesdays', '11:15:24', '16:15:13'),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 'Saturdays', '13:34:04', '18:34:04'),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 'Sundays', '08:34:04', '16:34:04'),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 'Mondays', '09:03:24', '15:04:20'),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 'Sundays', '09:03:24', '12:03:24'),
('rHXUVW5zi5QuZnL5LZ18Kdb3gXc2', 'Saturdays', '08:46:00', '16:46:00'),
('TrJerfYmH0VjE6XspnJg8Eue7Ig1', 'Thursdays', '17:18:50', '19:18:50'),
('u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'Saturdays', '10:00:45', '17:00:45'),
('Wjfhkdsjdhfskdjhsdl', 'Saturdays', '13:19:08', '12:19:08'),
('Wjfhkdsjdhfskdjhsdl', 'Sundays', '13:19:08', '12:19:08'),
('WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 'Saturdays', '05:15:00', '13:15:00'),
('zsI09y7W6NXDdidtj39speLEBrQ2', 'Saturdays', '08:21:43', '17:21:43'),
('zsI09y7W6NXDdidtj39speLEBrQ2', 'Sundays', '11:21:43', '15:21:43');

-- --------------------------------------------------------

--
-- Table structure for table `handymen_stripe_account`
--

CREATE TABLE `handymen_stripe_account` (
  `handyman_id` varchar(200) NOT NULL,
  `stripe_account_id` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `handymen_stripe_account`
--

INSERT INTO `handymen_stripe_account` (`handyman_id`, `stripe_account_id`) VALUES
('ByKHLbjNFtclcJuZDSRgWg9NOu22', 'acct_1GYAhRLDLyCSx2VL'),
('cGgV5SV8VhabXe4d6AhxqKcYsG02', 'acct_1GYAhRLDLyCSx2VL'),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 'acct_1GYAhRLDLyCSx2VL'),
('kT5funip2vbD0M5tSyRG3K5ZVuG2', 'acct_1GYAhRLDLyCSx2VL'),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 'acct_1GYAhRLDLyCSx2VL'),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 'acct_1GYAhRLDLyCSx2VL'),
('rHXUVW5zi5QuZnL5LZ18Kdb3gXc2', 'acct_1GYAhRLDLyCSx2VL'),
('TrJerfYmH0VjE6XspnJg8Eue7Ig1', 'acct_1GYAhRLDLyCSx2VL'),
('u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'acct_1GYAhRLDLyCSx2VL'),
('Wjfhkdsjdhfskdjhsdl', 'acct_1GYAhRLDLyCSx2VL'),
('WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 'acct_1GYAhRLDLyCSx2VL'),
('zsI09y7W6NXDdidtj39speLEBrQ2', 'acct_1GYAhRLDLyCSx2VL');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `address` varchar(250) NOT NULL,
  `address_lat` decimal(11,7) NOT NULL,
  `address_lng` decimal(11,7) NOT NULL,
  `budget` double DEFAULT NULL,
  `job_giver` varchar(250) NOT NULL,
  `online_payment_made` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `service_id`, `title`, `description`, `date`, `time`, `address`, `address_lat`, `address_lng`, `budget`, `job_giver`, `online_payment_made`) VALUES
(1, 1, 'test title', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.', '2019-12-20', '13:05:02', 'Britannia, Mauritius', '-20.4503000', '57.5575000', 500, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(2, 1, 'test title 2', 'Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc,', '2019-12-19', '15:05:02', 'Britannia, Mauritius', '-20.4503000', '57.5575000', 300, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(3, 2, 'test job number 3', 'some random  description about the test job', '2019-12-21', '22:03:06', 'Reduit, Mauritius', '-20.2300000', '57.4984000', 5300, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(4, 5, 'Integer tincidunt. Cras dapibus', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium.', '2019-12-30', '09:30:00', 'Grand Bassin, Mauritius', '-20.4177262', '57.4935429', 500, 'onXBCDg72BYb9lFIXJvR7YHUejT2', NULL),
(5, 7, 'Phasellus viverra nulla ut metus varius laoreet.', 'sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus.', '2019-11-30', '09:30:00', 'Grand Bassin, Mauritius', '-20.4177262', '57.4935429', 500, 'onXBCDg72BYb9lFIXJvR7YHUejT2', NULL),
(6, 12, 'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo.', 'Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.', '2019-12-25', '09:30:00', 'Grand Bassin, Mauritius', '-20.4177262', '57.4935429', 950, 'onXBCDg72BYb9lFIXJvR7YHUejT2', NULL),
(7, 12, 'Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo.', 'Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum.', '2019-12-25', '09:30:00', 'Grand Bassin, Mauritius', '-20.4177262', '57.4935429', 950, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(8, 13, 'Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem.', 'Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna.', '2019-12-30', '09:30:00', 'Riviere des Anguilles, Mauritius', '-20.4904850', '57.5585465', 1500, 'onXBCDg72BYb9lFIXJvR7YHUejT2', NULL),
(23, 1, 'Fix my washing machine', 'I think that there\'s a problem with the pipe', '2020-01-03', '02:58:06', 'Britannia, Mauritius', '-20.4503000', '57.5575000', 500, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(24, 3, 'Place a TV on my wall', 'Place the TV on my wall ', '2020-01-03', '03:31:18', 'RÃ©duit, Mauritius', '-20.2300000', '57.4984000', 500, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(25, 9, 'Test match algorithm', 'a job posted only to test the first prototype match algorithm implemented', '2019-12-23', '10:00:11', 'Britannia, Mauritius', '-20.4502800', '57.5575000', 1000, 'onXBCDg72BYb9lFIXJvR7YHUejT2', NULL),
(26, 14, 'Install a light switch', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus ', '2020-01-07', '17:10:10', 'Bon Accueil, Mauritius', '-20.1745003', '57.6703820', 200, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(27, 14, 'Fix electrical outlet', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla eget tortor faucibus, congue felis vitae, tristique tellus. Integer rutrum in est quis blandit. Vestibulum condimentum eros auctor dui volutpat, in vehicula orci ultrices. Aliquam at orci at metus congue porttitor.<br />\r\n<br />\r\n Donec a efficitur ipsum. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus at posuere dolor, ac auctor sapien. Sed iaculis nibh ac metus cursus, nec semper est iaculis.\r\nCurabitur et volutpat sapien, a interdum purus.', '2020-01-18', '09:25:17', 'RÃ©duit, Mauritius', '-20.2300000', '57.4984000', 300, '8YaIg1j26dWMb9VbDNOo20q7lnM2', 1),
(29, 1, 'Fix a table', 'The leg of the table broke', '2020-02-10', '17:05:33', 'Riviere des Anguilles, Mauritius', '-20.4904850', '57.5585465', 500, 'hwleWSjYm9TecLHSTneavmkptLp1', NULL),
(30, 2, 'A job for testing', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec,', '2020-02-15', '16:20:31', 'Britannia, Mauritius', '-20.4503000', '57.5575000', 300, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(31, 1, 'Test job to reschedule', 'This is a job for testing the reshcedule feature', '2020-02-23', '16:27:43', 'Surinam, Mauritius', '-20.5122913', '57.5096834', 200, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(32, 2, 'Test notification system', 'this job is for the purpose of testing the notification system. ', '2020-03-18', '15:23:39', 'Britannia, Mauritius', '-20.4503000', '57.5575000', 200, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(35, 1, 'Test job for new job page code design', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec', '2020-03-25', '18:04:53', 'bus stop, Old Savanne Rd, Britannia, Mauritius', '-20.4487138', '57.5595022', 420, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(36, 1, 'test job page 2', 'another test ', '2020-03-24', '14:19:24', 'Britannia, Mauritius', '-20.4503000', '57.5575000', 440, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL),
(37, 1, 'ewewe', 'sczxc', '2020-03-24', '14:27:18', 'RÃ©duit, Mauritius', '-20.2300000', '57.4984000', 111, '8YaIg1j26dWMb9VbDNOo20q7lnM2', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_pictures`
--

CREATE TABLE `job_pictures` (
  `job_id` int(11) NOT NULL,
  `picture` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `job_pictures`
--

INSERT INTO `job_pictures` (`job_id`, `picture`) VALUES
(23, '23_0.jpg'),
(23, '23_1.jpg'),
(24, '24_0.jpg'),
(27, '27_0.jpg'),
(32, '32_0.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `job_status`
--

CREATE TABLE `job_status` (
  `job_id` int(11) NOT NULL,
  `handyman_id` varchar(250) DEFAULT NULL,
  `status` varchar(50) NOT NULL COMMENT 'posted, booked, ongoing, completed, cancelled, reschedule',
  `job_cancelled_by` varchar(8) DEFAULT NULL,
  `reschedule_date` date DEFAULT NULL,
  `reschedule_time` time DEFAULT NULL,
  `rescheduled_by` varchar(8) DEFAULT NULL,
  `reschedule_reason` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `job_status`
--

INSERT INTO `job_status` (`job_id`, `handyman_id`, `status`, `job_cancelled_by`, `reschedule_date`, `reschedule_time`, `rescheduled_by`, `reschedule_reason`) VALUES
(1, 'cGgV5SV8VhabXe4d6AhxqKcYsG02', 'completed', NULL, NULL, NULL, NULL, NULL),
(2, 'u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'completed', NULL, NULL, NULL, NULL, NULL),
(3, 'u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'completed', NULL, NULL, NULL, NULL, NULL),
(4, 'u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'completed', NULL, NULL, NULL, NULL, NULL),
(5, NULL, 'posted', NULL, NULL, NULL, NULL, NULL),
(6, 'zsI09y7W6NXDdidtj39speLEBrQ2', 'ongoing', NULL, NULL, NULL, NULL, NULL),
(7, 'WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 'completed', NULL, NULL, NULL, NULL, NULL),
(8, 'cGgV5SV8VhabXe4d6AhxqKcYsG02', 'completed', NULL, NULL, NULL, NULL, NULL),
(23, NULL, 'posted', NULL, NULL, NULL, NULL, NULL),
(24, NULL, 'posted', NULL, NULL, NULL, NULL, NULL),
(25, NULL, 'posted', NULL, NULL, NULL, NULL, NULL),
(26, 'zsI09y7W6NXDdidtj39speLEBrQ2', 'completed', NULL, NULL, NULL, NULL, NULL),
(27, 'nwBfWEivmoTyZGfAywX6cEAg34g1', 'completed', NULL, NULL, NULL, NULL, NULL),
(29, 'rHXUVW5zi5QuZnL5LZ18Kdb3gXc2', 'cancelled', 'client', NULL, NULL, NULL, NULL),
(30, 'WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 'ongoing', NULL, NULL, NULL, NULL, NULL),
(31, 'rHXUVW5zi5QuZnL5LZ18Kdb3gXc2', 'ongoing', NULL, '2020-02-23', '16:27:43', 'client', 'Test reschedule accept button'),
(32, NULL, 'posted', NULL, NULL, NULL, NULL, NULL),
(35, 'u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'cancelled', NULL, '2020-03-28', '13:14:21', 'client', 'testsss'),
(36, 'u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'cancelled', 'client', NULL, NULL, NULL, NULL),
(37, 'u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'cancelled', 'client', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `service_description` varchar(250) NOT NULL,
  `service_name_fr` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  `service_description_fr` varchar(250) CHARACTER SET utf8mb4 DEFAULT NULL,
  `service_picture` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `service_description`, `service_name_fr`, `service_description_fr`, `service_picture`) VALUES
(1, 'Home Repairs', 'Jacks (and Jills) of all trades can handle most of your minor home repairs.', 'Reparations domiciliaires', 'Jacks (et Jills) de tous les metiers peuvent gerer la plupart de vos reparations mineures a domicile.', NULL),
(2, 'Furniture Assembly', 'Have a new desk or bookcase to put together? Our Handymen can assemble any of your furniture - quickly and professionally.', 'Assemblage de meubles', 'Vous avez un nouveau bureau ou une nouvelle bibliotheque a assembler? Nos bricoleurs peuvent assembler n\'importe lequel de vos meubles - rapidement et professionnellement.', NULL),
(3, '\r\nTV Mounting', 'We can properly mount your TV on the wall and leave you happily clicking the remote.', '\r\nTV Mounting', 'Nous pouvons installer correctement votre televiseur sur le mur et vous laisser avec plaisir en cliquant sur la telecommande.', NULL),
(4, '\r\nSmart Home Installation', 'Hire a Handyman to take your home into the 21st century with smart home installation services.', 'Installation de maison intelligente', 'Embauchez un homme a tout faire pour faire entrer votre maison dans le 21e siecle avec des services d\'installation domestique intelligente.', NULL),
(5, 'Furniture Shopping & Assembly', 'We will pick up, deliver, and assemble any furniture you need.', 'Shopping et assemblage de meubles', 'Nous ramasserons, livrerons et assemblerons tous les meubles dont vous avez besoin.', NULL),
(6, 'Heavy Lifting', 'Get help moving heavy furniture without sacrificing your back. From couches to beds to dressers, our Handymen can lend a hand with the heavy lifting.', 'Levage de charges lourdes', 'Obtenez de l\'aide pour deplacer des meubles lourds sans sacrifier votre dos. Des canapes aux lits en passant par les commodes, nos bricoleurs peuvent preter main forte au levage de charges lourdes.', NULL),
(7, 'Install Air Conditioner', 'Hire a handyman to help install or remove air conditioners from your windows.', 'Installer climatiseur', 'Embaucher un bricoleur pour aider a installer ou a retirer les climatiseurs de vos fenetres.', NULL),
(8, 'Painting', 'Whether it\'s an entire house, a room, or a wall, we can get it painted to your satisfaction.', 'La peinture', 'Qu\'il s\'agisse d\'une maison entiere, d\'une piece ou d\'un mur, nous pouvons le faire peindre a votre satisfaction.', NULL),
(9, 'Plumbing', 'Seasoned handymen will clear out your plumbing problems.', 'Plomberie', 'Des bricoleurs elimineront vos problemes de plomberie.', NULL),
(10, 'Yard Work', 'We can clean up your yard and remove any yard waste or rubbish.', 'Travaux de jardinage', 'Nous pouvons nettoyer votre cour et enlever tout dechet.', NULL),
(11, 'Hang Pictures', 'Need help hanging all those pictures? Handymen will hang pictures and art, ensuring they are level and securely mounted.', 'Accrocher des photos', 'Besoin d\'aide pour accrocher toutes ces photos? Les bricoleurs accrocheront des images et des ?uvres d\'art, en s\'assurant qu\'ils sont de niveau et solidement montes.', NULL),
(12, 'Mounting', 'From TVs to shelves to artwork to lights, Handymen will make sure it\'s properly mounted and hung.', 'Montage', 'Des televiseurs aux etageres en passant par les illustrations et les lumieres, les bricoleurs s\'assureront qu\'ils sont correctement montes et suspendus.', NULL),
(13, 'Light Installation', 'From replacing light bulbs to installing light fixtures, capable Handymen can shed some light on your space.', 'Installation du systeme d\'eclairage', 'Du remplacement des ampoules a l\'installation de luminaires, des bricoleurs capables peuvent eclairer votre espace.', NULL),
(14, 'Electrical Work', 'Professional Handymen can handle electrical work for you.', 'Travaux electriques', 'Les bricoleurs professionnels peuvent gerer les travaux electriques pour vous.', NULL),
(15, 'Carpentry', 'Need something built? Expert can help with carpentry and construction work.', 'Charpenterie', 'Besoin de quelque chose de construit? Des experts peuvent vous aider dans les travaux de menuiserie et de construction.', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `picture` varchar(250) DEFAULT NULL,
  `bio` text,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `username`, `picture`, `bio`, `email`, `phone`, `type`) VALUES
('8YaIg1j26dWMb9VbDNOo20q7lnM2', 'Client1', '8YaIg1j26dWMb9VbDNOo20q7lnM2.jpg', 'Welcome to my profile, i am a client', 'client1@email.com', '52222222', 'client'),
('bx66nHUBExWiHPx3GIfDF3xYZ9d2', 'test2', 'default-profile.png', NULL, 'test2@email.com', '52222222', 'client'),
('ByKHLbjNFtclcJuZDSRgWg9NOu22', 'editedusername', 'default-profile.png', 'Edited my bio test', 'edittest@email.com', '52222222', 'handyman'),
('cGgV5SV8VhabXe4d6AhxqKcYsG02', 'handyman6', 'default-profile.png', 'Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec', 'handyman6@email.com', '52222222', 'handyman'),
('hwleWSjYm9TecLHSTneavmkptLp1', 'Client3', 'default-profile.png', 'Newly written bio', 'client3@email.com', '52222222', 'client'),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 'handymanEditTest', 'default-profile.png', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.', 'handymanEditTest@email.com', '52222222', 'handyman'),
('kT5funip2vbD0M5tSyRG3K5ZVuG2', 'newhandy', 'default-profile.png', NULL, 'newhandy@email.com', '123456', 'handyman'),
('lq1fyUEECIORNHzVr7GogEb6Qlk1', 'test', 'default-profile.png', NULL, 'test@email.com', '52222222', 'client'),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 'handyman1', 'default-profile.png', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit.', 'handyman1@email.com', '52222222', 'handyman'),
('onXBCDg72BYb9lFIXJvR7YHUejT2', 'client2', 'default-profile.png', NULL, 'client2@email.com', '52222222', 'client'),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 'handyman7', 'default-profile.png', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.', 'handyman7@email.com', '52222222', 'handyman'),
('rHXUVW5zi5QuZnL5LZ18Kdb3gXc2', 'Handyman8', 'default-profile.png', 'Hello there I am new to this app', 'handyman8@email.com', '12345678', 'handyman'),
('TrJerfYmH0VjE6XspnJg8Eue7Ig1', 'handyman4', 'default-profile.png', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec', 'handyman4@email.com', '52222222', 'handyman'),
('u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'handyman2', 'default-profile.png', 'Bio written here about handyman2', 'handyman2@email.com', '52222222', 'handyman'),
('Wjfhkdsjdhfskdjhsdl', 'testhandy', 'default-profile.png', 'someBio written....', NULL, NULL, 'handyman'),
('WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 'handyman3', 'default-profile.png', 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.', 'handyman3@email.com', '52222222', 'handyman'),
('zsI09y7W6NXDdidtj39speLEBrQ2', 'handyman5', 'default-profile.png', 'Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec', 'handyman5@email.com', '52222222', 'handyman');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `uid` varchar(250) NOT NULL,
  `address` varchar(250) NOT NULL,
  `lat` decimal(11,7) NOT NULL,
  `lng` decimal(11,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`uid`, `address`, `lat`, `lng`) VALUES
('8YaIg1j26dWMb9VbDNOo20q7lnM2', 'Britannia, Mauritius', '-20.4503000', '57.5575000'),
('8YaIg1j26dWMb9VbDNOo20q7lnM2', 'RÃ©duit, Mauritius', '-20.2300000', '57.4984000'),
('ByKHLbjNFtclcJuZDSRgWg9NOu22', 'Moka, Mauritius', '-20.2522924', '57.5881313'),
('cGgV5SV8VhabXe4d6AhxqKcYsG02', 'Rose Belle North Government School, M2, Rose Belle, Mauritius', '-20.4002800', '57.5966700'),
('hwleWSjYm9TecLHSTneavmkptLp1', 'RiviÃ¨re Des Anguilles Government School, B8 - La Baraque Road, Mauritius', '-20.4887799', '57.5619462'),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 'Curepipe Hotel, College Lane, Curepipe, Mauritius', '-20.3147200', '57.5202800'),
('KFbTKo4ScoVOimAxL8hAIHoyJjy1', 'Midlands Dam, Mauritius', '-20.3271243', '57.5991236'),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 'Grand Bassin, Mauritius', '-20.4177262', '57.4935429'),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 'Grand Bois, Mauritius', '-20.4188774', '57.5511401'),
('onXBCDg72BYb9lFIXJvR7YHUejT2', 'Riviere des Anguilles, Mauritius', '-20.4904850', '57.5585465'),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 'Riviere des Anguilles, Mauritius', '-20.4904850', '57.5585465'),
('r2ihNDp9LHh3BctK3pwBeK1XabD3', 'Riviere Des Galets, Mauritius', '-20.4914596', '57.4633231'),
('rHXUVW5zi5QuZnL5LZ18Kdb3gXc2', 'Trois Boutiques, Mauritius', '-20.4518429', '57.6473237'),
('TrJerfYmH0VjE6XspnJg8Eue7Ig1', 'Surinam, Mauritius', '-20.5122913', '57.5096834'),
('u8e2IgloVXNXAxsqwgv7tRjAWbC3', 'Port Louis, Mauritius', '-20.1637281', '57.5045331'),
('Wjfhkdsjdhfskdjhsdl', 'Grand Bay', '-20.4188774', '57.5511401'),
('Wjfhkdsjdhfskdjhsdl', 'Grand Bois', '-20.4188774', '57.5511401'),
('WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 'Tyack Lake Road, Mauritius', '-20.3000000', '57.5833300'),
('zsI09y7W6NXDdidtj39speLEBrQ2', 'Curepipe Market, Curepipe, Mauritius', '-20.3181726', '57.5232019');

-- --------------------------------------------------------

--
-- Table structure for table `user_ratings`
--

CREATE TABLE `user_ratings` (
  `uid` varchar(250) NOT NULL,
  `job_id` int(11) NOT NULL,
  `rater_id` varchar(250) NOT NULL,
  `rating` int(11) NOT NULL,
  `review` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_ratings`
--

INSERT INTO `user_ratings` (`uid`, `job_id`, `rater_id`, `rating`, `review`, `date`) VALUES
('8YaIg1j26dWMb9VbDNOo20q7lnM2', 1, 'cGgV5SV8VhabXe4d6AhxqKcYsG02', 4, 'Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus.', '2020-01-20 09:14:14'),
('8YaIg1j26dWMb9VbDNOo20q7lnM2', 7, 'WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 3, 'Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus.', '2020-01-20 09:14:14'),
('8YaIg1j26dWMb9VbDNOo20q7lnM2', 27, 'nwBfWEivmoTyZGfAywX6cEAg34g1', 5, 'Very helpful client.', '2020-01-20 09:14:14'),
('cGgV5SV8VhabXe4d6AhxqKcYsG02', 1, '8YaIg1j26dWMb9VbDNOo20q7lnM2', 5, 'Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus.', '2020-01-20 09:14:14'),
('cGgV5SV8VhabXe4d6AhxqKcYsG02', 8, 'onXBCDg72BYb9lFIXJvR7YHUejT2', 3, 'Duis leo. Sed fringilla mauris sit amet nibh.', '2020-01-20 09:14:14'),
('nwBfWEivmoTyZGfAywX6cEAg34g1', 27, '8YaIg1j26dWMb9VbDNOo20q7lnM2', 5, 'He went above and beyond the call of duty. All of his work was first class, quick and professional. I absolutely will use your service again. Kudos to you for having an awesome employee like him. I highly recommend your services to anyone.', '2020-01-20 14:15:25'),
('onXBCDg72BYb9lFIXJvR7YHUejT2', 8, 'cGgV5SV8VhabXe4d6AhxqKcYsG02', 5, 'Duis leo. Sed fringilla mauris sit amet nibh.', '2020-01-20 09:14:14'),
('WXA4vLLsgBbQ0X0FFL8Ifb9R8Rv2', 7, '8YaIg1j26dWMb9VbDNOo20q7lnM2', 2, 'Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus.', '2020-01-20 09:14:14'),
('zsI09y7W6NXDdidtj39speLEBrQ2', 26, '8YaIg1j26dWMb9VbDNOo20q7lnM2', 4, 'niceeee work!!!', '2020-02-17 17:36:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `handyman_services`
--
ALTER TABLE `handyman_services`
  ADD PRIMARY KEY (`handyman_id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `handyman_working_days_time`
--
ALTER TABLE `handyman_working_days_time`
  ADD PRIMARY KEY (`handyman_id`,`day_name`);

--
-- Indexes for table `handymen_stripe_account`
--
ALTER TABLE `handymen_stripe_account`
  ADD PRIMARY KEY (`handyman_id`,`stripe_account_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `job_giver` (`job_giver`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `job_pictures`
--
ALTER TABLE `job_pictures`
  ADD PRIMARY KEY (`job_id`,`picture`);

--
-- Indexes for table `job_status`
--
ALTER TABLE `job_status`
  ADD PRIMARY KEY (`job_id`),
  ADD KEY `job_status_ibfk_2` (`handyman_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`uid`,`address`);

--
-- Indexes for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD PRIMARY KEY (`uid`,`job_id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `rater_id` (`rater_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `handyman_services`
--
ALTER TABLE `handyman_services`
  ADD CONSTRAINT `handyman_services_ibfk_1` FOREIGN KEY (`handyman_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `handyman_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `handyman_working_days_time`
--
ALTER TABLE `handyman_working_days_time`
  ADD CONSTRAINT `handyman_working_days_time_ibfk_1` FOREIGN KEY (`handyman_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `handymen_stripe_account`
--
ALTER TABLE `handymen_stripe_account`
  ADD CONSTRAINT `handymen_stripe_account_ibfk_1` FOREIGN KEY (`handyman_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`job_giver`) REFERENCES `users` (`uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `jobs_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `job_pictures`
--
ALTER TABLE `job_pictures`
  ADD CONSTRAINT `job_pictures_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_status`
--
ALTER TABLE `job_status`
  ADD CONSTRAINT `job_status_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_status_ibfk_2` FOREIGN KEY (`handyman_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD CONSTRAINT `user_ratings_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_ratings_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`job_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_ratings_ibfk_3` FOREIGN KEY (`rater_id`) REFERENCES `users` (`uid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
