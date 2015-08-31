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

	include_once $centreon_path."www/class/centreonGMT.class.php";
	include_once $centreon_path."www/class/centreonDB.class.php";

	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo = new CentreonDB("ndo");
	}

	/*
	 * Init GMT class
	 */
	$hostStr = $oreon->user->access->getHostsString("ID", ($oreon->broker->getBroker() == "ndo" ? $pearDBndo : $pearDBO));
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