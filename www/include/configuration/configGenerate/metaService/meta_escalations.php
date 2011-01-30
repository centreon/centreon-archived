<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	if (!isset($oreon))
 		exit();

	$str = NULL;
	$i = 1;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_escalations.cfg", $oreon->user->get_name());

	$DBRESULT = $pearDB->query("SELECT DISTINCT meta_service_meta_id FROM escalation_meta_service_relation");
	while ($service = $DBRESULT->fetchRow()) {
		if (isset($gbArr[7][$service["meta_service_meta_id"]]))	{
			$DBRESULT2 = $pearDB->query("SELECT esc.* FROM escalation esc, escalation_meta_service_relation emsr WHERE emsr.meta_service_meta_id = '".$service["meta_service_meta_id"]."' AND esc.esc_id = emsr.escalation_esc_id ORDER BY esc.esc_name");
			$escalation = array();
			while ($escalation = $DBRESULT2->fetchRow())	{
				$ret["comment"]["comment"] ? ($str .= "# '".$escalation["esc_name"]."' service escalation definition ".$i."\n") : NULL;
				if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
					$comment = array();
					$comment = explode("\n", $escalation["esc_comment"]);
					foreach ($comment as $cmt)
						$str .= "# ".$cmt."\n";
				}
				$str .= "define serviceescalation{\n";
				$str .= print_line("host_name", "_Module_Meta");
				$str .= print_line("service_description", "meta_".$service["meta_service_meta_id"]);
				$cg = array();
				$strTemp = NULL;
				$DBRESULT3 = $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
				while($cg = $DBRESULT3->fetchRow()) {
					if (isset($gbArr[1][$cg["cg_id"]]))
						$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
				}
				$DBRESULT3->free();
				if ($strTemp) 
					$str .= print_line("contact_groups", $strTemp);			
				if ($escalation["first_notification"] != NULL) 
					$str .= print_line("first_notification", $escalation["first_notification"]);
				if ($escalation["last_notification"] != NULL) 
					$str .= print_line("last_notification", $escalation["last_notification"]);
				if ($escalation["notification_interval"] != NULL) 
					$str .= print_line("notification_interval", $escalation["notification_interval"]);
				
				$DBRESULT4 = $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
				$tp = $DBRESULT4->fetchRow();
				$DBRESULT4->free();		
				if ($tp["tp_name"]) 
					$str .= print_line("escalation_period", $tp["tp_name"]);
				if ($escalation["escalation_options2"]) 
					$str .= print_line("escalation_options", $escalation["escalation_options2"]);
				$str .= "}\n\n";
				$i++;
			}
			unset($escalation);
			$DBRESULT2->free();
		}
	}
	unset($service);
	$DBRESULT->free();
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_escalations.cfg");
	fclose($handle);
	unset($str);
?>