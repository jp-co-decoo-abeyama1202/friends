CREATE DATABASE friends_statistics DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_bin;
use friends_statistics;

CREATE TABLE `daily_register` (
  `regist_date` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`regist_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `daily_register_device` (
  `regist_date` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`regist_date`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `daily_register_sex` (
  `regist_date` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`regist_date`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `uu_daily` (
  `id` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE `daily_uu` (
  `regist_date` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`regist_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `daily_uu_device` (
  `regist_date` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`regist_date`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `daily_uu_sex` (
  `regist_date` int(10) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`regist_date`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- -----------------------------------------------------
-- Table `friends_statistics`.`push`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `friends_statistics`.`push` ;

CREATE TABLE IF NOT EXISTS `friends_statistics`.`push` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `from_id` INT UNSIGNED NOT NULL,
  `to_id` INT UNSIGNED NOT NULL,
  `type` TINYINT UNSIGNED NOT NULL,
  `result` TINYINT UNSIGNED NOT NULL,
  `result_value` longtext NOT NULL,
  `create_time` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = MyISAM;