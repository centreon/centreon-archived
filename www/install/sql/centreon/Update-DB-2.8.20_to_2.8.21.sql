-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.21' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.20' LIMIT 1;

-- Temporary fix for limit realtime and configuration Rest API
ALTER TABLE `contact` ADD COLUMN `reach_api_rt` int(1) DEFAULT 0 AFTER `reach_api`;
-- Update users with right to reach api
UPDATE contact SET reach_api_rt = "1" WHERE reach_api = "1";
-- Manage new feature proposal
CREATE TABLE IF NOT EXISTS contact_feature (
  contact_id INT NOT NULL,
  feature VARCHAR(255) NOT NULL,
  feature_version VARCHAR(50) NOT NULL,
  feature_enabled TINYINT DEFAULT 0,
  PRIMARY KEY (contact_id, feature, feature_version),
  FOREIGN KEY (contact_id) REFERENCES contact (contact_id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
