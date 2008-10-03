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
		$request = "";
		
		if ($id != NULL) {
			$request = "WHERE action_log_id = '$id'";
		}
		
		$DBRESULT =& $pearDBO->query("SELECT * FROM log_action $request");
		if (PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->postDebugInfo()."<br />";		
		}
		
		while ($data =& $DBRESULT->fetchRow()) {
			$list_actions[$i]["action_log_id"] = $data["action_log_id"];
			$list_actions[$i]["action_log_date"] = date("d/m/Y H:m:i",$data["action_log_date"]);
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
		$i = 0;
		$list_modifications = array();

		if ($id != NULL) {
			$request = "WHERE action_log_id = '$id'";
		}
		
		$DBRESULT2 =& $pearDBO->query("SELECT action_log_id,field_name,field_value FROM `log_action_modification` $request");
		if (PEAR::isError($DBRESULT)) {
			print "DB Error : ".$DBRESULT->postDebugInfo()."<br />";		
		}
		
		while ($field =& $DBRESULT2->fetchRow()) {
			$list_modifications[$i]["action_log_id"] = $field["action_log_id"];
			$list_modifications[$i]["field_name"] = $field["field_name"];
			$list_modifications[$i]["field_value"] = $field["field_value"];
			$i++;
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