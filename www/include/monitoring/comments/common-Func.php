<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	if (!isset($oreon))
		exit();

	function DeleteComment($type, $hosts = array()){
		global $oreon, $_GET, $pearDB;

		foreach ($hosts as $key => $value)	{
			$res = preg_split("/\;/", $key);
			write_command(" DEL_".$type."_COMMENT;".$res[1], GetMyHostPoller($pearDB, $res[0]));
		}
	}

	function AddHostComment($host, $comment, $persistant){
		global $oreon, $pearDB;

		if (!isset($persistant))
			$persistant = 0;
		write_command(" ADD_HOST_COMMENT;".getMyHostName($host).";".$persistant.";".$oreon->user->get_alias().";".trim($comment), GetMyHostPoller($pearDB, getMyHostName($host)));
	}

	function AddSvcComment($host, $service, $comment, $persistant){
		global $oreon, $pearDB;

		if (!isset($persistant))
			$persistant = 0;
		write_command(" ADD_SVC_COMMENT;".getMyHostName($host).";".getMyServiceName($service).";".$persistant.";".$oreon->user->get_alias().";".trim($comment), GetMyHostPoller($pearDB, getMyHostName($host)));
	}
?>