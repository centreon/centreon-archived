<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	
	$handle = create_file($nagiosCFGPath."nagios.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1 : ".$DBRESULT->getMessage()."<br>";
	$nagios = $DBRESULT->fetchRow();
	$str = NULL;
	$ret["comment"]["comment"] ? ($str .= "# '".$nagios["nagios_name"]."'\n") : NULL;
	if ($ret["comment"]["comment"] && $nagios["nagios_comment"])	{
		$comment = array();
		$comment = explode("\n", $nagios["nagios_comment"]);
		foreach ($comment as $cmt)
			$str .= "# ".$cmt."\n";
	}
	$str .= "cfg_file=".$nagios["cfg_dir"]."hosts.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."services.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."misccommands.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."checkcommands.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."contactgroups.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."contacts.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."hostgroups.cfg\n";
	if ($oreon->user->get_version() == 2)
		$str .= "cfg_file=".$nagios["cfg_dir"]."servicegroups.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."timeperiods.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."escalations.cfg\n";
	$str .= "cfg_file=".$nagios["cfg_dir"]."dependencies.cfg\n";	
	if ($oreon->user->get_version() == 2)	{
		$str .= "cfg_file=".$nagios["cfg_dir"]."hostextinfo.cfg\n";
		$str .= "cfg_file=".$nagios["cfg_dir"]."serviceextinfo.cfg\n";
	}
	# Include for Meta Service the cfg file
	if ($oreon->optGen["perfparse_installed"] && ($files = glob("./include/configuration/configGenerate/metaService/*.php")))
		foreach ($files as $filename)	{
			$cfg = NULL;
			$file =& basename($filename);
			$file = explode(".", $file);
			$cfg .= $file[0];
			$str .= "cfg_file=".$nagios["cfg_dir"].$cfg.".cfg\n";
		}
	# Include for Module the cfg file
	if (isset($oreon->modules["osl"]))
		if ($oreon->modules["osl"]["gen"] && $files = glob("./modules/osl/generate_files/*.php"))
			foreach ($files as $filename)	{
				$cfg = NULL;
				$file =& basename($filename);
				$file = explode(".", $file);
				$cfg .= $file[0];
				$str .= "cfg_file=".$nagios["cfg_dir"].$cfg.".cfg\n";
			}
	$str .= "resource_file=".$nagios["cfg_dir"]."resource.cfg\n";
	$nagios["cfg_dir"] = NULL;
	foreach ($nagios as $key=>$value)	{
		if ($value != NULL && $key != "nagios_id" && $key != "nagios_name" && $key != "nagios_comment" && $key != "nagios_activate")	{	
			if ($key == "aggregate_status_updates" && $value == 2);
			else if ($key == "enable_notifications" && $value == 2);	
			else if ($key == "execute_service_checks" && $value == 2);	
			else if ($key == "accept_passive_service_checks" && $value == 2);	
			else if ($key == "execute_host_checks" && $value == 2);	
			else if ($key == "accept_passive_host_checks" && $value == 2);	
			else if ($key == "enable_event_handlers" && $value == 2);
			else if ($key == "check_external_commands" && $value == 2);
			else if ($key == "retain_state_information" && $value == 2);
			else if ($key == "use_retained_program_state" && $value == 2);
			else if ($key == "use_retained_scheduling_info" && $value == 2);
			else if ($key == "use_syslog" && $value == 2);
			else if ($key == "log_notifications" && $value == 2);
			else if ($key == "log_service_retries" && $value == 2);
			else if ($key == "log_host_retries" && $value == 2);
			else if ($key == "log_event_handlers" && $value == 2);
			else if ($key == "log_initial_states" && $value == 2);
			else if ($key == "log_external_commands" && $value == 2);
			else if ($key == "log_passive_service_checks" && ($value == 2 || $oreon->user->get_version() == 2));
			else if ($key == "log_passive_checks" && ($value == 2 || $oreon->user->get_version() == 1));
			else if ($key == "auto_reschedule_checks" && $value == 2);
			else if ($key == "use_agressive_host_checking" && $value == 2);
			else if ($key == "enable_flap_detection" && $value == 2);
			else if ($key == "soft_state_dependencies" && $value == 2);
			else if ($key == "obsess_over_services" && $value == 2);
			else if ($key == "obsess_over_hosts" && $value == 2);
			else if ($key == "process_performance_data" && $value == 2);
			else if ($key == "max_service_check_spread" && $oreon->user->get_version() == 1);
			else if ($key == "max_host_check_spread" && $oreon->user->get_version() == 1);
			else if ($key == "check_for_orphaned_services" && $value == 2);
			else if ($key == "check_service_freshness" && $value == 2);
			else if ($key == "check_host_freshness" && $value == 2);
			else if ($key == "use_regexp_matching" && $value == 2);
			else if ($key == "use_true_regexp_matching" && $value == 2);
			else if ($key == "service_inter_check_delay_method" && ($value == 2 || $oreon->user->get_version() == 1));
			else if ($key == "host_inter_check_delay_method" && ($value == 2 || $oreon->user->get_version() == 1));
			else if ($key == "inter_check_delay_method" && ($value == 2 || $oreon->user->get_version() == 2));
			else if ($key == "global_host_event_handler" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "global_service_event_handler" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "ocsp_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "ochp_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "host_perfdata_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "service_perfdata_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "host_perfdata_file_processing_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "service_perfdata_file_processing_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : SELECT command_name FROM `command` WHERE command_id = '".$value."' : ".$DBRESULT2->getMessage()."<br>";
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else
				$str .= $key."=".$value."\n";
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."nagios.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>