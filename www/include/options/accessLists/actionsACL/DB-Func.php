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
	if (!isset ($oreon))
		exit ();
	
	function testActionExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('acl_action_id');
		$DBRESULT =& $pearDB->query("SELECT acl_action_id, acl_action_name FROM acl_actions WHERE acl_action_name = '".htmlentities($name, ENT_QUOTES)."'");
		$cg =& $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $cg["acl_action_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $cg["acl_action_id"] != $id)
			return false;
		else
			return true;
	}

	function enableActionInDB ($acl_action_id = null)	{
		if (!$acl_action_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE acl_actions SET acl_action_activate = '1' WHERE acl_action_id = '".$acl_action_id."'");
	}
	
	function disableActionInDB ($acl_action_id = null)	{
		if (!$acl_action_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("UPDATE acl_actions SET acl_action_activate = '0' WHERE acl_action_id = '".$acl_action_id."'");
	}
	
	function deleteActionInDB ($Actions = array())	{
		global $pearDB;
		foreach($Actions as $key=>$value)	{
			$DBRESULT =& $pearDB->query("DELETE FROM acl_actions WHERE acl_action_id = '".$key."'");
			$DBRESULT =& $pearDB->query("DELETE FROM acl_actions_rules WHERE acl_action_rule_id = '".$key."'");
			$DBRESULT =& $pearDB->query("DELETE FROM acl_group_actions_relations WHERE acl_action_id = '".$key."'");
		}
	}
	
	function multipleActionInDB ($Actions = array(), $nbrDup = array())	{
		foreach($Actions as $key=>$value)	{
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT * FROM acl_actions WHERE acl_action_id = '".$key."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			$row["acl_action_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2) {
					$key2 == "acl_action_name" ? ($acl_action_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
				}
				if (testActionExistence($acl_action_name))	{
					$val ? $rq = "INSERT INTO acl_actions VALUES (".$val.")" : $rq = null;
					$DBRESULT =& $pearDB->query($rq);
					$DBRESULT =& $pearDB->query("SELECT MAX(acl_action_id) FROM acl_actions");
					$maxId =& $DBRESULT->fetchRow();
					$DBRESULT->free();
					if (isset($maxId["MAX(acl_action_id)"])) {
						$DBRESULT =& $pearDB->query("SELECT DISTINCT acl_group_id,acl_action_id FROM acl_group_actions_relations WHERE acl_action_id = '".$key."'");
						while ($cct =& $DBRESULT->fetchRow())	{
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_group_actions_relations VALUES ('', '".$maxId["MAX(acl_action_id)"]."', '".$cct["acl_group_id"]."')");
						}
						
						# Duplicate Actions
						$DBRESULT =& $pearDB->query("SELECT acl_action_rule_id,acl_action_name FROM acl_actions_rules WHERE acl_action_rule_id = '".$key."'");
						while ($acl =& $DBRESULT->fetchRow()) {
							//print $acl["acl_action_rule_id"]."<br />";
							//print $acl["acl_action_name"];
							$DBRESULT2 =& $pearDB->query("INSERT INTO acl_actions_rules VALUES ('', '".$maxId["MAX(acl_action_id)"]."', '".$acl["acl_action_name"]."')");
						}
						
						$DBRESULT->free();
					}
				}
			}
		}
	}	
	
	function insertActionInDB ($ret = array())	{
		$acl_action_id = insertAction($ret);
		updateGroupActions($acl_action_id, $ret);
		updateRulesActions($acl_action_id, $ret);
		return $acl_action_id;
	}
	
	function insertAction($ret)	{
		global $form, $pearDB;
		if (!count($ret))
			$ret = $form->getSubmitValues();
		$rq = "INSERT INTO acl_actions ";
		$rq .= "(acl_action_name, acl_action_description, acl_action_activate) ";
		$rq .= "VALUES ";
		$rq .= "('".htmlentities($ret["acl_action_name"], ENT_QUOTES)."', '".htmlentities($ret["acl_action_description"], ENT_QUOTES)."', '".htmlentities($ret["acl_action_activate"]["acl_action_activate"], ENT_QUOTES)."')";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(acl_action_id) FROM acl_actions");
		$cg_id =& $DBRESULT->fetchRow();
		return ($cg_id["MAX(acl_action_id)"]);
	}
	
	function updateActionInDB ($acl_action_id = NULL)	{
		if (!$acl_action_id) return;
		updateAction($acl_action_id);
		updateGroupActions($acl_action_id);
	}
	
	function updateAction($acl_action_id = null)	{
		if (!$acl_action_id) return;
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE acl_actions ";
		$rq .= "SET acl_action_name = '".htmlentities($ret["acl_action_name"], ENT_QUOTES)."', " .
				"acl_action_description = '".htmlentities($ret["acl_action_description"], ENT_QUOTES)."', " .
				"acl_action_activate = '".htmlentities($ret["acl_action_activate"]["acl_action_activate"], ENT_QUOTES)."' " .
				"WHERE acl_action_id = '".$acl_action_id."'";
		$DBRESULT =& $pearDB->query($rq);
	}
	
	function updateGroupActions($acl_action_id, $ret = array())	{
		if (!$acl_action_id) return;
		global $form, $pearDB;
		
		$rq = "DELETE FROM acl_group_actions_relations WHERE acl_action_id = '".$acl_action_id."'";
		$DBRESULT =& $pearDB->query($rq);
		if (isset($_POST["acl_groups"]))
			foreach ($_POST["acl_groups"] as $id){
				$rq = "INSERT INTO acl_group_actions_relations ";
				$rq .= "(acl_group_id, acl_action_id) ";
				$rq .= "VALUES ";
				$rq .= "('".$id."', '".$acl_action_id."')";
				$DBRESULT =& $pearDB->query($rq);
			}	
				
	}
	
	function updateRulesActions($acl_action_id, $ret = array())	{
	if (!$acl_action_id) return;
	global $form, $pearDB;
	
	$rq = "DELETE FROM acl_actions_rules WHERE acl_action_rule_id = '".$acl_action_id."'";
	$DBRESULT =& $pearDB->query($rq);
	
	$actions = array();
	$actions = listActions();
	
		foreach ($actions as $action){
			if(isset($_POST[$action])) {
			
				$rq = "INSERT INTO acl_actions_rules ";
				$rq .= "(acl_action_rule_id, acl_action_name) ";
				$rq .= "VALUES ";
				$rq .= "('".$acl_action_id."', '".$action."')";
				
				$DBRESULT =& $pearDB->query($rq);
			}
			
		}
			
	}
	
	function listActions() {
		$actions = array();
		
		# Services Actions
		$actions[] = "service_checks";
		$actions[] = "service_notifications";
		$actions[] = "service_acknowledgement";
		$actions[] = "service_schedule_check";
		$actions[] = "service_schedule_downtime";
		$actions[] = "service_comment";
		$actions[] = "service_event_handler";
		$actions[] = "service_flap_detection";
		$actions[] = "service_passive_checks";
		$actions[] = "service_submit_result";
		
		# Hosts Actions
		$actions[] = "host_checks";
		$actions[] = "host_notifications";
		$actions[] = "host_acknowledgement";
		$actions[] = "host_schedule_check";
		$actions[] = "host_schedule_downtime";
		$actions[] = "host_comment";
		$actions[] = "host_event_handler";
		$actions[] = "host_flap_detection";
		$actions[] = "host_checks_for_services";
		$actions[] = "host_notifications_for_services";
		
		return $actions;
	}
?>