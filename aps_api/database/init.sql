-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 10, 2023 at 07:42 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aps2`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `create_application` (IN `app_date` DATE, IN `app_merchant_name` VARCHAR(256), IN `app_branch` INT(11), IN `app_product_id` INT(11), IN `app_business_type_id` INT(11), IN `username` VARCHAR(64), IN `app_email` VARCHAR(256))   BEGIN
START TRANSACTION;
	INSERT INTO `application`(
    `app_date`, `app_merchant_name`, `app_branch`, `app_product_id`, `app_business_type_id`, `app_contact_email`
    ) 
    VALUES (
    app_date, app_merchant_name, app_branch, app_product_id, app_business_type_id, app_email
    );
    
    SET @app_id = null;
    SELECT LAST_INSERT_ID() INTO @app_id;
    
    SET @user_id = null;
    SELECT `usr_id` INTO @user_id FROM `user` WHERE `usr_username`=username; 
    
    INSERT INTO `application_checklist`(`app_chk_app_id`, `app_chk_chk_id`) 
    SELECT @app_id, `cheklist_item_id` FROM `product_check_list` WHERE `product_id`=app_product_id AND `business_type`=app_business_type_id;
    
    SET @log_id = null;
    INSERT INTO `application_status_log`(`log_datetime`, `log_app_id`, `log_status`, `log_comments`, `log_user`) 
    VALUES (now(), @app_id, '1', 'System', @user_id);
    SELECT last_insert_id() INTO @log_id;
    
    INSERT INTO `log_reason`(`log_id`, `reason_id`) 
    VALUES (@log_id, 1);
    
    SELECT @app_id AS last_app_id;
COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `create_log_new` (IN `log_application` INT(11), IN `log_status` INT(11), IN `log_comment` VARCHAR(256), IN `username` VARCHAR(64))   BEGIN
START TRANSACTION;

	SET @user_id = null;
    SELECT `usr_id` INTO @user_id FROM `user` WHERE `usr_username`=username; 
    
    INSERT INTO `application_status_log`(`log_datetime`, `log_app_id`, `log_status`, `log_comments`, `log_user`)
    VALUES (now(), log_application, log_status, log_comment, @user_id);

	SELECT last_insert_id() as `log_id`;

COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dashboard_stat` ()   BEGIN
START TRANSACTION;

SELECT `sts_name`, count(`log_app_id`) AS `count`, `sts_color` FROM (
	SELECT `log_app_id`,`sts_name`, `sts_color` FROM(
		SELECT max(`log_id`) as `last_log_id` FROM application_status_log GROUP BY `log_app_id`
	) AS A LEFT JOIN (
		SELECT `log_id`,`log_app_id`,`sts_name`,`sts_color` FROM `application_status_log` LEFT JOIN `standerd_status` ON `application_status_log`.`log_status`=`standerd_status`.`sts_id` 
	) AS B 
	ON A.last_log_id = B.`log_id`
) AS C GROUP BY `sts_name`;

COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `search_application` (IN `show_hidden` INT(11))   BEGIN
START TRANSACTION;

SELECT * FROM (
	SELECT `app_id`,`app_date`,`app_merchant_name`, `app_merchant_id`, `branch`.`branch_name`, `product`.`prod_name`, `business_type`.`bust_name` 
    FROM `application` 
    LEFT JOIN `branch` 
    ON `application`.`app_branch`=`branch`.`branch_id` 
    LEFT JOIN `product` 
    ON `application`.`app_product_id`=`product`.`prod_id` 
    LEFT JOIN `business_type` 
    ON `application`.`app_business_type_id`=`business_type`.`bust_id`
) AS A LEFT JOIN (
    SELECT * FROM (
    SELECT max(`log_id`) AS `last_log_id` FROM `application_status_log` GROUP BY `log_app_id`) AS C 
    LEFT JOIN (
    SELECT `log_id`,`log_app_id`, `log_datetime`, `sts_name`, `sts_color`, `sts_id`, `log_comments`
	FROM `application_status_log` 
    LEFT JOIN `standerd_status`
	ON `application_status_log`.`log_status` = `standerd_status`.`sts_id` 
	/*LEFT JOIN `standerd_status_reason` 
	ON `application_status_log`.`log_status_reason_id` = `standerd_status_reason`.`reason_id` */
    ) AS D 
    ON C.`last_log_id`=D.`log_id`
) AS B
ON A.`app_id`=B.`log_app_id` 
WHERE B.`sts_id` != show_hidden
ORDER BY `app_id`;
COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `view_application` (IN `in_app_id` INTEGER(11))   BEGIN
START TRANSACTION;

SET @precentage = null;
SET @all_chk_count = null;
SELECT count(`app_chk_chk_id`) INTO @all_chk_count FROM `application_checklist` WHERE `app_chk_app_id`=in_app_id;
SELECT FLOOR(count(`app_chk_chk_id`)/@all_chk_count*100) INTO @precentage FROM `application_checklist` WHERE `app_chk_app_id`=in_app_id AND `app_chk_checked`=1;

SELECT * FROM (
	SELECT @precentage AS `precentage`,`app_id`,`app_date`,`app_merchant_name`,`app_contact_email`, `app_merchant_id`, `app_one_time_fee`, `app_montly_fixed_fee`, `app_fee_rate`, `branch`.`branch_name`, `branch_email`,`product`.`prod_name`, `business_type`.`bust_name` 
    FROM `application` 
    LEFT JOIN `branch` 
    ON `application`.`app_branch`=`branch`.`branch_id` 
    LEFT JOIN `product` 
    ON `application`.`app_product_id`=`product`.`prod_id` 
    LEFT JOIN `business_type` 
    ON `application`.`app_business_type_id`=`business_type`.`bust_id`
) AS A LEFT JOIN (
    SELECT * FROM (
    SELECT max(`log_id`) AS `last_log_id` FROM `application_status_log` GROUP BY `log_app_id`) AS C 
    LEFT JOIN (
    SELECT `log_id`,`log_app_id`, `log_datetime`, `sts_name`, `sts_color`, `log_comments`
	FROM `application_status_log` 
    LEFT JOIN `standerd_status`
	ON `application_status_log`.`log_status` = `standerd_status`.`sts_id` 
	/*LEFT JOIN `standerd_status_reason` 
	ON `application_status_log`.`log_status_reason_id` = `standerd_status_reason`.`reason_id` */
    ) AS D 
    ON C.`last_log_id`=D.`log_id`
) AS B
ON A.`app_id`=B.`log_app_id` 
WHERE A.`app_id`=in_app_id 
ORDER BY `app_id`;
COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `application`
--

