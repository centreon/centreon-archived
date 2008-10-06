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
	
	function getContactname($id) {
	global $pearDB;
	
		$DBRESULT =& $pearDB->query("SELECT contact_name FROM `contact` WHERE contact_id = '$id' LIMIT 1");
		if (PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->postDebugInfo()."<br />";		
		}
		
		while ($data =& $DBRESULT->fetchRow()) {
			$name = $data["contact_name"];
		}
			
	return $name;
	}
	
	function listAction($id) { 
		global $pearDBO;
		$list_actions = array();
		$i = 0;
		
		$DBRESULT =& $pearDBO->query("SELECT * FROM log_action WHERE object_id ='".$id."' ORDER BY action_log_date DESC");
		if (PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->postDebugInfo()."<br />";		
		}
		
		while ($data =& $DBRESULT->fetchRow()) {
			$list_actions[$i]["action_log_id"] = $data["action_log_id"];
			$list_actions[$i]["action_log_date"] = date("d/m/Y H:i",$data["action_log_date"]);
			$list_actions[$i]["object_type"] = $data["object_type"];
			$list_actions[$i]["object_id"] = $data["object_id"];
			$list_actions[$i]["object_name"] = $data["object_name"];
			$list_actions[$i]["action_type"] = replaceActiontype($data["action_type"]);
			$list_actions[$i]["log_contact_id"] = getContactname($data["log_contact_id"]);
			$i++;
		}

		return $list_actions;
	}
	
	function listModification($id) {
		global $pearDBO;
		$list_modifications = array();
		$ref = array();
		$i = 0;
		$j = 0;
		$first_ref_flag = 0;
		
		$DBRESULT =& $pearDBO->query("SELECT action_log_id, action_log_date, action_type FROM log_action WHERE object_id ='".$id."' ORDER  BY action_log_date ASC");
		if (PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->postDebugInfo()."<br />";		
		}
		while ($row =& $DBRESULT->fetchRow()) {
			$DBRESULT2 =& $pearDBO->query("SELECT action_log_id,field_name,field_value FROM `log_action_modification` WHERE action_log_id='".$row['action_log_id']."'");
			if (PEAR::isError($DBRESULT2)) {
				print "DB Error : ".$DBRESULT2->postDebugInfo()."<br />";		
			}
			while ($field =& $DBRESULT2->fetchRow()) {
				if (($row["action_type"] == "a" || $row["action_type"] == "c") && (!$first_ref_flag || $first_ref_flag == $field["action_log_id"])) {
					$ref[$j]["action_log_id"] = $field["action_log_id"];
					$ref[$j]["field_name"] = $field["field_name"];
					$ref[$j]["field_value"] = $field["field_value"];
					$first_ref_flag = $field["action_log_id"];
					
					$list_modifications[$i]["action_log_id"] = $field["action_log_id"];
					$list_modifications[$i]["field_name"] = $field["field_name"];
					$list_modifications[$i]["field_value_before"] = $field["field_value"];
					$list_modifications[$i]["field_value_after"] = $field["field_value"];
					$j++;
				}
				else {
					foreach ($ref as $key => $value) {
						if (($field["field_name"] == $value["field_name"]) && ($field["field_value"] != $value["field_value"])) {
							$list_modifications[$i]["field_value_before"] = $value["field_value"];
							$list_modifications[$i]["action_log_id"] = $field["action_log_id"];
							$list_modifications[$i]["field_name"] = $field["field_name"];
							$list_modifications[$i]["field_value_after"] = $field["field_value"];
							$ref[$key]["field_value"] = $field["field_value"];
						}
					}
				}
				$i++;
			}
		}
		return $list_modifications;
	}
	 
	function replaceActiontype($action) {
		
		$actionList = array();
		$actionList["d"] = "Delete";
		$actionList["c"] = "Change";
		$actionList["a"] = "Create";
		$actionList["disable"] = "Disable";
		$actionList["enable"] = "Enable";
		$actionList["mc"] = "Massive change";	
		
		foreach($actionList as $key => $value) {
			if ($action == $key) {
				$action = $value;
			}
		}
		
		return $action;
	}
	
	function listObjecttype() {
		global $pearDBO;
		$objectList = array();

		$objectList[] = "...";		
		$objectList[] = "Command";
		$objectList[] = "TimePeriod";
		$objectList[] = "Contact";
		$objectList[] = "ContactGroup";
		$objectList[] = "Host";
		$objectList[] = "HostGroup";
		$objectList[] = "Service";
		$objectList[] = "ServiceGroup";
		$objectList[] = "Traps";
		$objectList[] = "Escalations";
		$objectList[] = "Host Dependency";											
		$objectList[] = "HostGroup Dependency";		
		$objectList[] = "Service";
		$objectList[] = "ServiceGroup Dependency";	
		
		return $objectList;
	}
	
	function listObjectname($id) {
		global $pearDBO;
		$objectList = array();
		
		$request = "";
		if(isset($id)) {
		$request = " WHERE object_type = '".$id."'";
		}
		
		$DBRESULT2 =& $pearDBO->query("SELECT DISTINCT object_id,object_name FROM `log_action`$request");
		if (PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->postDebugInfo()."<br />";		
		}
		
		while ($data =& $DBRESULT2->fetchRow()) {	
		$objectList[$data["object_id"]] = $data["object_name"];
		}
		
		return $objectList;
	}
	
 ?>