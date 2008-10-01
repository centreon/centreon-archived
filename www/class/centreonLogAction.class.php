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
 		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
		$logId = $DBRESULT2->fetchRow();
		if ($fields)
			$this->insertFieldsNameValue($logId["MAX(action_log_id)"], $fields);
 	}
 	
 	/*
 	 *  Returns logs 
 	 */
 	function displayLog() {

 	}
}
?>