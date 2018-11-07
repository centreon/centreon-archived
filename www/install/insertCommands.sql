--
-- table `command`
--

INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(35, 'host-notify-by-email', '/usr/bin/printf &quot;%b&quot; &quot;***** Centreon notification *****#BR##BR#Type:    $NOTIFICATIONTYPE$#BR#Host:    $HOSTALIAS$#BR#Address: $HOSTADDRESS$#BR#State:   $HOSTSTATE$#BR#Since:   $HOSTDURATION$#BR#Info:    $HOSTOUTPUT$#BR#`[ &quot;$NOTIFICATIONCOMMENT$&quot; ] && echo &quot;Comment: $NOTIFICATIONCOMMENT$&quot;`&quot; | @MAILER@ -s &quot;$HOSTNAME$ - `[ &quot;$NOTIFICATIONTYPE$&quot; == &quot;PROBLEM&quot; -o &quot;$NOTIFICATIONTYPE$&quot; == &quot;RECOVERY&quot; ] && echo $HOSTSTATE$ || echo $NOTIFICATIONTYPE$` - $DATE$ $TIME$&quot; -r &quot;Centreon <centreon-engine>&quot; -S replyto=&quot;Centreon admin <$ADMINEMAIL$>&quot; $CONTACTEMAIL$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(36, 'service-notify-by-email', '/usr/bin/printf &quot;%b&quot; &quot;***** Centreon notification *****#BR##BR#Type:    $NOTIFICATIONTYPE$#BR#Host:    $HOSTALIAS$#BR#Address: $HOSTADDRESS$#BR#Service: $SERVICEDESC$#BR#State:   $SERVICESTATE$#BR#Since:   $SERVICEDURATION$#BR#Info:    $SERVICEOUTPUT$#BR#`[ &quot;$NOTIFICATIONCOMMENT$&quot; ] && echo &quot;Comment: $NOTIFICATIONCOMMENT$&quot;`&quot; | @MAILER@ -s &quot;$HOSTNAME$/$SERVICEDESC$ - `[ &quot;$NOTIFICATIONTYPE$&quot; == &quot;PROBLEM&quot; -o &quot;$NOTIFICATIONTYPE$&quot; == &quot;RECOVERY&quot; ] && echo $SERVICESTATE$ || echo $NOTIFICATIONTYPE$` - $DATE$ $TIME$&quot; -r &quot;Centreon <centreon-engine>&quot; -S replyto=&quot;Centreon admin <$ADMINEMAIL$>&quot; $CONTACTEMAIL$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(37, 'host-notify-by-epager', '/usr/bin/printf &quot;%b&quot; &quot;Host $HOSTALIAS$ is $HOSTSTATE$#BR#Info: $OUTPUT$#BR#Time: $DATETIME$\\&quot; | @MAILER@ -s \\&quot;$NOTIFICATIONTYPE$ alert - Host $HOSTNAME$ is $HOSTSTATE$\\&quot; $CONTACTPAGER$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(38, 'service-notify-by-epager', '/usr/bin/printf &quot;%b&quot; &quot;Service: $SERVICEDESC$#BR#Host: $HOSTNAME$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR#Info: $OUTPUT$#BR#Date: $DATETIME$&quot; | @MAILER@ -s &quot;$NOTIFICATIONTYPE$: $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$&quot; $CONTACTPAGER$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(89, 'host-notify-by-jabber', '$USER1$/notify_via_jabber  $CONTACTPAGER$ &quot;Host &#039;$HOSTALIAS$&#039; is $HOSTSTATE$ - Info: $HOSTOUTPUT$&quot;', '', 1, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(90, 'service-notify-by-jabber', '$USER1$/notify_via_jabber $CONTACTPAGER$ &quot;$NOTIFICATIONTYPE$ $HOSTNAME$ $SERVICEDESC$ $SERVICESTATE$ $SERVICEOUTPUT$ $LONGDATETIME$&quot;', '', 1, 0, NULL);

UPDATE `command` SET `enable_shell` = 1 WHERE `command_id` IN (35,36,37,38);

