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

	if (isLocalInstance($tab['id'])) {
		$file = $oreon->optGen["nagios_path_plugins"]."centreon.conf" ;
	} else {
		$file = $nagiosCFGPath.$tab['id']."/centreon.conf" ;
	}

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
                $ini["MIB2"]["IF_ADMINSTATUS"] = ".1.3.6.1.2.1.2.2.1.7";

        $ini["JUNIPER"]["IF_IN_OCTET"] = ".1.3.6.1.4.1.3224.9.3.1.3";
        $ini["JUNIPER"]["IF_OUT_OCTET"] = ".1.3.6.1.4.1.3224.9.3.1.5";
        $ini["JUNIPER"]["IF_SPEED"] = ".1.3.6.1.2.1.2.2.1.5";
        $ini["JUNIPER"]["IF_OUT_OCTET_64_BITS"] = ".1.3.6.1.2.1.31.1.1.1.10";
        $ini["JUNIPER"]["IF_IN_OCTET_64_BITS"] = ".1.3.6.1.2.1.31.1.1.1.6";
        $ini["JUNIPER"]["IF_SPEED_64_BITS"] = ".1.3.6.1.2.1.31.1.1.1.15";
        $ini["JUNIPER"]["IF_DESC"] = ".1.3.6.1.4.1.3224.9.1.1.22";
        $ini["JUNIPER"]["IF_IN_ERROR"] = ".1.3.6.1.2.1.2.2.1.14";
        $ini["JUNIPER"]["IF_OUT_ERROR"] = ".1.3.6.1.2.1.2.2.1.20";
        $ini["JUNIPER"]["IF_OPERSTATUS"] = ".1.3.6.1.4.1.3224.9.1.1.5";

		// We write conf file
		writeINIfile($file,$ini, "", "");
		fclose($handle);
	}
?>