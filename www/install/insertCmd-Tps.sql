--
-- Commands and Timeperiods
--

INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(1, 'check_host_alive', '$USER1$/check_icmp -H $HOSTADDRESS$ -w 3000.0,80% -c 5000.0,100% -p 1', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(2, 'check_disk_smb', '$USER1$/check_disk_smb -H $HOSTADDRESS$ -s $ARG1$ -u $ARG2$ -p $ARG3$ -w $ARG4$ -c $ARG5$', '!share!user!passwd!80!90', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(3, 'check_distant_disk_space', '$USER1$/check_distant_disk_space -H $HOSTADDRESS$ -C $ARG1$ -p $ARG2$ -w $ARG3$ -c $ARG4$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(4, 'check_centreon_dummy', '$USER1$/check_centreon_dummy -s $ARG1$ -o $ARG2$', '!0!OK', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(5, 'check_centreon_load_average', '$USER1$/check_centreon_snmp_loadaverage -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -w $ARG1$ -c $ARG2$', '!4,3,2!6,5,4', 2, 8, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(6, 'check_centreon_ping', '$USER1$/check_icmp -H $HOSTADDRESS$ -n $ARG1$ -w $ARG2$ -c $ARG3$', '!3!200,20%!400,50%', 2, 2, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(7, 'check_centreon_process', '$USER1$/check_centreon_snmp_process -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -n -p $ARG1$', '!httpd', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(8, 'check_centreon_remote_storage', '$USER1$/check_centreon_snmp_remote_storage -H $HOSTADDRESS$ -n -d $ARG1$ -w $ARG2$ -c $ARG3$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$', '!/!80!90', 2, 3, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(10, 'check_centreon_traffic_limited', '$USER1$/check_centreon_snmp_traffic -H $HOSTADDRESS$ -n -i $ARG1$ -w $ARG2$ -c $ARG3$ -T $ARG6$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$', '!eth0!80!90!2', 2, 7, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(11, 'check_hpjd', '$USER1$/check_hpjd -H $HOSTADDRESS$ -C public', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(12, 'check_http', '$USER1$/check_http -H $HOSTADDRESS$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(13, 'check_https', '$USER1$/check_http -S $HOSTADDRESS$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(14, 'check_local_swap', '$USER1$/check_swap -w $ARG1$ -c $ARG2$ -v', '!80!90', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(15, 'check_local_disk', '$USER1$/check_disk -w $ARG2$ -c $ARG3$ -p $ARG1$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(16, 'check_local_load', '$USER1$/check_load -w $ARG1$ -c $ARG2$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(17, 'check_local_procs', '$USER1$/check_procs -w $ARG1$ -c $ARG2$ -u $ARG3$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(18, 'check_local_users', '$USER1$/check_users -w $ARG1$ -c $ARG2$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(19, 'check_nt_memuse', '$USER1$/check_nt -H $HOSTADDRESS$ -v MEMUSE -s &quot;public&quot; -p $ARG1$ -w $ARG2$ -c $ARG3$', '!12489!80!90', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(20, 'check_nt_cpu', '$USER1$/check_nt -H $HOSTADDRESS$ -v CPULOAD -s &quot;public&quot; -p $ARG1$ -l 2,90,95', '!12489', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(21, 'check_nt_disk', '$USER1$/check_nt -H $HOSTADDRESS$ -v USEDDISKSPACE -s &quot;public&quot; -l $ARG1$ -w $ARG2$ -c $ARG3$', '!C!80!90', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(23, 'check_tcp', '$USER1$/check_tcp -H $HOSTADDRESS$ -p $ARG1$ -w $ARG2$ -c $ARG3$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(24, 'check_nntp', '$USER1$/check_nntp -H $HOSTADDRESS$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(25, 'check_pop', '$USER1$/check_pop -H $HOSTADDRESS$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(26, 'check_smtp', '$USER1$/check_smtp -H $HOSTADDRESS$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(27, 'check_dns', '$USER1$/check_dns -H $ARG1$ -s $HOSTADDRESS$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(28, 'check_ftp', '$USER1$/check_ftp -H $HOSTADDRESS$', '!127.0.0.1', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(29, 'check_dhcp', '$USER1$/check_dhcp -s $HOSTADDRESS$ -i $ARG1$', '!eth0', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(30, 'check_dig', '$USER1$/check_dig -H $HOSTADDRESS$ -l $ARG1$', '!www.centreon-nms.org', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(31, 'check_snmp', '$USER1$/check_snmp -H $HOSTADDRESS$ -o $ARG1$ -w $ARG2$ -C $ARG3$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(32, 'check_telnet', '$USER1$/check_tcp -H $HOSTADDRESS$ -p 23', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(33, 'check_udp', '$USER1$/check_udp -H $HOSTADDRESS$ -p $ARG1$', '', 2, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(34, 'check_centreon_nt', '$USER1$/check_centreon_nt -H $HOSTADDRESS$ -p 12489 -v $ARG1$ -l $ARG2$ -s $ARG3$ -w $ARG4$ -c $ARG5$', '', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(35, 'host-notify-by-email', '/usr/bin/printf &quot;%b&quot; &quot;***** centreon Notification *****#BR##BR#Type:$NOTIFICATIONTYPE$#BR#Host: $HOSTNAME$#BR#State: $HOSTSTATE$#BR#Address: $HOSTADDRESS$#BR#Info: $HOSTOUTPUT$#BR#Date/Time: $DATE$&quot; | @MAILER@ -s &quot;Host $HOSTSTATE$ alert for $HOSTNAME$!&quot; $CONTACTEMAIL$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(36, 'service-notify-by-email', '/usr/bin/printf &quot;%b&quot; &quot;***** centreon Notification *****#BR##BR#Notification Type: $NOTIFICATIONTYPE$#BR##BR#Service: $SERVICEDESC$#BR#Host: $HOSTALIAS$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR##BR#Date/Time: $DATE$ Additional Info : $SERVICEOUTPUT$&quot; | @MAILER@ -s &quot;** $NOTIFICATIONTYPE$ alert - $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$ **&quot; $CONTACTEMAIL$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(37, 'host-notify-by-epager', '/usr/bin/printf &quot;%b&quot; &quot;Host $HOSTALIAS$ is $HOSTSTATE$#BR#Info: $OUTPUT$#BR#Time: $DATETIME$\\&quot; | @MAILER@ -s \\&quot;$NOTIFICATIONTYPE$ alert - Host $HOSTNAME$ is $HOSTSTATE$\\&quot; $CONTACTPAGER$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(38, 'service-notify-by-epager', '/usr/bin/printf &quot;%b&quot; &quot;Service: $SERVICEDESC$#BR#Host: $HOSTNAME$#BR#Address: $HOSTADDRESS$#BR#State: $SERVICESTATE$#BR#Info: $OUTPUT$#BR#Date: $DATETIME$&quot; | @MAILER@ -s &quot;$NOTIFICATIONTYPE$: $HOSTALIAS$/$SERVICEDESC$ is $SERVICESTATE$&quot; $CONTACTPAGER$', '', 1, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(39, 'submit-host-check-result', '$USER1$/submit_host_check_result $HOSTNAME$ $HOSTSTATE$ &#039;$HOSTOUTPUT$&#039;', '', 3, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(40, 'submit-service-check-result', '$USER1$/submit_service_check_result $HOSTNAME$ $SERVICEDESC$ $SERVICESTATE$ &#039;$SERVICEOUTPUT$&#039;', '', 3, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(41, 'process-service-perfdata', '$USER1$/process-service-perfdata  &quot;$LASTSERVICECHECK$&quot; &quot;$HOSTNAME$&quot; &quot;$SERVICEDESC$&quot; &quot;$LASTSERVICESTATE$&quot; &quot;$SERVICESTATE$&quot; &quot;$SERVICEPERFDATA$&quot;', '', 3, NULL, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(44, 'check_centreon_uptime', '$USER1$/check_centreon_snmp_uptime -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -d', '', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(59, 'check_centreon_nb_connections', '$USER1$/check_centreon_snmp_TcpConn -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -p $ARG1$ -w $ARG2$ -c $ARG3$', '!80!70!100', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(62, 'check_centreon_traffic', '$USER1$/check_centreon_snmp_traffic -H $HOSTADDRESS$ -n -i $ARG1$ -w $ARG2$ -c $ARG3$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$', '!eth0!80!90', 2, 7, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(76, 'check_load_average', '$USER1$/check_load $HOSTADDRESS$ -w $ARG1$ -c $ARG2$', '', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(77, 'check_local_cpu_load', '$USER1$/check_nt -H $HOSTADDRESS$ -v CPULOAD -l $ARG1$ -s &quot;public&quot;', '', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(78, 'check_local_disk_space', '$USER1$/check_nt -H $HOSTADDRESS$ -v USEDDISKSPACE -l $ARG1$ -w $ARG2$ -c $ARG3$ -s &quot;public&quot;', '', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(79, 'check_maxq', '$USER1$/check_maxq_script_return -r $ARG1$ -P $ARG2$', '', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(89, 'host-notify-by-jabber', '$USER1$/notify_via_jabber  $CONTACTPAGER$ &quot;Host &#039;$HOSTALIAS$&#039; is $HOSTSTATE$ - Info: $HOSTOUTPUT$&quot;', '', 1, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(90, 'service-notify-by-jabber', '$USER1$/notify_via_jabber $CONTACTPAGER$ &quot;$NOTIFICATIONTYPE$ $HOSTNAME$ $SERVICEDESC$ $SERVICESTATE$ $SERVICEOUTPUT$ $LONGDATETIME$&quot;', '', 1, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(94, 'check_centreon_snmp_value', '$USER1$/check_centreon_snmp_value -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -o $ARG1$ -w $ARG2$ -c $ARG53', '!1.3.6.1.4.1.705.1.8.1.0!190!210', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(95, 'check_centreon_snmp_proc_detailed', '$USER1$/check_centreon_snmp_process_detailed -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -n $ARG1$ -m $ARG2$', '!apache!20,50', 2, 0, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(96, 'check_centreon_cpu', '$USER1$/check_centreon_snmp_cpu -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -c $ARG1$ -w $ARG2$', '!80!90', 2, 5, NULL);
INSERT INTO `command` (`command_id`, `command_name`, `command_line`, `command_example`, `command_type`, `graph_id`, `cmd_cat_id`) VALUES(97, 'check_centreon_memory', '$USER1$/check_centreon_snmp_memory -H $HOSTADDRESS$ -v $_HOSTSNMPVERSION$ -C $_HOSTSNMPCOMMUNITY$ -w 80 -c 90', '', 2, 4, NULL);

UPDATE `command` SET `enable_shell` = 1 WHERE `command_type` = 1;

--
-- table `command_arg_description`
--

INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG1", "share");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG2", "user");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG3", "password");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG4", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (2, "ARG5", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (4, "ARG1", "status");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (4, "ARG2", "output");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (5, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (5, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (6, "ARG1", "count");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (6, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (6, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (7, "ARG1", "process name");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG1", "disk number");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (8, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG1", "interface");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (10, "ARG6", "Max bandwidth");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (14, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (14, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (15, "ARG1", "path");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (15, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (15, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (16, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (16, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (17, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (17, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (17, "ARG3", "process owner");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (18, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (18, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (21, "ARG1", "drive letter");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (21, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (21, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (23, "ARG1", "port");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (23, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (23, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (27, "ARG1", "hostname");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (29, "ARG1", "interface");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (30, "ARG1", "query address");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (31, "ARG1", "OID");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (31, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (31, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (33, "ARG1", "port");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG1", "variable");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG2", "params");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG3", "password");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG4", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (34, "ARG5", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (59, "ARG1", "port");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (59, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (59, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (62, "ARG1", "interface");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (62, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (62, "ARG3", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (76, "ARG1", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (76, "ARG2", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (78, "ARG1", "drive letter");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (78, "ARG2", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (78, "ARG1", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG2", "OID");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG3", "warning");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (94, "ARG4", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (95, "ARG1", "process name");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (95, "ARG2", "memory thresholds");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (96, "ARG1", "critical");
INSERT INTO `command_arg_description` (`cmd_id`, `macro_name`, `macro_description`) VALUES (96, "ARG2", "warning");

--
-- table `timeperiod`
--

INSERT INTO `timeperiod` VALUES (NULL, '24x7', '24_Hours_A_Day,_7_Days_A_Week', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES (NULL, 'none', 'No Time Is A Good Time', '', '', '', '', '', '', '');
INSERT INTO `timeperiod` VALUES (NULL, 'nonworkhours', 'Non-Work Hours', '00:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES (NULL, 'workhours', 'Work hours', '', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '');


