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
 
 	if (!isset($oreon))
 		exit();
 	
	function updateReportingTimePeriodInDB() {
		global $form, $pearDB;
		
		$ret = $form->getSubmitValues();
		(isset($ret["dayList"]["report_Monday"])) ? $ret["dayList"]["report_Monday"] = 1  : $ret["dayList"]["report_Monday"] = 0;
		(isset($ret["dayList"]["report_Tuesday"])) ? $ret["dayList"]["report_Tuesday"] = 1  : $ret["dayList"]["report_Tuesday"] = 0;
		(isset($ret["dayList"]["report_Wednesday"])) ? $ret["dayList"]["report_Wednesday"] = 1  : $ret["dayList"]["report_Wednesday"] = 0;
		(isset($ret["dayList"]["report_Thursday"])) ? $ret["dayList"]["report_Thursday"] = 1  : $ret["dayList"]["report_Thursday"] = 0;
		(isset($ret["dayList"]["report_Friday"])) ? $ret["dayList"]["report_Friday"] = 1  : $ret["dayList"]["report_Friday"] = 0;
		(isset($ret["dayList"]["report_Saturday"])) ? $ret["dayList"]["report_Saturday"] = 1  : $ret["dayList"]["report_Saturday"] = 0;
		(isset($ret["dayList"]["report_Sunday"])) ? $ret["dayList"]["report_Sunday"] = 1  : $ret["dayList"]["report_Sunday"] = 0;
		
		foreach ($ret["dayList"] as $key => $value){ 	
			$query = "UPDATE `contact_param` SET `cp_value` = '".$ret["dayList"][$key]."' WHERE `cp_contact_id` IS NULL AND `cp_key` = '$key'";
			$DBRESULT =& $pearDB->query($query);
			if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		}
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_hour_start"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_hour_start'";
		$DBRESULT =& $pearDB->query($query);
		if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_minute_start"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_minute_start'";
		$DBRESULT =& $pearDB->query($query);
		if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_hour_end"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_hour_end'";
		$DBRESULT =& $pearDB->query($query);
		if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		
		$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_minute_end"]."' WHERE cp_contact_id IS NULL AND cp_key = 'report_minute_end'";
		$DBRESULT =& $pearDB->query($query);
		if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	}
?>