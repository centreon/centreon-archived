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

	if (!isset($oreon))
		exit();

	#
	## Database retrieve information for ServiceGroup
	#

	$sg = array();
	if (($o == "c" || $o == "w") && $sg_id)	{
		$DBRESULT =& $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$sg_id."' LIMIT 1");

		# Set base value
		$sg = array_map("myDecode", $DBRESULT->fetchRow());

		# Set ServiceGroup Childs
		$DBRESULT =& $pearDB->query("SELECT host_host_id, service_service_id FROM servicegroup_relation WHERE servicegroup_sg_id = '".$sg_id."' AND host_host_id IS NOT NULL ORDER BY service_service_id");
		for ($i = 0; $host =& $DBRESULT->fetchRow(); $i++)
			$sg["sg_hServices"][$i] = $host["host_host_id"]."-".$host["service_service_id"];

		$DBRESULT =& $pearDB->query("SELECT hostgroup_hg_id, service_service_id FROM servicegroup_relation WHERE servicegroup_sg_id = '".$sg_id."' AND hostgroup_hg_id IS NOT NULL GROUP BY service_service_id");
		for ($i = 0; $services =& $DBRESULT->fetchRow(); $i++)
			$sg["sg_hgServices"][$i] = $services["hostgroup_hg_id"]."-".$services["service_service_id"];
		$DBRESULT->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Services comes from DB -> Store in $hServices Array and $hgServices
	$hServices = array();
	$hgServices = array();
	$initName = NULL;

	$DBRESULT =& $pearDB->query("SELECT host_name, host_id FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host =& $DBRESULT->fetchRow())	{
		$services = getMyHostServices($host["host_id"]);
		foreach ($services as $key => $s)
			$hServices[$host["host_id"]."-".$key] = $host["host_name"]."&nbsp;-&nbsp;".$s;
		unset($services);
	}
	$DBRESULT->free();

	$DBRESULT =& $pearDB->query(	"SELECT DISTINCT hg.hg_name, hg.hg_id, sv.service_description, sv.service_template_model_stm_id, sv.service_id " .
									"FROM host_service_relation hsr, service sv, hostgroup hg " .
									"WHERE sv.service_register = '1' " .
									"AND hsr.service_service_id = sv.service_id " .
									"AND hg.hg_id = hsr.hostgroup_hg_id " .
									"ORDER BY hg.hg_name, sv.service_description");
	while ($elem =& $DBRESULT->fetchRow())	{
		# If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		if (!$elem["service_description"])
			$elem["service_description"] = getMyServiceName($elem['service_template_model_stm_id']);

		$elem["service_description"] = str_replace("#S#", "/", $elem["service_description"]);
		$elem["service_description"] = str_replace("#BS#", "\\", $elem["service_description"]);

		$hgServices[$elem["hg_id"] . '-'.$elem["service_id"]] = $elem["hg_name"]."&nbsp;&nbsp;&nbsp;&nbsp;".$elem["service_description"];
	}
	$DBRESULT->free();

	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 400px; height: 250px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Service Group"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Service Group"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Service Group"));

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'sg_name', _("Service Group Name"), $attrsText);
	$form->addElement('text', 'sg_alias', _("Description"), $attrsText);

	##
	## Services Selection
	##
	$form->addElement('header', 'relation', _("Relations"));
	$ams1 =& $form->addElement('advmultiselect', 'sg_hServices', array(_("Linked Host Services"), _("Available"), _("Selected")), $hServices, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$form->addElement('header', 'relation', _("Relations"));
	$ams1 =& $form->addElement('advmultiselect', 'sg_hgServices', array(_("Linked Host Group Services"), _("Available"), _("Selected")), $hgServices, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$sgActivation[] = &HTML_QuickForm::createElement('radio', 'sg_activate', null, _("Enabled"), '1');
	$sgActivation[] = &HTML_QuickForm::createElement('radio', 'sg_activate', null, _("Disabled"), '0');
	$form->addGroup($sgActivation, 'sg_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('sg_activate' => '1'));
	$form->addElement('textarea', 'sg_comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));

	$form->addElement('hidden', 'sg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["sg_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('sg_name', 'myReplace');
	$form->addRule('sg_name', _("Compulsory Name"), 'required');
	$form->addRule('sg_alias', _("Compulsory Description"), 'required');
	$form->registerRule('exist', 'callback', 'testServiceGroupExistence');
	$form->addRule('sg_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a Service Group information
	if ($o == "w")	{
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sg_id."'"));
	    $form->setDefaults($sg);
		$form->freeze();
	}
	# Modify a Service Group information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($sg);
	}
	# Add a Service Group information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$tpl->assign('nagios', $oreon->user->get_version());
	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );

	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	$valid = false;
	if ($form->validate())	{
		$sgObj =& $form->getElement('sg_id');
		if ($form->getSubmitValue("submitA"))
			$sgObj->setValue(insertServiceGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceGroupInDB($sgObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");

	if ($valid && $action["action"]["action"])
		require_once($path."listServiceGroup.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formServiceGroup.ihtml");
	}
?>
