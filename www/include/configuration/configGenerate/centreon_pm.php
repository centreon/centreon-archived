<?php
/*
 * Copyright 2005-2011 MERETHIS
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