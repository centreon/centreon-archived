DELETE FROM topology WHERE topology_page IN (
'2021501',
'2021502',
'2021503',
'2020201',
'2020202',
'2020203',
'2020101',
'2020102',
'2020103',
'2020104');

DELETE FROM topology_JS WHERE id_page IN (
'2021501',
'2021502',
'2021503',
'2020201',
'2020202',
'2020203',
'2020101',
'2020102',
'2020103',
'2020104');

UPDATE `informations` SET `value` = '2.3.0-b4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b3' LIMIT 1;