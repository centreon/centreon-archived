<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");

$path = _CENTREON_PATH_."/www";
require_once("$path/class/centreon.class.php");
require_once("$path/class/centreonSession.class.php");
require_once("$path/class/centreonDB.class.php");

$DB = new CentreonDB();

CentreonSession::start();
if (!CentreonSession::checkSession(session_id(), $DB)) {
    print "Bad Session ID";
    exit();
} else {

	$centreon = $_SESSION['centreon'];

	if (isset($_POST["limit"]) && isset($_POST["url"])) {
		$centreon->historyLimit[$_POST["url"]] = $_POST["limit"];
	}

	if (isset($_POST["page"]) && isset($_POST["url"])) {
		$centreon->historyPage[$_POST["url"]] = $_POST["page"];
	}

	if (isset($_POST["search"]) && isset($_POST["url"])) {
		$centreon->historySearchService[$_POST["url"]] = addslashes($_POST["search"]);
	}

	if (isset($_POST["search_host"]) && isset($_POST["url"])) {
		$centreon->historySearch[$_POST["url"]] = addslashes($_POST["search_host"]);
	}

	if (isset($_POST["search_output"]) && isset($_POST["url"])) {
		$centreon->historySearchOutput[$_POST["url"]] = addslashes($_POST["search_output"]);
	}
}
