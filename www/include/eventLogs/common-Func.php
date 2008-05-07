<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
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
 
	function check_session($sid, $pearDB){
		if (isset($sid) && !check_injection($sid)){
			$sid = htmlentities($sid);
			$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
			if($res->fetchInto($session)){
				return $session["user_id"];
			} else
				get_error('bad session id');		
		}
		else
			get_error('need session identifiant !');
		return 0;
	}

	function get_user_param($user_id, $pearDB){
		$tab_row = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM contact_param where cp_contact_id = '".$user_id."'");
		if (PEAR::isError($DBRESULT)){
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			return null;		
		}
		while( $row = $DBRESULT->fetchRow())
			$tab_row[$row["cp_key"]] = $row["cp_value"];
		return $tab_row;
	}

	function set_user_param($user_id, $pearDB, $key, $value){
		$DBRESULT =& $pearDB->query("SELECT * FROM contact_param WHERE cp_value = '".$value."' AND cp_contact_id like '".$user_id."' AND cp_key like '".$key."'");		
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if ($DBRESULT->numRows()){
			$DBRESULT =& $pearDB->query("UPDATE contact_param set cp_value ='".$value."' where cp_contact_id like '".$user_id."' AND cp_key like '".$key."' ");		
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		} else {
			$DBRESULT =& $pearDB->query("INSERT INTO `contact_param` ( `cp_value`, `cp_contact_id`, `cp_key`) VALUES ('".$value."', '".$user_id."', '".$key."')");		
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
	}
 
 	function getMyHostIDService($svc_id = NULL)	{
		if (!$svc_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host h, host_service_relation hs WHERE h.host_id = hs.host_host_id AND hs.service_service_id = '".$svc_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}
 	
?>