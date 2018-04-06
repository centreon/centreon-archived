-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.23' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.22' LIMIT 1;

-- Manage new feature proposal
CREATE TABLE IF NOT EXISTS contact_feature (
  contact_id INT NOT NULL,
  feature VARCHAR(255) NOT NULL,
  feature_version VARCHAR(50) NOT NULL,
  feature_enabled TINYINT DEFAULT 0,
  PRIMARY KEY (contact_id, feature, feature_version),
  FOREIGN KEY (contact_id) REFERENCES contact (contact_id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
