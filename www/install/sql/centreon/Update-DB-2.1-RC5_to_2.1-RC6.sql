UPDATE `informations` SET `value` = '2.1-RC6' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC5' LIMIT 1;

UPDATE `command` SET `command_line` = '$USER1$#S#process-service-perfdata  &quot;$LASTSERVICECHECK$&quot; &quot;$HOSTNAME$&quot; &quot;$SERVICEDESC$&quot; &quot;$LASTSERVICESTATE$&quot; &quot;$SERVICESTATE$&quot; &quot;$SERVICEPERFDATA$&quot;' WHERE `command_name` = 'process-service-perfdata' LIMIT 1;
