CREATE TABLE IF NOT EXISTS auth_ressource (
 ar_id INT(11) NOT NULL AUTO_INCREMENT,
 ar_type VARCHAR(50) NOT NULL,
 ar_enable ENUM('0', '1') DEFAULT '0',
 ar_order INT(3) DEFAULT 0,
 PRIMARY KEY (ar_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO auth_ressource (ar_id, ar_type, ar_enable, ar_order) VALUES (2, 'ldap_tmpl', '0', 0);
INSERT INTO auth_ressource (ar_id, ar_type, ar_enable, ar_order) VALUES (1, 'ldap', '1', 1);

--
-- Contraintes pour la table `auth_ressource_info`
--
ALTER TABLE `auth_ressource_info`
  ADD CONSTRAINT `auth_ressource_info_ibfk_1` FOREIGN KEY (`ar_id`) REFERENCES `auth_ressource` (`ar_id`) ON DELETE CASCADE;

DELETE FROM topology_JS WHERE id_page = 60904;

UPDATE topology SET topology_page = '60909' WHERE topology_page = '60904' AND topology_name = 'Configuration';

INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60909, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60909, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (60909, 'w', './include/common/javascript/changetab.js', 'initChangeTab');

UPDATE `informations` SET `value` = '2.3.1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0' LIMIT 1;