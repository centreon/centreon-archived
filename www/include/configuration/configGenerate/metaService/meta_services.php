<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_services.cfg", $oreon->user->get_name());
	$str = NULL;
	
	$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	# Write Virtual Services For meta 
	while ($meta =& $DBRESULT->fetchRow())	{
		$strEval = NULL;
		$strEval .= "define service{\n";
		$strEval .= print_line("service_description", "meta_".$meta["meta_id"]);
		$strEval .= print_line("host_name", "_Module_Meta");
		$strEval .= print_line("check_command", "check_meta!" . $meta["meta_id"]);
		$strEval .= print_line("max_check_attempts", $meta["max_check_attempts"]);
		$strEval .= print_line("normal_check_interval", $meta["normal_check_interval"]);
		$strEval .= print_line("retry_check_interval", $meta["retry_check_interval"]);
		$strEval .= print_line("active_checks_enabled", "1");
		$strEval .= print_line("passive_checks_enabled", "0");
		
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT tp_name FROM timeperiod WHERE tp_id = '".$meta["check_period"]."' LIMIT 1");
		$period =& $DBRESULT2->fetchRow();
		if (isset($period) && $period["tp_name"])
			$strEval .= print_line("check_period", $period["tp_name"]);
		$DBRESULT2->free();
			
		$strEval .= print_line("notification_interval", $meta["notification_interval"]);
		
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT tp_name FROM timeperiod WHERE tp_id = '".$meta["notification_period"]."' LIMIT 1");
		$period =& $DBRESULT2->fetchRow();
		if (isset($period) && $period["tp_name"])
			$strEval .= print_line("notification_period", $period["tp_name"]);
		$DBRESULT2->free();
	
		$strEval .= print_line("notification_options", $meta["notification_options"]);
		if ($meta["notifications_enabled"] != 2) 
			print_line("notifications_enabled", $meta["notifications_enabled"] == 1 ? "1": "0");
		
		$contactGroup = array();
		$strTemp = NULL;
		$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM meta_contactgroup_relation mcgr, contactgroup cg WHERE mcgr.meta_id = '".$meta["meta_id"]."' AND mcgr.cg_cg_id = cg.cg_id ORDER BY `cg_name`");
		while ($contactGroup =& $DBRESULT2->fetchRow())	{
			if (isset($gbArr[1][$contactGroup["cg_id"]]))
				$strTemp != NULL ? $strTemp .= ", ".$contactGroup["cg_name"] : $strTemp = $contactGroup["cg_name"];
		}
		$DBRESULT2->free();
		unset($contactGroup);
		
		if ($strTemp) 
			$strEval .= print_line("contact_groups", $strTemp);
		$strEval .= print_line("register", "1");
		$strEval .= "\t}\n\n";

		$str .= $strEval;
	}
	
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_services.cfg");
	fclose($handle);
	unset($str);
	unset($meta);
	unset($strEval);
	unset($str);
?>