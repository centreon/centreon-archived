	/*
	 * Write command in nagios pipe or in centcore pipe. 
	 */
	function write_command($cmd, $poller){
		global $oreon, $key, $pearDB;
		
		$str = NULL;
		/*
		 * Destination is centcore pipe path
		 */
		$cmd = str_replace("`", "&#96;", $cmd);
		$cmd = str_replace("'", "&#39;", $cmd);

		$cmd = str_replace("\n", "<br>", $cmd);
		$informations = split(";", $key);
		if ($poller && isPollerLocalhost($pearDB, $poller)) {
			$str = "[" . time() . "]" . $cmd . "\n";
			$destination = $oreon->Nagioscfg["command_file"];
		} else if (isHostLocalhost($pearDB, $informations[0])) {
			$str = "[" . time() . "]" . $cmd . "\n";
			$destination = $oreon->Nagioscfg["command_file"];
		} else {
			$str = "EXTERNALCMD:$poller:[" . time() . "]" . $cmd . "\n";
			$destination = "@CENTREON_VARLIB@/centcore.cmd";
			if ($destination == "/centcore.cmd")
			    $destination = "/var/lib/centreon/centcore.cmd";
		}
		if(!$cmdfile = fopen($destination, "a"))
		    return _("Cannot open command file");
		if(!fwrite($cmdfile, $str)) {
		    fclose($cmdfile);
		    return _("Cannot write to command file");
		}
		fclose($cmdfile);
		return _("Your command has been sent");
	}


	function send_cmd($cmd, $poller = NULL){
		if (isset($cmd))
			$flg = write_command($cmd, $poller);
		isset($flg) && $flg ? $ret = $flg : $ret = _("Command execution problem");
		return $ret;
	}
