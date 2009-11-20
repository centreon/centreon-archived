INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 603, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'a', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'c', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'w', './include/common/javascript/changetab.js', 'initChangeTab');
INSERT INTO `topology_JS` (`id_t_js`, `id_page`, `o`, `PathName_js`, `Init`) VALUES(NULL, 60301, 'mc', './include/common/javascript/changetab.js', 'initChangeTab');

ALTER TABLE `host` ADD `host_retry_check_interval` INT NULL AFTER `host_check_interval` ;

ALTER TABLE `contact` ADD `contact_address1` VARCHAR( 200 ) NULL AFTER `contact_pager` ,
ADD `contact_address2` VARCHAR( 250 ) NULL AFTER `contact_address1` ,
ADD `contact_address3` VARCHAR( 200 ) NULL AFTER `contact_address2` ,
ADD `contact_address4` VARCHAR( 200 ) NULL AFTER `contact_address3` ,
ADD `contact_address5` VARCHAR( 200 ) NULL AFTER `contact_address4` ,
ADD `contact_address6` VARCHAR( 200 ) NULL AFTER `contact_address5` ;