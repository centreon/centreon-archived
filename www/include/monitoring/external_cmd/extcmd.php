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

	/*
	 * Write command in nagios pipe or in centcore pipe.
	 */

	function write_command($cmd, $poller){
		global $centreon, $key, $pearDB;

		$str = NULL;

		/*
		 * Destination is centcore pipe path
		 */
		$destination = "@CENTREON_VARLIB@/centcore.cmd";
		if ($destination == "/centcore.cmd")
			$destination = "/var/lib/centreon/centcore.cmd";

		$cmd = str_replace("`", "&#96;", $cmd);
		//$cmd = str_replace("'", "&#39;", $cmd);

		$cmd = str_replace("\n", "<br>", $cmd);
		$informations = preg_split("/\;/", $key);

		if (!mb_detect_encoding($cmd, 'UTF-8', true)) {
			$cmd = utf8_encode($cmd);
		}
		setlocale(LC_CTYPE, 'en_US.UTF-8');

        $str = "echo ". escapeshellarg("EXTERNALCMD:$poller:[" . time() . "]" . $cmd . "\n") . " >> " . $destination;
		return passthru($str);
	}

	function send_cmd($cmd, $poller = NULL){
		if (isset($cmd))
			$flg = write_command($cmd, $poller);
		isset($flg) && $flg ? $ret = $flg : $ret = _("Command execution problem");
		return $ret;
	}

?>
