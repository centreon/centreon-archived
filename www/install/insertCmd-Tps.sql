--
-- Commands and Timeperiods
--

INSERT INTO `command` VALUES ('', 'check_host_alive', '$USER1$#S#check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1', '', 2);

INSERT INTO `command` VALUES ('', 'check_local_cpu_load', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v CPULOAD -l $ARG1$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_swap', '$USER1$#S#check_swap -w $ARG1$ -c $ARG2$ -v', '!80!90', 2);
INSERT INTO `command` VALUES ('', 'check_local_disk_space', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v USEDDISKSPACE -l $ARG1$ -w $ARG2$ -c $ARG3$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_users', '$USER1$#S#check_users -H $HOSTADDRESS$ -w 3 -c 5', '', 2);

INSERT INTO `command` VALUES ('', 'check_disk_smb', '$USER1$#S#check_disk_smb -H $ARG1$ -s $ARG2$ -u $ARG3$ -p $ARG4$ -w $ARG5$', '', 2);
INSERT INTO `command` VALUES ('', 'check_distant_disk_space', '$USER1$#S#check_distant_disk_space -H $HOSTADDRESS$ -C $ARG1$ -p $ARG2$ -w $ARG3$ -c $ARG4$', '', 2);

INSERT INTO `command` VALUES ('', 'check_graph_load_average', '$USER1$#S#check_oreon_snmp_loadaverage $HOSTADDRESS$ -v $ARG1$ -C $ARG2$', '', 2);
INSERT INTO `command` VALUES ('', 'check_oreon_ping', '$USER1$#S#check_oreon_ping -H $HOSTADDRESS$ -w 200,20% -c 500,40% -n $ARG1$', '!3', 2);
INSERT INTO `command` VALUES ('', 'check_oreon_process', '$USER1$#S#check_oreon_snmp_process $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -n -w $ARG3$ -c $ARG4$ -p $ARG5$', '!2c!public!10!httpd', 2);
INSERT INTO `command` VALUES ('', 'check_oreon_remote_storage', '$USER1$#S#check_oreon_snmp_remote_storage $HOSTADDRESS$ -d $ARG1$  -C $ARG2$ -w $ARG3$ -c $ARG4$ -v $ARG5$', '!1!public!80!90!1', 2);
INSERT INTO `command` VALUES ('', 'check_oreon_traffic', '$USER1$#S#check_oreon_snmp_traffic -H $HOSTADDRESS$ -i $ARG1$ -w $ARG2$ -c $ARG3$ -C $ARG4$ -v $ARG5$', '!2!80!90!public!1', 2);
INSERT INTO `command` VALUES ('', 'check_oreon_traffic_limited', '$USER1$#S#check_oreon_snmp_traffic -H $HOSTADDRESS$ -i $ARG1$ -w $ARG2$ -c $ARG3$ -C $ARG4$ -v $ARG5$ -T $ARG6$', '!2!80!90!public!1!2', 2);

INSERT INTO `command` VALUES ('', 'check_hpjd', '$USER1$#S#check_hpjd -H $HOSTADDRESS$ -C public', '', 2);

INSERT INTO `command` VALUES ('', 'check_http', '$USER1$#S#check_http -H $HOSTADDRESS$', '127.0.0.1', 2);
INSERT INTO `command` VALUES ('', 'check_https', '$USER1$#S#check_http -S $HOSTADDRESS$', '', 2);

INSERT INTO `command` VALUES ('', 'check_load_average', '$USER1$#S#check_load $HOSTADDRESS$ -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_disk', '$USER1$#S#check_disk -w $ARG2$ -c $ARG3$ -p $ARG1$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_load', '$USER1$#S#check_load -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_procs', '$USER1$#S#check_procs -w $ARG1$ -c $ARG2$ -u $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_users', '$USER1$#S#check_users -w $ARG1$ -c $ARG2$', '', 2);

INSERT INTO `command` VALUES ('', 'check_nt_memuse', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v MEMUSE -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES ('', 'check_nt_service_state', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v SERVICESTATE -l $ARG1$ -s &quot;public&quot;', '', 2);

INSERT INTO `command` VALUES ('', 'check_tcp', '$USER1$#S#check_tcp -H $HOSTADDRESS$ -p $ARG1$ -w $ARG2$ -c $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_nntp', '$USER1$#S#check_nntp -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_pop', '$USER1$#S#check_pop -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_smtp', '$USER1$#S#check_smtp -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_dns', '$USER1$#S#check_dns -H $ARG1$ -s $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_ftp', '$USER1$#S#check_ftp -H $HOSTADDRESS$', '!127.0.0.1', 2);
INSERT INTO `command` VALUES ('', 'check_dhcp', '$USER1$#S#check_dhcp -s $HOSTADDRESS$ -i $ARG1$', '!eth0', 2);
INSERT INTO `command` VALUES ('', 'check_dig', '$USER1$#S#check_dig -H $HOSTADDRESS$ -l $ARG1$', '!www.oreon-project.org', 2);

INSERT INTO `command` VALUES ('', 'check_snmp', '$USER1$#S#check_snmp -H $HOSTADDRESS$ -o $ARG1$ -w $ARG2$ -C $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_telnet', '$USER1$#S#check_tcp -H $HOSTADDRESS$ -p 23', '', 2);
INSERT INTO `command` VALUES ('', 'check_udp', '$USER1$#S#check_udp -H $HOSTADDRESS$ -p $ARG1$', '', 2);
INSERT INTO `command` VALUES ('', 'check_graph_nt', '$USER1$#S#check_graph_nt.pl -H $HOSTADDRESS$ -p 1248 -v $ARG1$ -l $ARG2$ -s $ARG3$ -w $ARG4$ -c $ARG5$ -g -S $ARG6$', '', 2);

INSERT INTO `command` VALUES ('', 'host-notify-by-email-ng1', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon *****Notification#BR#Type:$NOTIFICATIONTYPE$#BR# Host: $HOSTNAME$#BR#State: $HOSTSTATE$Address: $HOSTADDRESS$#BR#Info: $OUTPUT$#BR#Date#S#Time: $DATETIME$&quot; | @MAILER@ -s &quot;Host $HOSTSTATE$ alert for $HOSTNAME$!&quot; $CONTACTEMAIL$', '', 1);
INSERT INTO `command` VALUES ('', 'host-notify-by-email-ng2', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon Notification *****#BR##BR#Type:$NOTIFICATIONTYPE$#BR#Host: $HOSTNAME$#BR#State: $HOSTSTATE$#BR#Address: $HOSTADDRESS$#BR#Info: $HOSTOUTPUT$#BR#Date#S#Time: $DATE$&quot; | @MAILER@ -s &quot;Host $HOSTSTATE$ alert for $HOSTNAME$!&quot; $CONTACTEMAIL$', '', 1);

INSERT INTO `command` VALUES ('', 'notify-by-email-ng1', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon  *****#BR##BR#Notification Type: $NOTIFICATIONTYPE$#BR##BR#Service: $SERVICEDESC$#BR#Host: $HOSTALIAS$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR#Date#S#Time: $DATETIME$#BR##BR#Additional Info:#BR##BR#$OUTPUT$&quot; | @MAILER@ -s &quot;** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$ **&quot; $CONTACTEMAIL$', '', 1);
INSERT INTO `command` VALUES ('', 'notify-by-email-ng2', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon Notification *****#BR##BR#Notification Type: $NOTIFICATIONTYPE$#BR##BR#Service: $SERVICEDESC$#BR#Host: $HOSTALIAS$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR##BR#Date#S#Time: $DATE$ Additional Info : $SERVICEOUTPUT$&quot; | @MAILER@ -s &quot;** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$ **&quot; $CONTACTEMAIL$', '', 1);

INSERT INTO `command` VALUES ('', 'host-notify-by-epager', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;Host $HOSTALIAS$ is $HOSTSTATE$#BR#Info: $OUTPUT$#BR#Time: $DATETIME$#BS#&quot; | @MAILER@ -s #BS#&quot;$NOTIFICATIONTYPE$ alert - Host $HOSTNAME$ is $HOSTSTATE$#BS#&quot; $CONTACTPAGER$', '', 1);
INSERT INTO `command` VALUES ('', 'notify-by-epager', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;Service: $SERVICEDESC$#BR#Host: $HOSTNAME$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR#Info: $OUTPUT$#BR#Date: $DATETIME$&quot; | @MAILER@ -s &quot;$NOTIFICATIONTYPE$: $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$&quot; $CONTACTPAGER$', '', 1);

INSERT INTO `command` VALUES ('', 'submit-host-check-result', '$USER1$#S#submit_host_check_result $HOSTNAME$ $HOSTSTATE$ &#039;$HOSTOUTPUT$&#039;', '', 3);
INSERT INTO `command` VALUES ('', 'submit-service-check-result', '$USER1$#S#submit_service_check_result $HOSTNAME$ $SERVICEDESC$ $SERVICESTATE$ &#039;$SERVICEOUTPUT$&#039;', '', 3);
INSERT INTO `command` VALUES ('', 'process-service-perfdata', '$USER1$#S#process-service-perfdata  &quot;$LASTSERVICECHECK$&quot; &quot;$HOSTNAME$&quot; &quot;$SERVICEDESC$&quot; &quot;$SERVICEOUTPUT$&quot; &quot;$SERVICESTATE$&quot; &quot;$SERVICEPERFDATA$&quot;', '', 3);

--
-- table `timeperiod`
--

INSERT INTO `timeperiod` VALUES ('', '24x7', '24_Hours_A_Day,_7_Days_A_Week', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES ('', 'none', 'No Time Is A Good Time', '', '', '', '', '', '', '');
INSERT INTO `timeperiod` VALUES ('', 'nonworkhours', 'Non-Work Hours', '00:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES ('', 'workhours', 'Work hours', '', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '');
