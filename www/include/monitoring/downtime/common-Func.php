<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
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