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
 
	if (!isset($oreon))
		exit();
	
		
	function DeleteDowntime($type, $hosts = array()){
		global $oreon, $_GET, $pearDB;
		
		foreach ($hosts as $key => $value)	{
			$res = split(";", $key);
			write_command(" DEL_".$type."_DOWNTIME;".$res[1]."\n", GetMyHostPoller($pearDB, $res[0]));
		}
	}

	function AddHostDowntime($host, $comment, $start, $end, $persistant){
		global $oreon, $pearDB, $centreonGMT;
		
		if (!isset($persistant))
			$persistant = 0;
		$res = preg_split("/ /", $start);
		$res1 = preg_split("/\//", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], "0", $res1[1], $res1[2], $res1[0]);
		
		$start_time = $centreonGMT->getUTCDate($start_time);
		
		$res = preg_split("/ /", $end);
		$res3 = preg_split("/\//", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[2], $res3[0]);
		
		$end_time = $centreonGMT->getUTCDate($end_time);
		
		$duration = $end_time - $start_time;
		
		$timestamp = time();
		write_command(" SCHEDULE_HOST_DOWNTIME;".getMyHostName($host).";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n", GetMyHostPoller($pearDB, getMyHostName($host)));
	}

	function AddSvcDowntime($host, $service, $comment, $start, $end, $persistant){
		global $oreon, $pearDB, $centreonGMT;
		
		if (!isset($persistant))
			$persistant = 0;
		$res = preg_split("/ /", $start);
		$res1 = preg_split("/\//", $res[0]);
		$res2 = preg_split("/:/", $res[1]);
		$start_time = mktime($res2[0], $res2[1], "0", $res1[1], $res1[2], $res1[0], -1);

		$start_time = $centreonGMT->getUTCDate($start_time);
	
		$res = preg_split("/ /", $end);
		$res3 = preg_split("/\//", $res[0]);
		$res4 = preg_split("/:/", $res[1]);
		$end_time = mktime($res4[0], $res4[1], "0", $res3[1], $res3[2], $res3[0], -1);

		$end_time = $centreonGMT->getUTCDate($end_time);
		
		$duration = $end_time - $start_time;

		$timestamp = time();
		write_command(" SCHEDULE_SVC_DOWNTIME;".getMyHostName($host).";".getMyServiceName($service).";".$start_time.";".$end_time.";".$persistant.";0;".$duration.";".$oreon->user->get_alias().";".$comment."\n", GetMyHostPoller($pearDB, getMyHostName($host)));
	}
?>