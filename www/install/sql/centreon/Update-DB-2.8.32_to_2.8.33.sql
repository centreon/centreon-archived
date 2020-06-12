-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.33' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.32' LIMIT 1;

DELETE FROM `topology_JS` WHERE `PathName_js` = './include/common/javascript/centreon/serviceFilterByHost.js';
