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

if (!isset($centreon)) {
  exit();
}

/*
 * Database retrieve information for LCA
 */
if ($o == "c" || $o == "w") {
  $DBRESULT = $pearDB->query("SELECT * FROM acl_topology WHERE acl_topo_id = '".$acl_id."' LIMIT 1");

  // Set base value
  $acl = array_map("myDecode", $DBRESULT->fetchRow());

  // Set Topology relations
  $DBRESULT = $pearDB->query("SELECT topology_topology_id, access_right FROM acl_topology_relations WHERE acl_topo_id = '".$acl_id."'");
  for ($i = 0; $topo = $DBRESULT->fetchRow(); $i++)
    $acl["acl_topos"][$topo["topology_topology_id"]] = $topo["access_right"];
  $DBRESULT->free();

  // Set Contact Groups relations
  $DBRESULT = $pearDB->query("SELECT DISTINCT acl_group_id FROM acl_group_topology_relations WHERE acl_topology_id = '".$acl_id."'");
  for($i = 0; $groups = $DBRESULT->fetchRow(); $i++)
    $acl["acl_groups"][$i] = $groups["acl_group_id"];
  $DBRESULT->free();
}

$groups = array();
$DBRESULT = $pearDB->query("SELECT acl_group_id, acl_group_name FROM acl_groups ORDER BY acl_group_name");
while ($group = $DBRESULT->fetchRow()) {
  $groups[$group["acl_group_id"]] = $group["acl_group_name"];
}
$DBRESULT->free();

if (!isset($acl["acl_topos"])) {
  $acl["acl_topos"] = array();
}

/*
 * Var information to format the element
 */

$attrsText      = array("size"=>"30");
$attrsAdvSelect = array("style" => "width: 300px; height: 180px;");
$attrsTextarea 	= array("rows"=>"5", "cols"=>"80");
$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

/*
 * Form begin
 */

$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
  $form->addElement('header', 'title', _("Add an ACL"));
} else if ($o == "c") {
  $form->addElement('header', 'title', _("Modify an ACL"));
} else if ($o == "w") {
  $form->addElement('header', 'title', _("View an ACL"));
}

/*
 * LCA basic information
 */
$form->addElement('header', 'information', _("General Information"));
$form->addElement('text',	'acl_topo_name', _("ACL Definition"), $attrsText);
$form->addElement('text', 	'acl_topo_alias', _("Alias"), $attrsText);

$ams1 = $form->addElement('advmultiselect', 'acl_groups', array(_("Linked Groups"), _("Available"), _("Selected")), $groups, $attrsAdvSelect, SORT_ASC);
$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
$ams1->setElementTemplate($eTemplate);
echo $ams1->getElementJs(false);

