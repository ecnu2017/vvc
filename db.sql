USE mysql;
DELETE FROM user WHERE password = '' AND user != 'root';

DROP DATABASE IF EXISTS vvc;
CREATE DATABASE vvc DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER IF NOT EXISTS vvc_admin@localhost identified by '123';
GRANT SELECT, INSERT, UPDATE, DELETE
ON vvc.* to vvc_admin@localhost identified by '123';

USE vvc;

/*Table structure for table `drug` */

DROP TABLE IF EXISTS `drug`;

CREATE TABLE `drug` (
  `drug_id` int(11) NOT NULL AUTO_INCREMENT,
  `drug_name` varchar(12) DEFAULT NULL,
  `drug_text` text DEFAULT NULL,
  `drug_picture` varchar(40) DEFAULT NULL,
  `drug_cost` float DEFAULT NULL,
  PRIMARY KEY (`drug_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `drug` */

/*Table structure for table `illdrug` */

DROP TABLE IF EXISTS `illdrug`;

CREATE TABLE `illdrug` (
  `ill_id` int(11) NOT NULL,
  `drug_id` int(11) NOT NULL,
  PRIMARY KEY (`ill_id`,`drug_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `illdrug` */

/*Table structure for table `illness` */

DROP TABLE IF EXISTS `illness`;

CREATE TABLE `illness` (
  `ill_id` int(11) NOT NULL AUTO_INCREMENT,
  `ill_name` varchar(12) DEFAULT NULL,
  `class_name` varchar(12) DEFAULT NULL,
  `ill_describe` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `illness` */

/*Table structure for table `illpic` */

DROP TABLE IF EXISTS `illpic`;

CREATE TABLE `illpic` (
  `ill_id` int(11) NOT NULL,
  `step_num` int(11) NOT NULL,
  `pic_path` varchar(50) NOT NULL,
  PRIMARY KEY (`ill_id`,`step_num`, `pic_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `illpic` */

/*Table structure for table `illvid` */

DROP TABLE IF EXISTS `illvid`;

CREATE TABLE `illvid` (
  `ill_id` int(11) DEFAULT NULL,
  `step_num` int(11) DEFAULT NULL,
  `vid_path` varchar(150) NOT NULL,
  PRIMARY KEY (`ill_id`,`step_num`, `vid_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `illvid` */

/*Table structure for table `payments` */

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `ill_id` int(11) DEFAULT NULL,
  `pay_name` varchar(50) DEFAULT NULL,
  `pay_cost` float DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  PRIMARY KEY (`pay_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `payments` */

/*Table structure for table `stepname` */

DROP TABLE IF EXISTS `stepname`;

CREATE TABLE `stepname` (
  `step_num` int(11) NOT NULL,
  `step_name` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`step_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `stepname` */

set names utf8;
INSERT INTO stepname values (1, '接诊');
INSERT INTO stepname values (2, '检查');
INSERT INTO stepname values (3, '诊断');
INSERT INTO stepname values (4, '治疗方案');

/*Table structure for table `steps` */

DROP TABLE IF EXISTS `steps`;

CREATE TABLE `steps` (
  `ill_id` int(11) NOT NULL,
  `step_num` int(11) NOT NULL,
  `step_text` text,
  PRIMARY KEY (`ill_id`,`step_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `steps` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(20) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `users` */
