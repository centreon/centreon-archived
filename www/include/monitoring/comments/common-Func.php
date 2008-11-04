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
	
	function DeleteComment($type, $hosts = array()){
		global $oreon, $_GET, $pearDB;
		
		foreach ($hosts as $key => $value)	{
			$res = split(";", $key);
			write_command(" DEL_".$type."_COMMENT;".$res[1]."\n", GetMyHostPoller($pearDB, $res[0]));
		}
	}
		
	function AddHostComment($host, $comment, $persistant){
		global $oreon, $pearDB;

		if (!isset($persistant))
			$persistant = 0;
		write_command(" ADD_HOST_COMMENT;".getMyHostName($host).";".$persistant.";".$oreon->user->get_alias().";".$comment."\n", GetMyHostPoller($pearDB, getMyHostName($host)));
	}

	function AddSvcComment($host, $service, $comment, $persistant){
		global $oreon, $pearDB;
		
		if (!isset($persistant))
			$persistant = 0;
		write_command(" ADD_SVC_COMMENT;".getMyHostName($host).";".getMyServiceName($service).";".$persistant.";".$oreon->user->get_alias().";".$comment."\n", GetMyHostPoller($pearDB, getMyHostName($host)));
	}
?>	