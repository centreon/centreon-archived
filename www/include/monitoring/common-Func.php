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
 * For information : contact@oreon-project.org
 */

	if (!isset($oreon))
		exit();

	function getMyHostRow($host_id = NULL, $rowdata)	{
		if (!$host_id) exit();
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_".$rowdata.", host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "Mysql Error : ".$DBRESULT->getMessage();
			$row =& $DBRESULT->fetchRow();
			if ($row["host_".$rowdata])
				return $row["host_$rowdata"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function AddSvcComment($host, $service, $comment, $persistant){
		global $oreon, $pearDB;
		
		if (!isset($persistant))
			$persistant = 0;
		$DBRESULT =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
		$r =& $DBRESULT->fetchRow();
		
		if (isset($host))
			$svc_description = getMyServiceName($service);
		exec("echo \"[".time()."] ADD_SVC_COMMENT;".$r["host_name"].";".$svc_description.";".$persistant.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]);
	}

	function AddHostComment($host, $comment, $persistant){
		global $oreon, $pearDB;

		if (!isset($persistant))
			$persistant = 0;
		$DBRESULT =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
		$r =& $DBRESULT->fetchRow();
		exec("echo \"[".time()."] ADD_HOST_COMMENT;".$r["host_name"].";".$persistant.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]) ;
	}

	function AddHostDowntime($host, $comment, $start, $end, $persistant){
		global $oreon, $pearDB;
		
		if (!isset($persistant))
			$persistant = 0;
		$res = preg_split("/ /", $start);
		$res1 = preg_split("/\//", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], "0", $res1[1], $res1[2], $res1[0]);
		$res = preg_split("/ /", $end);
		$res3 = preg_split("/\//", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[2], $res3[0]);
		$duration = $end_time - $start_time;

		$DBRESULT =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$pearDB->getMessage();
		$r =& $DBRESULT->fetchRow();
		$timestamp = time();
		if ($oreon->user->get_version() == 1)
			exec("echo \"[".$timestamp."] SCHEDULE_HOST_DOWNTIME;".$r["host_name"].";".$start_time.";".$end_time.";".$persistant.";".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]) ;
		else
			exec("echo \"[".$timestamp."] SCHEDULE_HOST_DOWNTIME;".$r["host_name"].";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]) ;
	}

	function AddSvcDowntime($host, $service, $comment, $start, $end, $persistant){
		global $oreon, $pearDB;
		
		if (!isset($persistant))
			$persistant = 0;
		$res = preg_split("/ /", $start);
		$res1 = preg_split("/\//", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], "0", $res1[1], $res1[2], $res1[0], -1);
		$res = preg_split("/ /", $end);
		$res3 = preg_split("/\//", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[2], $res3[0], -1);

		$duration = $end_time - $start_time;

		$DBRESULT =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
		$r =& $DBRESULT->fetchRow();
		if (isset($host))
			$svc_description = getMyServiceName($service);

		$timestamp = time();

		if ($oreon->user->get_version() == 1)
			exec("echo \"[".$timestamp."] SCHEDULE_SVC_DOWNTIME;".$r["host_name"].";".$svc_description.";".$start_time.";".$end_time.";".$persistant.";".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]);
		else
			exec("echo \"[".$timestamp."] SCHEDULE_SVC_DOWNTIME;".$r["host_name"].";".$svc_description.";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]);
	}

	function DeleteComment($type,$hosts = array()){
		global $oreon, $_GET, $pearDB;
		foreach($hosts as $key=>$value)	{
			exec ("echo \"[".time()."] DEL_".$type."_COMMENT;".$key."\n\" >> " . $oreon->Nagioscfg["command_file"]);
		}
	}
	
	function DeleteDowntime($type,$hosts = array()){
		global $oreon, $_GET, $pearDB;
		
		foreach ($hosts as $key => $value)	{
			$res = split(";", $key);
			exec ("echo \"[".time()."] DEL_".$type."_DOWNTIME;".$res[1]."\n\" >> " . $oreon->Nagioscfg["command_file"]);
		}
	}
?>