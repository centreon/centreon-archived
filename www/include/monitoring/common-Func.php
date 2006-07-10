<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();

	function getMyHostRow($host_id = NULL, $rowdata)	{
		if (!$host_id) exit();
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT host_".$rowdata.", host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $res->fetchRow();
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
		$res =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		$r =& $res->fetchRow();
		
		if (isset($host))	{
			$res =& $pearDB->query("SELECT DISTINCT sv.service_id, sv.service_description FROM service sv, host_service_relation hsr WHERE hsr.host_host_id = '".$host."' AND sv.service_id = '".$service."'");
			$res->fetchInto($service);
		}
		exec("echo \"[".time()."] ADD_SVC_COMMENT;".$r["host_name"].";".$service["service_description"].";".$persistant.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]);
	}

	function AddHostComment($host, $comment, $persistant){
		global $oreon, $pearDB;
		if (!isset($persistant))
			$persistant = 0;
		$res =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		$r =& $res->fetchRow();
		
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

		$res =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		$r =& $res->fetchRow();
		$timestamp = time();
		//print("echo \"[".time()."] SCHEDULE_HOST_DOWNTIME;".$r["host_name"].";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]) ;
		if ($oreon->user->get_version() == 1)
			exec("echo \"[".$timestamp."] SCHEDULE_HOST_DOWNTIME;".$r["host_name"].";".$start_time.";".$end_time.";".$persistant.";".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]) ;
		else
			exec("echo \"[".$timestamp."] SCHEDULE_HOST_DOWNTIME;".$r["host_name"].";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]) ;

		$pearDB->query("INSERT INTO downtime (host_id, entry_time , author , comment , start_time , end_time , fixed , duration , deleted) ".
									"VALUES ('".$host."', '".$timestamp."', '".$oreon->user->get_id()."', '".$comment."', '".$start."', '".$end."', '".$persistant."', '".$duration."', '0')");
		if (PEAR::isError($pearDB)) 
			print $db->getMessage();
	}

	function AddSvcDowntime($host, $service, $comment, $start, $end, $persistant){
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

		$res =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$host."'");
		$r =& $res->fetchRow();

		if (isset($host))	{
			$res =& $pearDB->query("SELECT DISTINCT sv.service_id, sv.service_description FROM service sv, host_service_relation hsr WHERE hsr.host_host_id = '".$host."' AND sv.service_id = '".$service."'");
			$res->fetchInto($service);
		}
		//attention timestamp ki peu etre decaler !
		$timestamp = time();
		//print("echo \"[".time()."] SCHEDULE_SVC_DOWNTIME;".$r["host_name"].";".$service["service_description"].";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]);
		if ($oreon->user->get_version() == 1)
			exec("echo \"[".$timestamp."] SCHEDULE_SVC_DOWNTIME;".$r["host_name"].";".$service["service_description"].";".$start_time.";".$end_time.";".$persistant.";".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]);
		else
			exec("echo \"[".$timestamp."] SCHEDULE_SVC_DOWNTIME;".$r["host_name"].";".$service["service_description"].";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n\" >> " . $oreon->Nagioscfg["command_file"]);

		$cmd = "INSERT INTO downtime (host_id , service_id , entry_time , author , comment , start_time , end_time , fixed , duration , deleted) ";
		$cmd .= "VALUES ('".$host."', '".$service["service_id"]."', '".$timestamp."', '".$oreon->user->get_id()."', '".$comment."', '".$start."', '".$end."', '".$persistant."', '".$duration."', '0')";

		$pearDB->query($cmd);
		
	}

	function DeleteComment($type){
		global $oreon, $_GET;
		exec ("echo \"[".time()."] DEL_".$type."_COMMENT;".$_GET["id"]."\n\" >> " . $oreon->Nagioscfg["command_file"]);  
	}
	
	function DeleteDowntime($type,$hosts = array()){
		global $oreon, $_GET, $pearDB;
		foreach($hosts as $key=>$value)	{
			$res = explode(";", $key);
			exec ("echo \"[".time()."] DEL_".$type."_DOWNTIME;".$res[0]."\n\" >> " . $oreon->Nagioscfg["command_file"]);
			$cmd = "DELETE FROM downtime where downtime_id = ".$res[1];
			$pearDB->query($cmd);
		}
	}


?>
