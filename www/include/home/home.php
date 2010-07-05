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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

/*
 * This script drawing pie charts with hosts and services statistics on home page.
 *
 * PHP version 5
 *
 * @package home.php
 * @author Damien Duponchelle
 * @version $Id$
 * @copyright (c) 2007-2008 Centreon
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

	// Variables $oreon must exist. it contains all personnals datas (Id, Name etc.) using by user to navigate on the interface.
	if (!isset($oreon)) {
		exit();
	}

	// Including files and dependences
	include_once "./include/monitoring/common-Func.php";
	include_once "./class/centreonDB.class.php";

	$pearDBndo = new CentreonDbPdo("ndo");

	if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
		print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
	} else {

		// The user must install the ndo table with the 'centreon_acl'
		if ($err_msg = table_not_exists("centreon_acl")) {
			print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
		}

		// Directory of Home pages
		$path = "./include/home/";

		// Displaying a Smarty Template
		$template = new Smarty();
		$template = initSmartyTpl($path, $template, "./");
		$template->assign("session", session_id());
		$template->assign("host_label", _("Hosts"));
		$template->assign("svc_label", _("Services"));
		$template->display("home.ihtml");
	}
?>