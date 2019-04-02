-- Manage new feature proposal
CREATE TABLE IF NOT EXISTS contact_feature (
  contact_id INT NOT NULL,
  feature VARCHAR(255) NOT NULL,
  feature_version VARCHAR(50) NOT NULL,
  feature_enabled TINYINT DEFAULT 0,
  PRIMARY KEY (contact_id, feature, feature_version),
  FOREIGN KEY (contact_id) REFERENCES contact (contact_id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
