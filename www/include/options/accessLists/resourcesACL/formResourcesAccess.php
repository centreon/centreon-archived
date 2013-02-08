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
	 * Database retrieve information for LCA
	 */
	if ($o == "c" || $o == "w")	{
		/*
		 * Set base value
		 */
		$DBRESULT = $pearDB->query("SELECT * FROM acl_resources WHERE acl_res_id = '".$acl_id."' LIMIT 1");
		$acl = array_map("myDecode", $DBRESULT->fetchRow());

		/*
		 * Set Poller relations
		 */
		$DBRESULT = $pearDB->query("SELECT poller_id FROM acl_resources_poller_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $pollers_list = $DBRESULT->fetchRow(); $i++) {
			$acl["acl_pollers"][$i] = $pollers_list["poller_id"];
		}
		$DBRESULT->free();

		/*
		 * Set Hosts relations
		 */
		$hostnotexludes = array();
		$DBRESULT = $pearDB->query("SELECT host_host_id FROM acl_resources_host_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $hosts_list = $DBRESULT->fetchRow(); $i++) {
			$acl["acl_hosts"][$i] = $hosts_list["host_host_id"];
			$hostnotexludes[$hosts_list["host_host_id"]] = 1;
		}
		$DBRESULT->free();

		/*
		 * Set Hosts exludes relations
		 */
		$DBRESULT = $pearDB->query("SELECT host_host_id FROM acl_resources_hostex_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $hosts_list = $DBRESULT->fetchRow(); $i++)
			$acl["acl_hostexclude"][$i] = $hosts_list["host_host_id"];
		$DBRESULT->free();

		/*
		 * Set Hosts Groups relations
		 */
		$DBRESULT = $pearDB->query("SELECT hg_hg_id FROM acl_resources_hg_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $hg_list = $DBRESULT->fetchRow(); $i++)
			$acl["acl_hostgroup"][$i] = $hg_list["hg_hg_id"];
		$DBRESULT->free();

		/*
		 * Set Groups relations
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_res_group_relations WHERE acl_res_id = '".$acl_id."'");
		for ($i = 0; $groups = $DBRESULT->fetchRow(); $i++)
			$acl["acl_groups"][$i] = $groups["acl_group_id"];
		$DBRESULT->free();

		/*
		 * Set Service Categories relations
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT sc_id FROM acl_resources_sc_relations WHERE acl_res_id = '".$acl_id."'");
		if ($DBRESULT->numRows())
			for ($i = 0; $sc = $DBRESULT->fetchRow(); $i++)
				$acl["acl_sc"][$i] = $sc["sc_id"];
		$DBRESULT->free();

		/*
		 * Set Host Categories
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT hc_id FROM acl_resources_hc_relations WHERE acl_res_id = '".$acl_id."'");
		if ($DBRESULT->numRows())
			for ($i = 0; $hc = $DBRESULT->fetchRow(); $i++)
				$acl["acl_hc"][$i] = $hc["hc_id"];
		$DBRESULT->free();

		/*
		 * Set Service Groups relations
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT sg_id FROM acl_resources_sg_relations WHERE acl_res_id = '".$acl_id."'");
		if ($DBRESULT->numRows())
			for ($i = 0; $sg = $DBRESULT->fetchRow(); $i++)
				$acl["acl_sg"][$i] = $sg["sg_id"];
		$DBRESULT->free();

		/*
		 * Set Meta Services relations
		 */
		$DBRESULT = $pearDB->query("SELECT DISTINCT meta_id FROM acl_resources_meta_relations WHERE acl_res_id = '".$acl_id."'");
		if ($DBRESULT->numRows())
			for ($i = 0; $ms = $DBRESULT->fetchRow(); $i++)
				$acl["acl_meta"][$i] = $ms["meta_id"];
		$DBRESULT->free();
	}

	$groups = array();
	$DBRESULT = $pearDB->query("SELECT acl_group_id, acl_group_name FROM acl_groups ORDER BY acl_group_name");
	while ($group = $DBRESULT->fetchRow())
		$groups[$group["acl_group_id"]] = $group["acl_group_name"];
	$DBRESULT->free();

	$pollers = array();
	$DBRESULT = $pearDB->query("SELECT id, name FROM nagios_server ORDER BY name");
	while ($poller = $DBRESULT->fetchRow())
		$pollers[$poller["id"]] = $poller["name"];
	$DBRESULT->free();

	$hosts = array();
	$DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host = $DBRESULT->fetchRow())
		$hosts[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();

	$hosttoexcludes = array();
	$DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host = $DBRESULT->fetchRow())
		$hosttoexcludes[$host["host_id"]] = $host["host_name"];
	$DBRESULT->free();

	$hostgroups = array();
	$DBRESULT = $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	while ($hg = $DBRESULT->fetchRow())
		$hostgroups[$hg["hg_id"]] = $hg["hg_name"];
	$DBRESULT->free();

	$service_categories = array();
	$DBRESULT = $pearDB->query("SELECT sc_id, sc_name FROM service_categories ORDER BY sc_name");
	while ($sc = $DBRESULT->fetchRow())
		$service_categories[$sc["sc_id"]] = $sc["sc_name"];
	$DBRESULT->free();

	$host_categories = array();
	$DBRESULT = $pearDB->query("SELECT hc_id, hc_name FROM hostcategories ORDER BY hc_name");
	while ($hc = $DBRESULT->fetchRow())
		$host_categories[$hc["hc_id"]] = $hc["hc_name"];
	$DBRESULT->free();

	$service_groups = array();
	$DBRESULT = $pearDB->query("SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name");
	while ($sg = $DBRESULT->fetchRow())
		$service_groups[$sg["sg_id"]] = $sg["sg_name"];
	$DBRESULT->free();

	$meta_services = array();
	$DBRESULT = $pearDB->query("SELECT meta_id, meta_name FROM meta_service ORDER BY meta_name");
	while ($ms = $DBRESULT->fetchRow())
		$meta_services[$ms["meta_id"]] = $ms["meta_name"];
	$DBRESULT->free();

	/*
	 * Var information to format the element
	 */
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"60");
	$attrsAdvSelect = array("style" => "width: 300px; height: 220px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"80");
	$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'POST', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add an ACL"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify an ACL"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View an ACL"));

	/*
	 * LCA basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('header', 'hostgroups', _("Hosts Groups Shared"));
	$form->addElement('header', 'services', _("Filters"));
	$form->addElement('text',	'acl_res_name', _("Access list name"), $attrsText);
	$form->addElement('text', 	'acl_res_alias', _("Description"), $attrsText2);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'acl_res_activate', null, _("Enabled"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'acl_res_activate', null, _("Disabled"), '0');
	$form->addGroup($tab, 'acl_res_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('acl_res_activate' => '1'));

	/*
	 * All ressources
	 */
	$allHosts[] = HTML_QuickForm::createElement('checkbox', 'all_hosts', '&nbsp;', "", array('id' => 'all_hosts', 'onclick' => 'advancedDisplay(this.id, "hostAdvancedSelect")'));
	$form->addGroup($allHosts, 'all_hosts', _("Include all hosts"), '&nbsp;&nbsp;', '');

	$allHostgroups[] = HTML_QuickForm::createElement('checkbox', 'all_hostgroups', '&nbsp;', "", array('id' => 'all_hostgroups', 'onclick' => 'advancedDisplay(this.id, "hostgroupAdvancedSelect")'));
	$form->addGroup($allHostgroups, 'all_hostgroups', _("Include all hostgroups"), '&nbsp;&nbsp;');

	$allServiceGroups[] = HTML_QuickForm::createElement('checkbox', 'all_servicegroups', '&nbsp;', "", array('id' => 'all_servicegroups', 'onclick' => 'advancedDisplay(this.id, "servicegroupAdvancedSelect")'));
	$form->addGroup($allServiceGroups, 'all_servicegroups', _("Include all servicegroups"), '&nbsp;&nbsp;');

	/*
	 * Contact implied
	 */
	$form->addElement('header', 'contacts_infos', _("People linked to this Access list"));

	$ams1 = $form->addElement('advmultiselect', 'acl_groups', array(_("Linked Groups"), _("Available"), _("Selected")), $groups, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$form->addElement('header', 'Host_infos', _("Shared Resouces"));
	$form->addElement('header', 'help', _("Help"));
	$form->addElement('header', 'HSharedExplain', _("<b><i>Help :</i></b> Select hosts and hostgroups that can be seen by associated users. You also have the possibilty to exclude host(s) from selected hostgroup(s)."));
        $form->addElement('header', 'SSharedExplain', _("<b><i>Help :</i></b> Select services that can be seen by associated users."));
        $form->addElement('header', 'MSSharedExplain', _("<b><i>Help :</i></b> Select meta services that can be seen by associated users."));
        $form->addElement('header', 'FilterExplain', _("<b><i>Help :</i></b> Select the filter(s) you want to apply to the resource definition for a more restrictive view."));

	/*
	 * Pollers
	 */
	$ams0 = $form->addElement('advmultiselect', 'acl_pollers', array(_("Poller Filter"), _("Available"), _("Selected")), $pollers, $attrsAdvSelect, SORT_ASC);
	$ams0->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams0->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams0->setElementTemplate($eTemplate);
	echo $ams0->getElementJs(false);

	/*
	 * Hosts
	 */
	$attrsAdvSelect['id'] = 'hostAdvancedSelect';
	$ams2 = $form->addElement('advmultiselect', 'acl_hosts', array(_("Hosts"), _("Available"), _("Selected")), $hosts, $attrsAdvSelect, SORT_ASC);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams2->setElementTemplate($eTemplate);
	echo $ams2->getElementJs(false);

	/*
	 * Host Groups
	 */
	$attrsAdvSelect['id'] = 'hostgroupAdvancedSelect';
	$ams2 = $form->addElement('advmultiselect', 'acl_hostgroup', array(_("Host Groups"), _("Available"), _("Selected")), $hostgroups, $attrsAdvSelect, SORT_ASC);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams2->setElementTemplate($eTemplate);
	echo $ams2->getElementJs(false);

	unset($attrsAdvSelect['id']);

	$ams2 = $form->addElement('advmultiselect', 'acl_hostexclude', array(_("Exclude hosts from selected host groups"), _("Available"), _("Selected")), $hosttoexcludes, $attrsAdvSelect, SORT_ASC);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams2->setElementTemplate($eTemplate);
	echo $ams2->getElementJs(false);

	/*
	 * Service Filters
	 */
	$ams2 = $form->addElement('advmultiselect', 'acl_sc', array(_("Service Category Filter"), _("Available"), _("Selected")), $service_categories, $attrsAdvSelect, SORT_ASC);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams2->setElementTemplate($eTemplate);
	echo $ams2->getElementJs(false);

	/*
	 * Host Filters
	 */
	$ams2 = $form->addElement('advmultiselect', 'acl_hc', array(_("Host Category Filter"), _("Available"), _("Selected")), $host_categories, $attrsAdvSelect, SORT_ASC);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams2->setElementTemplate($eTemplate);
	echo $ams2->getElementJs(false);


	/*
	 * Service Groups Add
	 */
	$attrsAdvSelect['id'] = 'servicegroupAdvancedSelect';
	$ams2 = $form->addElement('advmultiselect', 'acl_sg', array(_("Service Groups"), _("Available"), _("Selected")), $service_groups, $attrsAdvSelect, SORT_ASC);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams2->setElementTemplate($eTemplate);
	echo $ams2->getElementJs(false);
    unset($attrsAdvSelect['id']);

	/*
	 * Meta Services
	 */
	$ams2 = $form->addElement('advmultiselect', 'acl_meta', array(_("Meta Services"), _("Available"), _("Selected")), $meta_services, $attrsAdvSelect, SORT_ASC);
	$ams2->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams2->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams2->setElementTemplate($eTemplate);
	echo $ams2->getElementJs(false);

	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$form->addElement('textarea', 'acl_res_comment', _("Comments"), $attrsTextarea);


	$form->addElement('hidden', 'acl_res_id');

	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('acl_res_name', _("Required"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	if ($o == "a")
		$form->addRule('acl_res_name', _("Already exists"), 'exist');
	$form->setRequiredNote(_("Required field"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	if ($o == "w") {
		/*
		 * Just watch a LCA information
		 */
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&acl_id=".$acl_id."'"));
	    $form->setDefaults($acl);
		$form->freeze();
	} else if ($o == "c"){
		/*
		 * Modify a LCA information
		 */
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Delete"));
	    $form->setDefaults($acl);
	} else if ($o == "a") {
		/*
		 *  Add a LCA information
		 */
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Delete"));
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&lca_id=".$acl_id, "changeT"=>_("Modify")));
        
        // prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);
        
	$valid = false;
	if ($form->validate())	{
		$aclObj = $form->getElement('acl_res_id');
		if ($form->getSubmitValue("submitA"))
			$aclObj->setValue(insertLCAInDB());
		else if ($form->getSubmitValue("submitC"))
			updateLCAInDB($aclObj->getValue());
		require_once("listsResourcesAccess.php");
	} else {
		$action = $form->getSubmitValue("action");
		if ($valid && $action["action"]["action"])
			require_once("listsResourcesAccess.php");
		else	{
			/*
			 * Apply a template definition
			 */
			$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
			$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
			$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
			$form->accept($renderer);
			$tpl->assign('form', $renderer->toArray());
			$tpl->assign('o', $o);
			$tpl->assign("sort1", _("General Information"));
			$tpl->assign("sort2", _("Hosts Resources"));
			$tpl->assign("sort3", _("Services Resources"));
			$tpl->assign("sort4", _("Meta Services"));
			$tpl->assign("sort5", _("Filters"));
			$tpl->display("formResourcesAccess.ihtml");
		}
	}
?>
<script type='text/javascript'>
function hideAdvancedSelect(advId)
{
	$$("#"+advId).each(function(e) {
		e.up('table').setAttribute('style', 'display: none');
	});
}

function showAdvancedSelect(advId)
{
	$$("#"+advId).each(function(e) {
		e.up('table').setAttribute('style', 'display: visible');
	});
}

function advancedDisplay(checkboxId, advSelectId)
{
	$$("#"+checkboxId).each(function(e) {
		if (e.checked) {
			hideAdvancedSelect(advSelectId);
		} else {
			showAdvancedSelect(advSelectId);
		}
	});
}

advancedDisplay('all_hosts', 'hostAdvancedSelect');
advancedDisplay('all_hostgroups', 'hostgroupAdvancedSelect');
advancedDisplay('all_servicegroups', 'servicegroupAdvancedSelect');

</script>
