/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : bod_core

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2015-04-27 15:42:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for social_network
-- ----------------------------
DROP TABLE IF EXISTS `social_network`;
CREATE TABLE `social_network` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System given id.',
  `name` varchar(45) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Name of social network.',
  `url` text COLLATE utf8_turkish_ci COMMENT 'Social network url.',
  `url_key` varchar(155) COLLATE utf8_turkish_ci NOT NULL COMMENT 'Url key.',
  `site` int(10) unsigned NOT NULL,
  `icon` varchar(155) COLLATE utf8_turkish_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_turkish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxUSocialNetworkId` (`id`) USING BTREE,
  UNIQUE KEY `idxUSocialNetworkUrlKey` (`url_key`,`site`) USING BTREE,
  KEY `idxFSiteOfSocialNetwork` (`site`) USING BTREE,
  CONSTRAINT `idxFSiteOfSocialNetwork` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_turkish_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of social_network
-- ----------------------------