CREATE TABLE `application` (
  `app_id` int(11) NOT NULL,
  `app_date` date NOT NULL,
  `app_merchant_name` varchar(256) NOT NULL,
  `app_branch` int(11) NOT NULL,
  `app_product_id` int(11) NOT NULL,
  `app_business_type_id` int(11) NOT NULL,
  `app_contact_email` varchar(256) DEFAULT NULL,
  `app_merchant_id` varchar(32) DEFAULT NULL,
  `app_one_time_fee` float DEFAULT NULL,
  `app_montly_fixed_fee` float DEFAULT NULL,
  `app_fee_rate` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `application`
--

INSERT INTO `application` (`app_id`, `app_date`, `app_merchant_name`, `app_branch`, `app_product_id`, `app_business_type_id`, `app_contact_email`, `app_merchant_id`, `app_one_time_fee`, `app_montly_fixed_fee`, `app_fee_rate`) VALUES
(44, '2023-08-02', 'Lakmal', 548, 24, 2, NULL, '', 0, 0, 0),
(50, '2023-08-03', 'Rohita', 608, 24, 2, 'rohita@gmail.com', '50', 35000, 2500, 4),
(51, '2023-08-04', 'prasad', 481, 24, 1, 'lakmal@gmail.com', '51', 0, 0, 0),
(52, '2023-07-27', 'Amali', 514, 24, 2, 'amal@something.com', '52', 24000, 2400, 2.4),
(53, '2023-07-06', 'Jhon', 544, 27, 1, 'abc', '53', 0, 0, 0),
(54, '2023-07-15', 'merchantDog', 481, 24, 1, 'email@email', '54', 24000, 2400, 2.4),
(55, '2023-08-16', 'Janaa', 589, 25, 2, 'abc', '3240', 0, 0, 0),
(56, '2023-12-10', 'Jhon and Sons', 12, 1, 1, 'john@jas.com', NULL, NULL, NULL, NULL),
(57, '2023-08-16', 'suppa', 707, 24, 1, 'supunn@peoplesbank.lk', '45AB324', 24000, 2400, 2.4);

-- --------------------------------------------------------

--
-- Table structure for table `application_checklist`
--

CREATE TABLE `application_checklist` (
  `app_chk_app_id` int(11) NOT NULL,
  `app_chk_chk_id` int(11) NOT NULL,
  `app_chk_checked` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `application_checklist`
--

INSERT INTO `application_checklist` (`app_chk_app_id`, `app_chk_chk_id`, `app_chk_checked`) VALUES
(44, 28, 0),
(44, 29, 0),
(50, 28, 0),
(50, 29, 0),
(51, 26, 0),
(51, 27, 0),
(52, 28, 0),
(52, 29, 0),
(54, 26, 0),
(54, 27, 0),
(57, 26, 0),
(57, 27, 0);

-- --------------------------------------------------------

--
-- Table structure for table `application_status_log`
--

CREATE TABLE `application_status_log` (
  `log_id` int(11) NOT NULL,
  `log_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `log_app_id` int(11) NOT NULL,
  `log_status` int(11) NOT NULL,
  `log_comments` varchar(256) DEFAULT NULL,
  `log_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `application_status_log`
--

INSERT INTO `application_status_log` (`log_id`, `log_datetime`, `log_app_id`, `log_status`, `log_comments`, `log_user`) VALUES
(103, '2023-07-31 10:27:45', 44, 1, 'System', 70),
(104, '2023-07-31 11:01:40', 44, 4, 'new', 70),
(105, '2023-07-31 11:02:32', 44, 4, 'old', 70),
(106, '2023-07-31 11:37:26', 44, 4, NULL, 70),
(107, '2023-07-31 11:37:57', 44, 4, NULL, 70),
(108, '2023-07-31 12:22:07', 44, 2, NULL, 70),
(109, '2023-07-31 12:22:43', 44, 2, NULL, 70),
(110, '2023-07-31 12:23:04', 44, 2, NULL, 70),
(111, '2023-07-31 13:14:29', 50, 1, 'System', 70),
(112, '2023-07-31 13:15:36', 51, 1, 'System', 70),
(113, '2023-07-31 13:56:07', 52, 1, 'System', 70),
(114, '2023-07-31 13:56:26', 52, 4, NULL, 70),
(115, '2023-07-31 15:29:02', 51, 2, NULL, 70),
(116, '2023-07-31 15:29:17', 51, 12, NULL, 70),
(117, '2023-07-31 15:31:11', 51, 12, NULL, 70),
(118, '2023-07-31 15:31:57', 51, 2, NULL, 70),
(119, '2023-07-31 15:32:44', 51, 2, NULL, 70),
(120, '2023-07-31 16:06:56', 53, 1, 'System', 70),
(121, '2023-07-31 16:07:32', 54, 1, 'System', 70),
(122, '2023-07-31 16:15:53', 54, 4, 'ygughgh', 70),
(123, '2023-07-31 16:16:11', 54, 12, 'ygughgh', 70),
(124, '2023-08-07 08:04:52', 55, 1, 'System', 70),
(125, '2023-08-07 08:05:26', 55, 4, NULL, 70),
(126, '2023-08-09 14:34:15', 50, 4, NULL, 70),
(127, '2023-08-10 08:29:27', 50, 4, NULL, 70),
(128, '2023-08-10 08:30:52', 50, 2, NULL, 70),
(129, '2023-08-10 08:58:12', 54, 4, NULL, 70),
(130, '2023-08-10 08:58:24', 54, 4, NULL, 70),
(131, '2023-08-10 08:59:36', 54, 2, NULL, 70),
(132, '2023-08-10 09:46:13', 55, 4, NULL, 70),
(133, '2023-08-10 09:46:29', 55, 12, NULL, 70),
(134, '2023-08-10 10:12:09', 51, 4, NULL, 70),
(135, '2023-08-10 10:37:07', 51, 12, NULL, 70),
(136, '2023-08-10 10:39:52', 51, 2, NULL, 70),
(137, '2023-08-10 10:40:42', 51, 2, NULL, 70),
(138, '2023-08-10 10:42:51', 53, 12, NULL, 70),
(139, '2023-08-10 10:58:43', 56, 1, 'System', 37),
(140, '2023-08-10 10:59:08', 57, 1, 'System', 70),
(141, '2023-08-10 10:59:56', 57, 4, NULL, 70),
(142, '2023-08-10 10:59:58', 57, 2, NULL, 70),
(143, '2023-08-10 11:00:03', 57, 2, NULL, 70),
(144, '2023-08-10 11:00:57', 57, 12, NULL, 70),
(145, '2023-08-10 11:01:46', 57, 12, NULL, 70),
(146, '2023-08-10 11:02:30', 57, 12, NULL, 70),
(147, '2023-08-10 11:02:55', 57, 12, NULL, 70),
(148, '2023-08-10 11:03:16', 57, 11, NULL, 70);

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branch_id` int(11) NOT NULL,
  `branch_name` varchar(128) NOT NULL,
  `branch_code` varchar(16) NOT NULL,
  `branch_email` varchar(256) DEFAULT NULL,
  `branch_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `branch_name`, `branch_code`, `branch_email`, `branch_deleted`) VALUES
(362, 'Addalachchenei', '228', 'addal228@peoplesbank.lk', 0),
(363, 'Akkaraipattu', '63', 'akkar063@peoplesbank.lk', 0),
(364, 'Ampara', '15', 'ampar015@peoplesbank.lk', 0),
(365, 'Kalmunai', '23', 'kalmu023@peoplesbank.lk', 0),
(366, 'Karaitivu', '223', 'karai223@peoplesbank.lk', 0),
(367, 'Mahaoya', '181', 'mahao181@peoplesbank.lk', 0),
(368, 'Maruthamunai', '346', 'marut346@peoplesbank.lk', 0),
(369, 'Nintavur', '296', 'ninta296@peoplesbank.lk', 0),
(370, 'Pottuvil', '164', 'pottu164@peoplesbank.lk', 0),
(371, 'Sainthamaruthu', '338', 'saint338@peoplesbank.lk', 0),
(372, 'Sammanthurai', '64', 'samma064@peoplesbank.lk', 0),
(373, 'Thirukkovil', '224', 'thiru224@peoplesbank.lk', 0),
(374, 'Uhana', '189', 'uhana189@peoplesbank.lk', 0),
(375, 'Anuradhapura', '8', 'anura008@peoplesbank.lk', 0),
(376, 'Nuwarawewa', '220', 'anura220@peoplesbank.lk', 0),
(377, 'Eppawala', '170', 'eppaw170@peoplesbank.lk', 0),
(378, 'Galenbindunuwewa', '177', 'galen177@peoplesbank.lk', 0),
(379, 'Galkiriyagama', '301', 'galki301@peoplesbank.lk', 0),
(380, 'Galnewa', '179', 'galne179@peoplesbank.lk', 0),
(381, 'Horoupathana', '218', 'horow218@peoplesbank.lk', 0),
(382, 'Kahatagasdigiliya', '51', 'kahat051@peoplesbank.lk', 0),
(383, 'Kebithigollewa', '150', 'kebit150@peoplesbank.lk', 0),
(384, 'Kekirawa', '42', 'kekir042@peoplesbank.lk', 0),
(385, 'Medawachchiya', '96', 'medaw096@peoplesbank.lk', 0),
(386, 'Meegalewa', '246', 'meega246@peoplesbank.lk', 0),
(387, 'Nochchiyagama', '171', 'nochc171@peoplesbank.lk', 0),
(388, 'Padaviya', '43', 'padav043@peoplesbank.lk', 0),
(389, 'Talawa', '315', 'talaw315@peoplesbank.lk', 0),
(390, 'Thambuttegama', '219', 'thamb219@peoplesbank.lk', 0),
(391, 'Badulla', '10', 'badul010@peoplesbank.lk', 0),
(392, 'Muthiyangana', '269', 'badul269@peoplesbank.lk', 0),
(393, 'Bandarawela', '37', 'banda037@peoplesbank.lk', 0),
(394, 'Boralanda', '209', 'boral209@peoplesbank.lk', 0),
(395, 'Diyatalawa', '151', 'diyat151@peoplesbank.lk', 0),
(396, 'Giradurukotte', '268', 'giran268@peoplesbank.lk', 0),
(397, 'Haldummulla', '195', 'haldu195@peoplesbank.lk', 0),
(398, 'Haliela', '225', 'halie225@peoplesbank.lk', 0),
(399, 'Haputale', '216', 'haput216@peoplesbank.lk', 0),
(400, 'Kandaketiya', '250', 'kanda250@peoplesbank.lk', 0),
(401, 'Keppetiipola', '240', 'keppe240@peoplesbank.lk', 0),
(402, 'Koslanda', '260', 'kosla260@peoplesbank.lk', 0),
(403, 'Lunugala', '251', 'lunug251@peoplesbank.lk', 0),
(404, 'Mahiyangana', '58', 'mahiy058@peoplesbank.lk', 0),
(405, 'Passara', '116', 'passa116@peoplesbank.lk', 0),
(406, 'Uwaparanagama', '156', 'uvapa156@peoplesbank.lk', 0),
(407, 'Welimada ', '16', 'welim016@peoplesbank.lk', 0),
(408, 'Batticaloa', '75', 'batti075@peoplesbank.lk', 0),
(409, 'Batticaloa Town', '113', 'batti113@peoplesbank.lk', 0),
(410, 'Chenkalady', '227', 'chenk227@peoplesbank.lk', 0),
(411, 'Eravur', '123', 'eravu123@peoplesbank.lk', 0),
(412, 'Kaluwanchikudy', '190', 'kaluw190@peoplesbank.lk', 0),
(413, 'Katankudy', '65', 'katta065@peoplesbank.lk', 0),
(414, 'Kallar', '339', 'kalla339@peoplesbank.lk', 0),
(415, 'Kokkadicholei', '342', 'kokka342@peoplesbank.lk', 0),
(416, 'Oddamavadi', '340', 'oddam340@peoplesbank.lk', 0),
(417, 'Valachchenai', '102', 'valac102@peoplesbank.lk', 0),
(418, 'Anamaduwa', '267', 'anama267@peoplesbank.lk', 0),
(419, 'Chilaw', '24', 'chila024@peoplesbank.lk', 0),
(420, 'Dankotuwa', '291', 'danko291@peoplesbank.lk', 0),
(421, 'Kalpitiya', '125', 'kalpi125@peoplesbank.lk', 0),
(422, 'Madampe', '215', 'madam215@peoplesbank.lk', 0),
(423, 'Mahawewa', '303', 'mahaw303@peoplesbank.lk', 0),
(424, 'Marawila', '322', 'maraw322@peoplesbank.lk', 0),
(425, 'Nattandiya', '83', 'natta083@peoplesbank.lk', 0),
(426, 'Puttalam', '9', 'putta009@peoplesbank.lk', 0),
(427, 'Wennappuwa', '76', 'wenna076@peoplesbank.lk', 0),
(428, 'Central Rd', '298', 'gintu298@peoplesbank.lk', 0),
(429, 'Dam Street', '297', 'damst297@peoplesbank.lk', 0),
(430, 'Dematagoda', '71', 'demat071@peoplesbank.lk', 0),
(431, 'Duke Street', '1', 'dukes001@peoplesbank.lk', 0),
(432, 'First City Branch', '46', 'mudal046@peoplesbank.lk', 0),
(433, 'Grandpass', '126', 'grand126@peoplesbank.lk', 0),
(434, 'Kehelwatta', '259', 'kehel259@peoplesbank.lk', 0),
(435, 'Kotahena', '308', 'kotah308@peoplesbank.lk', 0),
(436, 'Malwatte Rd', '312', 'malwa312@peoplesbank.lk', 0),
(437, 'Mid City', '176', 'midci176@peoplesbank.lk', 0),
(438, 'Mutwal', '214', 'mutwa214@peoplesbank.lk', 0),
(439, 'Olcott Mw.', '275', 'olcot275@peoplesbank.lk', 0),
(440, 'Pettah', '139', 'petta139@peoplesbank.lk', 0),
(441, 'Sangaraja Maw.', '56', 'srisa056@peoplesbank.lk', 0),
(442, 'Sea Street', '277', 'seast277@peoplesbank.lk', 0),
(443, 'Bambalapitiya', '310', 'bamba310@peoplesbank.lk', 0),
(444, 'Borella', '78', 'borel078@peoplesbank.lk', 0),
(445, 'Borella Town(Golden Jubile)', '320', 'borel320@peoplesbank.lk', 0),
(446, 'Head Quarters', '204', 'headq204@peoplesbank.lk', 0),
(447, 'Hyde Park Corner', '25', 'parks025@peoplesbank.lk', 0),
(448, 'Kirillapona', '319', 'kirul319@peoplesbank.lk', 0),
(449, 'Kollupitiya Co-op.House', '210', 'kollu210@peoplesbank.lk', 0),
(450, 'Liberty Plaza', '309', 'kollu309@peoplesbank.lk', 0),
(451, 'Lucky Plaza', '331', 'lucky331@peoplesbank.lk', 0),
(452, 'Maradana', '236', 'marad236@peoplesbank.lk', 0),
(453, 'Majestic City', '200', 'majes200@peoplesbank.lk', 0),
(454, 'Narahenpita', '119', 'narah119@peoplesbank.lk', 0),
(455, 'Suduwella', '143', 'suduw143@peoplesbank.lk', 0),
(456, 'Thimbirigasyaya', '86', 'thimb086@peoplesbank.lk', 0),
(457, 'Town Hall', '167', 'townh167@peoplesbank.lk', 0),
(458, 'Union Place', '14', 'union014@peoplesbank.lk', 0),
(459, 'Ward Place -Premier Branch', '362', 'elegance362@peoplesbank.lk', 0),
(460, 'Wellawatte', '145', 'wella145@peoplesbank.lk', 0),
(461, 'Awissawella', '29', 'aviss029@peoplesbank.lk', 0),
(462, 'Battaramulla', '208', 'batta208@peoplesbank.lk', 0),
(463, 'Boralesgamuwa', '348', 'boral348@peoplesbank.lk', 0),
(464, 'Dehiwala', '19', 'dehiw019@peoplesbank.lk', 0),
(465, 'Dehiwala Galle Rd.', '337', 'dehiw337@peoplesbank.lk', 0),
(466, 'Gangodawila', '97', 'gango097@peoplesbank.lk', 0),
(467, 'Hanwella', '229', 'hanwe229@peoplesbank.lk', 0),
(468, 'Homagama', '49', 'homag049@peoplesbank.lk', 0),
(469, 'Kaduwela', '196', 'kaduw196@peoplesbank.lk', 0),
(470, 'Katubedda', '313', 'katub313@peoplesbank.lk', 0),
(471, 'Kesbewa', '327', 'kesbe327@peoplesbank.lk', 0),
(472, 'Kolonnawa', '194', 'kolon194@peoplesbank.lk', 0),
(473, 'Kotikawatta', '98', 'kotik098@peoplesbank.lk', 0),
(474, 'Kottawa', '328', 'kotta328@peoplesbank.lk', 0),
(475, 'Maharagama', '306', 'mahar306@peoplesbank.lk', 0),
(476, 'Moratumulla', '290', 'morat290@peoplesbank.lk', 0),
(477, 'Moratuwa', '91', 'morat091@peoplesbank.lk', 0),
(478, 'Mount Lavinia', '336', 'mount336@peoplesbank.lk', 0),
(479, 'Nugegoda', '174', 'nugeg174@peoplesbank.lk', 0),
(480, 'Nugegoda City', '335', 'nugeg335@peoplesbank.lk', 0),
(481, 'Piliyandala', '103', 'piliy103@peoplesbank.lk', 0),
(482, 'Piliyandala City ', '359', 'piliy359@peoplesbank.lk', 0),
(483, 'Pitakotte', '279', 'pitak279@peoplesbank.lk', 0),
(484, 'Ratmalana', '80', 'ratma080@peoplesbank.lk', 0),
(485, 'Athurugiriya', '364', 'athur364@peoplesbank.lk', 0),
(486, 'Ahangama', '188', 'ahang188@peoplesbank.lk', 0),
(487, 'Ambalngoda', '35', 'ambal035@peoplesbank.lk', 0),
(488, 'Baddegama', '87', 'badde087@peoplesbank.lk', 0),
(489, 'Balapitiya', '154', 'balap154@peoplesbank.lk', 0),
(490, 'Batapola', '234', 'batap234@peoplesbank.lk', 0),
(491, 'Elpitiya', '73', 'elpit073@peoplesbank.lk', 0),
(492, 'Galle Fort', '13', 'galle013@peoplesbank.lk', 0),
(493, 'Galle Main', '169', 'galle169@peoplesbank.lk', 0),
(494, 'Hikkaduwa', '136', 'hikka136@peoplesbank.lk', 0),
(495, 'Imaduwa', '247', 'imadu247@peoplesbank.lk', 0),
(496, 'Karapitiya', '343', 'karap343@peoplesbank.lk', 0),
(497, 'Koggala', '329', 'kogga329@peoplesbank.lk', 0),
(498, 'Talgaswala', '272', 'talga272@peoplesbank.lk', 0),
(499, 'Udugama', '131', 'uduga131@peoplesbank.lk', 0),
(500, 'Uragasmanhandiya', '197', 'uraga197@peoplesbank.lk', 0),
(501, 'Wanduramba', '325', 'wandu325@peoplesbank.lk', 0),
(502, 'Delgoda', '118', 'delgo118@peoplesbank.lk', 0),
(503, 'Gampaha', '26', 'gampa026@peoplesbank.lk', 0),
(504, 'Ganemulla', '332', 'ganem332@peoplesbank.lk', 0),
(505, 'Ja-ela', '239', 'jaela239@peoplesbank.lk', 0),
(506, 'Kadawatha', '273', 'kadaw273@peoplesbank.lk', 0),
(507, 'Kandana', '175', 'kanda175@peoplesbank.lk', 0),
(508, 'Katunaike', '276', 'katun276@peoplesbank.lk', 0),
(509, 'Kelaniya', '55', 'kelan055@peoplesbank.lk', 0),
(510, 'Kiribathgoda', '237', 'kirib237@peoplesbank.lk', 0),
(511, 'Kirindiwela', '202', 'kirin202@peoplesbank.lk', 0),
(512, 'Mahara', '217', 'mahar217@peoplesbank.lk', 0),
(513, 'Malwana', '191', 'malwa191@peoplesbank.lk', 0),
(514, 'Maradagahamula', '100', 'maran100@peoplesbank.lk', 0),
(515, 'Meerigama', '198', 'mirig198@peoplesbank.lk', 0),
(516, 'Minuwangoda', '21', 'minuw021@peoplesbank.lk', 0),
(517, 'Nittambuwa', '278', 'nitta278@peoplesbank.lk', 0),
(518, 'Pamunugama', '318', 'pamun318@peoplesbank.lk', 0),
(519, 'Pugoda', '93', 'pugod093@peoplesbank.lk', 0),
(520, 'Ragama', '316', 'ragam316@peoplesbank.lk', 0),
(521, 'Seeduwa', '324', 'seedu324@peoplesbank.lk', 0),
(522, 'Veyangoda', '79', 'veyan079@peoplesbank.lk', 0),
(523, 'Wattala', '222', 'watta222@peoplesbank.lk', 0),
(524, 'Yakkala', '333', 'yakka333@peoplesbank.lk', 0),
(525, 'Kochchikade', '142', 'kochc142@peoplesbank.lk', 0),
(526, 'Negombo', '34', 'negom034@peoplesbank.lk', 0),
(527, 'Ambalantota', '72', 'ambal072@peoplesbank.lk', 0),
(528, 'Angunakolapelessa', '205', 'angun205@peoplesbank.lk', 0),
(529, 'Beliatta', '244', 'belia244@peoplesbank.lk', 0),
(530, 'Hambantota', '7', 'hamba007@peoplesbank.lk', 0),
(531, 'Kudawella', '288', 'kudaw288@peoplesbank.lk', 0),
(532, 'Middeniya', '265', 'midde265@peoplesbank.lk', 0),
(533, 'Ranna', '345', 'ranna345@peoplesbank.lk', 0),
(534, 'Suriyawewa', '264', 'suriy264@peoplesbank.lk', 0),
(535, 'Tangalle', '67', 'tanga067@peoplesbank.lk', 0),
(536, 'Tissamaharama', '61', 'tissa061@peoplesbank.lk', 0),
(537, 'Walasmulla', '120', 'walas120@peoplesbank.lk', 0),
(538, 'Weeraketiya', '350', 'weera350@peoplesbank.lk', 0),
(539, 'Atchuvely', '107', 'atchu107@peoplesbank.lk', 0),
(540, 'Chankanai', '108', 'chank108@peoplesbank.lk', 0),
(541, 'Chavakachcheri', '110', 'chava110@peoplesbank.lk', 0),
(542, 'Chunnakam', '109', 'chunn109@peoplesbank.lk', 0),
(543, 'Kannathiddy', '284', 'jaffn284@peoplesbank.lk', 0),
(544, 'J/Main Street', '104', 'jaffn104@peoplesbank.lk', 0),
(545, 'J/Stanley Road', '30', 'jaffn030@peoplesbank.lk', 0),
(546, 'J/University', '162', 'jaffn162@peoplesbank.lk', 0),
(547, 'Tellipalai', '31', 'kanka031@peoplesbank.lk', 0),
(548, 'Kayts', '105', 'kayts105@peoplesbank.lk', 0),
(549, 'Kodikamam', '361', 'kodik361@peoplesbank.lk', 0),
(550, 'Nelliady', '106', 'nelli106@peoplesbank.lk', 0),
(551, 'Point Pedro', '285', 'point285@peoplesbank.lk', 0),
(552, 'Velvettithurai', '141', 'valve141@peoplesbank.lk', 0),
(553, 'Aluthgama', '84', 'aluth084@peoplesbank.lk', 0),
(554, 'Badureliya', '283', 'badur283@peoplesbank.lk', 0),
(555, 'Bandaragama', '121', 'banda121@peoplesbank.lk', 0),
(556, 'Beruwala', '311', 'beruw311@peoplesbank.lk', 0),
(557, 'Bulathsinghala', '161', 'bulat161@peoplesbank.lk', 0),
(558, 'Horana', '41', 'horan041@peoplesbank.lk', 0),
(559, 'Ingiriya', '300', 'ingir300@peoplesbank.lk', 0),
(560, 'Kalutara', '39', 'kalut039@peoplesbank.lk', 0),
(561, 'Maggona', '282', 'maggo282@peoplesbank.lk', 0),
(562, 'Matugama', '70', 'matug070@peoplesbank.lk', 0),
(563, 'Neboda', '249', 'nebod249@peoplesbank.lk', 0),
(564, 'Panadura', '148', 'panad148@peoplesbank.lk', 0),
(565, 'Panadura Town', '321', 'panad321@peoplesbank.lk', 0),
(566, 'Pelawatta', '261', 'pelaw261@peoplesbank.lk', 0),
(567, 'Wadduwa', '262', 'waddu262@peoplesbank.lk', 0),
(568, 'Akurana', '153', 'akura153@peoplesbank.lk', 0),
(569, 'Alawathugoda', '294', 'alawa294@peoplesbank.lk', 0),
(570, 'Ankumbura', '183', 'ankum183@peoplesbank.lk', 0),
(571, 'Daulagala', '206', 'davul206@peoplesbank.lk', 0),
(572, 'Deltota', '257', 'delto257@peoplesbank.lk', 0),
(573, 'Galagedara', '114', 'galag114@peoplesbank.lk', 0),
(574, 'Hataraliyadda', '341', 'hatha341@peoplesbank.lk', 0),
(575, 'Gampola', '18', 'gampo018@peoplesbank.lk', 0),
(576, 'Hasalaka', '140', 'hasal140@peoplesbank.lk', 0),
(577, 'Kadugannawa', '159', 'kadug159@peoplesbank.lk', 0),
(578, 'Kandy', '3', 'kandy003@peoplesbank.lk', 0),
(579, 'Kandy City Centre', '357', 'kcc357@peoplesbank.lk', 0),
(580, 'Katugastota', '89', 'katug089@peoplesbank.lk', 0),
(581, 'Menikhinna', '157', 'menik157@peoplesbank.lk', 0),
(582, 'Nawalapitiya', '53', 'nawal053@peoplesbank.lk', 0),
(583, 'Panwila', '211', 'panwi211@peoplesbank.lk', 0),
(584, 'Peradeniya', '57', 'perad057@peoplesbank.lk', 0),
(585, 'Pilimatalawa', '256', 'pilim256@peoplesbank.lk', 0),
(586, 'Poojapitiya', '358', 'pooja358@peoplesbank.lk', 0),
(587, 'Pussellawa', '274', 'pusse274@peoplesbank.lk', 0),
(588, 'Senkadagala', '158', 'senka158@peoplesbank.lk', 0),
(589, 'Teldeniya', '112', 'telde112@peoplesbank.lk', 0),
(590, 'Wattagama', '74', 'watte074@peoplesbank.lk', 0),
(591, 'Gelioya', '363', 'gelio363@peoplesbank.lk', 0),
(592, 'Aranayaka', '248', 'arana248@peoplesbank.lk', 0),
(593, 'Bulathkohupitiya', '252', 'bulat252@peoplesbank.lk', 0),
(594, 'Dehiowita', '293', 'dehio293@peoplesbank.lk', 0),
(595, 'Deraniyagala', '180', 'deran180@peoplesbank.lk', 0),
(596, 'Galigamuwa', '185', 'galig185@peoplesbank.lk', 0),
(597, 'Gonagaldeniya', '238', 'gonag238@peoplesbank.lk', 0),
(598, 'Hemmathagama', '221', 'hemma221@peoplesbank.lk', 0),
(599, 'Kegalle Main', '27', 'kegal027@peoplesbank.lk', 0),
(600, 'Kegalle Bazzar', '299', 'kegal299@peoplesbank.lk', 0),
(601, 'Kotiyakumbura', '355', 'kotiy355@peoplesbank.lk', 0),
(602, 'Mawanella', '69', 'mawan069@peoplesbank.lk', 0),
(603, 'Rambukkana', '101', 'rambu101@peoplesbank.lk', 0),
(604, 'Ruwanwella', '81', 'ruwan081@peoplesbank.lk', 0),
(605, 'Thulhiriya', '270', 'thulh270@peoplesbank.lk', 0),
(606, 'Warakapola', '54', 'warak054@peoplesbank.lk', 0),
(607, 'Yatiyantota', '47', 'yatiy047@peoplesbank.lk', 0),
(608, 'Alawwa', '149', 'alaww149@peoplesbank.lk', 0),
(609, 'Bingiriya', '172', 'bingi172@peoplesbank.lk', 0),
(610, 'Galgamuwa', '184', 'galga184@peoplesbank.lk', 0),
(611, 'Giriulla', '92', 'giriu092@peoplesbank.lk', 0),
(612, 'Hettipola', '144', 'hetti144@peoplesbank.lk', 0),
(613, 'Ibbagamuwa', '207', 'ibbag207@peoplesbank.lk', 0),
(614, 'Kobeigane', '281', 'kobei281@peoplesbank.lk', 0),
(615, 'Ethugalpura', '334', 'atuga334@peoplesbank.lk', 0),
(616, 'Kuru-Maliyadewa', '226', 'kurun226@peoplesbank.lk', 0),
(617, 'Kuliyapitiya', '28', 'kuliy028@peoplesbank.lk', 0),
(618, 'Kurunagala', '12', 'kurun012@peoplesbank.lk', 0),
(619, 'Maho', '52', 'maho052@peoplesbank.lk', 0),
(620, 'Makandura', '137', 'makan137@peoplesbank.lk', 0),
(621, 'Mawathagama', '199', 'mawat199@peoplesbank.lk', 0),
(622, 'Melsiripura', '344', 'melsi344@peoplesbank.lk', 0),
(623, 'Narammala', '82', 'naram082@peoplesbank.lk', 0),
(624, 'Nikaweratiya', '124', 'nikaw124@peoplesbank.lk', 0),
(625, 'Polgahawela', '59', 'polga059@peoplesbank.lk', 0),
(626, 'Polpitigama ', '360', 'polpi360@peoplesbank.lk', 0),
(627, 'Pothuhera', '280', 'pothu280@peoplesbank.lk', 0),
(628, 'Ridigama', '193', 'ridig193@peoplesbank.lk', 0),
(629, 'Wariyapola', '163', 'wariy163@peoplesbank.lk', 0),
(630, 'Dambulla', '138', 'dambu138@peoplesbank.lk', 0),
(631, 'Galewela', '115', 'galew115@peoplesbank.lk', 0),
(632, 'Matale', '2', 'matal002@peoplesbank.lk', 0),
(633, 'Naula', '146', 'naula146@peoplesbank.lk', 0),
(634, 'Pallepola', '241', 'palle241@peoplesbank.lk', 0),
(635, 'Raththota', '128', 'ratto128@peoplesbank.lk', 0),
(636, 'Ukuwela', '201', 'ukuwe201@peoplesbank.lk', 0),
(637, 'Wilgamuwa', '122', 'wilga122@peoplesbank.lk', 0),
(638, 'Akuressa', '117', 'akure117@peoplesbank.lk', 0),
(639, 'Deniyaya', '132', 'deniy132@peoplesbank.lk', 0),
(640, 'Devinuwara', '243', 'devin243@peoplesbank.lk', 0),
(641, 'Dickwella', '135', 'dikwe135@peoplesbank.lk', 0),
(642, 'Gandara', '307', 'ganda307@peoplesbank.lk', 0),
(643, 'Hakmana', '130', 'hakma130@peoplesbank.lk', 0),
(644, 'Kamburupitiya', '133', 'kambu133@peoplesbank.lk', 0),
(645, 'Matara Dha.Maw', '152', 'matar152@peoplesbank.lk', 0),
(646, 'Matara Uyanwatta', '32', 'matar032@peoplesbank.lk', 0),
(647, 'Morawaka', '60', 'moraw060@peoplesbank.lk', 0),
(648, 'Urubokka', '271', 'urubo271@peoplesbank.lk', 0),
(649, 'Walasgala', '304', 'walas304@peoplesbank.lk', 0),
(650, 'Weligama', '77', 'welig077@peoplesbank.lk', 0),
(651, 'Badalkumbura', '347', 'badal347@peoplesbank.lk', 0),
(652, 'Bibila', '11', 'bibil011@peoplesbank.lk', 0),
(653, 'Buttala', '147', 'butta147@peoplesbank.lk', 0),
(654, 'Kataragama', '168', 'katar168@peoplesbank.lk', 0),
(655, 'Medagama', '258', 'medag258@peoplesbank.lk', 0),
(656, 'Monaragala', '68', 'monar068@peoplesbank.lk', 0),
(657, 'Thanamalwila', '230', 'thana230@peoplesbank.lk', 0),
(658, 'Wellawaya', '62', 'wella062@peoplesbank.lk', 0),
(659, 'Siyambalanduwa', '365', 'siyam365@peoplesbank.lk', 0),
(660, 'Bogawantalawa', '354', 'bogaw354@peoplesbank.lk', 0),
(661, 'Ginigathhena', '302', 'ginig302@peoplesbank.lk', 0),
(662, 'Hatton', '186', 'hatto186@peoplesbank.lk', 0),
(663, 'Maskeliya', '178', 'maske178@peoplesbank.lk', 0),
(664, 'Nildandahinna', '127', 'nilda127@peoplesbank.lk', 0),
(665, 'Nuwaraeliya', '134', 'nuwar134@peoplesbank.lk', 0),
(666, 'Pundaluoya', '173', 'punda173@peoplesbank.lk', 0),
(667, 'Ragala', '36', 'ragal036@peoplesbank.lk', 0),
(668, 'Rikillagaskada', '353', 'rikil353@peoplesbank.lk', 0),
(669, 'Talawakele', '38', 'talaw038@peoplesbank.lk', 0),
(670, 'Udapussellawa', '292', 'udapu292@peoplesbank.lk', 0),
(671, 'Hanguranketha', '22', 'hangu022@peoplesbank.lk', 0),
(672, 'Balangoda', '17', 'balan017@peoplesbank.lk', 0),
(673, 'Eheliyagoda', '85', 'eheli085@peoplesbank.lk', 0),
(674, 'Embilipitiya', '45', 'embil045@peoplesbank.lk', 0),
(675, 'Godakawela', '245', 'godak245@peoplesbank.lk', 0),
(676, 'Kahawatta', '155', 'kahaw155@peoplesbank.lk', 0),
(677, 'Kalawana', '235', 'kalaw235@peoplesbank.lk', 0),
(678, 'Kaltota', '289', 'kalto289@peoplesbank.lk', 0),
(679, 'Kiriella', '266', 'kirie266@peoplesbank.lk', 0),
(680, 'Kuruwita', '263', 'kuruw263@peoplesbank.lk', 0),
(681, 'Nivitigala', '192', 'nivit192@peoplesbank.lk', 0),
(682, 'Pallebedda', '349', 'palle349@peoplesbank.lk', 0),
(683, 'Pelmadulla', '160', 'pelma160@peoplesbank.lk', 0),
(684, 'Rakwana', '129', 'rakwa129@peoplesbank.lk', 0),
(685, 'Ratnapura', '88', 'ratna088@peoplesbank.lk', 0),
(686, 'Rathnapura Town', '317', 'ratna317@peoplesbank.lk', 0),
(687, 'Udawalawa', '295', 'udawa295@peoplesbank.lk', 0),
(688, 'Aralaganwila', '253', 'arala253@peoplesbank.lk', 0),
(689, 'Bakamuna', '242', 'bakam242@peoplesbank.lk', 0),
(690, 'Dehiattakandiya', '330', 'dehia330@peoplesbank.lk', 0),
(691, 'Habarana', '203', 'habar203@peoplesbank.lk', 0),
(692, 'Hingurakgoda', '6', 'hingu006@peoplesbank.lk', 0),
(693, 'Medirigiriya', '231', 'medir231@peoplesbank.lk', 0),
(694, 'Polonnaruwa', '5', 'polon005@peoplesbank.lk', 0),
(695, 'Polonnaruwa T.', '232', 'polon232@peoplesbank.lk', 0),
(696, 'Thambala (SC Code 923)', '351', 'thamb351@peoplesbank.lk', 0),
(697, 'Welikanda', '254', 'welik254@peoplesbank.lk', 0),
(698, 'Kantalai', '90', 'kanta090@peoplesbank.lk', 0),
(699, 'Kinniya', '94', 'kinni094@peoplesbank.lk', 0),
(700, 'Muttur', '95', 'mutur095@peoplesbank.lk', 0),
(701, 'Pulmuday', '352', 'pulmu352@peoplesbank.lk', 0),
(702, 'Serunuwara', '233', 'serun233@peoplesbank.lk', 0),
(703, 'Trincomalee', '66', 'trinc066@peoplesbank.lk', 0),
(704, 'Trincomalee T.Br.', '255', 'trinc255@peoplesbank.lk', 0),
(705, 'Chettikulam', '356', 'chett356@peoplesbank.lk', 0),
(706, 'Kilinochchi', '48', 'kilin048@peoplesbank.lk', 0),
(707, 'Mankulam', '165', 'manku165@peoplesbank.lk', 0),
(708, 'Mullaitivu', '20', 'mulla020@peoplesbank.lk', 0),
(709, 'Paranthan', '111', 'paran111@peoplesbank.lk', 0),
(710, 'Murunkan', '166', 'murun166@peoplesbank.lk', 0),
(711, 'Mannar', '44', 'manna044@peoplesbank.lk', 0),
(712, 'Vauniya', '40', 'vavun040@peoplesbank.lk', 0);

-- --------------------------------------------------------

--
-- Table structure for table `business_type`
--

CREATE TABLE `business_type` (
  `bust_id` int(11) NOT NULL,
  `bust_name` varchar(64) NOT NULL,
  `bust_description` varchar(256) DEFAULT NULL,
  `bust_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `business_type`
--

INSERT INTO `business_type` (`bust_id`, `bust_name`, `bust_description`, `bust_deleted`) VALUES
(1, 'Individual', 'People like professionals', 0),
(2, 'Sole Proprietorship ', 'Business that own by one person', 0),
(3, 'Partnership', NULL, 0),
(4, 'Limited Liability Company ', NULL, 0),
(11, 'Mer', 'sac', 1);

-- --------------------------------------------------------

--
-- Table structure for table `log_reason`
--

CREATE TABLE `log_reason` (
  `log_id` int(11) NOT NULL,
  `reason_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `log_reason`
--

INSERT INTO `log_reason` (`log_id`, `reason_id`) VALUES
(104, 14),
(105, 10),
(105, 11),
(105, 14),
(106, 14),
(107, 11),
(109, 11),
(109, 12),
(109, 13),
(110, 11),
(110, 12),
(110, 13),
(111, 1),
(112, 1),
(113, 1),
(114, 11),
(115, 11),
(116, 11),
(116, 13),
(117, 13),
(118, 10),
(119, 14),
(120, 1),
(121, 1),
(122, 10),
(122, 14),
(123, 10),
(124, 1),
(125, 11),
(139, 1),
(140, 1),
(144, 11),
(145, 10),
(145, 11),
(146, 10),
(147, 10),
(148, 11);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `prod_id` int(11) NOT NULL,
  `prod_name` varchar(64) NOT NULL,
  `prod_description` varchar(256) DEFAULT NULL,
  `prod_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`prod_id`, `prod_name`, `prod_description`, `prod_deleted`) VALUES
(24, 'POS', 'Point of Sale', 0),
(25, 'IPG', 'Ipg', 0),
(26, 'New Product', 'No description 4', 0),
(27, 'MPOS', 'Mobile POS', 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_check_list`
--

CREATE TABLE `product_check_list` (
  `product_checklist_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `business_type` int(11) NOT NULL,
  `cheklist_item_id` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product_check_list`
--

INSERT INTO `product_check_list` (`product_checklist_id`, `product_id`, `business_type`, `cheklist_item_id`, `deleted`) VALUES
(44, 24, 1, 26, 0),
(45, 24, 1, 27, 0),
(46, 24, 2, 28, 0),
(47, 24, 2, 29, 0),
(48, 24, 3, 31, 0),
(49, 24, 3, 33, 0),
(50, 24, 4, 42, 0),
(51, 24, 4, 38, 0),
(52, 26, 1, 43, 0),
(53, 26, 1, 41, 0),
(54, 26, 1, 38, 0),
(55, 26, 2, 36, 0),
(56, 26, 2, 37, 0),
(57, 26, 2, 34, 0),
(58, 26, 3, 31, 0),
(59, 26, 3, 38, 0),
(60, 26, 4, 38, 0),
(61, 26, 4, 30, 0),
(62, 26, 4, 31, 0),
(63, 25, 1, 36, 0),
(64, 25, 1, 38, 0),
(65, 26, 1, 40, 0);

-- --------------------------------------------------------

--
-- Table structure for table `standerd_cheklist`
--

CREATE TABLE `standerd_cheklist` (
  `chk_id` int(11) NOT NULL,
  `chk_show_name` varchar(64) NOT NULL,
  `chk_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `standerd_cheklist`
--

INSERT INTO `standerd_cheklist` (`chk_id`, `chk_show_name`, `chk_deleted`) VALUES
(26, 'Application Signed by Merchant', 0),
(27, 'Each Page Signed by Merchant', 0),
(28, 'Completed Branch Evaluation', 0),
(29, 'NIC Copy/Copies of All Parties', 0),
(30, 'Billing Proof to Conform Address', 0),
(31, 'Relevant Document to Certify Profession', 0),
(32, 'Certified Copy/Copies of NIC', 0),
(33, 'Bank Statements of Last 3 Months (if Other Bank)', 0),
(34, 'Consent Letter from Merchant (if 3rd Party/ Joint AC)', 0),
(35, 'Certificate of Incorporation', 0),
(36, 'Articles of Association', 0),
(37, 'Board Resolution', 0),
(38, 'NIC Copies of All Directors', 0),
(39, 'Form No 1/48, Form No 20', 0),
(40, 'Recent Financial Statements', 0),
(41, 'Bank Statements of Last 3 Months (if Other Bank)', 0),
(42, 'Board Resolution (if 3rd Party/ Joint AC)', 0),
(43, 'Certified Copy of BR', 0),
(44, 'New checklist item', 1),
(45, 'rew', 1);

-- --------------------------------------------------------

--
-- Table structure for table `standerd_status`
--

CREATE TABLE `standerd_status` (
  `sts_id` int(11) NOT NULL,
  `sts_name` varchar(32) NOT NULL,
  `sts_color` varchar(16) DEFAULT NULL,
  `sts_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `standerd_status`
--

INSERT INTO `standerd_status` (`sts_id`, `sts_name`, `sts_color`, `sts_deleted`) VALUES
(1, 'New', '#0000FF', 0),
(2, 'Completed', '#eddb0c', 0),
(4, 'Active', '#00FF00', 0),
(11, 'Rejected', '#FF0000', 0),
(12, 'Pending', '#111fdf', 0),
(18, 'ddd', '#2b0303', 1),
(19, 'test', '#02b1a5', 1);

-- --------------------------------------------------------

--
-- Table structure for table `standerd_status_reason`
--

CREATE TABLE `standerd_status_reason` (
  `reason_id` int(11) NOT NULL,
  `reason_reason` varchar(128) NOT NULL,
  `reason_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `standerd_status_reason`
--

INSERT INTO `standerd_status_reason` (`reason_id`, `reason_reason`, `reason_deleted`) VALUES
(1, 'System Activity', 0),
(10, 'Application/ Agreement incorrect/ not available  \r\n(Please use the new application-1564/2022)', 0),
(11, 'Application should be signed', 0),
(12, 'Signature on each page of Agreement is required', 0),
(13, 'Completed Branch Evaluation Required', 0),
(14, 'Certified copy/copies of the merchant National identity Card (NIC) required', 0),
(15, 'cdsfcds', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `usr_id` int(11) NOT NULL,
  `usr_username` varchar(100) NOT NULL,
  `usr_fname` varchar(100) NOT NULL,
  `usr_lname` varchar(100) DEFAULT NULL,
  `usr_email` varchar(128) DEFAULT NULL,
  `usr_admin` tinyint(1) DEFAULT 0,
  `usr_create` tinyint(1) DEFAULT 0,
  `usr_update` tinyint(1) DEFAULT 0,
  `usr_view` tinyint(1) DEFAULT 0,
  `usr_delete` tinyint(1) DEFAULT 0,
  `usr_password` varchar(256) NOT NULL,
  `usr_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`usr_id`, `usr_username`, `usr_fname`, `usr_lname`, `usr_email`, `usr_admin`, `usr_create`, `usr_update`, `usr_view`, `usr_delete`, `usr_password`, `usr_deleted`) VALUES
(37, 'admin', 'Admin', 'Admin', 'admin@mail.com', 1, 1, 1, 1, 1, '*23AE809DDACAF96AF0FD78ED04B6A265E05AA257', 0),
(70, 'user', 'user', 'user', 'user@123', NULL, 1, 1, 1, 1, '*23AE809DDACAF96AF0FD78ED04B6A265E05AA257', 0),
(71, 'harshana', 'harshana', 'harshana', 'harshana@123', NULL, 1, 1, 1, 1, '*A4B6157319038724E3560894F7F932C8886EBFCF', 0),
(72, 'NotViewer', 'Not', 'Viewer', 'abc@gmail.com', NULL, 0, 1, NULL, 1, '*23AE809DDACAF96AF0FD78ED04B6A265E05AA257', 0),
(73, 'NotCreater', 'Not', 'Creater', 'notCreater@123', NULL, 0, 0, 1, 1, '*23AE809DDACAF96AF0FD78ED04B6A265E05AA257', 0),
(74, 'NotUpdater', 'Not', 'Updater', 'notUpdater@123', NULL, 1, 0, 1, 1, '*23AE809DDACAF96AF0FD78ED04B6A265E05AA257', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application`
--
ALTER TABLE `application`
  ADD PRIMARY KEY (`app_id`);

--
-- Indexes for table `application_checklist`
--
ALTER TABLE `application_checklist`
  ADD PRIMARY KEY (`app_chk_app_id`,`app_chk_chk_id`);

--
-- Indexes for table `application_status_log`
--
ALTER TABLE `application_status_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `business_type`
--
ALTER TABLE `business_type`
  ADD PRIMARY KEY (`bust_id`),
  ADD UNIQUE KEY `bust_name` (`bust_name`);

--
-- Indexes for table `log_reason`
--
ALTER TABLE `log_reason`
  ADD PRIMARY KEY (`log_id`,`reason_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`prod_id`),
  ADD UNIQUE KEY `prod_name` (`prod_name`);

--
-- Indexes for table `product_check_list`
--
ALTER TABLE `product_check_list`
  ADD PRIMARY KEY (`product_checklist_id`);

--
-- Indexes for table `standerd_cheklist`
--
ALTER TABLE `standerd_cheklist`
  ADD PRIMARY KEY (`chk_id`);

--
-- Indexes for table `standerd_status`
--
ALTER TABLE `standerd_status`
  ADD PRIMARY KEY (`sts_id`);

--
-- Indexes for table `standerd_status_reason`
--
ALTER TABLE `standerd_status_reason`
  ADD PRIMARY KEY (`reason_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`usr_id`),
  ADD UNIQUE KEY `usr_name` (`usr_username`),
  ADD UNIQUE KEY `usr_email` (`usr_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application`
--
ALTER TABLE `application`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `application_status_log`
--
ALTER TABLE `application_status_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=714;

--
-- AUTO_INCREMENT for table `business_type`
--
ALTER TABLE `business_type`
  MODIFY `bust_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `prod_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_check_list`
--
ALTER TABLE `product_check_list`
  MODIFY `product_checklist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `standerd_cheklist`
--
ALTER TABLE `standerd_cheklist`
  MODIFY `chk_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `standerd_status`
--
ALTER TABLE `standerd_status`
  MODIFY `sts_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `standerd_status_reason`
--
ALTER TABLE `standerd_status_reason`
  MODIFY `reason_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `usr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
