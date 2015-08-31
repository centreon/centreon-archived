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

	/*
	 * Connect to Database
	 */
	$broker = $oreon->broker->getBroker();
	if ($broker == "broker") {
	    $pearDBNdo = new CentreonDB("centstorage");
	} elseif ($broker == "ndo") {
        $pearDBNdo = new CentreonDB("ndo");
	}

	/**
	 * Get host icones
	 */
	$ehiCache = array();
	$DBRESULT = $pearDB->query("SELECT ehi_icon_image, host_host_id FROM extended_host_information");
	while ($ehi = $DBRESULT->fetchRow()) {
		$ehiCache[$ehi["host_host_id"]] = $ehi["ehi_icon_image"];
	}
	$DBRESULT->free();

	/**
	 * Get user list
	 */
	$contact = array("" => null);
	$DBRESULT = $pearDB->query("SELECT contact_id, contact_alias FROM contact WHERE contact_admin = '0' ORDER BY contact_alias");
	while ($ct = $DBRESULT->fetchRow()) {
		$contact[$ct["contact_id"]] = $ct["contact_alias"];
	}
	$DBRESULT->free();

	/*
	 * Object init
	 */
    $mediaObj 		= new CentreonMedia($pearDB);
    $host_method 	= new CentreonHost($pearDB);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_host", _("Host Name"));
	$tpl->assign("headerMenu_service", _("Service Description"));

	/*
	 * Different style between each lines
	 */
	$style = "one";

	$groups = "''";
	if (isset($_POST["contact"])) {
		$contact_id = (int)htmlentities($_POST["contact"], ENT_QUOTES, "UTF-8");
		$access = new CentreonACL($contact_id, 0);
		$groupList = $access->getAccessGroups();
		if (isset($groupList) && count($groupList)) {
			foreach ($groupList as $key => $value) {
				if ($groups != "") {
					$groups .= ",";
				}
				$groups .= "'".$key."'";
			}
		}
	} else {
		$contact_id = 0;
		$formData = array('contact' => $contact_id);
		$groups = "''";
	}

	$formData = array('contact' => $contact_id);

	/*
	 * Create select form
	 */
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$form->addElement('select', 'contact', _("Centreon Users"), $contact, array('id'=>'contact', 'onChange'=>'submit();'));
	$form->setDefaults($formData);

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	$DBRESULT = $pearDBNdo->query("SELECT DISTINCT host_name, service_description, host_id, service_id FROM centreon_acl WHERE group_id IN ($groups) ORDER BY host_name, service_description");
	for ($i = 0; $resources = $DBRESULT->fetchRow(); $i++) {

		if ((isset($ehiCache[$resources["host_id"]]) && $ehiCache[$resources["host_id"]])) {
		    $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$resources["host_id"]]);
		} elseif ($icone = $host_method->replaceMacroInString($resources["host_id"], getMyHostExtendedInfoImage($resources["host_id"], "ehi_icon_image", 1))) {
			$host_icone = "./img/media/" . $icone;
		} else {
			$host_icone = "./img/icones/16x16/server_network.gif";
		}
		$moptions = "";
		$elemArr[$i] = array("MenuClass"=>"list_".$style,
						"RowMenu_hico" => $host_icone,
						"RowMenu_host" => myDecode($resources["host_name"]),
						"RowMenu_service" => myDecode($resources["service_description"]),
						);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('msg', _("The selected user didn't see any resources"));
	$tpl->assign('msgSelect', _("Please select an user in order to display resources"));
	$tpl->assign('msgdisable', _("The selected user is not enable."));
	$tpl->assign('p', $p);
	$tpl->assign('i', $i);
	$tpl->assign('contact', $contact_id);
	$tpl->display("showUsersAccess.ihtml");
?>
