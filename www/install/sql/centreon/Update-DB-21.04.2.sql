DELETE FROM `topology` WHERE `topology_page` IN (6090901, 6090902);

CREATE TABLE `password_security_policy` (
  `password_length` int(11) UNSIGNED NOT NULL DEFAULT 12,
  `uppercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `lowercase_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `integer_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `special_characters` enum('0', '1') NOT NULL DEFAULT '1',
  `attempts` int(11) UNSIGNED NOT NULL DEFAULT 5,
  `blocking_duration` int(11) UNSIGNED NOT NULL DEFAULT 900,
  `password_expiration` int(11) UNSIGNED NOT NULL DEFAULT 7776000,
  `delay_before_new_password` int(11) UNSIGNED NOT NULL DEFAULT 3600
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `password_security_policy`
(`password_length`, `uppercase_characters`, `lowercase_characters`, `integer_characters`, `special_characters`,
`attempts`, `blocking_duration`, `password_expiration`, `delay_before_new_password`)
VALUES (12, '1', '1', '1', '1', 5, 900, 7776000, 3600);