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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/monitoring/external_cmd/cmd.php $
 * SVN : $Id: cmd.php 8142 2009-05-20 21:09:15Z jmathis $
 *
 */

	require_once("@CENTREON_ETC@/centreon.conf.php");
	//require_once("/etc/centreon/centreon.conf.php");

	require_once $centreon_path . "www/class/centreonSession.class.php";
	require_once $centreon_path . "www/class/centreon.class.php";
	require_once $centreon_path . "www/class/centreonDB.class.php";
	require_once $centreon_path . "www/class/centreonGMT.class.php";

	session_start();
	$oreon = $_SESSION['centreon'];

	global $oreon, $pearDB;

	/*
	 * Connect to DB
	 */
	$pearDB = new CentreonDB();

	/*
	 * GMT management
	 */
	$centreonGMT = new CentreonGMT($pearDB);

	require_once $centreon_path . "www/include/common/common-Func.php";
	require_once $centreon_path . "www/include/monitoring/common-Func.php";

	if (!isset($oreon))
		exit();

	include_once $centreon_path . "www/include/monitoring/external_cmd/functionsPopup.php";

	if (isset($_GET["select"]) && isset($_GET["sid"])) {
		$is_admin = isUserAdmin(htmlentities($_GET['sid'], ENT_QUOTES, "UTF-8"));
		foreach ($_GET["select"] as $key => $value){
			if (isset($_GET["cmd"])) {
				switch ($_GET["cmd"]) {
					case 70:	massiveServiceAck($key); break;
					case 72:	massiveHostAck($key); break;
					case 74:	massiveServiceDowntime($key); break;
					case 75:	massiveHostDowntime($key); break;
				}
			}
		}
	}
?>
