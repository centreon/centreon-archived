--
-- Commands and Timeperiods
--
INSERT INTO `command` ( `command_name`, `command_line`, `command_example`, `command_type`) VALUES 
( 'check_cpu_load', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v CPULOAD -l $ARG1$ -s &quot;public&quot;', '', 2),
( 'check_disk_smb', '$USER1$#S#check_disk_smb -H $ARG1$ -s $ARG2$ -u $ARG3$ -p $ARG4$ -w $ARG5$', '', 2),
( 'check_disk_space', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v USEDDISKSPACE -l $ARG1$ -w $ARG2$ -c $ARG3$ -s &quot;public&quot;', '', 2),
( 'check_distant_disk_space', '$USER1$#S#check_distant_disk_space -H $HOSTADDRESS$ -C $ARG1$ -p $ARG2$ -w $ARG3$ -c $ARG4$', '', 2),
( 'check_dns', '$USER1$#S#check_dns -H $ARG1$ -s $HOSTADDRESS$', '', 2),
( 'check_ftp', '$USER1$#S#check_ftp -H $HOSTADDRESS$', '!127.0.0.1', 2),
( 'check_centreon_load_average', '$USER1$#S#check_centreon_snmp_loadaverage -H $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -w $ARG3$ -c $ARG4$', '!1!public!&quot;1,2,3&quot;!&quot;2,3,4&quot;', 2),
( 'check_centreon_ping', '$USER1$#S#check_centreon_ping -H $HOSTADDRESS$ -w 200,20% -c 500,40% -f -n $ARG1$', '!3', 2),
( 'check_centreon_running_process', '$USER1$#S#check_centreon_snmp_process -H $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -p $ARG3$ -n', '!1!public!httpd', 2),
( 'check_centreon_remote_storage', '$USER1$#S#check_centreon_snmp_remote_storage -H $HOSTADDRESS$ -$ARG1$ -C $ARG2$ -w $ARG3$ -c $ARG4$ -d $ARG5$  -n', '!1!public!80!90!&quot;#S#srv&quot;', 2),
( 'check_centreon_traffic', '$USER1$#S#check_centreon_snmp_traffic -H $HOSTADDRESS$ -v $ARG1$  -C $ARG2$ -w $ARG3$ -c $ARG4$ -i $ARG5$ -n', '!1!public!80!90!&quot;eth0&quot;', 2),
( 'check_host_alive', '$USER1$#S#check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1', '', 2),
( 'check_hpjd', '$USER1$#S#check_hpjd -H $HOSTADDRESS$ -C public', '', 2),
( 'check_http', '$USER1$#S#check_http -H $HOSTADDRESS$ -s $ARG1$ -v', '127.0.0.1', 2),
( 'check_https', '$USER1$#S#check_http -S $HOSTADDRESS$', '', 2),
( 'check_http_up', '$USER1$#S#check_http -H $HOSTADDRESS$', '', 2),
( 'check_load_average', '$USER1$#S#check_load $HOSTADDRESS$ -w $ARG1$ -c $ARG2$', '', 2),
( 'check_local_disk', '$USER1$#S#check_disk -w $ARG2$ -c $ARG3$ -p $ARG1$', '', 2),
( 'check_local_load', '$USER1$#S#check_load -w $ARG1$ -c $ARG2$', '', 2),
( 'check_local_procs', '$USER1$#S#check_procs -w $ARG1$ -c $ARG2$ -u $ARG3$', '', 2),
( 'check_local_users', '$USER1$#S#check_users -w $ARG1$ -c $ARG2$', '', 2),
( 'check_memuse', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v MEMUSE -s &quot;public&quot;', '', 2),
( 'check_nntp', '$USER1$#S#check_nntp -H $HOSTADDRESS$', '', 2),
( 'check_pop', '$USER1$#S#check_pop -H $HOSTADDRESS$', '', 2),
( 'check_process', '$USER1$#S#check_graph_process.pl $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -p $ARG3$', '!1!public!httpd', 2),
( 'check_snmp', '$USER1$#S#check_snmp -H $HOSTADDRESS$ -o $ARG1$ -w $ARG2$ -C $ARG3$', '', 2),
( 'check_snmp_disk', '$USER1$#S#check_disk_space.pl $HOSTADDRESS$ $ARG1$ $ARG2$  $ARG3$ $ARG4$', '', 2),
( 'check_swap', '$USER1$#S#check_swap -w $ARG1$ -c $ARG2$ -v', '!80!90', 2),
( 'check_centreon_tcp', '$USER1$#S#check_centreon_tcp -H $HOSTADDRESS$ -p $ARG1$ -w $ARG2$ -c $ARG3$', '!80!30!40', 2),
( 'check_telnet', '$USER1$#S#check_tcp -H $HOSTADDRESS$ -p 23', '', 2),
( 'check_udp', '$USER1$#S#check_udp -H $HOSTADDRESS$ -p $ARG1$', '', 2),
( 'check_uptime', '$USER1$#S#check_uptime.pl $HOSTADDRESS$ $ARG1$ $ARG2$', '', 2),
( 'process-service-perfdata', '$USER1$#S#process-service-perfdata  &quot;$LASTSERVICECHECK$&quot; &quot;$HOSTNAME$&quot; &quot;$SERVICEDESC$&quot; &quot;$SERVICEOUTPUT$&quot; &quot;$SERVICESTATE$&quot; &quot;$SERVICEPERFDATA$&quot;', '', 1),
( 'check_centreon_nt', '$USER1$#S#check_centreon_nt -H $HOSTADDRESS$ -p 1248 -v $ARG1$ -l $ARG2$ -s $ARG3$ -w $ARG4$ -c $ARG5$', '', 2),
( 'check_centreon_uptime', '$USER1$#S#check_centreon_snmp_uptime -H $HOSTADDRESS$ -C $ARG1$ -v $ARG2$ -d', '', 2),
( 'check_centreon_http', '$USER1$#S#check_centreon_http -H $HOSTADDRESS$', '', 2),
( 'check_service_state', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v SERVICESTATE -l $ARG1$ -s &quot;public&quot;', '', 2),
( 'check_users', '$USER1$#S#check_users -H $HOSTADDRESS$ -w 3 -c 5', '', 2),
( 'check_http2', '$USER1$#S#check_http -I $HOSTADDRESS$ -u $ARG1$', '', 2),
( 'host-notify-by-email-ng1', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon *****Notification#BR#Type:$NOTIFICATIONTYPE$#BR# Host: $HOSTNAME$#BR#State: $HOSTSTATE$Address: $HOSTADDRESS$#BR#Info: $OUTPUT$#BR#Date#S#Time: $DATETIME$&quot; | @MAILER@ -s &quot;Host $HOSTSTATE$ alert for $HOSTNAME$!&quot; $CONTACTEMAIL$', '', 1),
( 'notify-by-email-ng1', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon  *****#BR##BR#Notification Type: $NOTIFICATIONTYPE$#BR##BR#Service: $SERVICEDESC$#BR#Host: $HOSTALIAS$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR#Date#S#Time: $DATETIME$#BR##BR#Additional Info:#BR##BR#$OUTPUT$&quot; | @MAILER@ -s &quot;** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$ **&quot; $CONTACTEMAIL$', '', 1),
( 'host-notify-by-email-ng2', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon Notification *****#BR##BR#Type:$NOTIFICATIONTYPE$#BR#Host: $HOSTNAME$#BR#State: $HOSTSTATE$#BR#Address: $HOSTADDRESS$#BR#Info: $HOSTOUTPUT$#BR#Date#S#Time: $DATE$&quot; | @MAILER@ -s &quot;Host $HOSTSTATE$ alert for $HOSTNAME$!&quot; $CONTACTEMAIL$', '', 1),
( 'notify-by-email-ng2', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon Notification *****#BR##BR#Notification Type: $NOTIFICATIONTYPE$#BR##BR#Service: $SERVICEDESC$#BR#Host: $HOSTALIAS$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR##BR#Date#S#Time: $DATE$ Additional Info : $SERVICEOUTPUT$&quot; | @MAILER@ -s &quot;** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$ **&quot; $CONTACTEMAIL$', '', 1),
( 'notify-by-epager', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;Service: $SERVICEDESC$#BR#Host: $HOSTNAME$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR#Info: $OUTPUT$#BR#Date: $DATETIME$&quot; | @MAILER@ -s &quot;$NOTIFICATIONTYPE$: $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$&quot; $CONTACTPAGER$', '', 1),
( 'host-notify-by-epager', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;Host $HOSTALIAS$ is $HOSTSTATE$#BR#Info: $OUTPUT$#BR#Time: $DATETIME$#BS#&quot; | @MAILER@ -s #BS#&quot;$NOTIFICATIONTYPE$ alert - Host $HOSTNAME$ is $HOSTSTATE$#BS#&quot; $CONTACTPAGER$', '', 1),
( 'submit-host-check-result', '$USER1$#S#submit_host_check_result $HOSTNAME$ $HOSTSTATE$ &#039;$HOSTOUTPUT$&#039;', '', 2),
( 'submit-service-check-result', '$USER1$#S#submit_service_check_result $HOSTNAME$ $SERVICEDESC$ $SERVICESTATE$ &#039;$SERVICEOUTPUT$&#039;', '', 2);

--
-- table `timeperiod`
--

INSERT INTO `timeperiod` VALUES ('', '24x7', '24_Hours_A_Day,_7_Days_A_Week', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES ('', 'none', 'No Time Is A Good Time', '', '', '', '', '', '', '');
INSERT INTO `timeperiod` VALUES ('', 'nonworkhours', 'Non-Work Hours', '00:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES ('', 'workhours', 'Work hours', '', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '');
