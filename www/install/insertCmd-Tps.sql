--
-- Commands and Timeperiods
--

INSERT INTO `command` VALUES (1, 'check_cpu_load', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v CPULOAD -l $ARG1$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES (2, 'check_disk_smb', '$USER1$/check_disk_smb -H $ARG1$ -s $ARG2$ -u $ARG3$ -p $ARG4$ -w $ARG5$', '', 2);
INSERT INTO `command` VALUES (3, 'check_disk_space', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v USEDDISKSPACE -l $ARG1$ -w $ARG2$ -c $ARG3$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES (4, 'check_distant_disk_space', '$USER1$/check_distant_disk_space -H $HOSTADDRESS$ -C $ARG1$ -p $ARG2$ -w $ARG3$ -c $ARG4$', '', 2);
INSERT INTO `command` VALUES (5, 'check_dns', '$USER1$/check_dns -H $ARG1$ -s $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES (6, 'check_ftp', '$USER1$/check_ftp -H $HOSTADDRESS$', '!127.0.0.1', 2);
INSERT INTO `command` VALUES (7, 'check_graph_load_average', '$USER1$/check_graph_load_average.pl $HOSTADDRESS$ -v $ARG1$ -W -C $ARG2$ -g -S $ARG3$', '', 2);
INSERT INTO `command` VALUES (8, 'check_graph_ping', '$USER1$#S#check_graph_ping.pl -H $HOSTADDRESS$ -w 200,20% -c 500,40% -f -n $ARG1$ -g -S $ARG2$', '!3', 2);
INSERT INTO `command` VALUES (9, 'check_graph_process', '$USER1$/check_graph_process.pl $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -n -g -S $ARG3$', '!2c!public', 2);
INSERT INTO `command` VALUES (10, 'check_graph_remote_storage', '$USER1$/check_graph_remote_storage.pl $HOSTADDRESS$ -d $ARG1$  -C $ARG2$ -w $ARG3$ -c $ARG4$ -v $ARG5$ -g -S $ARG6$', '!1!public!80!90!1', 2);
INSERT INTO `command` VALUES (11, 'check_graph_traffic', '$USER1$/check_graph_traffic.pl -H $HOSTADDRESS$ -i $ARG1$ -w $ARG2$ -c $ARG3$ -C $ARG4$ -v $ARG5$ -g -S $ARG6$', '!2!80!90!public!1', 2);
INSERT INTO `command` VALUES (12, 'check_host_alive', '$USER1$/check_ping -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1', '', 2);
INSERT INTO `command` VALUES (13, 'check_hpjd', '$USER1$/check_hpjd -H $HOSTADDRESS$ -C public', '', 2);
INSERT INTO `command` VALUES (14, 'check_http', '$USER1$/check_http -H $HOSTADDRESS$ -s $ARG1$ -v', '127.0.0.1', 2);
INSERT INTO `command` VALUES (15, 'check_https', '$USER1$/check_http -S $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES (16, 'check_http_up', '$USER1$/check_http -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES (17, 'check_load_average', '$USER1$/check_load $HOSTADDRESS$ -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES (18, 'check_local_disk', '$USER1$/check_disk -w $ARG2$ -c $ARG3$ -p $ARG1$', '', 2);
INSERT INTO `command` VALUES (19, 'check_local_load', '$USER1$/check_load -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES (20, 'check_local_procs', '$USER1$/check_procs -w $ARG1$ -c $ARG2$ -u $ARG3$', '', 2);
INSERT INTO `command` VALUES (21, 'check_local_users', '$USER1$/check_users -w $ARG1$ -c $ARG2$', '', 2);
INSERT INTO `command` VALUES (22, 'check_memuse', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v MEMUSE -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES (23, 'check_nntp', '$USER1$/check_nntp -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES (24, 'check_pop', '$USER1$/check_pop -H $HOSTADDRESS$', '', 2);
INSERT INTO `command` VALUES (26, 'check_process', '$USER1$/check_graph_process.pl $HOSTADDRESS$ -v $ARG1$ -C $ARG2$ -p $ARG3$', '!1!public!httpd', 2);
INSERT INTO `command` VALUES (27, 'check_snmp', '$USER1$/check_snmp -H $HOSTADDRESS$ -o $ARG1$ -w $ARG2$ -C $ARG3$', '', 2);
INSERT INTO `command` VALUES (28, 'check_snmp_disk', '$USER1$/check_disk_space.pl $HOSTADDRESS$ $ARG1$ $ARG2$  $ARG3$ $ARG4$', '', 2);
INSERT INTO `command` VALUES (29, 'check_swap', '$USER1$/check_swap -w $ARG1$ -c $ARG2$ -v', '!80!90', 2);
INSERT INTO `command` VALUES (30, 'check_tcp', '$USER1$/check_tcp -H $HOSTADDRESS$ -p $ARG1$ -w $ARG2$ -c $ARG3$', '', 2);
INSERT INTO `command` VALUES (31, 'check_telnet', '$USER1$/check_tcp -H $HOSTADDRESS$ -p 23', '', 2);
INSERT INTO `command` VALUES (32, 'check_udp', '$USER1$/check_udp -H $HOSTADDRESS$ -p $ARG1$', '', 2);
INSERT INTO `command` VALUES (33, 'check_uptime', '$USER1$/check_uptime.pl $HOSTADDRESS$ $ARG1$ $ARG2$', '', 2);
INSERT INTO `command` VALUES (34, 'host-notify-by-email', '/usr/bin/printf "%b" "***** Oreon *****Notification\\nType:$NOTIFICATIONTYPE$\\n Host: $HOSTNAME$\\nState: $HOSTSTATE$Address: $HOSTADDRESS$\\nInfo: $OUTPUT$\\nDate/Time: $DATETIME$" | @MAILER@ -s "Host $HOSTSTATE$ alert for $HOSTNAME$!" $CONTACTEMAIL$', '', 1);
INSERT INTO `command` VALUES (35, 'host-notify-by-epager', '#S#usr#S#bin#S#printf #BS#&quot;%b#BS#&quot; #BS#&quot;Host #BS#&#039;$HOSTALIAS$#BS#&#039; is $HOSTSTATE$#BR#Info: $OUTPUT$#BR#Time: $DATETIME$#BS#&quot; | @MAILER@ -s #BS#&quot;$NOTIFICATIONTYPE$ alert - Host $HOSTNAME$ is $HOSTSTATE$#BS#&quot; $CONTACTPAGER$', '', 1);
INSERT INTO `command` VALUES (36, 'notify-by-email', '/usr/bin/printf "%b" "***** Oreon  *****\\n\\nNotification Type: $NOTIFICATIONTYPE$\\n\\nService: $SERVICEDESC$\\nHost: $HOSTALIAS$\\nAddress: $HOSTADDRESS$\\nState: $SERVICESTATE$\\n\\nDate/Time: $DATETIME$\\n\\nAdditional Info:\\n\\n$OUTPUT$" | @MAILER@ -s "** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$ **" $CONTACTEMAIL$', '', 1);
INSERT INTO `command` VALUES (37, 'notify-by-epager', '/usr/bin/printf "%b" "Service: $SERVICEDESC$\\nHost: $HOSTNAME$\\nAddress: $HOSTADDRESS$\\nState: $SERVICESTATE$\\nInfo: $OUTPUT$\\nDate: $DATETIME$" | @MAILER@ -s "$NOTIFICATIONTYPE$: $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$" $CONTACTPAGER$', '', 1);
INSERT INTO `command` VALUES (38, 'process-host-perfdata', '#S#usr#S#bin#S#printf #BS#&quot;%b#BS#&quot; #BS#&quot;$LASTCHECK$t$HOSTNAME$#T#$HOSTSTATE$#T#$HOSTATTEMPT$#T#$STATETYPE$#T#$EXECUTIONTIME$#T#$OUTPUT$#T#$PERFDATA$#BS#&quot; &gt;&gt; #S#usr#S#local#S#nagios#S#var#S#host-perfdata.out', '', 1);
INSERT INTO `command` VALUES (39, 'process-service-perfdata', '#S#usr#S#local#S#nagios#S#libexec#S#process-service-perfdata  &quot;$LASTCHECK$&quot; &quot;$HOSTNAME$&quot; &quot;$SERVICEDESC$&quot; &quot;$OUTPUT$&quot; &quot;$SERVICESTATE$&quot; &quot;$PERFDATA$&quot;', '', 1);
INSERT INTO `command` VALUES (40, 'check_graph_nt', '$USER1$/check_graph_nt.pl -H $HOSTADDRESS$ -p 1248 -v $ARG1$ -l $ARG2$ -s $ARG3$ -w $ARG4$ -c $ARG5$ -g -S $ARG6$', '', 2);
INSERT INTO `command` VALUES (41, 'check_graph_uptime', '$USER1$/check_graph_uptime.pl -H $HOSTADDRESS$ -C $ARG1$ -v $ARG2$ -d -g -S $ARG3$', '', 2);
INSERT INTO `command` VALUES (42, 'check_graph_http', '$USER1$#S#check_graph_http.pl -H $HOSTADDRESS$ -f', '', 2);
INSERT INTO `command` VALUES (44, 'check_service_state', '$USER1$#S#check_nt -H $HOSTADDRESS$ -v SERVICESTATE -l $ARG1$ -s &quot;public&quot;', '', 2);
INSERT INTO `command` VALUES (45, 'check_users', '$USER1$#S#check_users -H $HOSTADDRESS$ -w 3 -c 5', '', 2);
INSERT INTO `command` VALUES (46, 'check_http2', '$USER1$#S#check_http -I $HOSTADDRESS$ -u $ARG1$', '', 2);

--
-- table `timeperiod`
--

INSERT INTO `timeperiod` VALUES (1, '24x7', '24_Hours_A_Day,_7_Days_A_Week', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES (2, 'none', 'No Time Is A Good Time', '', '', '', '', '', '', '');
INSERT INTO `timeperiod` VALUES (3, 'nonworkhours', 'Non-Work Hours', '00:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES (4, 'workhours', 'Work hours', '', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '');
