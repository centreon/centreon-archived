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
	
	if (!isLocalInstance($tab['id']))
		$file = $oreon->optGen["nagios_path_plugins"]."centreon.conf" ;
	else
		$file = $nagiosCFGPath.$tab['id']."/centreon.conf" ;
	
	if (!file_exists($file)) {
	
		$handle = fopen($file, 'w');
		$ini = readINIfile ($file, ";");
		// We modify [GLOBAL] section
		$ini["GLOBAL"]["DIR_OREON"] = $oreon->optGen["oreon_path"];
		$ini["GLOBAL"]["NAGIOS_LIBEXEC"] = $oreon->optGen["nagios_path_plugins"];
		$ini["GLOBAL"]["NAGIOS_ETC"] = $oreon->Nagioscfg["cfg_dir"];
		
		# other section
		$ini["NT"]["CPU"] = ".1.3.6.1.2.1.25.3.3.1.2";
		$ini["NT"]["HD_USED"] = ".1.3.6.1.2.1.25.2.3.1.6";
		$ini["NT"]["HD_NAME"] = ".1.3.6.1.2.1.25.2.3.1.3";
		
		$ini["CISCO"]["NB_CONNECT"] = ".1.3.6.1.4.1.9.9.147.1.2.2.2.1.5.40.6";
		
		$ini["UNIX"]["CPU_USER"] = ".1.3.6.1.4.1.2021.11.50.0";
		$ini["UNIX"]["CPU_SYSTEM"] = ".1.3.6.1.4.1.2021.11.52.0";
		$ini["UNIX"]["CPU_LOAD_1M"] = ".1.3.6.1.4.1.2021.10.1.3.1";
		$ini["UNIX"]["CPU_LOAD_5M"] = ".1.3.6.1.4.1.2021.10.1.3.2";
		$ini["UNIX"]["CPU_LOAD_15M"] = ".1.3.6.1.4.1.2021.10.1.3.3";
		
		$ini["DELL"]["TEMP"] = ".1.3.6.1.4.1.674.10892.1.700.20.1.6.1";
		
		$ini["ALTEON"]["VIRT"] = "1.3.6.1.4.1.1872.2.1.8.2.7.1.3.1";
		$ini["ALTEON"]["FRONT"] = "1.3.6.1.4.1.1872.2.1.8.2.5.1.3.1";
		
		$ini["MIB2"]["SW_RUNNAME"] = ".1.3.6.1.2.1.25.4.2.1.2";
		$ini["MIB2"]["SW_RUNINDEX"] = ".1.3.6.1.2.1.25.4.2.1.1";
		$ini["MIB2"]["SW_RUNSTATUS"] = ".1.3.6.1.2.1.25.4.2.1.7";
		$ini["MIB2"]["HR_STORAGE_DESCR"] = ".1.3.6.1.2.1.25.2.3.1.3";
		$ini["MIB2"]["HR_STORAGE_ALLOCATION_UNITS"] = ".1.3.6.1.2.1.25.2.3.1.4";
		$ini["MIB2"]["HR_STORAGE_SIZE"] = ".1.3.6.1.2.1.25.2.3.1.5";
		$ini["MIB2"]["HR_STORAGE_USED"] = ".1.3.6.1.2.1.25.2.3.1.6";
		$ini["MIB2"]["OBJECTID"] = ".1.3.6.1.2.1.1.1.0";
		$ini["MIB2"]["UPTIME_WINDOWS"] = ".1.3.6.1.2.1.1.3.0";
		$ini["MIB2"]["UPTIME_OTHER"] = ".1.3.6.1.2.1.25.1.1.0";
		$ini["MIB2"]["IF_IN_OCTET"] = ".1.3.6.1.2.1.2.2.1.10";
		$ini["MIB2"]["IF_OUT_OCTET"] = ".1.3.6.1.2.1.2.2.1.16";
		$ini["MIB2"]["IF_SPEED"] = ".1.3.6.1.2.1.2.2.1.5";
		$ini["MIB2"]["IF_DESC"] = ".1.3.6.1.2.1.2.2.1.2";
		$ini["MIB2"]["IF_OPERSTATUS"] = ".1.3.6.1.2.1.2.2.1.8";
		
		// We write conf file
		writeINIfile($file,$ini, "", "");
		fclose($handle);
	}
?>