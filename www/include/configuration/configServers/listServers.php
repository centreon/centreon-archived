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

	if (!isset($centreon)) {
		exit();
	}

	/*
	 * Connect to NDO database.
	 */
	if ($centreon->broker->getBroker() == "ndo") {
		$pearDBNdo = new CentreonDB("ndo");
	}

	include("./include/common/autoNumLimit.php");

	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

	/*
	 * start quickSearch form
	 */
	include_once("./include/common/quickSearch.php");

	$LCASearch = "";
	if (isset($search)) {
	  $LCASearch = " WHERE name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%'";
	}

	/*
	 * nagios servers comes from DB
	 */
	$nagios_servers = array();
	$nagios_restart = array();
	$DBRESULT = $pearDB->query("SELECT id, name, last_restart FROM `nagios_server` ORDER BY name");
	while ($nagios_server = $DBRESULT->fetchRow()) {
		$nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
		$nagios_restart[$nagios_server["id"]] = $nagios_server["last_restart"];
	}
	$DBRESULT->free();

	/*
	 * Get information info RTM
	 */
	if ($centreon->broker->getBroker() == "broker") {
		$nagiosInfo = array();
		$DBRESULT = $pearDBO->query("SELECT start_time AS program_start_time, running AS is_currently_running, pid AS process_id, instance_id, name AS instance_name , last_alive FROM instances");
		while ($info = $DBRESULT->fetchRow()) {
			$nagiosInfo[$info["instance_name"]] = $info;
		}
		$DBRESULT->free();
	} else {
		$ndoPrefix = getNDOPrefix();
		$nagiosInfo = array();
		$DBRESULT = $pearDBNdo->query("SELECT UNIX_TIMESTAMP(program_start_time) as program_start_time, is_currently_running, process_id, p.instance_id, instance_name, UNIX_TIMESTAMP(status_update_time) AS last_alive FROM `".$ndoPrefix."programstatus` p, ".$ndoPrefix."instances i WHERE p.instance_id = i.instance_id");
		while ($info = $DBRESULT->fetchRow()) {
			$nagiosInfo[$info["instance_name"]] = $info;
		}
		$DBRESULT->free();
	}

	/*
	 * Get Nagios / Icinga / Shinken / Scheduler version
	 */
	$pollerNumber = count($nagios_servers);
	if ($pollerNumber == 0) {
		$pollerNumber = 1;
	}
	if ($centreon->broker->getBroker() == "broker") {
		$DBRESULT = $pearDBO->query("SELECT DISTINCT instance_id, version AS program_version, engine AS program_name, name AS instance_name FROM instances LIMIT $pollerNumber");
		while ($info = $DBRESULT->fetchRow()) {
			$nagiosInfo[$info["instance_name"]]["version"] = $info["program_name"] . " " . $info["program_version"];
		}
		$DBRESULT->free();
	} else {
		$DBRESULT = $pearDBNdo->query("SELECT DISTINCT p.instance_id, program_version, program_name, instance_name FROM `".$ndoPrefix."processevents` p, ".$ndoPrefix."instances i WHERE p.instance_id = i.instance_id ORDER BY processevent_id DESC LIMIT $pollerNumber");
		while ($info = $DBRESULT->fetchRow()) {
			$nagiosInfo[$info["instance_name"]]["version"] = $info["program_name"] . " " . $info["program_version"];
		}
		$DBRESULT->free();
	}

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/* Access level */
	($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
	$tpl->assign('mode_access', $lvl_access);

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_ip_address", _("IP Address"));
	$tpl->assign("headerMenu_localisation", _("Localhost"));
	$tpl->assign("headerMenu_is_running", _("Is running ?"));
	$tpl->assign("headerMenu_hasChanged", _("Conf Changed"));
	$tpl->assign("headerMenu_pid", _("PID"));
	$tpl->assign("headerMenu_version", _("Version"));
	$tpl->assign("headerMenu_startTime", _("Start time"));
	$tpl->assign("headerMenu_lastUpdateTime", _("Last Update"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_default", _("Default"));
	$tpl->assign("headerMenu_options", _("Options"));

	/*
	 * Nagios list
	 */
	$rq = "SELECT SQL_CALC_FOUND_ROWS id, name, ns_activate, ns_ip_address, localhost, is_default FROM `nagios_server` $LCASearch ORDER BY name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT = $pearDB->query($rq);

	$rows = $DBRESULT->numRows();

	include("./include/common/checkPagination.php");

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

	/*
	 * Different style between each lines
	 */
	$style = "one";

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $config = $DBRESULT->fetchRow(); $i++) {
		$moptions = "";
		$selectedElements = $form->addElement('checkbox', "select[".$config['id']."]");
		if ($config["ns_activate"]) {
			$moptions .= "<a href='main.php?p=".$p."&server_id=".$config['id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		} else {
			$moptions .= "<a href='main.php?p=".$p."&server_id=".$config['id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		}
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$config['id']."]'></input>";
		
		if (!isset($nagiosInfo[$config["name"]]["is_currently_running"])) {
		  $nagiosInfo[$config["name"]]["is_currently_running"] = 0;
		}

		/* 
		 * Manage flag for changes
		 */
		$hasChanged = checkChangeState($config['id'], $nagios_restart[$config['id']]);

		/*
                 * Manage flag for update time
                 */
		$lastUpdateTimeFlag = 0;
		if (!isset($nagiosInfo[$config["name"]]["last_alive"])) {
		  $lastUpdateTimeFlag = 0;
		} else if (time() - $nagiosInfo[$config["name"]]["last_alive"] > 10 * 60) {
		  $lastUpdateTimeFlag = 1;
		}

		$elemArr[$i] = array(
				     "MenuClass" => "list_".$style,
				     "RowMenu_select" => $selectedElements->toHtml(),
				     "RowMenu_name" => $config["name"],
				     "RowMenu_ip_address" => $config["ns_ip_address"],
				     "RowMenu_link" => "?p=".$p."&o=c&server_id=".$config['id'],
				     "RowMenu_localisation" => $config["localhost"] ? _("Yes") : "-",
				     "RowMenu_is_running" => (isset($nagiosInfo[$config["name"]]["is_currently_running"]) && $nagiosInfo[$config["name"]]["is_currently_running"] == 1) ? _("Yes") : _("No"),
				     "RowMenu_is_runningFlag" => $nagiosInfo[$config["name"]]["is_currently_running"],
				     "RowMenu_is_default" => $config["is_default"] ? _("Yes") : _("No"),
				     "RowMenu_hasChanged" => $hasChanged ? _("Yes") : _("No"),
				     "RowMenu_hasChangedFlag" => $hasChanged,
				     "RowMenu_version" => (isset($nagiosInfo[$config["name"]]["version"]) ? $nagiosInfo[$config["name"]]["version"] : _("N/A")),
				     "RowMenu_startTime" => (isset($nagiosInfo[$config["name"]]["is_currently_running"]) && $nagiosInfo[$config["name"]]["is_currently_running"] == 1) ? $centreonGMT->getDate(_("d/m/Y H:i:s"), $nagiosInfo[$config["name"]]["program_start_time"]) : "-",
				     "RowMenu_lastUpdateTime" => (isset($nagiosInfo[$config["name"]]["last_alive"]) && $nagiosInfo[$config["name"]]["last_alive"]) ? $centreonGMT->getDate(_("d/m/Y H:i:s"), $nagiosInfo[$config["name"]]["last_alive"]) : "-",
				     "RowMenu_lastUpdateTimeFlag" => $lastUpdateTimeFlag, 
				     "RowMenu_pid" => (isset($nagiosInfo[$config["name"]]["is_currently_running"]) && $nagiosInfo[$config["name"]]["is_currently_running"] == 1) ? $nagiosInfo[$config["name"]]["process_id"] : "-",
				     "RowMenu_status" => $config["ns_activate"] ? _("Enabled") : _("Disabled"),
				     "RowMenu_statusVal" => $config["ns_activate"],
				     "RowMenu_options" => $moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	/*
	 * Toolbar select
	 */
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
    $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
	$form->setDefaults(array('o1' => NULL));
	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);

	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
	$form->setDefaults(array('o2' => NULL));

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);

	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listServers.ihtml");
?>