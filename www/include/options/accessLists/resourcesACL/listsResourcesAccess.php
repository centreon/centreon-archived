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

	if (!isset($oreon)) {
		exit();
	}

	include("./include/common/autoNumLimit.php");

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");

	$SearchStr = "";
	if (isset($search)) {
		$SearchStr = " WHERE (acl_res_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR acl_res_alias LIKE '".htmlentities($search, ENT_QUOTES, "UTF-8")."')";
	}
	$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM acl_resources" . $SearchStr);

	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_alias", _("Description"));
	$tpl->assign("headerMenu_contacts", _("Contacts"));
	$tpl->assign("headerMenu_allH", _("All Hosts"));
	$tpl->assign("headerMenu_allHG", _("All Hostgroups"));
	$tpl->assign("headerMenu_allSG", _("All Servicegroups"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));

	$SearchStr = "";
	if ($search) {
		$SearchStr = "WHERE (acl_res_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR acl_res_alias LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%')";
	}
	$rq = "SELECT acl_res_id, acl_res_name, acl_res_alias, all_hosts, all_hostgroups, all_servicegroups, acl_res_activate FROM acl_resources ". $SearchStr ." ORDER BY acl_res_name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT = $pearDB->query($rq);

	$search = tidySearchKey($search, $advanced_search);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

	/*
	 * Different style between each lines
	 */
	$style = "one";

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $resources = $DBRESULT->fetchRow(); $i++) {
		$selectedElements = $form->addElement('checkbox', "select[".$resources['acl_res_id']."]");

		if ($resources["acl_res_activate"]) {
			$moptions = "<a href='main.php?p=".$p."&acl_res_id=".$resources['acl_res_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		} else {
			$moptions = "<a href='main.php?p=".$p."&acl_res_id=".$resources['acl_res_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		}
		$moptions .= "&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$resources['acl_res_id']."]'></input>";

		/* Contacts */
		$ctNbr = array();
		$rq = "SELECT COUNT(*) AS nbr FROM acl_resources_host_relations WHERE acl_res_id = '".$resources['acl_res_id']."'";
		$DBRESULT2 = $pearDB->query($rq);
		$ctNbr = $DBRESULT2->fetchRow();
		$elemArr[$i] = array("MenuClass" => "list_".$style,
						"RowMenu_select" => $selectedElements->toHtml(),
						"RowMenu_name" => $resources["acl_res_name"],
						"RowMenu_alias" => myDecode($resources["acl_res_alias"]),
						"RowMenu_all_hosts" => (isset($resources["all_hosts"]) && $resources["all_hosts"] == 1 ? _("Yes") : _("No")),
						"RowMenu_all_hostgroups" => (isset($resources["all_hostgroups"]) && $resources["all_hostgroups"] == 1 ? _("Yes") : _("No")),
						"RowMenu_all_servicegroups" => (isset($resources["all_servicegroups"]) && $resources["all_servicegroups"] == 1 ? _("Yes") : _("No")),
						"RowMenu_link" => "?p=".$p."&o=c&acl_res_id=".$resources['acl_res_id'],
						"RowMenu_status"  =>  $resources["acl_res_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_options"  =>  $moptions);

		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT" => _("Add"), "testT" => _("Check User View"), "testL"=>"?p=".$p."&o=t&min=1", "delConfirm"=>_("Do you confirm the deletion ?")));

	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);

	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listsResourcesAccess.ihtml");
?>