-- Add new column
ALTER TABLE `platform_topology` ADD COLUMN `hostname` varchar(255) NULL AFTER `address`;

UPDATE `cfg_nagios`
SET `illegal_object_name_chars` = '~!$%^&*"|''<>?,()='
WHERE `illegal_object_name_chars` = '~!$%^&amp;*&quot;|&#039;&lt;&gt;?,()=';

UPDATE `cfg_nagios`
SET `illegal_macro_output_chars` = '`~$^&"|''<>'
WHERE `illegal_macro_output_chars` = '`~$^&amp;&quot;|&#039;&lt;&gt;';
