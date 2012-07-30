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

 	ini_set("display_errors", "Off");

 	/**
 	 * Include configuration
 	 */
	include_once "@CENTREON_ETC@/centreon.conf.php";

	/**
	 * Include Classes / Methods
	 */
	include_once $centreon_path . "www/class/centreonDB.class.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	/** *****************************************
	 * Connect MySQL DB
	 */
	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");

	/**
	 * Security check
	 */
	(isset($_GET["sid"])) ? $sid = htmlentities($_GET["sid"], ENT_QUOTES, "UTF-8") : $sid = "-1";

	/**
	 * Check Session ID
	 */
	if (isset($sid)){
		$sid = htmlentities($sid, ENT_QUOTES, "UTF-8");
		$res = $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if ($session = $res->fetchRow()) {
			$_POST["sid"] = $sid;
		} else
			get_error('bad session id');
	} else {
			get_error('need session identifiant !');
	}

	/**
	 * save of the XML flow in $flow
	 */
	$csv_flag = 1; //setting the csv_flag variable to change limit in SQL request of getODSXmlLog.php when CSV exporting
	ob_start();
	require_once $centreon_path."www/include/eventLogs/GetXmlLog.php";
	$flow = ob_get_contents();
	ob_end_clean();

	$nom = "EventLog";

	/**
	 * Send Headers
	 */
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$nom.".csv");
	header("Cache-Control: cache, must-revalidate");
    header("Pragma: public");

    /**
     * Read flow
     */
	$xml = new SimpleXMLElement($flow);

	echo _("Begin date")."; "._("End date").";\n";
	echo date('d/m/y (H:i:s)', intval($xml->infos->start)).";".date('d/m/y (H:i:s)', intval($xml->infos->end))."\n";
	echo "\n";

	echo _("Type").";"._("Notification").";"._("Alert").";"._("error")."\n";
	echo ";".$xml->infos->notification.";".$xml->infos->alert.";".$xml->infos->error."\n";
	echo "\n";

	echo _("Host").";"._("Up").";"._("Down").";"._("Unreachable")."\n";
	echo ";".$xml->infos->up.";".$xml->infos->down.";".$xml->infos->unreachable."\n";
	echo "\n";

	echo _("Service").";"._("Ok").";"._("Warning").";"._("Critical").";"._("Unknown")."\n";
	echo ";".$xml->infos->ok.";".$xml->infos->warning.";".$xml->infos->critical.";".$xml->infos->unknown."\n";
	echo "\n";

	echo _("Day").";"._("Time").";"._("Host").";"._("Address").";"._("Service").";"._("Status").";"._("Type").";"._("Retry").";"._("Output").";"._("Contact").";"._("Cmd")."\n";
	foreach ($xml->line as $line) {
		echo $line->date.";".$line->time.";".$line->host_name.";".$line->address.";".$line->service_description.";".$line->status.";".$line->type.";".$line->retry.";".$line->output.";".$line->contact.";".$line->contact_cmd."\n";
	}

?>