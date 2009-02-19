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

class CentreonLogAction {
	var $logUser;

	/*
	 * Initializes variables
	 */
	function CentreonLogAction($usr) {
		$this->logUser = $usr;
	}
	
	/*
 	 *  Inserts configuration into DB
 	 */
 	function insertFieldsNameValue($logId, $fields) {
 		global $pearDBO;

 		$query = "INSERT INTO `log_action_modification` (field_name, field_value, action_log_id) VALUES ";
 		foreach ($fields as $key => $value) {
 			$query .= "('".$key."', '".$value."', '".$logId."'), ";
 		}
 		$query[strlen($query)-2] = " "; //removes the last coma
 		$DBRESULT =& $pearDBO->query($query); 		
 	}
	
	/*
 	 *  Inserts logs : add, delete or modification of an object
 	 */
 	function insertLog($object_type, $object_id, $object_name, $action_type, $fields = NULL) {
 		global $pearDBO;
 		$now = time();
 		$str_query = "INSERT INTO `log_action` (action_log_date, object_type, object_id, object_name, action_type, log_contact_id) VALUES ('".$now."', '".$object_type."', '".$object_id."', '".$object_name."', '".$action_type."', '".$this->logUser->user_id."')";
 		$DBRESULT =& $pearDBO->query($str_query);
 		
 		$DBRESULT2 =& $pearDBO->query("SELECT MAX(action_log_id) FROM `log_action`");		
		$logId = $DBRESULT2->fetchRow();
		if ($fields)
			$this->insertFieldsNameValue($logId["MAX(action_log_id)"], $fields);
 	}
 	
 	/*
	 * returns the contact name
	 */
	function getContactname($id) {
		global $pearDB;
	
		$DBRESULT =& $pearDB->query("SELECT contact_name FROM `contact` WHERE contact_id = '$id' LIMIT 1");				
		while ($data =& $DBRESULT->fetchRow()) {
			$name = $data["contact_name"];
		}
			
	return $name;
	}
	
	/*
	 * returns the list of actions ("create","delete","change","massive change", "enable", "disable")
	 */
	function listAction($id) { 
		global $pearDBO;
		$list_actions = array();
		$i = 0;
		
		$DBRESULT =& $pearDBO->query("SELECT * FROM log_action WHERE object_id ='".$id."' ORDER BY action_log_date DESC");		
		
		while ($data =& $DBRESULT->fetchRow()) {
			$list_actions[$i]["action_log_id"] = $data["action_log_id"];
			$list_actions[$i]["action_log_date"] = date("d/m/Y H:i",$data["action_log_date"]);
			$list_actions[$i]["object_type"] = $data["object_type"];
			$list_actions[$i]["object_id"] = $data["object_id"];
			$list_actions[$i]["object_name"] = $data["object_name"];
			$list_actions[$i]["action_type"] = $this->replaceActiontype($data["action_type"]);
			$list_actions[$i]["log_contact_id"] = $this->getContactname($data["log_contact_id"]);
			$i++;
		}

		return $list_actions;
	}
	
	/*
	 *  returns list of modifications
	 */
	function listModification($id) {
		global $pearDBO;
		$list_modifications = array();
		$ref = array();
		$i = 0;
		$j = 0;
		$first_ref_flag = 0;
		
		$DBRESULT =& $pearDBO->query("SELECT action_log_id, action_log_date, action_type FROM log_action WHERE object_id ='".$id."' ORDER  BY action_log_date ASC");	
		while ($row =& $DBRESULT->fetchRow()) {
			$DBRESULT2 =& $pearDBO->query("SELECT action_log_id,field_name,field_value FROM `log_action_modification` WHERE action_log_id='".$row['action_log_id']."'");			
			while ($field =& $DBRESULT2->fetchRow()) {
				if (($row["action_type"] == "mc" || $row["action_type"] == "a" || $row["action_type"] == "c") && (!$first_ref_flag || $first_ref_flag == $field["action_log_id"])) {
					$ref[$field["field_name"]] = $field["field_value"];
					$first_ref_flag = $field["action_log_id"];
					
					$list_modifications[$i]["action_log_id"] = $field["action_log_id"];
					$list_modifications[$i]["field_name"] = $field["field_name"];
					$list_modifications[$i]["field_value_before"] = "";//$field["field_value"];
					$list_modifications[$i]["field_value_after"] = $field["field_value"];
					$j++;
				}
				else {
					foreach ($ref as $key => $value) {
						if (!isset($ref[$field["field_name"]])) {
							$list_modifications[$i]["field_value_before"] = "";
							$list_modifications[$i]["action_log_id"] = $field["action_log_id"];
							$list_modifications[$i]["field_name"] = $field["field_name"];
							$list_modifications[$i]["field_value_after"] = $field["field_value"];
							$ref[$field["field_name"]] = $field["field_value"];
						} else if (($field["field_name"] == $key) && ($field["field_value"] != $value)) {
							$list_modifications[$i]["field_value_before"] = $value;
							$list_modifications[$i]["action_log_id"] = $field["action_log_id"];
							$list_modifications[$i]["field_name"] = $field["field_name"];
							$list_modifications[$i]["field_value_after"] = $field["field_value"];
							$ref[$key] = $field["field_value"];
						}
					}
				}
				$i++;
			}
		}
		return $list_modifications;
	}
	 
	/*
	 *  Display clear action labels
	 */
	function replaceActiontype($action) {
		
		$actionList = array();
		$actionList["d"] = "Delete";
		$actionList["c"] = "Change";
		$actionList["a"] = "Create";
		$actionList["disable"] = "Disable";
		$actionList["enable"] = "Enable";
		$actionList["mc"] = "Massive change";	
		
		foreach ($actionList as $key => $value) {
			if ($action == $key) {
				$action = $value;
			}
		}
		
		return $action;
	}
	
	/*
	 *  list object types
	 */
	function listObjecttype() {
		$object_type_tab = array();

		$object_type_tab[0] = _("All");
		$object_type_tab[1] = "command";
		$object_type_tab[2] = "timeperiod";
		$object_type_tab[3] = "contact";
		$object_type_tab[4] = "contactgroup";
		$object_type_tab[5] = "host";
		$object_type_tab[6] = "hostgroup";
		$object_type_tab[7] = "service";
		$object_type_tab[8] = "servicegroup";
		$object_type_tab[9] = "snmp traps";
		$object_type_tab[10] = "escalation";
		$object_type_tab[11] = "host dependency";
		$object_type_tab[12] = "hostgroup dependency";
		$object_type_tab[13] = "service dependency";
		$object_type_tab[14] = "servicegroup dependency";	
		
		return $object_type_tab;
	}
}
?>