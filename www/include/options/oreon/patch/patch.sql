ALTER TABLE `general_opt`
ADD COLUMN `patch_type_stable` ENUM('Y', 'N') DEFAULT 'Y',
ADD COLUMN `patch_type_RC` ENUM('Y', 'N') DEFAULT 'N',
ADD COLUMN `patch_type_patch` ENUM('Y', 'N') DEFAULT 'N',
ADD COLUMN `patch_type_secu` ENUM('Y', 'N') DEFAULT 'Y',
ADD COLUMN `patch_type_beta` ENUM('Y', 'N') DEFAULT 'N',
ADD COLUMN `patch_url_service` VARCHAR(255),
ADD COLUMN `patch_url_download` VARCHAR(255),
ADD COLUMN `patch_path_download` VARCHAR(255);

UPDATE `general_opt` SET `patch_url_service`='http://localhost/SiteOreonPatch/version.php',
`patch_url_download`='http://localhost/SiteOreonPatch/patches/', `patch_path_download`='/tmp' WHERE `gopt_id`=1;

INSERT INTO `topology` (`topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) 
VALUES ('m_patch', './img/icones/16x16/download.gif', 501, 50105, 11, 1, './include/options/oreon/patch/checkVersion.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) 
VALUES ('m_checkVersion', '', 50105, 5010501, 1, 1, './include/options/oreon/patch/checkVersion.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) 
VALUES ('m_patchOptions', '', 50105, 5010502, 2, 1, './include/options/oreon/patch/patchOptions.php', NULL, '0', '0', '1');
INSERT INTO `topology` (`topology_name` , `topology_icone` , `topology_parent` , `topology_page` , `topology_order` , `topology_group` , `topology_url` , `topology_url_opt` , `topology_popup` , `topology_modules` , `topology_show`) 
VALUES ('m_preUpdate', '', 50105, 5010503, 3, 1, './include/options/oreon/patch/preUpdate.php', NULL, '0', '0', '0');