--
-- Commands and Timeperiods
--

INSERT INTO `command` VALUES ('', 'check_cpu_load', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v CPULOAD -l $ARG1$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES ('', 'check_disk_smb', '$USER1$#S#check_disk_smb -H $ARG1$ -s $ARG2$ -u $ARG3$ -p $ARG4$ -w $ARG5$', '', 2);
INSERT INTO `command` VALUES ('', 'check_disk_space', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v USEDDISKSPACE -l $ARG1$ -w $ARG2$ -c $ARG3$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES ('', 'check_distant_disk_space', '$USER1$#S#check_distant_disk_space -H $HOSTADDRESS$ -C $ARG1$ -p $ARG2$ -w $ARG3$ -c $ARG4$', '', 2);
INSERT INTO `command` VALUES ('', 'check_dns', '$USER1$#S#check_dns -H $ARG1$ -s $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_ftp', '$USER1$#S#check_ftp -H $HOSTADDRESS$', '!127.0.0.1', 2);
INSERT INTO `command` VALUES ('', 'check_graph_load_average', '$USER1$#S#check_graph_load_average.pl $HOSTADDRESS$ -v $ARG1$ -W -C $ARG2$ -g -S $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_graph_ping', '$USER1$#S#check_graph_ping.pl -H $HOSTADDRESS$ -w 200,20% -c 500,40% -f -n $ARG1$ -g -S $ARG2$', '!3', 2);
INSERT INTO `command` VALUES ('', 'check_graph_process', '$USER1$#S#check_graph_process.pl $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -n -g -S $ARG3$', '!2c!public', 2);
INSERT INTO `command` VALUES ('', 'check_graph_remote_storage', '$USER1$#S#check_graph_remote_storage.pl $HOSTADDRESS$ -d $ARG1$  -C $ARG2$ -w $ARG3$ -c $ARG4$ -v $ARG5$ -g -S $ARG6$', '!1!public!80!90!1', 2);
INSERT INTO `command` VALUES ('', 'check_graph_traffic', '$USER1$#S#check_graph_traffic.pl -H $HOSTADDRESS$ -i $ARG1$ -w $ARG2$ -c $ARG3$ -C $ARG4$ -v $ARG5$ -g -S $ARG6$', '!2!80!90!public!1', 2);
INSERT INTO `command` VALUES ('', 'check_host_alive', '$USER1$#S#check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1', '', 2);
INSERT INTO `command` VALUES ('', 'check_hpjd', '$USER1$#S#check_hpjd -H $HOSTADDRESS$ -C public', '', 2);
INSERT INTO `command` VALUES ('', 'check_http', '$USER1$#S#check_http -H $HOSTADDRESS$ -s $ARG1$ -v', '127.0.0.1', 2);
INSERT INTO `command` VALUES ('', 'check_https', '$USER1$#S#check_http -S $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_http_up', '$USER1$#S#check_http -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_load_average', '$USER1$#S#check_load $HOSTADDRESS$ -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_disk', '$USER1$#S#check_disk -w $ARG2$ -c $ARG3$ -p $ARG1$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_load', '$USER1$#S#check_load -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_procs', '$USER1$#S#check_procs -w $ARG1$ -c $ARG2$ -u $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_local_users', '$USER1$#S#check_users -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES ('', 'check_memuse', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v MEMUSE -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES ('', 'check_nntp', '$USER1$#S#check_nntp -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_pop', '$USER1$#S#check_pop -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES ('', 'check_process', '$USER1$#S#check_graph_process.pl $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -p $ARG3$', '!1!public!httpd', 2);
INSERT INTO `command` VALUES ('', 'check_snmp', '$USER1$#S#check_snmp -H $HOSTADDRESS$ -o $ARG1$ -w $ARG2$ -C $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_snmp_disk', '$USER1$#S#check_disk_space.pl $HOSTADDRESS$ $ARG1$ $ARG2$  $ARG3$ $ARG4$', '', 2);
INSERT INTO `command` VALUES ('', 'check_swap', '$USER1$#S#check_swap -w $ARG1$ -c $ARG2$ -v', '!80!90', 2);
INSERT INTO `command` VALUES ('', 'check_tcp', '$USER1$#S#check_tcp -H $HOSTADDRESS$ -p $ARG1$ -w $ARG2$ -c $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_telnet', '$USER1$#S#check_tcp -H $HOSTADDRESS$ -p 23', '', 2);
INSERT INTO `command` VALUES ('', 'check_udp', '$USER1$#S#check_udp -H $HOSTADDRESS$ -p $ARG1$', '', 2);
INSERT INTO `command` VALUES ('', 'check_uptime', '$USER1$#S#check_uptime.pl $HOSTADDRESS$ $ARG1$ $ARG2$', '', 2);
INSERT INTO `command` VALUES ('', 'process-host-perfdata', '#S#usr#S#bin#S#printf #BS#&quot;%b#BS#&quot; #BS#&quot;$LASTCHECK$t$HOSTNAME$#T#$HOSTSTATE$#T#$HOSTATTEMPT$#T#$STATETYPE$#T#$EXECUTIONTIME$#T#$OUTPUT$#T#$PERFDATA$#BS#&quot; &gt;&gt; #S#usr#S#local#S#nagios#S#var#S#host-perfdata.out', '', 1);
INSERT INTO `command` VALUES ('', 'process-service-perfdata', '#S#usr#S#local#S#nagios#S#libexec#S#process-service-perfdata  &quot;$LASTCHECK$&quot; &quot;$HOSTNAME$&quot; &quot;$SERVICEDESC$&quot; &quot;$OUTPUT$&quot; &quot;$SERVICESTATE$&quot; &quot;$PERFDATA$&quot;', '', 1);
INSERT INTO `command` VALUES ('', 'check_graph_nt', '$USER1$#S#check_graph_nt.pl -H $HOSTADDRESS$ -p 1248 -v $ARG1$ -l $ARG2$ -s $ARG3$ -w $ARG4$ -c $ARG5$ -g -S $ARG6$', '', 2);
INSERT INTO `command` VALUES ('', 'check_graph_uptime', '$USER1$#S#check_graph_uptime.pl -H $HOSTADDRESS$ -C $ARG1$ -v $ARG2$ -d -g -S $ARG3$', '', 2);
INSERT INTO `command` VALUES ('', 'check_graph_http', '$USER1$#S#check_graph_http.pl -H $HOSTADDRESS$ -f', '', 2);
INSERT INTO `command` VALUES ('', 'check_service_state', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v SERVICESTATE -l $ARG1$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES ('', 'check_users', '$USER1$#S#check_users -H $HOSTADDRESS$ -w 3 -c 5', '', 2);
INSERT INTO `command` VALUES ('', 'check_http2', '$USER1$#S#check_http -I $HOSTADDRESS$ -u $ARG1$', '', 2);


INSERT INTO `command` VALUES ('', 'host-notify-by-email-ng1', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon *****Notification#BS##BR#Type:$NOTIFICATIONTYPE$#BS##BR# Host: $HOSTNAME$#BS##BR#State: $HOSTSTATE$Address: $HOSTADDRESS$#BS##BR#Info: $OUTPUT$#BS##BR#Date#S#Time: $DATETIME$&quot; | @MAILER@ -s &quot;Host $HOSTSTATE$ alert for $HOSTNAME$!&quot; $CONTACTEMAIL$', '', 1);
INSERT INTO `command` VALUES ('', 'host-notify-by-epager', '#S#usr#S#bin#S#printf #BS#&quot;%b#BS#&quot; #BS#&quot;Host #BS#&#039;$HOSTALIAS$#BS#&#039; is $HOSTSTATE$#BR#Info: $OUTPUT$#BR#Time: $DATETIME$#BS#&quot; | @MAILER@ -s #BS#&quot;$NOTIFICATIONTYPE$ alert - Host $HOSTNAME$ is $HOSTSTATE$#BS#&quot; $CONTACTPAGER$', '', 1);

INSERT INTO `command` VALUES ('', 'notify-by-email-ng1', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon  *****#BS##BS#n#BS##BS#nNotification Type: $NOTIFICATIONTYPE$#BS##BS#n#BS##BS#nService: $SERVICEDESC$#BS##BS#nHost: $HOSTALIAS$#BS##BS#nAddress: $HOSTADDRESS$#BS##BS#nState: $SERVICESTATE$#BS##BS#n#BS##BS#nDate#S#Time: $DATETIME$#BS##BS#n#BS##BS#nAdditional Info:#BS##BS#n#BS##BS#n$OUTPUT$&quot; | @MAILER@ -s &quot;** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$ **&quot; $CONTACTEMAIL$', '', 1);
INSERT INTO `command` VALUES ('', 'notify-by-epager', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;Service: $SERVICEDESC$#BS##BR#Host: $HOSTNAME$#BS##BR#Address: $HOSTADDRESS$#BS##BR#State: $SERVICESTATE$#BS##BR#Info: $OUTPUT$#BS##BR#Date: $DATETIME$&quot; | @MAILER@ -s &quot;$NOTIFICATIONTYPE$: $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$&quot; $CONTACTPAGER$', '', 1);

INSERT INTO `command` VALUES ('', 'host-notify-by-email-nagios2', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon Notifcation *****#BR##BR#Type:$NOTIFICATIONTYPE$#BR#Host: $HOSTNAME$#BR#State: $HOSTSTATE$#BR#Address: $HOSTADDRESS$#BR#Info: $HOSTOUTPUT$#BR#Date#S#Time: $DATE$&quot; | @MAILER@ -s &quot;Host $HOSTSTATE$ alert for $HOSTNAME$!&quot; $CONTACTEMAIL$', '', 1);
INSERT INTO `command` VALUES ('', 'notify-by-email-nagios2', '#S#usr#S#bin#S#printf &quot;%b&quot; &quot;***** Oreon Notifications *****#R##BR##R##BR#Notification Type: $NOTIFICATIONTYPE$#R##BR##R##BR#Service: $SERVICEDESC$#R##BR#Host: $HOSTALIAS$#R##BR#Address: $HOSTADDRESS$#R##BR#State: $SERVICESTATE$#R##BR##R##BR#Date#S#Time: $DATE$ Additional Info : $SERVICEOUTPUT$&quot; | @MAILER@ -s &quot;** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$#S#$SERVICEDESC$ is $SERVICESTATE$ **&quot; $CONTACTEMAIL$', '', 1);

--
-- table `timeperiod`
--

INSERT INTO `timeperiod` VALUES ('', '24x7', '24_Hours_A_Day,_7_Days_A_Week', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES ('', 'none', 'No Time Is A Good Time', '', '', '', '', '', '', '');
INSERT INTO `timeperiod` VALUES ('', 'nonworkhours', 'Non-Work Hours', '00:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES ('', 'workhours', 'Work hours', '', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '');
