<?
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();
	// restriction
	
	require_once './class/other.class.php';
	
	if (isset($_POST["o"]) && !strcmp($_POST["o"], "r")){
		$stdout = shell_exec("sudo /etc/init.d/nagios restart"); 
		$tab = preg_split ("/[\n]+/", $stdout);
	}
	if (isset($oreon->Lca[$oreon->user->get_id()]) && $oreon->Lca[$oreon->user->get_id()]->get_admin_server() == 0)
		include("./include/security/error.php");
	else {
		if ($Logs->log_p->status_proc == 1)		{
			$running_time = $Logs->log_p->last_command_check - $Logs->log_p->program_start;
			$d = date("j", $running_time) - 1;
	?>
		<table align="left" border="0">
			<tr>
				<td valign="top">
					<table border='1' style='border-width: thin; border-style: dashed; border-color=#9C9C9C;' cellpadding='4' cellspacing='2'>
						<tr>
						  <td colspan=3 class="text14b" align="center"><? echo $lang['mon_proc_options']; ?></td>
						 </tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_notif_enabled']; ?></td>
						  <td align="center">
							<? 
							if (!strcmp($Logs->log_p->get_enable_notifications(), "0"))
								echo $lang['no'];
							else 
								echo $lang['yes']; 
							?>
						  </td>
						  <td align="center">
						  <? 
							if (!strcmp($Logs->log_p->get_enable_notifications(), "0")) 
								print "<a href='?p=306&cmd=22'><img src='./img/enabled.gif' border='0' alt='Enable notifications' width='20'></a>";
							else
								print "<a href='?p=306&cmd=23'><img src='./img/disabled.gif' border='0' alt='Disable notifications' width='20'></a>";	
						  ?>
						  </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_service_check_executed']; ?></td>
						  <td align="center">
							<? 
							if (!strcmp($Logs->log_p->get_execute_service_checks(), "0"))
								echo $lang['no'];
							else 
								echo $lang['yes']; 
							?>
						  </td>
						  <td align="center">
						  <? 
							if (!strcmp($Logs->log_p->get_execute_service_checks(), "0")) 
								print "<a href='?p=306&cmd=29'><img src='./img/enabled.gif' border='0' alt='Start executing service checks' width='20'></a>";
							else
								print "<a href='?p=306&cmd=30'><img src='./img/disabled.gif' border='0' alt='Stop executing service checks' width='20'></a>";	
						  ?>
						  </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_passive_service_check_executed']; ?></td>
						  <td align="center">
							<? 
							if (!strcmp($Logs->log_p->get_accept_passive_service_checks(), "0"))
								echo $lang['no'];
							else 
								echo $lang['yes']; 
							?>
						  </td>
						  <td align="center">
						  <? 
							if (!strcmp($Logs->log_p->get_accept_passive_service_checks(), "0")) 
								print "<a href='?p=306&cmd=31'><img src='./img/enabled.gif' border='0' alt='Start accepting passive service checks' width='20'></a>";
							else
								print "<a href='?p=306&cmd=32'><img src='./img/disabled.gif' border='0' alt='Stop accepting passive service checks' width='20'></a>";	
						  ?>
						  </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_eh_enabled']; ?></td>
						  <td align="center">
							<? 
							if (!strcmp($Logs->log_p->get_enable_event_handlers(), "0"))
								echo $lang['no'];
							else 
								echo $lang['yes']; 
							?>
						  </td>
						  <td align="center">
						  <? 
							if (!strcmp($Logs->log_p->get_enable_event_handlers(), "0")) 
								print "<a href='?p=306&cmd=35'><img src='./img/enabled.gif' border='0' alt='Enable event handlers' width='20'></a>";
							else
								print "<a href='?p=306&cmd=36'><img src='./img/disabled.gif' border='0' alt='Disable event handlers' width='20'></a>";	
						  ?>
						  </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_obess_over_services']; ?></td>
						  <td align="center">
							<? 
							if (!strcmp($Logs->log_p->get_obsess_over_services(), "0"))
								echo $lang['no'];
							else 
								echo $lang['yes']; 
							?>
						  </td>
						  <td align="center">
						  <? 
							if (!strcmp($Logs->log_p->get_obsess_over_services(), "0")) 
								print "<a href='?p=306&cmd=37'><img src='./img/enabled.gif' border='0' alt='Start obsessing over services' width='20'></a>";
							else
								print "<a href='?p=306&cmd=38'><img src='./img/disabled.gif' border='0' alt='Stop obsessing over services' width='20'></a>";	
						  ?>
						  </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_fp_detection_enabled']; ?></td>
						  <td align="center">
							<? 
							if (!strcmp($Logs->log_p->get_enable_flap_detection(), "0"))
								echo $lang['no'];
							else 
								echo $lang['yes']; 
							?>
						  </td>
						  <td align="center">
						  <? 
							if (!strcmp($Logs->log_p->get_enable_flap_detection(), "0")) 
								print "<a href='?p=306&cmd=39'><img src='./img/enabled.gif' border='0' alt='Enable flap detection' width='20'></a>";
							else
								print "<a href='?p=306&cmd=40'><img src='./img/disabled.gif' border='0' alt='Disable flap detection' width='20'></a>";	
						  ?>
						  </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_perf_data_process']; ?></td>
						  <td align="center">
							<? 
							if (!strcmp($Logs->log_p->get_process_performance_data(), "0\n"))
								echo $lang['no'];
							else 
								echo $lang['yes']; 
							?>
						  </td>
						  <td align="center">
						  <? 
							if (!strcmp($Logs->log_p->get_process_performance_data(), "0\n")) 
								print "<a href='?p=306&cmd=41'><img src='./img/enabled.gif' border='0' alt='Enable performance data' width='20'></a>";
							else
								print "<a href='?p=306&cmd=42'><img src='./img/disabled.gif' border='0' alt='Disable performance data' width='20'></a>";	
						  ?>
						  </td>
						</tr>
					</table>
				</td>
				<td style="padding-left: 20px;"></td>
				<td valign="top">
					<table border='1' style='border-width: thin; border-style: dashed; border-color=#9C9C9C;' cellpadding='4' cellspacing='2'>
						<tr>
						  <td	class="text14b" align="center" colspan="2"><? echo $lang['mon_process_infos']; ?></td>
						 </tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_process_start_time']; ?></td>
						  <td align="center"><? 	if (isset($Logs->log_p->program_start)){print date("j/m/Y - H:i:s", $Logs->log_p->get_program_start());}?></td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_total_run_time']; ?></td>
						  <td align="center"><? if (isset($Logs->log_p->last_command_check)){print Duration::toString($Logs->log_p->get_last_command_check() - $Logs->log_p->get_program_start());} ?></td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_last_ext_command_check']; ?></td>
						  <td align="center"><? if (isset($Logs->log_p->last_command_check))print date("j/m/Y - H:i:s", $Logs->log_p->get_last_command_check()) ; ?></td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_last_log_file_rotation']; ?></td>
						  <td align="center">
							<? 
							if (!isset($Logs->log_p->last_log_rotation))
								print "N/A";
							else
								print date("j/m/Y - H:i:s", $Logs->log_p->get_last_log_rotation()); 
							?>
						   </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_nagios_pid']; ?></td>
						  <td align="center"><? if (isset($Logs->log_p->nagios_pid)) print $Logs->log_p->get_nagios_pid(); ?></td>
						</tr>
					</table>
				</td>
				<td style="padding-left: 20px;"></td>
				<td valign="top">
					<table border='1' style='border-width: thin; border-style: dashed; border-color=#9C9C9C;' cellpadding='4' cellspacing='2'>
						<tr>
						  <td colspan=2 class="text14b" align="center"><? echo $lang['mon_process_cmds']; ?></td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left>
						  <?						  
							if ($Logs->log_p->get_status_proc() == 1)
								echo $lang['mon_stop_nagios_proc'];
							else 
								echo $lang['mon_start_nagios_proc']						  
						  ?>
						  </td>
						  <td align="center">
						  <?						  
							if ($Logs->log_p->get_status_proc() == 1)
								print "<a href='?p=306&cmd=24'><img src='./img/disabled.gif' border='0'></a>";
							else 
								print "<a href='?p=306&cmd=25'><img src='./img/disabled.gif' border='0'></a>";						  
						  ?>
						  </td>
						</tr>
						<tr bgColor="#eaecef">
						  <td align=left><? echo $lang['mon_restart_nagios_proc']; ?></td>
						  <td align="center"><a href='?p=306&cmd=25'><img src='./img/enabled.gif' border="0"></a></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	<?	} else {
			if (isset($_POST["o"]) && !strcmp($_POST["o"], "r")) {?>
			<table border='0'>
			<tr>
				<td valign="top" align="center"> 
					<table cellpadding='0' cellspacing="0" class="tabTableTitle">
						<tr>
							<td style="white-space: nowrap;">
							<?	
								$i = 0;					
								foreach ($tab as $str){
									if (preg_match("/^Running configuration check/", $str, $matches))
										print "<b><font color='blue'>" . $str . "</font></b><br>";
									else if (preg_match("/^Starting/", $str, $matches))
										print "<b><font color='red'>" . $str . "</font></b><br>";
									else if (preg_match("/^/", $str, $matches))
										print  $str . "<br>";
									$i++;
									unset($str);
								}
							?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			</table><? 
				print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"document.location.href=\'oreon.php?p=303&o=proc\'\",2000)</SCRIPT>";
			} else {?>
			<table border='0'>
			<tr>
				<td valign="top" align="center">
					<table border='0' align="center" cellpadding='0' cellspacing="0" class="tabTableTitle">
						<tr>
							<td valign="middle"><? echo $lang['mon_restart_nagios_proc']; ?></td>
							<td valign="middle"><form action="" method="post"><input name="o" type="hidden" value="r"><input name="Reboot" type="submit" value="<? echo "Redemarrer"; ?>"></form></td>
						</tr>
					</table>
				</td>
			</tr>
			</table>	
		<? }
		}	
	} 
?>