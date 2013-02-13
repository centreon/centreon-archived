ALTER TABLE `nagios_acknowledgements` ADD INDEX ( `entry_time` , `object_id` , `acknowledgement_type` );
