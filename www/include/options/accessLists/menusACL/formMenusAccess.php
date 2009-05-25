<?php
/*
 * Copyright 2005-2009 MERETHIS
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
		$DBRESULT =& $pearDB->query("SELECT * FROM acl_topology WHERE acl_topo_id = '".$acl_id."' LIMIT 1");
		
		# Set base value
		$acl = array_map("myDecode", $DBRESULT->fetchRow());
		
		# Set Topology relations
		$DBRESULT =& $pearDB->query("SELECT topology_topology_id FROM acl_topology_relations WHERE acl_topo_id = '".$acl_id."'");
		for ($i = 0; $topo =& $DBRESULT->fetchRow(); $i++)
			$acl["acl_topos"][$topo["topology_topology_id"]] = 1;
		$DBRESULT->free();
		
		# Set Contact Groups relations
		$DBRESULT =& $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_group_topology_relations WHERE acl_topology_id = '".$acl_id."'");
		for($i = 0; $groups =& $DBRESULT->fetchRow(); $i++)
			$acl["acl_groups"][$i] = $groups["acl_group_id"];
		$DBRESULT->free();
	}

	$groups = array();
	$DBRESULT =& $pearDB->query("SELECT acl_group_id, acl_group_name FROM acl_groups ORDER BY acl_group_name");	
	while ($group =& $DBRESULT->fetchRow())
		$groups[$group["acl_group_id"]] = $group["acl_group_name"];
	$DBRESULT->free();

	if (!isset($acl["acl_topos"]))
		$acl["acl_topos"] = array();

	/*
	 * Var information to format the element
	 */
	
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
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
	$form->addElement('text',	'acl_topo_name', _("ACL Definition"), $attrsText);
	$form->addElement('text', 	'acl_topo_alias', _("Alias"), $attrsText);

    $ams1 =& $form->addElement('advmultiselect', 'acl_groups', _("Linked Groups"), $groups, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'acl_topo_activate', null, _("Enabled"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'acl_topo_activate', null, _("Disabled"), '0');
	$form->addGroup($tab, 'acl_topo_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('acl_topo_activate' => '1'));

	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$form->addElement('textarea', 'lca_comment', _("Comments"), $attrsTextarea);
	
	/*
	 * Create buffer group list for Foorth level.
	 */
	
	$DBRESULT =& $pearDB->query("SELECT topology_group, topology_name, topology_parent FROM `topology` WHERE topology_page IS NULL ORDER BY topology_group, topology_page");
	while ($group =& $DBRESULT->fetchRow()) {
		if (!isset($groups[$group["topology_group"]]))
			$groups[$group["topology_group"]] = array();
		$groups[$group["topology_group"]][$group["topology_parent"]] = $group["topology_name"];
	}
	$DBRESULT->free();
	unset($group);
	
	/*
	 * Topology concerned
	 */
	$form->addElement('header', 'pages', _("Accessible Pages"));
	$DBRESULT1 =& $pearDB->query("SELECT topology_id, topology_page, topology_name, topology_parent FROM topology WHERE topology_parent IS NULL AND topology_page IN (".$oreon->user->access->getTopologyString().") AND topology_show = '1' ORDER BY topology_order, topology_group");
	
	$acl_topos 	= array();
	$acl_topos2 = array();
	$a = 0;
	while ($topo1 =& $DBRESULT1->fetchRow())	{
		$acl_topos2[$a] = array();
		$acl_topos2[$a]["name"] = _($topo1["topology_name"]);
		$acl_topos2[$a]["id"] = $topo1["topology_id"];
		$acl_topos2[$a]["checked"] = isset($acl["acl_topos"][$topo1["topology_id"]]) ? "true" : "false";
		$acl_topos2[$a]["c_id"] = $a;
		$acl_topos2[$a]["childs"] = array();

		$acl_topos[] =  &HTML_QuickForm::createElement('checkbox', $topo1["topology_id"], null, _($topo1["topology_name"]), array("style"=>"margin-top: 5px;", "id"=>$topo1["topology_id"]));

		$b = 0;
	 	$DBRESULT2 =& $pearDB->query("SELECT topology_id, topology_page, topology_name, topology_parent FROM topology WHERE topology_parent = '".$topo1["topology_page"]."' AND topology_page IN (".$oreon->user->access->getTopologyString().") AND topology_show = '1' ORDER BY topology_order");
		while ($topo2 =& $DBRESULT2->fetchRow())	{
			$acl_topos2[$a]["childs"][$b] = array();
			$acl_topos2[$a]["childs"][$b]["name"] = _($topo2["topology_name"]);
			$acl_topos2[$a]["childs"][$b]["id"] = $topo2["topology_id"];
			$acl_topos2[$a]["childs"][$b]["checked"] = isset($acl["acl_topos"][$topo2["topology_id"]]) ? "true" : "false";
			$acl_topos2[$a]["childs"][$b]["c_id"] = $a."_".$b;
			$acl_topos2[$a]["childs"][$b]["childs"] = array();
			
		 	$acl_topos[] =  &HTML_QuickForm::createElement('checkbox', $topo2["topology_id"], NULL, _($topo2["topology_name"])."<br />", array("style"=>"margin-top: 5px; margin-left: 20px;"));
			$c = 0;
		 	$DBRESULT3 =& $pearDB->query("SELECT topology_id, topology_name, topology_parent, topology_page, topology_group FROM topology WHERE topology_parent = '".$topo2["topology_page"]."' AND topology_page IN (".$oreon->user->access->getTopologyString().") AND topology_show = '1' ORDER BY topology_group, topology_order");
			while ($topo3 =& $DBRESULT3->fetchRow()){
				$acl_topos2[$a]["childs"][$b]["childs"][$c] = array();
				$acl_topos2[$a]["childs"][$b]["childs"][$c]["name"] = _($topo3["topology_name"]);
				
				if (isset($groups[$topo3["topology_group"]]) && isset($groups[$topo3["topology_group"]][$topo3["topology_parent"]]))
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["group"] = $groups[$topo3["topology_group"]][$topo3["topology_parent"]];
				else
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["group"] = _("Main Menu");
					
				$acl_topos2[$a]["childs"][$b]["childs"][$c]["id"] = $topo3["topology_id"];
				$acl_topos2[$a]["childs"][$b]["childs"][$c]["checked"] = isset($acl["acl_topos"][$topo3["topology_id"]]) ? "true" : "false";
				$acl_topos2[$a]["childs"][$b]["childs"][$c]["c_id"] = $a."_".$b."_".$c;
				$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"] = array();

			 	$acl_topos[] =  &HTML_QuickForm::createElement('checkbox', $topo3["topology_id"], null, _($topo3["topology_name"])."<br />", array("style"=>"margin-top: 5px; margin-left: 40px;"));
				$d = 0;
			 	$DBRESULT4 =& $pearDB->query("SELECT topology_id, topology_name, topology_parent FROM topology WHERE topology_parent = '".$topo3["topology_page"]."' AND topology_page IN (".$oreon->user->access->getTopologyString().") AND topology_show = '1' ORDER BY topology_order");
				while ($topo4 =& $DBRESULT4->fetchRow()){
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d] = array();
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["name"] = _($topo4["topology_name"]);
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["id"] = $topo4["topology_id"];
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["checked"] = isset($acl["acl_topos"][$topo4["topology_id"]]) ? "true" : "false";
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["c_id"] = $a."_".$b."_".$c."_".$d;
					$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["childs"] = array();

					/*old*/
				 	$acl_topos[] =  &HTML_QuickForm::createElement('checkbox', $topo4["topology_id"], null, _("Name"), array("style"=>"margin-top: 5px; margin-left: 55px;"));
					/*old*/					
					$d++;
				}
				$c++;		
			}
			$b++;
		}
		$a++;
	}
	/*
	if ($o == "a")	{
		function one($v)	{
			$v->setValue(1);
			return $v;
		}
		$acl_topos = array_map("one", $acl);
	}
	*/
	$form->addGroup($acl_topos, 'acl_topos', _("Visible page"), '&nbsp;&nbsp;');
	$form->addElement('hidden', 'acl_topo_id');
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	 
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('lca_name', _("Required"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('lca_name', _("Already exists"), 'exist');
	$form->setRequiredNote(_("Required field"));

	/*
	 * Smarty template Init
	 */ 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Just watch a LCA information
	 */

	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&acl_id=".$acl_id."'"));
	    $form->setDefaults($acl);
		$form->freeze();
	} else if ($o == "c"){ # Modify a LCA information
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	    $form->setDefaults($acl);
	} else if ($o == "a"){	# Add a LCA information
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&lca_id=".$acl_id, "changeT"=>_("Modify")));

	$tpl->assign("lca_topos2", $acl_topos2);
	$tpl->assign("sort1", _("General Information"));
	$tpl->assign("sort2", _("Resources"));
	$tpl->assign("sort3", _("Topology"));

	$valid = false;
	if ($form->validate())	{
		$aclObj =& $form->getElement('acl_topo_id');
		if ($form->getSubmitValue("submitA"))
			$aclObj->setValue(insertLCAInDB());
		else if ($form->getSubmitValue("submitC"))
			updateLCAInDB($aclObj->getValue());
		require_once("listsMenusAccess.php");
	} else {
		$action = $form->getSubmitValue("action");
		if ($valid && $action["action"]["action"])
			require_once("listsMenusAccess.php");
		else	{
			#Apply a template definition
			$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
			$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
			$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
			$form->accept($renderer);
			$tpl->assign('form', $renderer->toArray());
			$tpl->assign('o', $o);
			$tpl->assign('acl_topos2', $acl_topos2);
			$tpl->display("formMenusAccess.ihtml");
		}
	}
?>