$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'acl_topo_activate', null, _("Enabled"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'acl_topo_activate', null, _("Disabled"), '0');
$form->addGroup($tab, 'acl_topo_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('acl_topo_activate' => '1'));

/*
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$form->addElement('textarea', 'acl_comments', _("Comments"), $attrsTextarea);

/*
 * Create buffer group list for Foorth level.
 */

$DBRESULT = $pearDB->query("SELECT topology_group, topology_name, topology_parent FROM `topology` WHERE topology_page IS NULL ORDER BY topology_group, topology_page");
while ($group = $DBRESULT->fetchRow()) {
  if (!isset($groupMenus[$group["topology_group"]])) {
    $groupMenus[$group["topology_group"]] = array();
  }
  $groupMenus[$group["topology_group"]][$group["topology_parent"]] = $group["topology_name"];
}
$DBRESULT->free();
unset($group);

/*
 * Topology concerned
 */
$form->addElement('header', 'pages', _("Accessible Pages"));
$DBRESULT1 = $pearDB->query("SELECT topology_id, topology_page, topology_name, topology_parent, readonly FROM topology WHERE topology_parent IS NULL ORDER BY topology_order, topology_group");

$acl_topos  = array();
$acl_topos2 = array();

$a = 0;
while ($topo1 = $DBRESULT1->fetchRow())	{
  $acl_topos2[$a] = array();
  $acl_topos2[$a]["name"] = _($topo1["topology_name"]);
  $acl_topos2[$a]["id"] = $topo1["topology_id"];
  $acl_topos2[$a]["access"] = isset($acl["acl_topos"][$topo1["topology_id"]]) ? $acl["acl_topos"][$topo1["topology_id"]] : 0;
  $acl_topos2[$a]["c_id"] = $a;
  $acl_topos2[$a]['readonly'] = $topo1['readonly'];
  $acl_topos2[$a]["childs"] = array();

  $acl_topos[] = HTML_QuickForm::createElement('checkbox', $topo1["topology_id"], null, _($topo1["topology_name"]), array("style"=>"margin-top: 5px;", "id"=>$topo1["topology_id"]));

  $b = 0;
  $DBRESULT2 = $pearDB->query("SELECT topology_id, topology_page, topology_name, topology_parent, readonly FROM topology WHERE topology_parent = '".$topo1["topology_page"]."' ORDER BY topology_order");
  while ($topo2 = $DBRESULT2->fetchRow()) {
    $acl_topos2[$a]["childs"][$b] = array();
    $acl_topos2[$a]["childs"][$b]["name"] = _($topo2["topology_name"]);
    $acl_topos2[$a]["childs"][$b]["id"] = $topo2["topology_id"];
    $acl_topos2[$a]["childs"][$b]["access"] = isset($acl["acl_topos"][$topo2["topology_id"]]) ? $acl["acl_topos"][$topo2["topology_id"]] : 0;
    $acl_topos2[$a]["childs"][$b]["c_id"] = $a."_".$b;
    $acl_topos2[$a]["childs"][$b]['readonly'] = $topo2['readonly'];
    $acl_topos2[$a]["childs"][$b]["childs"] = array();

    $acl_topos[] = HTML_QuickForm::createElement('checkbox', $topo2["topology_id"], NULL, _($topo2["topology_name"])."<br />", array("style"=>"margin-top: 5px; margin-left: 20px;"));

    $c = 0;
    $DBRESULT3 = $pearDB->query("SELECT topology_id, topology_name, topology_parent, topology_page, topology_group, readonly FROM topology WHERE topology_parent = '".$topo2["topology_page"]."' AND topology_page IS NOT NULL ORDER BY topology_group, topology_order");
    while ($topo3 = $DBRESULT3->fetchRow()){
      $acl_topos2[$a]["childs"][$b]["childs"][$c] = array();
      $acl_topos2[$a]["childs"][$b]["childs"][$c]["name"] = _($topo3["topology_name"]);

      if (isset($groupMenus[$topo3["topology_group"]]) && isset($groupMenus[$topo3["topology_group"]][$topo3["topology_parent"]])) {
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["group"] = $groupMenus[$topo3["topology_group"]][$topo3["topology_parent"]];
      } else {
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["group"] = _("Main Menu");
      }

      $acl_topos2[$a]["childs"][$b]["childs"][$c]["id"] = $topo3["topology_id"];
      $acl_topos2[$a]["childs"][$b]["childs"][$c]["access"] = isset($acl["acl_topos"][$topo3["topology_id"]]) ? $acl["acl_topos"][$topo3["topology_id"]] : 0;
      $acl_topos2[$a]["childs"][$b]["childs"][$c]["c_id"] = $a."_".$b."_".$c;
      $acl_topos2[$a]["childs"][$b]["childs"][$c]['readonly'] = $topo3['readonly'];
      $acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"] = array();
      

      $acl_topos[] = HTML_QuickForm::createElement('checkbox', $topo3["topology_id"], null, _($topo3["topology_name"])."<br />", array("style"=>"margin-top: 5px; margin-left: 40px;"));

      $d = 0;
      $DBRESULT4 = $pearDB->query("SELECT topology_id, topology_name, topology_parent, readonly FROM topology WHERE topology_parent = '".$topo3["topology_page"]."' AND topology_page IS NOT NULL ORDER BY topology_order");
      while ($topo4 = $DBRESULT4->fetchRow()) {
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d] = array();
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["name"] = _($topo4["topology_name"]);
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["id"] = $topo4["topology_id"];
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["access"] = isset($acl["acl_topos"][$topo4["topology_id"]]) ? $acl["acl_topos"][$topo4["topology_id"]] : 0;
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["c_id"] = $a."_".$b."_".$c."_".$d;
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]['readonly'] = $topo4['readonly'];
	$acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"][$d]["childs"] = array();

	/* old */
	//$acl_topos[] = HTML_QuickForm::createElement('checkbox', $topo4["topology_id"], null, _("Name"), array("style"=>"margin-top: 5px; margin-left: 55px;"));
	/* old */
	$d++;
      }
      $acl_topos2[$a]["childs"][$b]["childs"][$c]["childNumber"] = count($acl_topos2[$a]["childs"][$b]["childs"][$c]["childs"]);
      $c++;
    }
    $acl_topos2[$a]["childs"][$b]["childNumber"] = count($acl_topos2[$a]["childs"][$b]["childs"]);
    $b++;
  }
  $acl_topos2[$a]["childNumber"] = count($acl_topos2[$a]["childs"]);
  $a++;
}

$form->addGroup($acl_topos, 'acl_topos', _("Visible page"), '&nbsp;&nbsp;');
$form->addElement('hidden', 'acl_topo_id');

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */

$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('acl_topo_name', _("Required"), 'required');
$form->registerRule('exist', 'callback', 'testExistence');
if ($o == "a") {
  $form->addRule('acl_topo_name', _("Already exists"), 'exist');
}
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
  $subC = $form->addElement('submit', 'submitC', _("Save"));
  $res = $form->addElement('reset', 'reset', _("Delete"));
  $form->setDefaults($acl);
} else if ($o == "a"){	# Add a LCA information
  $subA = $form->addElement('submit', 'submitA', _("Save"));
  $res = $form->addElement('reset', 'reset', _("Delete"));
}
$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&lca_id=".$acl_id, "changeT"=>_("Modify")));

$tpl->assign("lca_topos2", $acl_topos2);
$tpl->assign("sort1", _("General Information"));
$tpl->assign("sort2", _("Resources"));
$tpl->assign("sort3", _("Topology"));

$tpl->assign("label_none", _("No access"));
$tpl->assign("label_readwrite", _("Read/Write"));
$tpl->assign("label_readonly", _("Read Only"));

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
  $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate())	{
  $aclObj = $form->getElement('acl_topo_id');
  if ($form->getSubmitValue("submitA"))
    $aclObj->setValue(insertLCAInDB());
  else if ($form->getSubmitValue("submitC"))
    updateLCAInDB($aclObj->getValue());
  require_once("listsMenusAccess.php");
} else {
  $action = $form->getSubmitValue("action");
  if ($valid && $action["action"]) {
    require_once("listsMenusAccess.php");
  } else {
    // Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
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