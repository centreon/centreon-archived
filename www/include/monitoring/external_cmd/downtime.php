<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
		
	// Downtime
	
	function add_host_downtime_in_db($oreon, $dtm, $start_time, $end_time, $duration){
		$check = array("on" => 1, "off" => 0);
		global $pearDB;
		$str =	"INSERT INTO `downtime` (`downtime_id`, `host_id`, `service_id`, `entry_time`, `author`, `comment`, `start_time`, `end_time`, `fixed`, `duration`)".
				" VALUES ('', '".$dtm["host_name"]."', NULL, NOW( ) , '".$oreon->user->get_alias()."', '".$dtm["comment"]."', '".$start_time."', '".$end_time."', '".$check[$dtm["fixed"]]."', '".$duration."');";
		$insert =& $pearDB->query($str);
		if (PEAR::isError($pearDB))
   			die($pearDB->getMessage());
	}
	
	function add_hostGroup_downtime_in_db($oreon, $dtm, $start_time, $end_time, $duration, $host){
		$check = array("on" => 1, "off" => 0);
		global $pearDB;
		$str = 	"INSERT INTO `downtime` (`downtime_id`, `host_id`, `service_id`, `entry_time`, `author`, `comment`, `start_time`, `end_time`, `fixed`, `duration`)".
				" VALUES ('', '".$host."', NULL, NOW( ) , '".$oreon->user->get_alias()."', '".$dtm["comment"]."', '".$start_time."', '".$end_time."', '".$check[$dtm["fixed"]]."', '".$duration."');";
		$insert =& $pearDB->query($str);
		if (PEAR::isError($pearDB))
   			die($pearDB->getMessage());
	}
	
	function add_svc_downtime_in_db($oreon, $dtm, $start_time, $end_time, $duration){
		$check = array("on" => 1, "off" => 0);
		global $pearDB;
		$str = 	"INSERT INTO `downtime` (`downtime_id`, `host_id`, `service_id`, `entry_time`, `author`, `comment`, `start_time`, `end_time`, `fixed`, `duration`)".
				" VALUES ('', '".$dtm["host_id"]."', '".$dtm["service"]."', NOW( ) , '".$oreon->user->get_alias()."', '".$dtm["comment"]."', '".$start_time."', '".$end_time."', '".$check[$dtm["fixed"]]."', '".$duration."');";
		$insert =& $pearDB->query($str);
		if (PEAR::isError($pearDB))
   			die($pearDB->getMessage());
	}
	
	function del_svc_downtime_in_db($start_time, $host, $svc){
		global $pearDB;
		$host =& $pearDB->query("SELECT host_id FROM host WHERE `host_name` = '".$host."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$host_name = $host->fetchRow();				
		$service =& $pearDB->query("SELECT service_id FROM service WHERE `service_description` = '".$svc."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$service_desc = $service->fetchRow();				
		$str = 	"UPDATE `downtime` SET `deleted` = '1' WHERE `service_id` = '".$service_desc["service_id"]."' AND `host_id` = '".$host_name["host_id"]."' AND `start_time` = '".$start_time."' LIMIT 1 ;";
		$update =& $pearDB->query($str);
		if (PEAR::isError($pearDB))
   			die($pearDB->getMessage());
	}
	
	function del_host_downtime_in_db($start_time, $host){
		global $pearDB;
		$host =& $pearDB->query("SELECT host_id FROM host WHERE `host_name` = '".$host."'");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$host_name = $host->fetchRow();				
		$str = 	"UPDATE `downtime` SET `deleted` = '1' WHERE `host_id` = '".$host_name["host_id"]."' AND `start_time` = '".$start_time."' LIMIT 1 ;";
		$update =& $pearDB->query($str);
		if (PEAR::isError($pearDB))
   			die($pearDB->getMessage());
	}
	
	function add_host_downtime($oreon, $dtm, $lang){
		$check = array("on" => 1, "off" => 0);
		$res = preg_split("/ /", $dtm["strtime"]);
		$res1 = preg_split("/-/", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], $res2[2], $res1[1], $res1[0], $res1[2]);
		$res = preg_split("/ /", $dtm["endtime"]);
		$res3 = preg_split("/-/", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], $res4[2], $res3[1], $res3[0], $res3[2]);
		$duration = $end_time - $start_time;
		if (!isset($dtm["fixed"]))
			$dtm["fixed"] = "off";
		add_host_downtime_in_db(&$oreon, $dtm, $start_time, $end_time,$duration);
		$str = "echo '[" . time() . "] SCHEDULE_HOST_DOWNTIME;".$oreon->hosts[$dtm["host_name"]]->get_name().";".$start_time.";".$end_time.";".$check[$dtm["fixed"]].";".$duration.";".$oreon->user->get_alias().";".$dtm["comment"]."' >> " . $oreon->Nagioscfg->command_file;
		system($str);
		print "<div style='padding-top: 50px' class='text11b'><center>".$lang["dtm_added"]."</center></div>";
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=308'\",2000)</SCRIPT>";
	}
	
	function add_svc_downtime($oreon, $dtm, $lang){
		$check = array("on" => 1, "off" => 0);
		$res = preg_split("/ /", $dtm["strtime"]);
		$res1 = preg_split("/-/", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], "0", $res1[1], $res1[0], $res1[2]);
		$res = preg_split("/ /", $dtm["endtime"]);
		$res3 = preg_split("/-/", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[0], $res3[2]);
		$duration = $end_time - $start_time;
		if (!isset($dtm["fixed"])) $dtm["fixed"] = "off";
		add_svc_downtime_in_db(&$oreon, $dtm, $start_time, $end_time,$duration);
		$str = "echo '[" . time() . "] SCHEDULE_SVC_DOWNTIME;".$oreon->hosts[$dtm["host_id"]]->get_name().";".$oreon->services[$dtm["service"]]->get_description().";".$start_time.";".$end_time.";".$check[$dtm["fixed"]].";".$duration.";".$oreon->user->get_alias().";".$dtm["comment"]."' >> " . $oreon->Nagioscfg->command_file;
		system($str);
		print "<div style='padding-top: 50px' class='text11b'><center>".$lang["dtm_added"]."</center></div>";
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=308'\",2000)</SCRIPT>";
	}
	
	function add_svc_hostgroup_downtime($oreon, $dtm, $lang){
		$check = array("on" => 1, "off" => 0);
		$res = preg_split("/ /", $dtm["strtime"]);
		$res1 = preg_split("/-/", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], $res2[2], $res1[1], $res1[0], $res1[2]);
		$res = preg_split("/ /", $dtm["endtime"]);
		$res3 = preg_split("/-/", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], $res4[2], $res3[1], $res3[0], $res3[2]);
		$duration = $end_time - $start_time;
		if (!isset($dtm["fixed"]))
			$dtm["fixed"] = "off";
		foreach ($oreon->hostGroups[$dtm["host_group"]]->hosts as $h){
			foreach ($h->services as $s){
				add_hostGroup_downtime_in_db(&$oreon, $dtm, $start_time, $end_time,$duration, $h->get_id());
				$str = "echo '[" . time() . "] SCHEDULE_SVC_DOWNTIME;".$h->get_name().";".$s->get_description().";".$start_time.";".$end_time.";".$check[$dtm["fixed"]].";".$duration.";".$oreon->user->get_alias().";".$dtm["comment"]."' >> " . $oreon->Nagioscfg->command_file;
				system($str);
				unset($s);
			}
			unset($h);
		}
		if (isset($dtm["host_too"]))
			foreach ($oreon->hostGroups[$dtm["host_group"]]->hosts as $h){
				$str = "echo '[" . time() . "] SCHEDULE_HOST_DOWNTIME;".$h->get_name().";".$start_time.";".$end_time.";".$check[$dtm["fixed"]].";".$duration.";".$oreon->user->get_alias().";".$dtm["comment"]."' >> " . $oreon->Nagioscfg->command_file;
				system($str);
				unset($h);
			}
		print "<div style='padding-top: 50px' class='text11b'><center>".$lang["dtm_added"]."</center></div>";
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=308'\",2000)</SCRIPT>";
	}
	
	function add_hostgroup_downtime($oreon, $dtm, $lang){
		$check = array("on" => 1, "off" => 0);
		$res = preg_split("/ /", $dtm["strtime"]);
		$res1 = preg_split("/-/", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], $res2[2], $res1[1], $res1[0], $res1[2]);
		$res = preg_split("/ /", $dtm["endtime"]);
		$res3 = preg_split("/-/", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], $res4[2], $res3[1], $res3[0], $res3[2]);
		$duration = $end_time - $start_time;
		if (!isset($dtm["fixed"]))
			$dtm["fixed"] = "off";
		foreach ($oreon->hostGroups[$dtm["host_group"]]->hosts as $h){
			add_hostGroup_downtime_in_db(&$oreon, $dtm, $start_time, $end_time, $duration, $h->get_id());
			$str = "echo '[" . time() . "] SCHEDULE_HOST_DOWNTIME;".$h->get_name().";".$start_time.";".$end_time.";".$check[$dtm["fixed"]].";".$duration.";".$oreon->user->get_alias().";".$dtm["comment"]."' >> " . $oreon->Nagioscfg->command_file;
			system($str);
		}
		print "<div style='padding-top: 50px' class='text11b'><center>".$lang["dtm_added"]."</center></div>";
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=308'\",2000)</SCRIPT>";
	}
	
	function del_host_downtime($oreon, $arg, $lang){
		$str = "echo '[" . time() . "] DEL_HOST_DOWNTIME;".$arg["id"]."' >> " . $oreon->Nagioscfg->command_file;
		del_host_downtime_in_db($arg["start_time"], $arg["host"]);
		print "<div style='padding-top: 50px' class='text11b'><center>".$lang["dtm_del"]."</center></div>";
		system($str);
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=308'\",2000)</SCRIPT>";
	}
	
	function del_svc_downtime($oreon, $arg, $lang){
		$str = "echo '[" . time() . "] DEL_SVC_DOWNTIME;".$arg["id"]."' >> " . $oreon->Nagioscfg->command_file;
		//print $arg["svc"];
		del_svc_downtime_in_db($arg["start_time"], $arg["host"], $arg["svc"]);
		print "<div style='padding-top: 50px' class='text11b'><center>".$lang["dtm_del"]."</center></div>";
		system($str);
		print "<SCRIPT LANGUAGE='JavaScript'> setTimeout(\"javascript:document.location.href='oreon.php?p=308'\",2000)</SCRIPT>";
	}
?>