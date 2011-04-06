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

	/*
	 * Init Centcore Pipe
	 */
	$centcore_pipe = "@CENTREON_VARLIB@/centcore.cmd";
	if ($centcore_pipe == "/centcore.cmd") {
		$centcore_pipe = "/var/lib/centreon/centcore.cmd";
	}

	/*
	 *  Get Poller List
	 */
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `localhost` DESC");
	$n = $DBRESULT->numRows();

	/*
	 * Display null option
	 */
	if ($n > 1) {
		$tab_nagios_server = array(-1 => "");
	}

	/*
	 * Display all servers list
	 */
	for ($i = 0; $nagios = $DBRESULT->fetchRow(); $i++) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	}
	$DBRESULT->free();


	/*
	 * Display all server options
	 */
	if ($n > 1) {
		$tab_nagios_server[0] = _("All Nagios Servers");
	}

	/*
	 * Form begin
	 */
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	/*
	 * Init Header for tables in template
	 */
	$form->addElement('header', 'title', 	_("SNMP Trap Generation"));
	$form->addElement('header', 'opt', 		_("Export Options"));
	$form->addElement('header', 'result', 	_("Actions"));
    $form->addElement('header', 'infos', 	_("Implied Server"));

	$form->addElement('select', 'host', 	_("Nagios Server"), $tab_nagios_server, $attrSelect);

	/*
	 * Add checkbox for enable restart
	 */
	$form->addElement('checkbox', 'restart', _("Generate configuration files for SNMP Traps"));

	/*
	 * Set checkbox checked.
	 */
	$form->setDefaults(array('restart' => '1', 'opt' => '1'));

	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub = $form->addElement('submit', 'submit', _("Generate"));
	$msg = NULL;
	$stdout = NULL;
	if ($form->validate())	{
		$ret = $form->getSubmitValues();
        $host_list = array();
		foreach ($tab_nagios_server as $key => $value) {
			if ($key && ($res["host"] == 0 || $res["host"] == $key)) {
				$host_list[$key] = $value;
			}
		}
		if (isset($ret["restart"]["restart"]) && $ret["restart"]["restart"] && ($ret["host"] == 0 || $ret["host"]))	{
			/*
			 * Create Server List to snmptt generation file
			 */
			$tab_server = array();
			$DBRESULT_Servers = $pearDB->query("SELECT `name`, `id`, `localhost` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `localhost` DESC");
			while ($tab = $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
					$tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
				}
			}
			$stdout = "";
            if (!isset($msg_restart)) {
                $msg_restart = array();
            }
			/* even if we generate files for a remote server, we push snmptt config files on the local server */
            shell_exec("$centreon_path/bin/centGenSnmpttConfFile 2>&1");

            foreach ($tab_server as $host) {
                if (!isset($msg_restart[$host["id"]])) {
                    $msg_restart[$host["id"]] = "";
                }
                if (isset($host['localhost']) && $host['localhost'] == 0) {
                    system("echo 'SYNCTRAP:".$host["id"]."' >> $centcore_pipe");
                    $msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A Synctrap signal has been sent to ".$host["name"]."\n");
                } else {
                    $stdout = shell_exec("$centreon_path/bin/centGenSnmpttConfFile 2>&1");
                    $msg_restart[$host["id"]] .= "<br>".str_replace ("\n", "<br>", $stdout)."<br>";
                }
				foreach ($msg_restart as $key => $str) {
					$msg_restart[$key] = str_replace("\n", "<br>", $str);
				}
            }
		}
	}

	$form->addElement('header', 'status', _("Status"));
	if (isset($msg) && $msg) {
		$tpl->assign('msg', $msg);
	}
	if (isset($msg_restart) && $msg_restart) {
		$tpl->assign('msg_restart', $msg_restart);
	}
	if (isset($tab_server) && $tab_server) {
		$tpl->assign('tab_server', $tab_server);
	}
	if (isset($host_list) && $host_list) {
		$tpl->assign('host_list', $host_list);
	}

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formGenerateTraps.ihtml");
?>