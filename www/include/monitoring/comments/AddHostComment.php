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

	include_once $centreon_path."www/class/centreonGMT.class.php";
	include_once $centreon_path."www/class/centreonDB.class.php";

	$pearDBndo = new CentreonDB("ndo");

	/*
	 * Init GMT class
	 */
	$hostStr = $oreon->user->access->getHostsString("ID", $pearDBndo);
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);
	if ($oreon->user->access->checkAction("host_comment")) {
		/*
		 * ACL
		 */
		if (isset($_GET["host_name"])){
			$host_id = getMyHostID($_GET["host_name"]);
			$host_name = $_GET["host_name"];
		} else
			$host_name = "";

		$data = array();
		if (isset($host_id))
			$data = array("host_id" => $host_id);


			/*
			 * Database retrieve information for differents elements list we need on the page
			 */
			$hosts = array(""=>"");
			$query = "SELECT host_id, host_name " .
					"FROM `host` " .
					"WHERE host_register = '1' " .
					"AND host_activate = '1'" .
					$oreon->user->access->queryBuilder("AND", "host_id", $hostStr) .
					"ORDER BY host_name";
			$DBRESULT = $pearDB->query($query);
			while ($host = $DBRESULT->fetchRow()){
				$hosts[$host["host_id"]]= $host["host_name"];
			}
			$DBRESULT->free();

			$debug = 0;
			$attrsTextI		= array("size"=>"3");
			$attrsText 		= array("size"=>"30");
			$attrsTextarea 	= array("rows"=>"7", "cols"=>"100");

			/*
			 * Form begin
			 */
			$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
			if ($o == "ah")
				$form->addElement('header', 'title', _("Add a comment for Host"));

			/*
			 * Indicator basic information
			 */
			$redirect = $form->addElement('hidden', 'o');
			$redirect->setValue($o);

		    $selHost = $form->addElement('select', 'host_id', _("Host Name"), $hosts);
			$form->addElement('checkbox', 'persistant', _("Persistent"));
			$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);

			$form->addRule('host_id', _("Required Field"), 'required');
			$form->addRule('comment', _("Required Field"), 'required');

			$subA = $form->addElement('submit', 'submitA', _("Save"));
			$res = $form->addElement('reset', 'reset', _("Reset"));

			$form->setDefaults($data);

		  	$valid = false;
			if ((isset($_POST["submitA"]) && $_POST["submitA"]) && $form->validate())	{
				if (!isset($_POST["persistant"]))
					$_POST["persistant"] = 0;
				if (!isset($_POST["comment"]))
					$_POST["comment"] = 0;
				AddHostComment($_POST["host_id"], $_POST["comment"], $_POST["persistant"]);
				$valid = true;
		    	require_once($path."viewHostComment.php");
		    } else {
				/*
				 * Smarty template Init
				 */
				$tpl = new Smarty();
				$tpl = initSmartyTpl($path, $tpl, "template/");

				/*
				 * Apply a template definition
				 */
				$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
				$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
				$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
				$form->accept($renderer);
				$tpl->assign('form', $renderer->toArray());
				$tpl->assign('o', $o);
				$tpl->display("AddHostComment.ihtml");
		    }
		}
?>