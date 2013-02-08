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
	$DBRESULT = $pearDB->query("SELECT contact_id, contact_alias FROM contact WHERE contact_admin = '0' ORDER BY contact_name");
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