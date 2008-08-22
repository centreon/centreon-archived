<?php
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
	
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["dayList"]["report_Monday"]."' WHERE cp_contact_id is null AND cp_key = 'report_Monday'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["dayList"]["report_Tuesday"]."' WHERE cp_contact_id  is null AND cp_key = 'report_Tuesday'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value =' ".$ret["dayList"]["report_Wednesday"]."' WHERE cp_contact_id is null AND cp_key = 'report_Wednesday'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["dayList"]["report_Thursday"]."' WHERE cp_contact_id  is null AND cp_key = 'report_Thursday'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["dayList"]["report_Friday"]."' WHERE cp_contact_id is null AND cp_key = 'report_Friday'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["dayList"]["report_Saturday"]."' WHERE cp_contact_id is null AND cp_key = 'report_Saturday'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["dayList"]["report_Sunday"]."' WHERE cp_contact_id is null AND cp_key = 'report_Sunday'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_hour_start"]."' WHERE cp_contact_id is null AND cp_key = 'report_hour_start'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_minute_start"]."' WHERE cp_contact_id is null AND cp_key = 'report_minute_start'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_hour_end"]."' WHERE cp_contact_id is null AND cp_key = 'report_hour_end'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$query = "UPDATE `contact_param` SET cp_value = '".$ret["report_minute_end"]."' WHERE cp_contact_id is null AND cp_key = 'report_minute_end'";
	$DBRESULT =& $pearDB->query($query);
	if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
}
?>