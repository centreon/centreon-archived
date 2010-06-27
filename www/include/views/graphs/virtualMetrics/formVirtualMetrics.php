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
 *
 * formVirtualMetrics.php david PORTE $
 *
 */

	if (!isset($oreon))
		exit;

	function checkServiceSet(){
		global $form;
		$gsvs = NULL;
		if (isset($form))
			$gsvs = $form->getSubmitValues();
		if ( $gsvs["index_id"] == NULL )
			return false;
		else
			return true;
	}

	#
	## Database retrieve information
	#
	$vmetric = array();
	if (($o == "c" || $o == "w") && $vmetric_id)	{
		$p_qy =& $pearDB->query("SELECT *, hidden vhidden FROM virtual_metrics WHERE vmetric_id = '".$vmetric_id."' LIMIT 1");
		# Set base value
		$vmetric = array_map("myDecode", $p_qy->fetchRow());
		$p_qy->free();
		$hs_data = array();
		$p_qy =& $pearDBO->query("SELECT host_id, service_id FROM index_data WHERE id='".$vmetric["index_id"]."' LIMIT 1;");
		$hs_data = $p_qy->fetchRow();
		$vmetric["host_id"] = $hs_data["host_id"];
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	#	
	# Existing Data Index List comes from DBO -> Store in $indds Array
	#
	$indds = array(""=>"Host list&nbsp;&nbsp;&nbsp;");
	$mx_l = strlen($indds[""]);

	$dbindd =& $pearDBO->query("SELECT DISTINCT host_id, host_name FROM index_data;");
	if (PEAR::isError($dbindd))
		print "DB Error : ".$dbindd->getDebugInfo()."<br />";
	while($indd = $dbindd->fetchRow()) {
		$indds[$indd["host_id"]] = $indd["host_name"]."&nbsp;&nbsp;&nbsp;";
		$hn_l = strlen($indd["host_name"]);
		if ( $hn_l > $mx_l)
			$mx_l = $hn_l;
	}
	$dbindd->free();
	for ($i = strlen($indds[""]); $i != $mx_l; $i++)
		$indds[""] .= "&nbsp;";
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	
	$attrsText 	= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"4", "cols"=>"60");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'ftitle', _("Add a Virtual Metric"));
	else if ($o == "c")
		$form->addElement('header', 'ftitle', _("Modify a Virtual Metric"));
	else if ($o == "w")
		$form->addElement('header', 'ftitle', _("View a Virtual Metric"));

	# Basic information
	## Header
	$form->addElement('header', 'information', _("General Information"));
        $form->addElement('header', 'function', _("RPN Function"));
	$form->addElement('header', 'options', _("Options"));
	## General Information
	$form->addElement('text', 'vmetric_name', _("Metric Name"), $attrsText);
	#$form->addElement('text', 'hs_relation', _("Host / Service Data Source"), $attrsText);
	$form->addElement('static', 'hsr_text',_("Choose a host, then its associated service."));
    $form->addElement('select', 'host_id', _("Host / Service Data Source"), $indds, "onChange=update_select_list(0,this.value);update_select_list(1,0);");

	$form->addElement('select', 'def_type', _("DEF Type"), array(0=>"CDEF&nbsp;&nbsp;&nbsp;",1=>"VDEF&nbsp;&nbsp;&nbsp;"), "onChange=manageVDEF();");
	## RPN Function
	$form->addElement('textarea', 'rpn_function', _("RPN (Reverse Polish Notation) Function"), $attrsTextarea);
	$form->addElement('static', 'rpn_text',_("<br><i><b><font color=\"#B22222\">Notes </font>:</b></i><br>&nbsp;&nbsp;&nbsp;- Do not mix metrics of different sources.<br>&nbsp;&nbsp;&nbsp;- Only aggregation functions work in VDEF rpn expressions."));
	#$form->addElement('select', 'real_metrics', null, $rmetrics);
	$form->addElement('text', 'unit_name', _("Metric Unit"), $attrsText2);
	## Options
	$form->addElement('checkbox', 'vhidden', _("Hidden Graph And Legend"), "", "onChange=manageVDEF();");
	$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	
	$form->addElement('hidden', 'vmetric_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	

	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('vmetric_name', _("Compulsory Name"), 'required');
	#$form->addRule('hs_relation', _("Required Field"), 'required');
	$form->addRule('rpn_function', _("Required Field"), 'required');
	$form->addRule('host_id', _("Required Fields 'Host' and 'Service' list"), 'required');


	$form->registerRule('existName', 'callback', 'NameTestExistence');
    $form->registerRule('checkService', 'callback', 'checkServiceSet');
	$form->addRule('vmetric_name', _("Name already in use for this Host/Service"), 'existName');
	$form->addRule('host_id', _("Required Field 'Service' list"), 'checkService');
	
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&vmetric_id=".$vmetric_id."'"));
		$form->setDefaults($vmetric);
		$form->freeze();
	}
	# Modify
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"), array("onClick"=>"javascript:resetLists(".$vmetric["host_id"].",".$vmetric["index_id"].");"));
	   	$form->setDefaults($vmetric);
	}
	# Add
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"), array("onClick"=>"javascript:resetLists(0,0)"));
	}

	if ($o == "c" || $o == "a") {
?>
	<script type='text/javascript'>
		function insertValueQuery() {
			var e_txtarea = document.Form.rpn_function;
			var e_select = document.getElementById('sl_list_metrics');
			var sd_o = e_select.selectedIndex;
			if (e_select.options[sd_o].value != "null") {
				var chaineAj = '';
				chaineAj = e_select.options[sd_o].text;
				//chaineAj = chaineAj.substring(0, chaineAj.length - 3);
				chaineAj = chaineAj.replace(/\s(\[[CV]DEF\]|)\s*$/,"");

				if (document.selection) {
					// IE support
					e_txtarea.focus();
					sel = document.selection.createRange();
					sel.text = chaineAj;
					document.Form.insert.focus();
				} else if (e_txtarea.selectionStart || e_txtarea.selectionStart == '0') {
					// MOZILLA/NETSCAPE support
					var pos_s = e_txtarea.selectionStart;
					var pos_e = e_txtarea.selectionEnd;
					var str_rpn = e_txtarea.value;
					e_txtarea.value = str_rpn.substring(0, pos_s) + chaineAj + str_rpn.substring(pos_e, str_rpn.length);
				} else {
					e_txtarea.value += chaineAj;
				}
			}
		}

		function manageVDEF() {
			var e_checkbox = document.Form.vhidden;
			var vdef_state = document.Form.def_type.value;
			if ( vdef_state == 1) {
				e_checkbox.checked = true;
			} 
		}
	</script><?php
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&vmetric_id=".$vmetric_id, "changeT"=>_("Modify")));

	$tpl->assign("sort1", _("Properties"));
	$tpl->assign("sort2", _("Graphs"));

	$valid = false;
	if ($form->validate())	{
		$vmetricObj =& $form->getElement('vmetric_id');
		if ($form->getSubmitValue("submitA"))
			$vmetricObj->setValue(insertVirtualMetricInDB());
		else if ($form->getSubmitValue("submitC"))
			updateVirtualMetricInDB($vmetricObj->getValue());
		$o = "w";
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&vmetric_id=".$vmetricObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listVirtualMetrics.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formVirtualMetrics.ihtml");
	}
	$vdef=1; /* Display VDEF too */
	include_once("./include/views/graphs/common/makeJS_formMetricsList.php");
?><script type="text/javascript">
<?php
		$test_valid = true;
		if ($o == "c" || $o == "w") {
			isset($_POST["host_id"]) && $_POST["host_id"] != NULL ? $ph_id=$_POST["host_id"]: $ph_id=$vmetric["host_id"];
			isset($_POST["index_id"]) && $_POST["index_id"] != NULL ? $ix_id=$_POST["index_id"]: $ix_id=$vmetric["index_id"];
		} else if ($o == "a") {
			isset($_POST["host_id"]) && $_POST["host_id"] != NULL ? $ph_id=$_POST["host_id"]: $ph_id=0;
			isset($_POST["index_id"]) && $_POST["index_id"] != NULL ? $ix_id=$_POST["index_id"]: $ix_id=0;
		}
?>	
	update_select_list(0,'<?php echo $ph_id;?>','<?php echo $ix_id;?>');
	update_select_list(1,'<?php echo $ix_id;?>');
</script>
