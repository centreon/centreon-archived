<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	/*
	 * Create file
	 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG", $oreon->user->get_name(), false);

	/*
	 * Get all information for nagios.cfg for this poller
	 */
	$DBRESULT = $pearDB->query("SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' AND `nagios_server_id` = '".$tab['id']."' LIMIT 1");
	$nagios = $DBRESULT->fetchRow();
        $cfgNagios = $nagios['cfg_file'];
        unset($nagios['cfg_file']);
        if (isset($tab["monitoring_engine"]) && ($tab["monitoring_engine"] != "CENGINE") && isset($nagios["log_pid"])) {
            unset($nagios["log_pid"]);
        }
	$DBRESULT->free();

	/*
	 * Get broker module informations
	 */
	$DBRESULT = $pearDB->query("SELECT broker_module FROM `cfg_nagios_broker_module` WHERE `cfg_nagios_id` = '".$nagios["nagios_id"]."'");
	$nagios["broker_module"] = NULL;
	while ($arBk = $DBRESULT->fetchRow()) {
		$nagios["broker_module"][] = $arBk;
	}
	$DBRESULT->free();

	$str = NULL;

	$ret["comment"] ? ($str .= "# '".$nagios["nagios_name"]."'\n") : NULL;
	if ($ret["comment"] && $nagios["nagios_comment"])	{
		$comment = array();
		$comment = explode("\n", $nagios["nagios_comment"]);
		foreach ($comment as $cmt) {
			$str .= "# ".$cmt."\n";
		}
	}

	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/hosts.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/hostTemplates.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/serviceTemplates.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/services.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/misccommands.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/checkcommands.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/contactgroups.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/contactTemplates.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/contacts.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/hostgroups.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/servicegroups.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/timeperiods.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/escalations.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/dependencies.cfg\n";
    
    if (isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "CENGINE")
        $str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/connectors.cfg\n";

	/*
	 * Include for Meta Service the cfg file
	 * Include shinken broker cfg if necessary
	 */
	if (isset($tab['localhost']) && $tab['localhost']) {
	    if (isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "SHINKEN") {
            $str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/shinkenBroker.cfg\n";
		}
	    if ($files = glob("./include/configuration/configGenerate/metaService/*.php")) {
			foreach ($files as $filename)	{
				$cfg = NULL;
				$file = basename($filename);
				$file = explode(".", $file);
				$cfg .= $file[0];
				$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/".$cfg.".cfg\n";
			}
	    }
	}

	# Include for Module the cfg file
	foreach ($oreon->modules as $name => $tab2) {
		if ($oreon->modules[$name]["gen"] && $files = glob("./modules/$name/generate_files/*.php")) {
			foreach ($files as $filename)	{
				$cfg = NULL;
				$file = basename($filename);
				$file = explode(".", $file);
				$cfg .= $file[0];
				$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/".$cfg.".cfg\n";
			}
		}
	}
	$str .= "resource_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/resource.cfg\n";

	/*
	 * Generate all parameters
	 */
	require "./include/configuration/configGenerate/genMainFile.php";

	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
	
	$DBRESULT->free();
	unset($str);
?>
